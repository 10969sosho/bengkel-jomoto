<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ServiceAdviserWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sa_processes_customer_booking_into_assigned_work_order(): void
    {
        $sa = $this->user('Rina', 'sa');
        $technician = $this->user('Budi Santoso', 'teknisi');
        $customer = $this->user('Hendra', 'customer');
        $vehicle = $this->vehicle($customer);
        $booking = ServiceOrder::create([
            'wo_number' => 'WO-BOOK-0001', 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'customer_complaint' => 'Booking keluhan rem', 'service_type' => 'regular', 'location_type' => 'onsite',
            'odometer' => 1200, 'status' => 'draft',
        ]);

        $this->withSession(['demo_user_id' => $sa->id])->get(route('sa.index'))
            ->assertOk()->assertSee('Booking Service Masuk')->assertSee('Booking keluhan rem');
        $this->withSession(['demo_user_id' => $sa->id])->get(route('sa.create', ['booking_id' => $booking->id]))
            ->assertOk()->assertSee('Booking keluhan rem')->assertSee('value="1200"', false);

        $response = $this->withSession(['demo_user_id' => $sa->id])->post(route('sa.store'), [
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'technician_id' => $technician->id,
            'location_type' => 'onsite',
            'odometer' => 1250,
            'customer_complaint' => 'Booking keluhan rem',
        ]);

        $response->assertRedirectToRoute('sa.show', $booking);
        $this->assertDatabaseHas('service_orders', [
            'id' => $booking->id,
            'sa_id' => $sa->id,
            'technician_id' => $technician->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'odometer' => 1250,
        ]);
        $this->assertSame(1, ServiceOrder::count());
        $this->assertSame(1250, $vehicle->fresh()->last_odometer);

        $this->withSession(['demo_user_id' => $sa->id])->post(route('sa.store'), [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'technician_id' => $technician->id,
            'location_type' => 'onsite',
            'odometer' => 1250,
            'customer_complaint' => 'Walk-in customer',
        ])->assertRedirect();
        $this->assertDatabaseHas('service_orders', ['customer_complaint' => 'Walk-in customer', 'technician_id' => $technician->id]);
    }

    public function test_sa_can_create_customer_add_a_vehicle_and_update_its_details(): void
    {
        $sa = $this->user('Rina', 'sa');

        $this->withSession(['demo_user_id' => $sa->id])->post(route('sa.customers.store'), [
            'name' => 'Citra Customer', 'phone' => '0812345678', 'whatsapp' => '62812345678', 'email' => 'citra@example.test',
        ])->assertRedirect();
        $customer = User::where('email', 'citra@example.test')->firstOrFail();
        $this->assertSame('customer', $customer->role);
        $this->assertModelExists($customer);

        $this->withSession(['demo_user_id' => $sa->id])->post(route('sa.vehicles.store', $customer), [
            'brand' => 'Honda', 'model' => 'Beat', 'plate_number' => 'B 1234 CD', 'year' => 2024,
            'engine_number' => 'ENG-1', 'chassis_number' => 'CHS-1', 'fuel_type' => 'gasoline', 'last_odometer' => 800,
        ])->assertRedirect();
        $vehicle = $customer->vehicles()->firstOrFail();

        $this->withSession(['demo_user_id' => $sa->id])->put(route('sa.vehicles.update', $vehicle), [
            'brand' => 'Honda', 'model' => 'Beat Deluxe', 'plate_number' => 'B 1234 CD', 'year' => 2025,
            'engine_number' => 'ENG-2', 'chassis_number' => 'CHS-2', 'fuel_type' => 'gasoline', 'last_odometer' => 950,
        ])->assertRedirect();

        $this->withSession(['demo_user_id' => $sa->id])->get(route('sa.customers.show', $customer))
            ->assertOk()->assertSee('Citra Customer')->assertSee('Beat Deluxe')->assertSee('Riwayat Servis');
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'model' => 'Beat Deluxe', 'last_odometer' => 950]);
    }

    public function test_technician_dashboard_only_shows_orders_assigned_to_current_technician(): void
    {
        $budi = $this->user('Budi Santoso', 'teknisi');
        $agus = $this->user('Agus Pratama', 'teknisi');
        $customer = $this->user('Customer', 'customer');
        $vehicle = $this->vehicle($customer);
        $budiOrder = $this->order($customer, $vehicle, $budi, 'WO-BUDI-0001');
        $agusOrder = $this->order($customer, $vehicle, $agus, 'WO-AGUS-0001');

        $this->withSession(['demo_user_id' => $budi->id])->get(route('teknisi.index'))
            ->assertOk()->assertSee('WO-BUDI-0001')->assertDontSee('WO-AGUS-0001');
        $this->withSession(['demo_user_id' => $agus->id])->get(route('teknisi.index'))
            ->assertOk()->assertSee('WO-AGUS-0001')->assertDontSee('WO-BUDI-0001');
        $this->withSession(['demo_user_id' => $agus->id])->get(route('teknisi.inspect', $budiOrder))->assertForbidden();
        $this->withSession(['demo_user_id' => $budi->id])->get(route('sa.customers.index'))->assertRedirectToRoute('dashboard');
        $this->post(route('demo.switch'), ['role' => 'teknisi', 'technician' => 'Agus Pratama'])
            ->assertSessionHas('demo_user_id', $agus->id);
    }

    public function test_cashier_lists_invoices_and_records_method_details_after_payment(): void
    {
        $sa = $this->user('Rina', 'sa');
        $invoice = $this->invoice();

        $this->withSession(['demo_user_id' => $sa->id])->get(route('sa.payments.index'))
            ->assertOk()->assertSee($invoice->invoice_number)->assertSee('QRIS')->assertSee('Uang kembalian')->assertSee('Transfer Bank');

        $this->withSession(['demo_user_id' => $sa->id])->post(route('sa.payments.pay', $invoice), [
            'payment_method' => 'cash', 'cash_received' => 1000,
        ])->assertSessionHasErrors('cash_received');
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'payment_status' => 'unpaid']);

        $this->withSession(['demo_user_id' => $sa->id])->post(route('sa.payments.pay', $invoice), [
            'payment_method' => 'transfer',
            'bank_account' => 'BCA · 1234567890 a.n. Bengkel Jomoto',
            'payment_reference' => 'TRX-001',
        ])->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'payment_status' => 'paid',
            'payment_method' => 'transfer',
            'bank_account' => 'BCA · 1234567890 a.n. Bengkel Jomoto',
            'payment_reference' => 'TRX-001',
        ]);
    }

    #[DataProvider('paymentMethods')]
    public function test_cashier_accepts_each_payment_method(string $method, array $paymentData): void
    {
        $sa = $this->user('Rina', 'sa');
        $invoice = $this->invoice();

        $this->withSession(['demo_user_id' => $sa->id])->post(route('sa.payments.pay', $invoice), [
            'payment_method' => $method,
            ...$paymentData,
        ])->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'payment_status' => 'paid',
            'payment_method' => $method,
        ]);
    }

    public function test_demo_seeder_creates_booking_technician_assignments_and_unpaid_invoice(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $budi = User::where('name', 'Budi Santoso')->firstOrFail();
        $agus = User::where('name', 'Agus Pratama')->firstOrFail();
        $this->assertDatabaseHas('service_orders', ['technician_id' => $budi->id]);
        $this->assertDatabaseHas('service_orders', ['technician_id' => $agus->id]);
        $this->assertDatabaseHas('service_orders', ['status' => 'draft', 'sa_id' => null]);
        $this->assertDatabaseHas('invoices', ['payment_status' => 'unpaid']);
    }

    public static function paymentMethods(): array
    {
        return [
            'QRIS' => ['qris', ['payment_reference' => 'QRIS-INV-001']],
            'tunai' => ['cash', ['cash_received' => 2000]],
            'transfer bank' => ['transfer', [
                'bank_account' => 'BCA · 1234567890 a.n. Bengkel Jomoto',
                'payment_reference' => 'TRX-002',
            ]],
        ];
    }

    private function user(string $name, string $role): User
    {
        return User::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'password' => 'password',
            'role' => $role,
        ]);
    }

    private function vehicle(User $customer): Vehicle
    {
        return $customer->vehicles()->create([
            'brand' => 'Honda', 'model' => 'Beat', 'plate_number' => 'L 1234 AB', 'year' => 2024,
            'fuel_type' => 'gasoline', 'last_odometer' => 1000,
        ]);
    }

    private function order(User $customer, Vehicle $vehicle, User $technician, string $number): ServiceOrder
    {
        return ServiceOrder::create([
            'wo_number' => $number, 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'technician_id' => $technician->id, 'customer_complaint' => 'Servis', 'service_type' => 'regular',
            'location_type' => 'onsite', 'status' => 'approved',
        ]);
    }

    private function invoice(): Invoice
    {
        $customer = $this->user('Billing Customer', 'customer');
        $vehicle = $this->vehicle($customer);
        $technician = $this->user('Billing Technician', 'teknisi');
        $order = ServiceOrder::create([
            'wo_number' => 'WO-INVOICE-0001', 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'technician_id' => $technician->id, 'customer_complaint' => 'Servis', 'service_type' => 'regular',
            'location_type' => 'onsite', 'status' => 'invoiced',
        ]);
        $order->items()->create([
            'item_type' => 'service', 'item_name' => 'Servis Ringan', 'quantity' => 1,
            'unit_price' => 1000, 'subtotal' => 1000, 'is_free' => false,
        ]);

        return Invoice::createFromOrder($order);
    }
}
