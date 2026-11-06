<?php

namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicianTimerPauseTest extends TestCase
{
    use RefreshDatabase;

    public function test_pausing_timer_requires_a_reason_and_records_it_as_work_history(): void
    {
        $technician = $this->user('Budi Santoso', 'teknisi');
        $sa = $this->user('Rina Wulandari', 'sa');
        $order = $this->order($technician);

        $this->withSession(['demo_user_id' => $technician->id])
            ->post(route('teknisi.timer', $order), ['action' => 'start'])
            ->assertRedirect();
        $this->assertDatabaseHas('service_orders', ['id' => $order->id, 'status' => 'working']);

        $this->withSession(['demo_user_id' => $technician->id])
            ->post(route('teknisi.timer', $order), ['action' => 'pause'])
            ->assertSessionHasErrors('pause_reason');
        $this->assertNull($order->workLogs()->first()->stopped_at, 'Timer tetap berjalan saat alasan jeda kosong.');

        $reason = 'Sparepart tidak tersedia sehingga perlu dilakukan pembelian terlebih dahulu.';
        $this->withSession(['demo_user_id' => $technician->id])
            ->post(route('teknisi.timer', $order), ['action' => 'pause', 'pause_reason' => $reason])
            ->assertSessionHas('success');

        $log = $order->workLogs()->first();
        $this->assertNotNull($log->stopped_at);
        $this->assertSame($reason, $log->pause_reason);
        $this->assertDatabaseHas('service_orders', ['id' => $order->id, 'status' => 'working']);

        $this->withSession(['demo_user_id' => $technician->id])
            ->get(route('teknisi.index'))
            ->assertOk()
            ->assertSee('Riwayat Jeda Timer')
            ->assertSee($reason);

        $this->withSession(['demo_user_id' => $sa->id])
            ->get(route('sa.show', $order))
            ->assertOk()
            ->assertSee('Riwayat Waktu Pengerjaan')
            ->assertSee('Jeda: '.$reason);
    }

    public function test_technician_can_resume_a_paused_timer(): void
    {
        $technician = $this->user('Agus Pratama', 'teknisi');
        $order = $this->order($technician);

        $this->withSession(['demo_user_id' => $technician->id])
            ->post(route('teknisi.timer', $order), [
                'action' => 'pause',
                'pause_reason' => 'Menunggu konfirmasi dari konsumen.',
            ])
            ->assertSessionHas('error', 'Timer sedang tidak berjalan, tidak ada yang bisa dijeda.');

        $this->withSession(['demo_user_id' => $technician->id])
            ->post(route('teknisi.timer', $order), ['action' => 'start'])
            ->assertSessionHas('success', 'Timer pengerjaan dimulai.');

        $this->withSession(['demo_user_id' => $technician->id])
            ->post(route('teknisi.timer', $order), ['action' => 'pause', 'pause_reason' => 'Menunggu konfirmasi dari konsumen.'])
            ->assertSessionHas('success', 'Timer dijeda. Alasan jeda dicatat dalam riwayat pengerjaan.');

        $this->withSession(['demo_user_id' => $technician->id])
            ->post(route('teknisi.timer', $order), ['action' => 'start'])
            ->assertSessionHas('success', 'Timer dilanjutkan kembali.');

        $this->assertSame(2, $order->workLogs()->count());
        $this->assertNull($order->workLogs()->latest('id')->first()->stopped_at);
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

    private function order(User $technician): ServiceOrder
    {
        $customer = $this->user('Hendra Wijaya', 'customer');
        $vehicle = $customer->vehicles()->create([
            'brand' => 'Honda', 'model' => 'Beat', 'plate_number' => 'L 1234 AB', 'year' => 2024,
            'fuel_type' => 'gasoline', 'last_odometer' => 1000,
        ]);

        return ServiceOrder::create([
            'wo_number' => 'WO-2611-0011', 'customer_id' => $customer->id, 'vehicle_id' => $vehicle->id,
            'technician_id' => $technician->id, 'customer_complaint' => 'Rem depan bunyi',
            'service_type' => 'regular', 'location_type' => 'onsite', 'status' => 'approved',
        ]);
    }
}
