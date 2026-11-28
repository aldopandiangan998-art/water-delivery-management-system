<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard_with_admin_theme(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Bisnis');
        $response->assertSee('Profil Akun');
        $response->assertDontSee('Buka Web Publik');
    }

    public function test_admin_can_view_and_update_profile(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Lama',
            'phone' => '08111111111',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $viewResponse = $this->actingAs($admin)->get(route('admin.profile'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Pengaturan Profil Akun');
        $viewResponse->assertSee($admin->name);

        $updateResponse = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Admin Baru Sukses',
            'phone' => '08999999999',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $updateResponse->assertSessionHas('success');

        $admin->refresh();
        $this->assertEquals('Admin Baru Sukses', $admin->name);
        $this->assertEquals('08999999999', $admin->phone);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $admin->password));
    }
}
