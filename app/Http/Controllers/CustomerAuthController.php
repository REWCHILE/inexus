<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerAuthController extends Controller
{
    /**
     * Show customer login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->is_admin) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('customer.account');
        }

        return view('pages.auth.login');
    }

    /**
     * Handle login submission
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->is_admin) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', '¡Bienvenido Administrador! Sesión iniciada con éxito.');
            }

            return redirect()->intended(route('customer.account'))
                ->with('success', '¡Bienvenido nuevamente, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Las credenciales ingresadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Show customer registration form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('customer.account');
        }

        return view('pages.auth.register');
    }

    /**
     * Handle customer registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:30',
            'account_type' => 'required|in:personal,company',
            'rut' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'company_rut' => 'nullable|string|max:20',
            'company_giro' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:100',
            'shipping_region' => 'nullable|string|max:100',
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya se encuentra registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'account_type' => $validated['account_type'],
            'rut' => $validated['rut'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'company_rut' => $validated['company_rut'] ?? null,
            'company_giro' => $validated['company_giro'] ?? null,
            'shipping_address' => $validated['shipping_address'] ?? null,
            'shipping_city' => $validated['shipping_city'] ?? null,
            'shipping_region' => $validated['shipping_region'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_admin' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.account')
            ->with('success', '¡Cuenta creada con éxito! Bienvenido a INEXUS.');
    }

    /**
     * Customer Account Dashboard & Orders
     */
    public function account()
    {
        $user = Auth::user();
        $orders = Order::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('customer_email', $user->email);
        })->latest()->take(20)->get();

        return view('pages.auth.account', compact('user', 'orders'));
    }

    /**
     * Update customer profile data
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'rut' => 'nullable|string|max:20',
            'account_type' => 'required|in:personal,company',
            'company_name' => 'nullable|string|max:255',
            'company_rut' => 'nullable|string|max:20',
            'company_giro' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:100',
            'shipping_region' => 'nullable|string|max:100',
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->rut = $validated['rut'];
        $user->account_type = $validated['account_type'];
        $user->company_name = $validated['company_name'];
        $user->company_rut = $validated['company_rut'];
        $user->company_giro = $validated['company_giro'];
        $user->shipping_address = $validated['shipping_address'];
        $user->shipping_city = $validated['shipping_city'];
        $user->shipping_region = $validated['shipping_region'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Tus datos de cuenta han sido actualizados correctamente.');
    }

    /**
     * Logout customer
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Has cerrado sesión exitosamente.');
    }
}
