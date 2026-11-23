<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Customer',
            'email' => 'john@example.com',
            'phone' => '081299998888',
            'customer_type' => 'household',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('customer', $user->role);
        $this->assertNotNull($user->profile);
        $this->assertEquals('household', $user->profile->customer_type);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Budi Customer',
            'email' => 'budi@example.com',
            'phone' => '081233334444',
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'budi@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_redirected_to_admin_dashboard_on_login(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '081211112222',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::create([
            'name' => 'Disabled User',
            'email' => 'disabled@example.com',
            'role' => 'customer',
            'is_active' => false,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'disabled@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'Logout User',
            'email' => 'logout@example.com',
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user);
        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
