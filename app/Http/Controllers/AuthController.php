<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Already logged in? Go to the right start page for this role.
        if (auth()->check()) {
            return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'claims.create');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! auth()->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(auth()->user()->isAdmin() ? route('admin.dashboard') : route('claims.create'));
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
