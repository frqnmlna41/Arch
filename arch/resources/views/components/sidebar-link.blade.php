@props(['route', 'icon'])

@php
    $isPattern = str_contains($route, '*');

    $active = request()->routeIs($route);

    $resolvedRoute = $isPattern ? str_replace('.*', '.index', $route) : $route;

    $href = Route::has($resolvedRoute) ? route($resolvedRoute) : '#';
@endphp

<a href="{{ $href }}"
    class="flex items-center gap-3 px-6 py-3 transition
   {{ $active
       ? 'bg-orange-50 text-orange-600 border-r-4 border-orange-500'
       : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">

    <i class="fas fa-{{ $icon }}"></i>
    <span>{{ $slot }}</span>
</a>
