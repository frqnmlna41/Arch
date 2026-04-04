@extends('layouts.guest')

@section('title', 'Login')

@section('content')

    <h2 class="text-xl font-semibold text-gray-700 mb-6 text-center">
        Login ke Akun
    </h2>

    <form action="{{ route('auth.login.post') }}" method="POST" class="space-y-5">
        {{-- <form action="{{ route('auth.login.post') }}" method="POST" class="space-y-5"> --}}
        @csrf

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
                Email
            </label>
            <input type="email" name="email" value="{{ old('email') }}"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none @error('email') border-red-500 @enderror"
                placeholder="email@example.com" required>

            @error('email')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
                Password
            </label>
            <input type="password" name="password"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none @error('password') border-red-500 @enderror"
                placeholder="••••••••" required>

            @error('password')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember -->
        <div class="flex items-center justify-between">
            <label class="flex items-center text-sm text-gray-600">
                <input type="checkbox" name="remember" class="mr-2">
                Ingat saya
            </label>
        </div>

        <!-- Button -->
        <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold transition">
            Login
        </button>

        <!-- Divider -->
        <div class="flex items-center my-4">
            <div class="flex-grow border"></div>
            <span class="px-3 text-sm text-gray-400">atau</span>
            <div class="flex-grow border"></div>
        </div>

        <!-- Register -->
        <a href="{{ route('auth.register-perguruan') }}"
            class="block text-center w-full border border-blue-600 text-blue-600 py-2 rounded-lg hover:bg-blue-50 transition">
            Daftar Perguruan
        </a>

    </form>

@endsection
