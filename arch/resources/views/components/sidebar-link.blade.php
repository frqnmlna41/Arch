@props(['route', 'icon'])

@php
    $active = request()->routeIs($route);
@endphp

<a href="{{ $route === '#' ? '#' : ($route === 'admin.athletes.*' ? '/athletes' : (Route::has(str_replace('.*', '.index', $route)) ? route(str_replace('.*', '.index', $route)) : '#')) }}"
    class="flex items-center gap-3 px-6 py-3 transition
   {{ $active ? 'bg-orange-50 text-orange-600 border-r-4 border-orange-500' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">

    <i class="fas fa-{{ $icon }}"></i>
    <span>{{ $slot }}</span>
</a>
