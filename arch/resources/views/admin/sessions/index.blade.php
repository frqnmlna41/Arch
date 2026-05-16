@extends('layouts.admin')
@section('title', 'Jadwal Sesi')

@section('content')

{{-- HEADER --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-gray-800">Jadwal Sesi Pertandingan</h2>
        <p class="text-sm text-gray-500 mt-0.5">Kelola sesi per disiplin · kategori umur · gender</p>
    </div>
    <a href="{{ route('admin.sessions.create') }}"
        class="btn btn-sm bg-orange-500 hover:bg-orange-600 text-white border-none gap-2">
        <i class="fas fa-plus"></i>
        Buat Sesi
    </a>
</div>

{{-- STATS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-orange-500 uppercase tracking-wider">Total Sesi</p>
        <p class="text-3xl font-black text-orange-600 mt-1">{{ $sessions->total() }}</p>
    </div>
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-blue-500 uppercase tracking-wider">Total Atlet</p>
        <p class="text-3xl font-black text-blue-600 mt-1">{{ $sessions->sum('contests_count') }}</p>
    </div>
    <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-yellow-600 uppercase tracking-wider">Belum Mulai</p>
        <p class="text-3xl font-black text-yellow-600 mt-1">
            {{ $sessions->getCollection()->where('status', 'draft')->count() }}
        </p>
    </div>
    <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-green-500 uppercase tracking-wider">Selesai</p>
        <p class="text-3xl font-black text-green-600 mt-1">
            {{ $sessions->getCollection()->where('status', 'done')->count() }}
        </p>
    </div>
</div>

{{-- BELUM ADA SESI --}}
@if($unsessioned->count() > 0)
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-start gap-3">
    <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
    <div>
        <p class="text-sm font-semibold text-amber-700">
            {{ $unsessioned->count() }} kategori belum memiliki sesi
        </p>
        <p class="text-xs text-amber-600 mt-0.5">
            {{ $unsessioned->map(fn($c) => $c->discipline->name . ' - ' . $c->ageCategory->name)->join(', ') }}
        </p>
    </div>
    <a href="{{ route('admin.sessions.create') }}"
        class="ml-auto btn btn-xs bg-amber-500 text-white border-none flex-shrink-0">
        Buat Sesi
    </a>
</div>
@endif

{{-- SESSION CARDS --}}
<div class="space-y-3">
    @forelse($sessions as $session)
    @php
        $total    = $session->contests_count;
        $duration = $total * $session->duration_per_athlete;
        $endTime  = $session->start_time->copy()->addMinutes($duration);
        $genderColor = $session->gender === 'male' ? 'blue' : ($session->gender === 'female' ? 'pink' : 'purple');
        $genderLabel = ['male' => 'Putra', 'female' => 'Putri', 'mixed' => 'Campuran'][$session->gender];
    @endphp

    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden hover:shadow-md transition-shadow">
        <div class="flex items-stretch">

            {{-- Color bar kiri berdasarkan gender --}}
            <div class="w-1.5 flex-shrink-0
                {{ $session->gender === 'male' ? 'bg-blue-400' : ($session->gender === 'female' ? 'bg-pink-400' : 'bg-purple-400') }}">
            </div>

            <div class="flex-1 p-4">
                <div class="flex items-start justify-between gap-4">

                    {{-- Info utama --}}
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                            {{ $session->gender === 'male' ? 'bg-blue-100' : ($session->gender === 'female' ? 'bg-pink-100' : 'bg-purple-100') }}">
                            <i class="fas fa-{{ $session->gender === 'male' ? 'mars text-blue-500' : ($session->gender === 'female' ? 'venus text-pink-500' : 'transgender text-purple-500') }}"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-bold text-gray-800">
                                    {{ $session->eventCategory->discipline->name }}
                                </p>
                                <span class="badge badge-sm badge-ghost text-xs">
                                    {{ $session->eventCategory->ageCategory->name }}
                                </span>
                                <span class="badge badge-sm text-xs text-white
                                    {{ $session->gender === 'male' ? 'bg-blue-400' : ($session->gender === 'female' ? 'bg-pink-400' : 'bg-purple-400') }}">
                                    {{ $genderLabel }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 mt-1 text-xs text-gray-500 flex-wrap">
                                <span><i class="fas fa-map-marker-alt text-orange-400 mr-1"></i>{{ $session->lapangan }}</span>
                                <span><i class="fas fa-clock text-gray-400 mr-1"></i>
                                    {{ $session->start_time->format('d M Y, H:i') }} —
                                    {{ $endTime->format('H:i') }}
                                </span>
                                <span><i class="fas fa-users text-gray-400 mr-1"></i>{{ $total }} atlet</span>
                                <span><i class="fas fa-hourglass-half text-gray-400 mr-1"></i>
                                    {{ $session->duration_per_athlete }} menit/atlet · estimasi {{ $duration }} menit
                                </span>
                            </div>
                            @if($session->notes)
                            <p class="text-xs text-gray-400 mt-1 italic">{{ $session->notes }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Status + Aksi --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @switch($session->status)
                            @case('draft')
                                <span class="badge badge-ghost text-xs">Draft</span>
                                @break
                            @case('ongoing')
                                <span class="badge badge-warning text-xs animate-pulse">Berlangsung</span>
                                @break
                            @case('done')
                                <span class="badge badge-success text-xs">Selesai</span>
                                @break
                        @endswitch

                        <a href="{{ route('admin.sessions.show', $session) }}"
                            class="btn btn-xs bg-orange-500 hover:bg-orange-600 text-white border-none gap-1">
                            <i class="fas fa-list-ol text-[10px]"></i>
                            Kelola
                        </a>
                        <a href="{{ route('admin.sessions.edit', $session) }}"
                            class="btn btn-xs btn-ghost text-gray-500 hover:bg-gray-100 gap-1">
                            <i class="fas fa-cog text-[10px]"></i>
                            Edit
                        </a>
                    </div>
                </div>

                {{-- Progress bar atlet selesai --}}
                @php $done = $session->contests->filter(fn($c) => $c->isScored())->count(); @endphp
                @if($total > 0)
                <div class="mt-3 flex items-center gap-2">
                    <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-green-400 transition-all"
                            style="width: {{ round($done / $total * 100) }}%"></div>
                    </div>
                    <span class="text-xs text-gray-400 flex-shrink-0">
                        {{ $done }}/{{ $total }} dinilai
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="py-20 text-center">
        <i class="fas fa-calendar-times text-5xl text-gray-200 mb-4"></i>
        <p class="text-gray-400 font-medium">Belum ada sesi pertandingan</p>
        <a href="{{ route('admin.sessions.create') }}"
            class="btn btn-sm bg-orange-500 text-white border-none mt-3">
            Buat Sesi Pertama
        </a>
    </div>
    @endforelse
</div>

@if($sessions->hasPages())
<div class="mt-4">{{ $sessions->links() }}</div>
@endif

@endsection
