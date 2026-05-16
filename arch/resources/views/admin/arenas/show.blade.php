@extends('layouts.admin')
@section('title', 'Detail Arena - ' . $arena->name)

@section('content')

{{-- BACK + HEADER --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.arenas.index') }}"
        class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500 transition">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div class="flex-1">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-gray-800">{{ $arena->name }}</h2>
            @if($arena->is_active)
                <span class="badge badge-success text-xs gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block"></span>
                    Aktif
                </span>
            @else
                <span class="badge badge-ghost text-xs">Non-aktif</span>
            @endif
        </div>
        @if($arena->location)
        <p class="text-sm text-gray-500 mt-0.5">
            <i class="fas fa-map-marker-alt text-orange-400 mr-1"></i>{{ $arena->location }}
        </p>
        @endif
    </div>
    <a href="{{ route('admin.arenas.edit', $arena) }}"
        class="btn btn-sm btn-ghost text-orange-500 hover:bg-orange-50 gap-1">
        <i class="fas fa-edit"></i> Edit
    </a>
</div>

{{-- INFO CARDS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-orange-500 uppercase tracking-wider">Kapasitas</p>
        <p class="text-2xl font-black text-orange-600 mt-1">
            {{ $arena->capacity ? number_format($arena->capacity) : '—' }}
        </p>
        @if($arena->capacity)
        <p class="text-xs text-orange-400">orang</p>
        @endif
    </div>
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-blue-500 uppercase tracking-wider">Total Pertandingan</p>
        <p class="text-2xl font-black text-blue-600 mt-1">{{ $arena->matches_count }}</p>
    </div>
    <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-green-500 uppercase tracking-wider">Selesai</p>
        <p class="text-2xl font-black text-green-600 mt-1">
            {{ $arena->matches()->where('status', 'done')->count() }}
        </p>
    </div>
    <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-yellow-600 uppercase tracking-wider">Terjadwal</p>
        <p class="text-2xl font-black text-yellow-600 mt-1">
            {{ $arena->matches()->whereIn('status', ['scheduled', 'ongoing'])->count() }}
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- LEFT: Detail --}}
    <div class="space-y-4">
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="font-bold text-gray-700 text-sm uppercase tracking-wider mb-4">Detail Arena</p>

            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-map-marker-alt text-orange-500 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Lokasi</p>
                        <p class="text-sm text-gray-700 font-semibold">{{ $arena->location ?? '—' }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-users text-blue-500 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Kapasitas</p>
                        <p class="text-sm text-gray-700 font-semibold">
                            {{ $arena->capacity ? number_format($arena->capacity) . ' orang' : '—' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-info-circle text-gray-500 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Deskripsi</p>
                        <p class="text-sm text-gray-700">{{ $arena->description ?? '—' }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-calendar text-green-500 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Dibuat</p>
                        <p class="text-sm text-gray-700 font-semibold">
                            {{ $arena->created_at->format('d M Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Danger zone --}}
        @if($arena->matches_count === 0)
        <div class="bg-red-50 border border-red-100 rounded-xl p-4">
            <p class="text-xs font-bold text-red-500 uppercase tracking-wider mb-2">Zona Berbahaya</p>
            <form action="{{ route('admin.arenas.destroy', $arena) }}" method="POST"
                onsubmit="return confirm('Yakin hapus arena {{ addslashes($arena->name) }}?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="btn btn-sm btn-outline border-red-300 text-red-500 hover:bg-red-500 hover:text-white hover:border-red-500 w-full gap-2">
                    <i class="fas fa-trash"></i>
                    Hapus Arena Ini
                </button>
            </form>
            <p class="text-xs text-red-400 mt-2">Arena tanpa pertandingan dapat dihapus permanen.</p>
        </div>
        @endif
    </div>

    {{-- RIGHT: Recent Matches --}}
    <div class="lg:col-span-2">
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="font-bold text-gray-700 text-sm uppercase tracking-wider mb-4">
                Pertandingan Terakhir
            </p>

            @forelse($recentMatches as $match)
            <div class="flex items-center gap-3 py-3 border-b border-gray-50 last:border-0">
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-fist-raised text-orange-500 text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">
                        {{ $match->eventCategory->discipline->name ?? '—' }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ $match->eventCategory->ageCategory->name ?? '—' }}
                        @if($match->start_time)
                        · {{ $match->start_time->format('d M Y, H:i') }}
                        @endif
                    </p>
                </div>
                @switch($match->status)
                    @case('scheduled')
                        <span class="badge badge-ghost text-xs flex-shrink-0">Terjadwal</span>
                        @break
                    @case('ongoing')
                        <span class="badge badge-warning text-xs flex-shrink-0">Berlangsung</span>
                        @break
                    @case('done')
                        <span class="badge badge-success text-xs flex-shrink-0">Selesai</span>
                        @break
                @endswitch
            </div>
            @empty
            <div class="py-10 text-center">
                <i class="fas fa-calendar-times text-4xl text-gray-200 mb-3"></i>
                <p class="text-gray-400 text-sm">Belum ada pertandingan di arena ini</p>
            </div>
            @endforelse

            @if($arena->matches_count > 10)
            <div class="mt-3 text-center">
                <p class="text-xs text-gray-400">
                    Menampilkan 10 dari {{ $arena->matches_count }} pertandingan
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection