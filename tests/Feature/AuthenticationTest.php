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

    public function test_authenticated_admin_visiting_login_page_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-guest-test@example.com',
            'phone' => '081211113333',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($admin)->get('/login');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_authenticated_customer_visiting_login_page_is_redirected_to_customer_dashboard(): void
    {
        $customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer-guest-test@example.com',
            'phone' => '081211114444',
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($customer)->get('/login');

        $response->assertRedirect(route('customer.dashboard'));
    }

    public function test_authenticated_user_accessing_dashboard_fallback_route_redirects_based_on_role(): void
    {
        $admin = User::create([
            'name' => 'Admin Route Test',
            'email' => 'admin-route-test@example.com',
            'phone' => '081211115555',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertRedirect(route('admin.dashboard'));
    }
}
