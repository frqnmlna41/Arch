@extends('layouts.admin')
@section('title', 'Arena')

@section('content')

{{-- HEADER --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-gray-800">Daftar Arena</h2>
        <p class="text-sm text-gray-500 mt-0.5">Kelola arena / lapangan pertandingan</p>
    </div>
    <a href="{{ route('admin.arenas.create') }}"
        class="btn btn-sm bg-orange-500 hover:bg-orange-600 text-white border-none gap-2">
        <i class="fas fa-plus"></i>
        Tambah Arena
    </a>
</div>

{{-- FILTER --}}
<form method="GET" class="flex items-center gap-2 mb-5 flex-wrap">
    <div class="relative flex-1 min-w-48">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Cari nama arena..."
            class="input input-sm input-bordered w-full pl-8 focus:outline-orange-400">
    </div>
    <label class="flex items-center gap-2 cursor-pointer select-none text-sm text-gray-600">
        <input type="checkbox" name="active" value="1" class="checkbox checkbox-sm checkbox-orange"
            {{ request('active') ? 'checked' : '' }} onchange="this.form.submit()">
        Aktif saja
    </label>
    <button type="submit" class="btn btn-sm btn-ghost text-gray-500 hover:bg-gray-100">
        <i class="fas fa-filter"></i> Filter
    </button>
    @if(request()->hasAny(['search', 'active']))
    <a href="{{ route('admin.arenas.index') }}"
        class="btn btn-sm btn-ghost text-red-400 hover:bg-red-50">
        <i class="fas fa-times"></i> Reset
    </a>
    @endif
</form>

{{-- STATS --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-orange-500 uppercase tracking-wider">Total Arena</p>
        <p class="text-3xl font-black text-orange-600 mt-1">{{ $arenas->total() }}</p>
    </div>
    <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-green-500 uppercase tracking-wider">Aktif</p>
        <p class="text-3xl font-black text-green-600 mt-1">
            {{ $arenas->getCollection()->where('is_active', true)->count() }}
        </p>
    </div>
    <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Non-aktif</p>
        <p class="text-3xl font-black text-gray-600 mt-1">
            {{ $arenas->getCollection()->where('is_active', false)->count() }}
        </p>
    </div>
</div>

{{-- TABLE --}}
<div class="overflow-x-auto rounded-xl border border-gray-100">
    <table class="table table-sm w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3 px-4 w-10">#</th>
                <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3">Nama Arena</th>
                <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3">Lokasi</th>
                <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3 text-center">Kapasitas</th>
                <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3 text-center">Pertandingan</th>
                <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3 text-center">Status</th>
                <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($arenas as $i => $arena)
            <tr class="hover:bg-gray-50 transition-colors">

                <td class="py-3 px-4 text-gray-400 text-sm">{{ $arenas->firstItem() + $i }}</td>

                {{-- Nama --}}
                <td class="py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-orange-500 text-sm"></i>
                        </div>
                        <div>
                            <a href="{{ route('admin.arenas.show', $arena) }}"
                                class="font-semibold text-gray-800 hover:text-orange-500 transition-colors text-sm">
                                {{ $arena->name }}
                            </a>
                            @if($arena->description)
                            <p class="text-xs text-gray-400 truncate max-w-xs">{{ $arena->description }}</p>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- Lokasi --}}
                <td class="py-3">
                    <span class="text-sm text-gray-600">{{ $arena->location ?? '—' }}</span>
                </td>

                {{-- Kapasitas --}}
                <td class="py-3 text-center">
                    @if($arena->capacity)
                        <span class="text-sm font-semibold text-gray-700">
                            {{ number_format($arena->capacity) }}
                        </span>
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                </td>

                {{-- Jumlah Pertandingan --}}
                <td class="py-3 text-center">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                        {{ $arena->matches_count > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400' }}
                        font-bold text-sm">
                        {{ $arena->matches_count }}
                    </span>
                </td>

                {{-- Status --}}
                <td class="py-3 text-center">
                    @if($arena->is_active)
                        <span class="badge badge-success text-xs gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block"></span>
                            Aktif
                        </span>
                    @else
                        <span class="badge badge-ghost text-xs gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span>
                            Non-aktif
                        </span>
                    @endif
                </td>

                {{-- Aksi --}}
                <td class="py-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('admin.arenas.show', $arena) }}"
                            class="btn btn-xs btn-ghost text-blue-500 hover:bg-blue-50 gap-1" title="Detail">
                            <i class="fas fa-eye text-[10px]"></i>
                        </a>
                        <a href="{{ route('admin.arenas.edit', $arena) }}"
                            class="btn btn-xs btn-ghost text-orange-500 hover:bg-orange-50 gap-1" title="Edit">
                            <i class="fas fa-edit text-[10px]"></i>
                        </a>
                        <form action="{{ route('admin.arenas.destroy', $arena) }}" method="POST"
                            onsubmit="return confirm('Hapus arena {{ addslashes($arena->name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="btn btn-xs btn-ghost text-red-400 hover:bg-red-50" title="Hapus"
                                {{ $arena->matches_count > 0 ? 'disabled' : '' }}>
                                <i class="fas fa-trash text-[10px]"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-20 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <i class="fas fa-map-marker-alt text-5xl text-gray-200"></i>
                        <p class="text-gray-400 font-medium">Belum ada arena</p>
                        <a href="{{ route('admin.arenas.create') }}"
                            class="btn btn-sm bg-orange-500 text-white border-none">
                            Tambah Arena Pertama
                        </a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($arenas->hasPages())
<div class="mt-4">{{ $arenas->links() }}</div>
@endif

@endsection