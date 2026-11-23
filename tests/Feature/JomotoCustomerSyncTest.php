<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JomotoCustomerSyncService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JomotoCustomerSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.jomoto_main', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        Schema::connection('jomoto_main')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('role');
            $table->timestamps();
        });
    }

    public function test_sync_imports_website_buyers_as_bengkel_customers(): void
    {
        $hash = Hash::make('website-password');
        DB::connection('jomoto_main')->table('users')->insert([
            'name' => 'Budi Santoso',
            'email' => 'budi@jomotocenter.com',
            'password' => $hash,
            'phone' => '081234567890',
            'address' => 'Kapasari 73 Surabaya',
            'role' => 'buyer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = app(JomotoCustomerSyncService::class)->sync();

        $this->assertSame(['created' => 1, 'updated' => 0, 'skipped' => 0], $result);

        $customer = User::where('email', 'budi@jomotocenter.com')->firstOrFail();
        $this->assertSame('customer', $customer->role);
        $this->assertSame('Budi Santoso', $customer->name);
        $this->assertSame('081234567890', $customer->phone);
        $this->assertSame('Kapasari 73 Surabaya', $customer->address);
        $this->assertSame($hash, $customer->password);
        $this->assertTrue(Hash::check('website-password', $customer->password));
        $this->assertNotNull($customer->ecommerce_user_id);
    }

    public function test_sync_is_idempotent_and_updates_changed_profile_data(): void
    {
        DB::connection('jomoto_main')->table('users')->insert([
            'name' => 'Siti Aminah',
            'email' => 'siti@jomotocenter.com',
            'password' => Hash::make('website-password'),
            'phone' => '081111111111',
            'role' => 'buyer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(JomotoCustomerSyncService::class)->sync();

        DB::connection('jomoto_main')->table('users')->where('email', 'siti@jomotocenter.com')->update([
            'name' => 'Siti Aminah Baru',
            'phone' => '082222222222',
        ]);

        $result = app(JomotoCustomerSyncService::class)->sync();

        $this->assertSame(['created' => 0, 'updated' => 1, 'skipped' => 0], $result);
        $this->assertSame(1, User::where('role', 'customer')->count());

        $customer = User::where('email', 'siti@jomotocenter.com')->firstOrFail();
        $this->assertSame('Siti Aminah Baru', $customer->name);
        $this->assertSame('082222222222', $customer->phone);
    }

    public function test_sync_never_takes_over_non_customer_accounts(): void
    {
        $admin = User::factory()->create(['email' => 'admin@bengkel.test', 'role' => 'admin']);

        DB::connection('jomoto_main')->table('users')->insert([
            'name' => 'Admin Baru',
            'email' => $admin->email,
            'password' => Hash::make('website-password'),
            'role' => 'buyer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = app(JomotoCustomerSyncService::class)->sync();

        $this->assertSame(1, $result['skipped']);
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_sa_can_trigger_sync_from_customers_page(): void
    {
        DB::connection('jomoto_main')->table('users')->insert([
            'name' => 'Pelanggan Website',
            'email' => 'pelanggan@jomotocenter.com',
            'password' => Hash::make('website-password'),
            'phone' => '089999999999',
            'role' => 'buyer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sa = User::factory()->create(['role' => 'sa']);

        $this->actingAs($sa)
            ->from(route('sa.customers.index'))
            ->post(route('sa.customers.syncJomoto'))
            ->assertSessionHas('success')
            ->assertRedirect(route('sa.customers.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'pelanggan@jomotocenter.com',
            'role' => 'customer',
        ]);
    }
}
