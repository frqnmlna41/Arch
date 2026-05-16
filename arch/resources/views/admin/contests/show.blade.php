@extends('layouts.admin')

@section('title', 'Detail Jadwal - ' . $eventCategory->name)

@section('content')
<div class="min-h-screen bg-base-200 p-6">

    {{-- Breadcrumb --}}
    <div class="breadcrumbs text-sm mb-6">
        <ul>
            <li><a href="{{ route('admin.contests.index') }}" class="text-primary">Kelola Jadwal</a></li>
            <li class="text-base-content/60">{{ $eventCategory->name }}</li>
        </ul>
    </div>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-base-content">{{ $eventCategory->name }}</h1>
            <p class="text-sm text-base-content/60 mt-0.5">{{ $eventCategory->event->name ?? 'Detail pertandingan' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.contests.schedule', $eventCategory) }}" class="btn btn-outline btn-sm rounded-xl gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Jadwal
            </a>
            @if(!($eventCategory->contests_generated ?? false))
            <form action="{{ route('admin.contests.generate', $eventCategory) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm rounded-xl gap-2"
                    onclick="return confirm('Generate jadwal pertandingan?')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Generate Jadwal
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-base-100 rounded-2xl p-5 border border-base-300 shadow-sm">
            <div class="text-xs text-base-content/50 uppercase tracking-wider mb-1">Total Pertandingan</div>
            <div class="text-3xl font-bold text-primary">{{ $contests->count() }}</div>
        </div>
        <div class="bg-base-100 rounded-2xl p-5 border border-base-300 shadow-sm">
            <div class="text-xs text-base-content/50 uppercase tracking-wider mb-1">Status</div>
            @if($eventCategory->contests_generated ?? false)
                <div class="badge badge-success badge-lg mt-1">Jadwal Aktif</div>
            @else
                <div class="badge badge-warning badge-lg mt-1">Belum Generate</div>
            @endif
        </div>
        <div class="bg-base-100 rounded-2xl p-5 border border-base-300 shadow-sm">
            <div class="text-xs text-base-content/50 uppercase tracking-wider mb-1">Selesai Dinilai</div>
            <div class="text-3xl font-bold text-success">
                {{ $contests->whereNotNull('score')->count() }} / {{ $contests->count() }}
            </div>
        </div>
    </div>

    {{-- Contest Table --}}
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
                        <th class="py-4 px-5">Asal</th>
                        <th class="py-4 px-5">Jadwal Tampil</th>
                        <th class="py-4 px-5">Nilai</th>
                        <th class="py-4 px-5">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contests as $contest)
                    <tr class="hover:bg-base-50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="badge badge-neutral badge-lg font-bold">{{ $contest->order ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-primary/10 text-primary rounded-full w-9">
                                        <span class="text-sm font-bold">{{ substr($contest->participant->name ?? 'P', 0, 1) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm">{{ $contest->participant->name ?? '-' }}</div>
                                    <div class="text-xs text-base-content/50">ID: {{ $contest->participant->registration_number ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-base-content/70">
                            {{ $contest->participant->contingent->name ?? '-' }}
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
                                <div class="font-bold text-primary">{{ number_format($contest->final_score, 3) }}</div>
                            @else
                                <span class="text-base-content/30 text-sm">Belum dinilai</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($contest->final_score)
                                <div class="badge badge-success badge-sm">Selesai</div>
                            @else
                                <div class="badge badge-ghost badge-sm">Menunggu</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-16">
                            <div class="flex flex-col items-center gap-3 text-base-content/40">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span class="text-sm">Jadwal belum di-generate</span>
                                @if(!($eventCategory->contests_generated ?? false))
                                <form action="{{ route('admin.contests.generate', $eventCategory) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm rounded-xl mt-1">Generate Sekarang</button>
                                </form>
                                @endif
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