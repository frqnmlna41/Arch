@extends('auth.layout')

@section('title', 'Masuk')

@section('card-title', 'Selamat Datang')
@section('card-subtitle', 'Masuk ke akun SPORA Anda untuk melanjutkan.')

@section('form')
<form action="{{ route('login') }}" method="POST" id="loginForm" novalidate>
    @csrf

    {{-- Email --}}
    <div class="auth-field">
        <label class="auth-label" for="email">
            <svg xmlns="http://www.w3.org/2000/svg" class="auth-label__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
            </svg>
            Alamat Email
        </label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            class="input auth-input @error('email') input-error @enderror"
            placeholder="nama@email.com"
            autocomplete="email"
            autofocus
            required
        >
        @error('email')
            <span class="auth-field__error">{{ $message }}</span>
        @enderror
    </div>

    {{-- Password --}}
    <div class="auth-field">
        <label class="auth-label" for="password">
            <svg xmlns="http://www.w3.org/2000/svg" class="auth-label__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Kata Sandi
        </label>
        <div class="auth-input-wrap">
            <input
                id="password"
                type="password"
                name="password"
                class="input auth-input @error('password') input-error @enderror"
                placeholder="••••••••"
                autocomplete="current-password"
                required
            >
            <button type="button" class="auth-eye-toggle" data-target="password" aria-label="Tampilkan kata sandi">
                <svg class="eye-icon eye-icon--show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg class="eye-icon eye-icon--hide hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </button>
        </div>
        @error('password')
            <span class="auth-field__error">{{ $message }}</span>
        @enderror
    </div>

    {{-- Remember + Forgot --}}
    <div class="auth-row">
        <label class="auth-check cursor-pointer">
            <input type="checkbox" name="remember" class="checkbox checkbox-sm auth-checkbox" {{ old('remember') ? 'checked' : '' }}>
            <span class="auth-check__label">Ingat saya</span>
        </label>
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="auth-link">Lupa kata sandi?</a>
        @endif
    </div>

    {{-- Submit --}}
    <button type="submit" class="btn auth-btn-primary w-full mt-6" id="loginBtn">
        <span class="btn-text">Masuk Sekarang</span>
        <span class="loading loading-spinner loading-sm hidden" id="loginSpinner"></span>
    </button>

    {{-- Divider --}}
    <div class="auth-divider">
        <span>atau</span>
    </div>

    {{-- Register link --}}
    @if (Route::has('register'))
    <p class="auth-switch">
        Belum punya akun?
        <a href="{{ route('register') }}" class="auth-link auth-link--bold">Daftar gratis →</a>
    </p>
    @endif
</form>
@endsection
