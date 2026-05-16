@extends('auth.layout')

@section('title', 'Buat Kata Sandi Baru')

@section('card-title', 'Kata Sandi Baru')
@section('card-subtitle', 'Buat kata sandi baru yang kuat untuk akun Anda.')

@section('form')
<form action="{{ route('password.store') }}" method="POST" id="resetForm" novalidate>
    @csrf

    {{-- Hidden token --}}
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    {{-- Email (readonly) --}}
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
            value="{{ old('email', $request->email) }}"
            class="input auth-input auth-input--readonly @error('email') input-error @enderror"
            readonly
        >
        @error('email')
            <span class="auth-field__error">{{ $message }}</span>
        @enderror
    </div>

    {{-- New Password --}}
    <div class="auth-field">
        <label class="auth-label" for="password">
            <svg xmlns="http://www.w3.org/2000/svg" class="auth-label__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Kata Sandi Baru
        </label>
        <div class="auth-input-wrap">
            <input
                id="password"
                type="password"
                name="password"
                class="input auth-input @error('password') input-error @enderror"
                placeholder="Minimal 8 karakter"
                autocomplete="new-password"
                autofocus
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
        <div class="pw-strength" id="pwStrength">
            <div class="pw-strength__bar">
                <div class="pw-strength__fill" id="pwFill"></div>
            </div>
            <span class="pw-strength__label" id="pwLabel"></span>
        </div>
        @error('password')
            <span class="auth-field__error">{{ $message }}</span>
        @enderror
    </div>

    {{-- Confirm --}}
    <div class="auth-field">
        <label class="auth-label" for="password_confirmation">
            <svg xmlns="http://www.w3.org/2000/svg" class="auth-label__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Konfirmasi Kata Sandi
        </label>
        <div class="auth-input-wrap">
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="input auth-input"
                placeholder="Ulangi kata sandi baru"
                autocomplete="new-password"
                required
            >
            <button type="button" class="auth-eye-toggle" data-target="password_confirmation" aria-label="Tampilkan konfirmasi">
                <svg class="eye-icon eye-icon--show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg class="eye-icon eye-icon--hide hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </button>
        </div>
        <span class="auth-field__error hidden" id="confirmError">Kata sandi tidak cocok.</span>
    </div>

    <button type="submit" class="btn auth-btn-primary w-full mt-6" id="resetBtn">
        <span class="btn-text">Simpan Kata Sandi Baru</span>
        <span class="loading loading-spinner loading-sm hidden" id="resetSpinner"></span>
    </button>
</form>
@endsection
