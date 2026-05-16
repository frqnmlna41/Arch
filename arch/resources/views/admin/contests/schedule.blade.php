@extends('layouts.admin')

@section('title', 'Edit Jadwal - ' . $eventCategory->name)

@section('content')
<div class="min-h-screen bg-base-200 p-6">

    {{-- Breadcrumb --}}
    <div class="breadcrumbs text-sm mb-6">
        <ul>
            <li><a href="{{ route('admin.contests.index') }}" class="text-primary">Kelola Jadwal</a></li>
            <li><a href="{{ route('admin.contests.show', $eventCategory) }}" class="text-primary">{{ $eventCategory->name }}</a></li>
            <li class="text-base-content/60">Edit Jadwal</li>
        </ul>
    </div>

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-base-content">Edit Jadwal Pertandingan</h1>
        <p class="text-sm text-base-content/60 mt-0.5">{{ $eventCategory->name }} — Atur urutan dan waktu tampil peserta</p>
    </div>

    @if($errors->any())
    <div class="alert alert-error mb-6 rounded-2xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.contests.schedule.update', $eventCategory) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Global Schedule Settings --}}
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6 mb-6">
            <h2 class="font-semibold text-base-content mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Pengaturan Umum
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Tanggal Pertandingan</span>
                    </label>
                    <input type="date" name="competition_date"
                           value="{{ old('competition_date', $eventCategory->competition_date) }}"
                           class="input input-bordered rounded-xl @error('competition_date') input-error @enderror" />
                    @error('competition_date')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Waktu Mulai</span>
                    </label>
                    <input type="time" name="start_time"
                           value="{{ old('start_time', $eventCategory->start_time) }}"
                           class="input input-bordered rounded-xl @error('start_time') input-error @enderror" />
                    @error('start_time')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Durasi per Peserta (menit)</span>
                    </label>
                    <input type="number" name="duration_per_contestant" min="1" max="60"
                           value="{{ old('duration_per_contestant', $eventCategory->duration_per_contestant ?? 5) }}"
                           class="input input-bordered rounded-xl @error('duration_per_contestant') input-error @enderror" />
                    @error('duration_per_contestant')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Contestant Order --}}
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 overflow-hidden mb-6">
            <div class="p-5 border-b border-base-300 flex items-center justify-between">
                <h2 class="font-semibold text-base-content flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    Urutan Peserta
                </h2>
                <span class="text-sm text-base-content/50">{{ $contests->count() }} peserta</span>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-base-200 text-base-content/70 text-xs uppercase tracking-wider">
                            <th class="py-3 px-5">No. Urut</th>
                            <th class="py-3 px-5">Peserta</th>
                            <th class="py-3 px-5">Asal Kontingen</th>
                            <th class="py-3 px-5">Waktu Tampil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contests as $contest)
                        <tr class="border-b border-base-200 hover:bg-base-50 transition-colors">
                            <td class="px-5 py-4">
                                <input type="hidden" name="contests[{{ $loop->index }}][id]" value="{{ $contest->id }}" />
                                <input type="number" name="contests[{{ $loop->index }}][order]"
                                       value="{{ old("contests.{$loop->index}.order", $contest->order) }}"
                                       min="1"
                                       class="input input-bordered input-sm w-20 rounded-lg text-center font-bold" />
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
                                        <div class="text-xs text-base-content/50">{{ $contest->participant->registration_number ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-base-content/70">
                                {{ $contest->participant->contingent->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <input type="datetime-local" name="contests[{{ $loop->index }}][scheduled_at]"
                                       value="{{ old("contests.{$loop->index}.scheduled_at", $contest->scheduled_at ? \Carbon\Carbon::parse($contest->scheduled_at)->format('Y-m-d\TH:i') : '') }}"
                                       class="input input-bordered input-sm rounded-lg" />
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.contests.show', $eventCategory) }}" class="btn btn-ghost rounded-xl gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
            <button type="submit" class="btn btn-primary rounded-xl gap-2 px-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Simpan Jadwal
            </button>
        </div>
    </form>

</div>
@endsection