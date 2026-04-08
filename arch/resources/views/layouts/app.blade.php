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

<footer class="bg-base-200 text-center p-4 mt-10">
    <p class="text-sm text-gray-500">©2026 {{ config('title', 'Arch Tournament') }}. All rights reserved.</p>
</footer>

</html>
