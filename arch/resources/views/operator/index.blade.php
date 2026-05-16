@extends('layouts.admin')

@section('title', 'Input Nilai')

@section('content')
<div class="min-h-screen bg-base-200 p-6">

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <div class="bg-success/10 rounded-xl p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-base-content">Input Nilai</h1>
                <p class="text-sm text-base-content/60">Penilaian pertandingan Taolu</p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-6 rounded-2xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Filter --}}
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-5 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="form-control flex-1">
                <select name="category" class="select select-bordered rounded-xl">
                    <option value="">Semua Kategori</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-control flex-1">
                <select name="status" class="select select-bordered rounded-xl">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Belum Dinilai</option>
                    <option value="scored" {{ request('status') == 'scored' ? 'selected' : '' }}>Sudah Dinilai</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary rounded-xl gap-2 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
        </form>
    </div>

    {{-- Progress --}}
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-5 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-base-content">Progress Penilaian</span>
            <span class="text-sm font-bold text-primary">
                {{ $contests->where('final_score', '!=', null)->count() }} / {{ $contests->total() ?? $contests->count() }}
            </span>
        </div>
        @php
            $total = $contests->total() ?? $contests->count();
            $scored = $contests->where('final_score', '!=', null)->count();
            $percent = $total > 0 ? round(($scored / $total) * 100) : 0;
        @endphp
        <progress class="progress progress-primary w-full" value="{{ $percent }}" max="100"></progress>
        <div class="text-xs text-base-content/50 mt-1">{{ $percent }}% selesai dinilai</div>
    </div>

    {{-- Table --}}
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 overflow-hidden">
        <div class="p-5 border-b border-base-300">
            <h2 class="font-semibold text-base-content">Daftar Pertandingan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr class="bg-base-200 text-base-content/70 text-xs uppercase tracking-wider">
                        <th class="py-4 px-5">No. Urut</th>
                        <th class="py-4 px-5">Peserta</th>
                        <th class="py-4 px-5">Kategori</th>
                        <th class="py-4 px-5">Waktu Tampil</th>
                        <th class="py-4 px-5">Nilai Akhir</th>
                        <th class="py-4 px-5">Status</th>
                        <th class="py-4 px-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contests as $contest)
                    <tr class="hover:bg-base-50 transition-colors {{ !$contest->final_score ? 'border-l-4 border-l-warning' : 'border-l-4 border-l-success' }}">
                        <td class="px-5 py-4">
                            <div class="badge badge-neutral badge-lg font-bold">{{ $contest->order ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-success/10 text-success rounded-full w-9">
                                        <span class="text-sm font-bold">{{ substr($contest->participant->name ?? 'P', 0, 1) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm">{{ $contest->participant->name ?? '-' }}</div>
                                    <div class="text-xs text-base-content/50">{{ $contest->participant->contingent->name ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="badge badge-outline badge-sm">{{ $contest->eventCategory->name ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            @if($contest->scheduled_at)
                                <div class="text-sm">{{ \Carbon\Carbon::parse($contest->scheduled_at)->format('d M Y') }}</div>
                                <div class="text-xs text-base-content/50">{{ \Carbon\Carbon::parse($contest->scheduled_at)->format('H:i') }} WIB</div>
                            @else
                                <span class="text-base-content/40 text-sm">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($contest->final_score)
                                <div class="font-bold text-2xl text-success">{{ number_format($contest->final_score, 3) }}</div>
                            @else
                                <span class="text-base-content/30">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($contest->final_score)
                                <div class="badge badge-success badge-sm gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Selesai
                                </div>
                            @else
                                <div class="badge badge-warning badge-sm gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Menunggu
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <a href="{{ route('operator.scores.edit', $contest) }}"
                               class="btn btn-sm {{ $contest->final_score ? 'btn-ghost' : 'btn-primary' }} rounded-xl gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                {{ $contest->final_score ? 'Edit Nilai' : 'Input Nilai' }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-16">
                            <div class="flex flex-col items-center gap-3 text-base-content/40">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span class="text-sm">Tidak ada data pertandingan</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($contests instanceof \Illuminate\Pagination\LengthAwarePaginator && $contests->hasPages())
        <div class="p-5 border-t border-base-300">
            {{ $contests->links() }}
        </div>
        @endif
    </div>

</div>
@endsection