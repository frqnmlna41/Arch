<!DOCTYPE html>
<html lang="id" data-theme="sport">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Olahraga') — SPORA</title>

    {{-- DaisyUI + Tailwind CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Google Fonts: Bebas Neue (display) + DM Sans (body) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap"
        rel="stylesheet">

    {{-- Custom Auth CSS --}}
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

    @stack('head')
</head>

<body class="auth-body">

    {{-- Animated background --}}
    <div class="auth-bg" aria-hidden="true">
        <div class="auth-bg__orb auth-bg__orb--1"></div>
        <div class="auth-bg__orb auth-bg__orb--2"></div>
        <div class="auth-bg__orb auth-bg__orb--3"></div>
        <div class="auth-bg__grid"></div>
    </div>

    {{-- Floating sport icons --}}
    <div class="auth-floaters" aria-hidden="true">
        <span class="floater" style="--d:0s;--x:10%;--y:15%">⚽</span>
        <span class="floater" style="--d:1.2s;--x:85%;--y:20%">🥋</span>
        <span class="floater" style="--d:0.6s;--x:70%;--y:75%">🏅</span>
        <span class="floater" style="--d:2s;--x:20%;--y:80%">🏆</span>
        <span class="floater" style="--d:1.8s;--x:50%;--y:10%">🎯</span>
        <span class="floater" style="--d:0.3s;--x:92%;--y:55%">⚡</span>
    </div>

    {{-- Main content --}}
    <main class="auth-main">

        {{-- Brand mark --}}
        <div class="auth-brand">
            <div class="auth-brand__logo">
                <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="18" stroke="currentColor" stroke-width="2.5" />
                    <path d="M12 20 L20 12 L28 20 L20 28 Z" fill="currentColor" opacity="0.9" />
                    <circle cx="20" cy="20" r="4" fill="var(--accent)" />
                </svg>
            </div>
            <span class="auth-brand__name">SPORA</span>
        </div>

        {{-- Card --}}
        <div class="auth-card">
            <div class="auth-card__inner">

                {{-- Header slot --}}
                <div class="auth-card__header">
                    <h1 class="auth-card__title">@yield('card-title')</h1>
                    <p class="auth-card__subtitle">@yield('card-subtitle')</p>
                </div>

                {{-- Alert: validation errors --}}
                @if ($errors->any())
                    <div role="alert" class="alert alert-error auth-alert mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.539-1.333-3.308 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Alert: session status --}}
                @if (session('status'))
                    <div role="alert" class="alert alert-success auth-alert mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm">{{ session('status') }}</span>
                    </div>
                @endif

                {{-- Form content --}}
                @yield('form')

            </div>
        </div>

        {{-- Footer --}}
        <p class="auth-footer">
            &copy; {{ date('Y') }} SPORA — Portal Pendaftaran Event Olahraga
        </p>
    </main>

    {{-- JS --}}
    <script src="{{ asset('js/auth.js') }}"></script>
    @stack('scripts')
</body>

</html>
