@extends('auth.layout')

@section('title', 'Lupa Kata Sandi')

@section('card-title', 'Reset Kata Sandi')
@section('card-subtitle', 'Masukkan email Anda dan kami akan mengirimkan tautan reset.')

@section('form')
<form action="{{ route('password.email') }}" method="POST" id="forgotForm" novalidate>
    @csrf

    <div class="auth-info-box mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm">
            Masukkan alamat email yang terdaftar. Link reset akan aktif selama <strong>60 menit</strong>.
        </p>
    </div>

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

    <button type="submit" class="btn auth-btn-primary w-full mt-6" id="forgotBtn">
        <span class="btn-text">Kirim Link Reset</span>
        <span class="loading loading-spinner loading-sm hidden" id="forgotSpinner"></span>
    </button>

    <div class="auth-divider"><span>atau</span></div>

    <p class="auth-switch">
        Ingat kata sandi?
        <a href="{{ route('login') }}" class="auth-link auth-link--bold">← Kembali masuk</a>
    </p>
</form>
@endsection
