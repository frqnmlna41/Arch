<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        @yield('title') - {{ config('app.name', 'Arch Tournament CMS') }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')
</head>

<body class="bg-gray-100 text-gray-800">

    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-white shadow-lg border-r hidden md:block">

            <!-- LOGO -->
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <i class="fas fa-trophy text-orange-500"></i>
                    <span>Arch CMS</span>
                </h2>
            </div>

            <!-- MENU -->
            <nav class="mt-4 text-sm">

                {{-- DASHBOARD --}}
                <x-sidebar-link route="admin.dashboard" icon="tachometer-alt">
                    Dashboard
                </x-sidebar-link>

                {{-- MASTER --}}
                <p class="px-6 mt-6 mb-2 text-xs text-gray-400 uppercase">Master</p>

                <x-sidebar-link route="admin.sports.*" icon="trophy">
                    Olahraga
                </x-sidebar-link>

                <x-sidebar-link route="admin.disciplines.*" icon="gavel">
                    Disiplin
                </x-sidebar-link>

                <x-sidebar-link route="admin.age-categories.*" icon="users">
                    Kategori Umur
                </x-sidebar-link>

                <x-sidebar-link route="admin.arenas.*" icon="map-marker-alt">
                    Arena
                </x-sidebar-link>

                {{-- EVENT --}}
                <p class="px-6 mt-6 mb-2 text-xs text-gray-400 uppercase">Event</p>

                <x-sidebar-link route="admin.events.*" icon="calendar">
                    Event
                </x-sidebar-link>

                <x-sidebar-link route="admin.matches.*" icon="fist-raised">
                    Pertandingan
                </x-sidebar-link>

                {{-- USER --}}
                <p class="px-6 mt-6 mb-2 text-xs text-gray-400 uppercase">User</p>

                <x-sidebar-link route="admin.perguruan.*" icon="school">
                    Perguruan
                </x-sidebar-link>

                <x-sidebar-link route="admin.athletes.*" icon="user-friends">
                    Atlet
                </x-sidebar-link>

                {{-- RESULT --}}
                <p class="px-6 mt-6 mb-2 text-xs text-gray-400 uppercase">Hasil</p>

                <x-sidebar-link route="admin.winners.*" icon="medal">
                    Pemenang
                </x-sidebar-link>

                <x-sidebar-link route="admin.certificates.*" icon="certificate">
                    Sertifikat
                </x-sidebar-link>

                {{-- LOGOUT --}}
                <div class="mt-10 px-4">
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button
                            class="w-full flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </form>
                </div>

            </nav>
        </aside>

        <!-- MAIN -->
        <div class="flex-1 flex flex-col">

            <!-- TOPBAR -->
            <header class="bg-white shadow-sm px-6 py-4 flex justify-between items-center">

                <h1 class="font-semibold text-lg">
                    @yield('title', 'Dashboard')
                </h1>

                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">
                        {{ auth()->user()->name ?? 'Guest' }}
                    </span>

                    <div class="w-8 h-8 bg-orange-500 text-white flex items-center justify-center rounded-full">
                        {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}
                    </div>
                </div>

            </header>

            <!-- CONTENT -->
            <main class="p-6">
                @include('components._alerts')
                @yield('content')
            </main>

        </div>

    </div>

    @stack('scripts')
</body>

</html>
