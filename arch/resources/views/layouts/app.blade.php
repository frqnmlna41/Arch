<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Arch Tournament CMS'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>

<body class="bg-base-200 text-base-content">
    @yield('content')
    
    @stack('scripts')
</body>

<footer class="bg-base-200 flex flex-col lg:flex-row items-center justify-center p-6 mt-10 gap-4 text-center">
    <p class="text-sm text-base-content">
        © 2026 {{ config('title', 'Arch Tournament') }}. All rights reserved.
    </p>

    <p class="hidden lg:block text-sm text-base-content/60 mx-2">|</p>

    <div class="flex flex-wrap justify-center gap-4 lg:gap-6">
        <a href="#" class="text-sm text-base-content/60 hover:text-warning">Privacy Policy</a>
        <a href="#" class="text-sm text-base-content/60 hover:text-warning">Cookie Policy</a>
        <a href="#" class="text-sm text-base-content/60 hover:text-warning">Cookie Settings</a>
        <a href="#" class="text-sm text-base-content/60 hover:text-warning">License</a>
        <a href="#" class="text-sm text-base-content/60 hover:text-warning">Contact</a>
    </div>
</footer>

</html>
