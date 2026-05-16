@extends('auth.layout')

@section('title', 'Verifikasi Email')

@section('card-title', 'Cek Email Anda')
@section('card-subtitle', 'Kami telah mengirimkan link verifikasi ke email Anda.')

@section('form')
<div class="auth-verify-icon" aria-hidden="true">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
    </svg>
</div>

<p class="auth-verify-text">
    Sebelum melanjutkan, periksa email Anda dan klik tautan verifikasi yang telah dikirimkan.
    Jika tidak menerima email, klik tombol di bawah untuk mengirim ulang.
</p>

@if (session('status') == 'verification-link-sent')
<div role="alert" class="alert alert-success auth-alert mb-4">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span class="text-sm">Link verifikasi baru telah dikirimkan ke email Anda.</span>
</div>
@endif

<form method="POST" action="{{ route('verification.send') }}" class="mt-4">
    @csrf
    <button type="submit" class="btn auth-btn-primary w-full">
        Kirim Ulang Email Verifikasi
    </button>
</form>

<form method="POST" action="{{ route('logout') }}" class="mt-3">
    @csrf
    <button type="submit" class="btn auth-btn-ghost w-full">
        Keluar
    </button>
</form>
@endsection
