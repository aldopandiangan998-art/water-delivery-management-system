<?php

namespace App\Http\Controllers;

use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda telah dinonaktifkan oleh administrator.']);
            }

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'customer_type' => ['nullable', 'string', 'in:household,retail_shop,office,restaurant'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make($validated['password']),
        ]);

        CustomerProfile::create([
            'user_id' => $user->id,
            'customer_type' => $validated['customer_type'] ?? 'household',
            'gallon_deposit_balance' => 0,
        ]);

        Auth::login($user);

        return redirect()->route('customer.dashboard')->with('success', 'Pendaftaran berhasil! Selamat datang di AquaFlow.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Anda telah berhasil keluar.');
    }

    protected function redirectBasedOnRole(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'driver') {
            return redirect()->route('driver.dashboard');
        }

        return redirect()->route('customer.dashboard');
    }
}
