<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Set default business
            $business = $user->businesses()->first();
            if ($business) {
                Session::put('current_business_id', $business->id);
            }

            // Log activity
            activity_log('login', 'User logged in', $user);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|min:8|confirmed',
            'business_name' => 'required|string|max:150',
            'gstin'         => 'nullable|string|size:15',
            'mobile'        => 'required|string|size:10',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'mobile'   => $request->mobile,
            'password' => Hash::make($request->password),
            'role'     => 'admin',
        ]);

        // Create first business
        $business = Business::create([
            'user_id'       => $user->id,
            'name'          => $request->business_name,
            'gstin'         => $request->gstin,
            'financial_year'=> now()->month >= 4
                                ? now()->year . '-' . (now()->year + 1)
                                : (now()->year - 1) . '-' . now()->year,
        ]);

        Auth::login($user);
        Session::put('current_business_id', $business->id);

        return redirect()->route('dashboard')->with('success', 'Welcome! Your account is ready.');
    }

    public function logout(Request $request)
    {
        activity_log('logout', 'User logged out', Auth::user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function switchBusiness(Request $request)
    {
        $request->validate(['business_id' => 'required|exists:businesses,id']);

        $business = Auth::user()->businesses()->findOrFail($request->business_id);
        Session::put('current_business_id', $business->id);

        return redirect()->back()->with('success', "Switched to {$business->name}");
    }

    public function profile()
    {
        return view('auth.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name'   => 'required|string|max:100',
            'mobile' => 'nullable|string|max:15',
        ]);

        $user->update($request->only('name', 'mobile'));

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Profile updated.');
    }
}

// Helper function for activity log
if (!function_exists('activity_log')) {
    function activity_log(string $action, string $description, $user = null): void
    {
        try {
            \DB::table('activity_logs')->insert([
                'user_id'     => $user?->id ?? Auth::id(),
                'business_id' => currentBusinessId(),
                'action'      => $action,
                'description' => $description,
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail — don't break app on log failure
        }
    }
}
