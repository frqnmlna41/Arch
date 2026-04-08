<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

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

<body class="bg-gray-200 text-gray-800">

    <div class="drawer lg:drawer-open">
        <input id="admin-drawer" type="checkbox" class="drawer-toggle" />

        <!-- SIDEBAR WRAPPER -->
        <div class="drawer-side z-50">
            <label for="admin-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
            <!-- SIDEBAR -->
            <aside class="w-64 bg-white flex flex-col min-h-screen">

            <!-- LOGO -->
            <div class="h-16 flex items-center px-6">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center text-white">
                        <i class="fas fa-trophy text-sm"></i>
                    </div>
                    Arch CMS
                </h2>
            </div>

            <!-- MENU -->
            <nav class="flex-1 overflow-y-auto py-4 text-sm">

                <div class="px-4 space-y-1">

                    <x-sidebar-link route="dashboard.perguruan" icon="tachometer-alt">
                        Dashboard
                    </x-sidebar-link>

                    <p class="mt-6 mb-2 text-xs text-gray-400 uppercase">Management</p>

                    <x-sidebar-link route="#" icon="school">
                        Perguruan
                    </x-sidebar-link>

                    <x-sidebar-link route="#" icon="user-friends">
                        Atlet
                    </x-sidebar-link>

                    <p class="mt-6 mb-2 text-xs text-gray-400 uppercase">Event</p>

                    <x-sidebar-link route="#" icon="calendar">
                        Event
                    </x-sidebar-link>

                    <x-sidebar-link route="#" icon="fist-raised">
                        Pertandingan
                    </x-sidebar-link>

                    <p class="mt-6 mb-2 text-xs text-gray-400 uppercase">Hasil</p>

                    <x-sidebar-link route="#" icon="medal">
                        Pemenang
                    </x-sidebar-link>

                    <x-sidebar-link route="#" icon="certificate">
                        Sertifikat
                    </x-sidebar-link>

                </div>

                <!-- LOGOUT -->
                <div class="px-4 mt-6">
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button
                            class="w-full flex items-center gap-2 px-4 py-2 text-red-500 hover:bg-red-50 rounded-lg transition">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </form>
                </div>

            </nav>
            </aside>
        </div>

        <!-- MAIN -->
        <div class="drawer-content flex flex-col min-h-screen">

            <!-- TOPBAR -->
            <header class="h-16 bg-white px-6 flex items-center justify-between">

                <!-- LEFT -->
                <div class="flex items-center gap-4">
                    <!-- Mobile Menu Button -->
                    <label for="admin-drawer" class="lg:hidden text-gray-500 hover:text-orange-500 cursor-pointer">
                        <i class="fas fa-bars text-xl"></i>
                    </label>

                    <h1 class="text-lg font-semibold">
                        @yield('title', 'Dashboard')
                    </h1>

                    <!-- SEARCH -->
                    <div class="hidden md:flex items-center bg-gray-100 px-3 py-1 rounded-lg">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                        <input type="text" placeholder="Search..."
                            class="bg-transparent outline-none px-2 text-sm w-40">
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="flex items-center gap-4">

                    <!-- NOTIF -->
                    <button class="relative text-gray-500 hover:text-orange-500">
                        <i class="fas fa-bell"></i>
                        <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <!-- USER -->
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium">
                                {{ auth()->user()->name ?? 'Guest' }}
                            </p>
                            <p class="text-xs text-gray-400">Admin</p>
                        </div>

                        <div
                            class="w-9 h-9 bg-orange-500 text-white flex items-center justify-center rounded-full font-semibold">
                            {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}
                        </div>
                    </div>

                </div>

            </header>

            <!-- CONTENT -->
            <main class="p-6 space-y-6">

                @include('components._alerts')

                <!-- CONTENT WRAPPER -->
                <div class="bg-base-100 rounded-xl shadow-sm p-6">
                    @yield('content')
                </div>

            </main>

        </div>

    </div>

    @stack('scripts')
</body>

</html>
