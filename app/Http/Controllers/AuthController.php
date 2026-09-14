<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * BL-010. DR-001: both fields required. DR-002/CQ-002: exact
     * username+password match, hashed comparison. CQ-019: username
     * uniqueness is a schema constraint, not re-checked here (the legacy's
     * "exactly one row" ambiguous-match guard is unreachable once the
     * column is UNIQUE). CQ-020: the DB lookup's collation is
     * case-insensitive, so the matched user's username is re-checked
     * against the submitted one with an exact-case comparison, mirroring
     * the legacy's own `===` recheck. One generic rejection message
     * (FR-5) regardless of which of these fails.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ], [
            'username.required' => 'Username is required',
            'password.required' => 'Password is required',
        ]);

        $user = User::where('username', $validated['username'])->first();

        if (! $user || $user->username !== $validated['username'] || ! Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['username' => 'Incorrect username or password.'])->onlyInput('username');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
