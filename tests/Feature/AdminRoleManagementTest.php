<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_user_management(): void
    {
        $technician = User::factory()->create(['role' => 'teknisi']);

        $response = $this->actingAs($technician)->get(route('admin.users'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_user_management_page_and_filter_by_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Super Admin']);
        $sa = User::factory()->create(['role' => 'sa', 'name' => 'SA Budi']);
        $tech = User::factory()->create(['role' => 'teknisi', 'name' => 'Teknisi Joko']);

        $response = $this->actingAs($admin)->get(route('admin.users'));
        $response->assertStatus(200);
        $response->assertSee('Kelola User & Role');
        $response->assertSee('SA Budi');
        $response->assertSee('Teknisi Joko');

        // Test filter role
        $filterResponse = $this->actingAs($admin)->get(route('admin.users', ['role' => 'sa']));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('SA Budi');
        $filterResponse->assertDontSee('Teknisi Joko');
    }

    public function test_admin_can_create_new_user_with_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $postData = [
            'name' => 'Staff Baru SA',
            'email' => 'sa.baru@jomotocenter.com',
            'phone' => '081234567890',
            'role' => 'sa',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'address' => 'Jl. Kapasari No. 73',
        ];

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $postData);
        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Staff Baru SA',
            'email' => 'sa.baru@jomotocenter.com',
            'role' => 'sa',
        ]);
    }

    public function test_admin_can_update_user_role_and_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'role' => 'teknisi',
            'name' => 'Teknisi Rama',
            'email' => 'rama@jomotocenter.com',
        ]);

        $updateData = [
            'name' => 'Rama Promoted SA',
            'email' => 'rama@jomotocenter.com',
            'phone' => '08222333444',
            'role' => 'sa',
            'address' => 'Surabaya',
        ];

        $response = $this->actingAs($admin)->put(route('admin.users.update', $user), $updateData);
        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('sa', $user->role);
        $this->assertEquals('Rama Promoted SA', $user->name);
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@jomotocenter.com']);

        $updateData = [
            'name' => 'Admin Boss',
            'email' => 'admin@jomotocenter.com',
            'role' => 'teknisi', // mencoba turun role
        ];

        $response = $this->actingAs($admin)
            ->from(route('admin.users'))
            ->put(route('admin.users.update', $admin), $updateData);
        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHasErrors(['role']);

        $admin->refresh();
        $this->assertEquals('admin', $admin->role);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($admin)
            ->from(route('admin.users'))
            ->delete(route('admin.users.destroy', $user));
        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->from(route('admin.users'))
            ->delete(route('admin.users.destroy', $admin));
        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHasErrors(['error']);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
