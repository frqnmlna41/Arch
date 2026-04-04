<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah'
            ])->withInput();
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // 🔒 VALIDASI STATUS
        if ($user->status !== 'active') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Akun belum disetujui admin'
            ]);
        }

        // 🎯 ROLE BASED REDIRECT
        return $this->redirectByRole($user);
    }

    protected function redirectByRole($user)
    {
        return match (true) {
            $user->hasRole('admin')   => redirect()->route('admin.dashboard'),
            $user->hasRole('coach')   => redirect()->route('coach.dashboard'),
            $user->hasRole('athlete') => redirect()->route('athlete.dashboard'),
            default                   => redirect('/dashboard'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
