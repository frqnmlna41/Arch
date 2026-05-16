@extends('layouts.admin')
@section('title', 'Edit Sesi')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.sessions.show', $session) }}"
        class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500 transition">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div>
        <h2 class="text-xl font-bold text-gray-800">Edit Sesi</h2>
        <p class="text-sm text-gray-500">
            {{ $session->eventCategory->discipline->name }} ·
            {{ $session->eventCategory->ageCategory->name }} ·
            {{ ['male' => 'Putra', 'female' => 'Putri', 'mixed' => 'Campuran'][$session->gender] }}
        </p>
    </div>
</div>

<div class="max-w-2xl">
    <form action="{{ route('admin.sessions.update', $session) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Info (read-only) --}}
        <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-2">Info Sesi (tidak dapat diubah)</p>
            <div class="flex gap-4 flex-wrap text-sm text-gray-600">
                <span><i class="fas fa-fist-raised text-orange-400 mr-1"></i>{{ $session->eventCategory->discipline->name }}</span>
                <span><i class="fas fa-users text-gray-400 mr-1"></i>{{ $session->eventCategory->ageCategory->name }}</span>
                <span><i class="fas fa-{{ $session->gender === 'male' ? 'mars text-blue-400' : 'venus text-pink-400' }} mr-1"></i>
                    {{ ['male' => 'Putra', 'female' => 'Putri', 'mixed' => 'Campuran'][$session->gender] }}
                </span>
            </div>
        </div>

        {{-- Jadwal --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5 space-y-4">
            <p class="font-bold text-gray-700 text-sm uppercase tracking-wider">Jadwal & Lapangan</p>

            {{-- Lapangan --}}
            <div>
                <label class="text-sm font-semibold text-gray-600 mb-2 block">
                    Lapangan / Mat <span class="text-red-400">*</span>
                </label>
                <div class="flex gap-2 flex-wrap">
                    @foreach(['Mat A', 'Mat B', 'Mat C', 'Mat D', 'Gelanggang 1', 'Gelanggang 2', 'Gelanggang 3'] as $lap)
                    <label class="cursor-pointer">
                        <input type="radio" name="lapangan" value="{{ $lap }}"
                            class="peer hidden"
                            {{ (old('lapangan', $session->lapangan) === $lap) ? 'checked' : '' }}>
                        <span class="inline-block px-3 py-1.5 text-xs font-semibold border-2 border-gray-200 rounded-lg
                            peer-checked:border-orange-400 peer-checked:bg-orange-50 peer-checked:text-orange-700
                            hover:border-orange-300 transition-all cursor-pointer">
                            {{ $lap }}
                        </span>
                    </label>
                    @endforeach
                </div>
                @error('lapangan')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                {{-- Waktu Mulai --}}
                <div>
                    <label class="text-sm font-semibold text-gray-600 mb-1 block">
                        Waktu Mulai <span class="text-red-400">*</span>
                    </label>
                    <input type="datetime-local" name="start_time"
                        value="{{ old('start_time', $session->start_time->format('Y-m-d\TH:i')) }}"
                        class="input input-bordered w-full focus:outline-orange-400 @error('start_time') input-error @enderror">
                    @error('start_time')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Durasi --}}
                <div>
                    <label class="text-sm font-semibold text-gray-600 mb-1 block">
                        Durasi per Atlet (menit) <span class="text-red-400">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="range" name="duration_per_athlete" id="durationRange"
                            min="1" max="15"
                            value="{{ old('duration_per_athlete', $session->duration_per_athlete) }}"
                            class="range range-xs range-orange flex-1"
                            oninput="document.getElementById('durationVal').textContent = this.value; updateEstimasi()">
                        <span id="durationVal" class="w-8 text-center font-black text-orange-600 text-lg">
                            {{ old('duration_per_athlete', $session->duration_per_athlete) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Estimasi --}}
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
            <p class="text-xs font-bold text-orange-600 uppercase tracking-wider mb-2">
                <i class="fas fa-clock mr-1"></i> Estimasi Sesi
            </p>
            @php
                $total = $session->contests->count();
                $durasi = $total * $session->duration_per_athlete;
                $end = $session->start_time->copy()->addMinutes($durasi);
            @endphp
            <div class="grid grid-cols-4 gap-3 text-center">
                <div>
                    <p class="text-xs text-orange-500">Atlet</p>
                    <p class="font-black text-orange-700 text-lg">{{ $total }}</p>
                </div>
                <div>
                    <p class="text-xs text-orange-500">Per Atlet</p>
                    <p class="font-black text-orange-700 text-lg" id="estDur">{{ $session->duration_per_athlete }} mnt</p>
                </div>
                <div>
                    <p class="text-xs text-orange-500">Total</p>
                    <p class="font-black text-orange-700 text-lg" id="estTotal">{{ $durasi }} mnt</p>
                </div>
                <div>
                    <p class="text-xs text-orange-500">Est. Selesai</p>
                    <p class="font-black text-orange-700 text-lg" id="estEnd">{{ $end->format('H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Catatan --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <label class="text-sm font-semibold text-gray-600 mb-1 block">Catatan (opsional)</label>
            <textarea name="notes" rows="2"
                class="textarea textarea-bordered w-full focus:outline-orange-400 text-sm">{{ old('notes', $session->notes) }}</textarea>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.sessions.show', $session) }}" class="btn btn-ghost text-gray-500 btn-sm">Batal</a>
            <button type="submit"
                class="btn btn-sm bg-orange-500 hover:bg-orange-600 text-white border-none gap-2">
                <i class="fas fa-save"></i>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
const totalAtlet = {{ $session->contests->count() }};

function updateEstimasi() {
    const dur     = parseInt(document.getElementById('durationRange').value);
    const start   = document.querySelector('[name=start_time]').value;
    const total   = dur * totalAtlet;

    document.getElementById('estDur').textContent   = dur + ' mnt';
    document.getElementById('estTotal').textContent = total + ' mnt';

    if (start) {
        const end = new Date(new Date(start).getTime() + total * 60000);
        const hh  = String(end.getHours()).padStart(2, '0');
        const mm  = String(end.getMinutes()).padStart(2, '0');
        document.getElementById('estEnd').textContent = `${hh}:${mm}`;
    }
}

document.querySelector('[name=start_time]')?.addEventListener('input', updateEstimasi);
</script>
@endpush
