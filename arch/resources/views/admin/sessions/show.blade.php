@extends('layouts.admin')
@section('title', 'Kelola Sesi')

@section('content')

@php
    $total    = $session->contests->count();
    $duration = $total * $session->duration_per_athlete;
    $endTime  = $session->start_time->copy()->addMinutes($duration);
    $done     = $session->contests->filter(fn($c) => $c->isScored())->count();
    $genderLabel = ['male' => 'Putra', 'female' => 'Putri', 'mixed' => 'Campuran'][$session->gender];
@endphp

{{-- BACK + HEADER --}}
<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('admin.sessions.index') }}"
        class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500 transition">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div class="flex-1">
        <div class="flex items-center gap-2 flex-wrap">
            <h2 class="text-xl font-bold text-gray-800">{{ $session->eventCategory->discipline->name }}</h2>
            <span class="badge badge-ghost text-xs">{{ $session->eventCategory->ageCategory->name }}</span>
            <span class="badge text-white text-xs
                {{ $session->gender === 'male' ? 'bg-blue-400' : ($session->gender === 'female' ? 'bg-pink-400' : 'bg-purple-400') }}">
                {{ $genderLabel }}
            </span>
        </div>
        <p class="text-sm text-gray-500 mt-0.5">
            <i class="fas fa-map-marker-alt text-orange-400 mr-1"></i>{{ $session->lapangan }} ·
            <i class="fas fa-clock text-gray-400 mr-1 ml-1"></i>
            {{ $session->start_time->format('d M Y, H:i') }} — {{ $endTime->format('H:i') }}
        </p>
    </div>
    <a href="{{ route('admin.sessions.edit', $session) }}"
        class="btn btn-sm btn-ghost text-gray-500 gap-1">
        <i class="fas fa-cog"></i> Edit Sesi
    </a>
</div>

{{-- SUMMARY BAR --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 text-center">
        <p class="text-xs text-blue-500 font-semibold uppercase tracking-wider">Atlet</p>
        <p class="text-2xl font-black text-blue-600">{{ $total }}</p>
    </div>
    <div class="bg-orange-50 border border-orange-100 rounded-xl p-3 text-center">
        <p class="text-xs text-orange-500 font-semibold uppercase tracking-wider">Per Atlet</p>
        <p class="text-2xl font-black text-orange-600">{{ $session->duration_per_athlete }}<span class="text-sm font-medium"> mnt</span></p>
    </div>
    <div class="bg-purple-50 border border-purple-100 rounded-xl p-3 text-center">
        <p class="text-xs text-purple-500 font-semibold uppercase tracking-wider">Total Durasi</p>
        <p class="text-2xl font-black text-purple-600">{{ $duration }}<span class="text-sm font-medium"> mnt</span></p>
    </div>
    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 text-center">
        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Mulai</p>
        <p class="text-lg font-black text-gray-700">{{ $session->start_time->format('H:i') }}</p>
    </div>
    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 text-center">
        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Est. Selesai</p>
        <p class="text-lg font-black text-gray-700">{{ $endTime->format('H:i') }}</p>
    </div>
</div>

{{-- PROGRESS --}}
@if($total > 0)
<div class="flex items-center gap-3 mb-5">
    <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
        <div class="h-full bg-green-400 rounded-full transition-all"
            style="width: {{ $total > 0 ? round($done / $total * 100) : 0 }}%"></div>
    </div>
    <span class="text-xs text-gray-500 flex-shrink-0">{{ $done }}/{{ $total }} atlet dinilai</span>
</div>
@endif

{{-- URUTAN ATLET --}}
<form action="{{ route('admin.sessions.order', $session) }}" method="POST" id="orderForm">
    @csrf
    @method('PUT')

    <div class="flex items-center justify-between mb-3">
        <p class="font-bold text-gray-700 flex items-center gap-2">
            <i class="fas fa-list-ol text-orange-400"></i>
            Urutan Penampilan
        </p>
        <div class="flex items-center gap-2">
            <button type="button" id="autoNumberBtn"
                class="btn btn-xs btn-ghost text-gray-500 hover:bg-gray-100 gap-1">
                <i class="fas fa-sort-numeric-up text-[10px]"></i>
                Auto Nomor
            </button>
            <button type="submit"
                class="btn btn-xs bg-orange-500 hover:bg-orange-600 text-white border-none gap-1">
                <i class="fas fa-save text-[10px]"></i>
                Simpan Urutan
            </button>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto rounded-xl border border-gray-100">
        <table class="table table-sm w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-3 w-8"></th>
                    <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3 w-20">No. Urut</th>
                    <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3">Atlet</th>
                    <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3">Perguruan</th>
                    <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3 text-center">Est. Tampil</th>
                    <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3 text-center">Nilai</th>
                    <th class="text-xs text-gray-500 font-semibold uppercase tracking-wider py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody id="sortableBody" class="divide-y divide-gray-50">
                @forelse($session->contests as $idx => $contest)
                @php
                    $estTime = $session->estimatedTimeForOrder($contest->order_number ?? $idx + 1);
                @endphp
                <tr class="hover:bg-gray-50/50 transition-colors sortable-row"
                    data-id="{{ $contest->id }}">

                    {{-- Drag handle --}}
                    <td class="py-3 px-3">
                        <i class="fas fa-grip-vertical text-gray-300 cursor-grab drag-handle"></i>
                    </td>

                    {{-- Input hidden --}}
                    <input type="hidden" name="contests[{{ $idx }}][id]" value="{{ $contest->id }}" class="row-id-input">

                    {{-- No. Urut --}}
                    <td class="py-3">
                        <input type="number"
                            name="contests[{{ $idx }}][order_number]"
                            value="{{ $contest->order_number ?? $idx + 1 }}"
                            min="1"
                            class="input input-sm input-bordered w-16 text-center font-black text-orange-600 focus:outline-orange-400 order-input">
                    </td>

                    {{-- Atlet --}}
                    <td class="py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm
                                {{ $session->gender === 'male' ? 'bg-blue-100 text-blue-600' : 'bg-pink-100 text-pink-600' }}">
                                {{ strtoupper(substr($contest->athlete->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">{{ $contest->athlete->name }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $contest->athlete->birth_date?->age ?? '?' }} tahun
                                </p>
                            </div>
                        </div>
                    </td>

                    {{-- Perguruan --}}
                    <td class="py-3">
                        <span class="text-sm text-gray-600">{{ $contest->athlete->perguruan->name ?? '—' }}</span>
                    </td>

                    {{-- Est. Tampil --}}
                    <td class="py-3 text-center est-time"
                        data-order="{{ $contest->order_number ?? $idx + 1 }}">
                        <span class="font-mono text-sm font-bold text-gray-700">
                            {{ $estTime->format('H:i') }}
                        </span>
                    </td>

                    {{-- Nilai --}}
                    <td class="py-3 text-center">
                        @if($contest->score && $contest->score->final_score !== null)
                            <span class="font-black text-green-600">
                                {{ number_format($contest->score->final_score, 3) }}
                            </span>
                        @else
                            <span class="text-gray-300 text-xs">Belum dinilai</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="py-3 text-center">
                        @switch($contest->status)
                            @case('scheduled')
                                <span class="badge badge-ghost text-xs">Terjadwal</span>
                                @break
                            @case('done')
                                <span class="badge badge-success text-xs">Selesai</span>
                                @break
                            @case('withdrawn')
                                <span class="badge badge-error text-xs">Mundur</span>
                                @break
                        @endswitch
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <i class="fas fa-users text-5xl text-gray-200"></i>
                            <p class="text-gray-400">Belum ada atlet dalam sesi ini</p>
                            <p class="text-xs text-gray-400">
                                Atlet akan otomatis ter-generate dari registrasi yang approved
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>

{{-- RANKING (jika ada yang sudah dinilai) --}}
@php $scored = $session->contests->filter(fn($c) => $c->isScored())->sortByDesc('score.final_score'); @endphp
@if($scored->count() > 0)
<div class="mt-6">
    <p class="font-bold text-gray-800 mb-3 flex items-center gap-2">
        <i class="fas fa-medal text-orange-400"></i>
        Ranking Sementara — {{ $genderLabel }}
    </p>
    <div class="space-y-2">
        @foreach($scored->values() as $rank => $contest)
        <div class="flex items-center gap-3 p-3 rounded-xl border
            {{ $rank === 0 ? 'bg-yellow-50 border-yellow-200' : ($rank === 1 ? 'bg-gray-50 border-gray-200' : ($rank === 2 ? 'bg-orange-50 border-orange-200' : 'bg-white border-gray-100')) }}">
            <div class="w-8 text-center flex-shrink-0">
                @if($rank === 0) <i class="fas fa-trophy text-yellow-500"></i>
                @elseif($rank === 1) <i class="fas fa-trophy text-gray-400"></i>
                @elseif($rank === 2) <i class="fas fa-trophy text-orange-400"></i>
                @else <span class="text-gray-400 font-bold text-sm">{{ $rank + 1 }}</span>
                @endif
            </div>
            <div class="flex-1">
                <p class="font-bold text-gray-800 text-sm">{{ $contest->athlete->name }}</p>
                <p class="text-xs text-gray-500">{{ $contest->athlete->perguruan->name ?? '—' }}</p>
            </div>
            <p class="text-xl font-black text-orange-600">{{ number_format($contest->score->final_score, 3) }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection

@push('scripts')
{{-- SortableJS untuk drag & drop --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
const startTime  = new Date('{{ $session->start_time->toIso8601String() }}');
const durMinutes = {{ $session->duration_per_athlete }};

// Sortable drag & drop
const tbody = document.getElementById('sortableBody');
if (tbody) {
    Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'bg-orange-50',
        onEnd: function() {
            reindexRows();
        }
    });
}

function reindexRows() {
    const rows = tbody.querySelectorAll('.sortable-row');
    rows.forEach((row, i) => {
        const orderInput = row.querySelector('.order-input');
        const idInput    = row.querySelector('.row-id-input');
        const estCell    = row.querySelector('.est-time');

        // Update name index
        if (orderInput) {
            orderInput.name = `contests[${i}][order_number]`;
            orderInput.value = i + 1;
        }
        if (idInput) {
            idInput.name = `contests[${i}][id]`;
        }

        // Update estimasi waktu
        if (estCell) {
            const est = new Date(startTime.getTime() + i * durMinutes * 60000);
            const hh  = String(est.getHours()).padStart(2, '0');
            const mm  = String(est.getMinutes()).padStart(2, '0');
            estCell.querySelector('span').textContent = `${hh}:${mm}`;
        }
    });
}

// Auto numbering
document.getElementById('autoNumberBtn')?.addEventListener('click', function() {
    const rows = tbody.querySelectorAll('.sortable-row');
    rows.forEach((row, i) => {
        const orderInput = row.querySelector('.order-input');
        if (orderInput) orderInput.value = i + 1;
    });
    reindexRows();
});

// Manual order input → update estimasi
tbody.addEventListener('input', function(e) {
    if (e.target.classList.contains('order-input')) {
        // Re-sort rows by order input value
        const rows = Array.from(tbody.querySelectorAll('.sortable-row'));
        rows.sort((a, b) => {
            const va = parseInt(a.querySelector('.order-input').value) || 999;
            const vb = parseInt(b.querySelector('.order-input').value) || 999;
            return va - vb;
        });
        rows.forEach(r => tbody.appendChild(r));
        reindexRows();
    }
});
</script>
@endpush
