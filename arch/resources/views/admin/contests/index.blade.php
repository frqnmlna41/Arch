@extends('layouts.admin')

@section('title', 'Kelola Jadwal Pertandingan')

@section('content')
<div class="min-h-screen bg-base-200 p-6">

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <div class="bg-primary/10 rounded-xl p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-base-content">Kelola Jadwal</h1>
                <p class="text-sm text-base-content/60">Manajemen jadwal pertandingan per kategori</p>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-300">
            <div class="stat-figure text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div class="stat-title text-xs">Total Kategori</div>
            <div class="stat-value text-primary text-2xl">{{ $categories->count() }}</div>
        </div>
        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-300">
            <div class="stat-figure text-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-title text-xs">Jadwal Dibuat</div>
            <div class="stat-value text-success text-2xl">{{ $categories->where('contests_generated', true)->count() }}</div>
        </div>
        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-300">
            <div class="stat-figure text-warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-title text-xs">Menunggu</div>
            <div class="stat-value text-warning text-2xl">{{ $categories->where('contests_generated', false)->count() }}</div>
        </div>
        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-300">
            <div class="stat-figure text-info">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="stat-title text-xs">Total Peserta</div>
            <div class="stat-value text-info text-2xl">{{ $categories->sum('participants_count') ?? 0 }}</div>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success mb-6 rounded-2xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 overflow-hidden">
        <div class="p-5 border-b border-base-300 flex items-center justify-between">
            <h2 class="font-semibold text-base-content">Daftar Kategori Event</h2>
            <div class="join">
                <input class="input input-sm input-bordered join-item rounded-xl" placeholder="Cari kategori..." />
                <button class="btn btn-sm btn-primary join-item rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr class="bg-base-200 text-base-content/70 text-xs uppercase tracking-wider">
                        <th class="py-4 px-5">#</th>
                        <th class="py-4 px-5">Kategori</th>
                        <th class="py-4 px-5">Jenis</th>
                        <th class="py-4 px-5">Peserta</th>
                        <th class="py-4 px-5">Status Jadwal</th>
                        <th class="py-4 px-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $i => $category)
                    <tr class="hover:bg-base-50 transition-colors">
                        <td class="px-5 py-4 text-base-content/50 text-sm">{{ $i + 1 }}</td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-base-content">{{ $category->name }}</div>
                            <div class="text-xs text-base-content/50">{{ $category->event->name ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="badge badge-outline badge-sm">{{ $category->type ?? 'Taolu' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="avatar-group -space-x-2">
                                    @for($j = 0; $j < min(3, $category->participants_count ?? 0); $j++)
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-6 text-xs">
                                            <span>{{ chr(65 + $j) }}</span>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <span class="text-sm font-medium">{{ $category->participants_count ?? 0 }} peserta</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @if($category->contests_generated ?? false)
                                <div class="badge badge-success gap-1 badge-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Sudah Dibuat
                                </div>
                            @else
                                <div class="badge badge-warning gap-1 badge-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Belum Dibuat
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.contests.show', $category) }}"
                                   class="btn btn-xs btn-ghost tooltip" data-tip="Lihat Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <a href="{{ route('admin.contests.schedule', $category) }}"
                                   class="btn btn-xs btn-ghost tooltip" data-tip="Edit Jadwal">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                @if(!($category->contests_generated ?? false))
                                <form action="{{ route('admin.contests.generate', $category) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-primary tooltip" data-tip="Generate Jadwal"
                                        onclick="return confirm('Generate jadwal untuk kategori {{ $category->name }}?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-16">
                            <div class="flex flex-col items-center gap-3 text-base-content/40">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <span class="text-sm">Belum ada kategori event</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection