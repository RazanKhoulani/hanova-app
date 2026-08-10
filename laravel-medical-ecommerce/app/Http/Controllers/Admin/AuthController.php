<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (
            Auth::check()
            && (
                Auth::user()->hasRole('admin')
                || Auth::user()->hasRole('doctor')
                || Auth::user()->hasRole('delivery')
            )
        ) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Check role after login
            if (
                !Auth::user()->hasRole('admin')
                && !Auth::user()->hasRole('doctor')
                && !Auth::user()->hasRole('delivery')
            ) {
                Auth::logout();
                return back()->withErrors([
                    'phone' => 'You do not have permission to access the dashboard.',
                ])->onlyInput('phone');
            }

            if (Auth::user()->hasRole('delivery')) {
                return redirect()->intended(route('admin.orders.index'));
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'phone' => 'The provided credentials do not match our records.',
        ])->onlyInput('phone');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
