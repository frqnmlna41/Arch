<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Arch Tournament CMS')</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md">

        <!-- Logo / Title -->
        <div class="text-center mb-6">
            <i class="fas fa-medal text-4xl text-yellow-500 mb-3"></i>
            <h1 class="text-2xl font-bold text-gray-800">
                Arch Tournament CMS
            </h1>
            <p class="text-sm text-gray-500">
                Sistem Manajemen Kejuaraan
            </p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl p-6">

            @include('partials._alerts')

            @yield('content')

        </div>

        <!-- Footer -->
        <p class="text-center text-sm text-gray-400 mt-6">
            © {{ date('Y') }} Arch System
        </p>

    </div>

</body>

</html>
