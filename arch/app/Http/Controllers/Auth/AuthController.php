<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Perguruan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


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
            $user->hasRole('coach')   => redirect()->route('dashboard.perguruan'),
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

    public function registerPerguruan(Request $request)
    {
        // 1. Validate the incoming request
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => 'required|string|min:8|confirmed',
            'perguruan_name'        => 'required|string|max:255',
            'address'               => 'nullable|string',
            'phone'                 => 'nullable|string|max:20',
            'logo'                  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Handle the logo upload if it exists
        $logoPath = null;
        if ($request->hasFile('logo')) {
            // This will store the image in storage/app/public/logos
            // Make sure you have run `php artisan storage:link`
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        // 3. Save to the database using a transaction
        // (This ensures both User and Perguruan are created safely, or neither are)
        DB::beginTransaction();
        try {
            // Create the User account
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Optional: If you are using spatie/laravel-permission for roles
            // $user->assignRole('perguruan');

            // Create the linked Perguruan profile (Adjust this to match your actual DB structure)
            
            $perguruan = Perguruan::create([
                'user_id' => $user->id,
                'name'    => $validated['perguruan_name'],
                'slug'    => Str::slug($validated['perguruan_name']),
                'address' => $validated['address'],
                'phone'   => $validated['phone'],
                'logo'    => $logoPath,
            ]);
            

            DB::commit();

            // Redirect to login with a success message
            return redirect('/login')->with('success', 'Pendaftaran perguruan berhasil! Silakan login.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Redirect back with error if something fails
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

}
