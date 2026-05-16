@extends('layouts.admin')
@section('title', 'Buat Sesi Baru')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.sessions.index') }}"
        class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500 transition">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div>
        <h2 class="text-xl font-bold text-gray-800">Buat Sesi Baru</h2>
        <p class="text-sm text-gray-500">Satu sesi = 1 disiplin + 1 kategori umur + 1 gender</p>
    </div>
</div>

<div class="max-w-2xl">
    <form action="{{ route('admin.sessions.store') }}" method="POST" class="space-y-5">
        @csrf

        {{-- Kategori --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5 space-y-4">
            <p class="font-bold text-gray-700 text-sm uppercase tracking-wider">Kategori Sesi</p>

            <div>
                <label class="text-sm font-semibold text-gray-600 mb-1 block">
                    Disiplin & Kategori Umur <span class="text-red-400">*</span>
                </label>
                <select name="event_category_id"
                    class="select select-bordered w-full focus:outline-orange-400 @error('event_category_id') select-error @enderror">
                    <option value="">— Pilih Kategori —</option>
                    @foreach($eventCategories as $cat)
                    <option value="{{ $cat->id }}" {{ old('event_category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->discipline->name }} · {{ $cat->ageCategory->name }}
                    </option>
                    @endforeach
                </select>
                @error('event_category_id')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-600 mb-2 block">
                    Gender <span class="text-red-400">*</span>
                </label>
                <div class="flex gap-3">
                    @foreach(['male' => ['label' => 'Putra', 'icon' => 'mars', 'color' => 'blue'], 'female' => ['label' => 'Putri', 'icon' => 'venus', 'color' => 'pink'], 'mixed' => ['label' => 'Campuran', 'icon' => 'transgender', 'color' => 'purple']] as $val => $opt)
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="gender" value="{{ $val }}"
                            class="peer hidden" {{ old('gender') === $val ? 'checked' : '' }}>
                        <!-- <div class="border-2 border-gray-200 rounded-xl p-3 text-center transition-all
                            peer-checked:border-{{ $opt['color'] }}-400 peer-checked:bg-{{ $opt['color'] }}-50"> -->
                            <div class="border-2 border-gray-200 rounded-xl p-3 text-center transition-all duration-200
                                hover:border-{{ $opt['color'] }}-300
                                hover:bg-{{ $opt['color'] }}-50/40
                                hover:scale-[1.02]
                                hover:shadow-md

                                peer-checked:border-{{ $opt['color'] }}-400
                                peer-checked:bg-{{ $opt['color'] }}-50
                                peer-checked:shadow-md
                                peer-checked:scale-[1.02]">
                            <i class="fas fa-{{ $opt['icon'] }} text-{{ $opt['color'] }}-400 text-lg mb-1"></i>
                            <p class="text-xs font-bold text-gray-700">{{ $opt['label'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('gender')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Jadwal --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5 space-y-4">
            <p class="font-bold text-gray-700 text-sm uppercase tracking-wider">Jadwal & Lapangan</p>

            <div>
                <label class="text-sm font-semibold text-gray-600 mb-1 block">
                    Lapangan / Mat <span class="text-red-400">*</span>
                </label>
                <div class="flex gap-2 flex-wrap">
                    <!-- @foreach(['Mat A', 'Mat B', 'Mat C', 'Mat D', 'Gelanggang 1', 'Gelanggang 2', 'Gelanggang 3'] as $lap)
                    <label class="cursor-pointer">
                        <input type="radio" name="lapangan" value="{{ $lap }}"
                            class="peer hidden" {{ old('lapangan') === $lap ? 'checked' : '' }}>
                        <span class="inline-block px-3 py-1.5 text-xs font-semibold border-2 border-gray-200 rounded-lg
                            peer-checked:border-orange-400 peer-checked:bg-orange-50 peer-checked:text-orange-700
                            hover:border-orange-300 transition-all cursor-pointer">
                            {{ $lap }}
                        </span>
                    </label>
                    @endforeach -->
@foreach($arenas as $arena)
<label class="cursor-pointer">
    <input type="radio" name="arena_id" value="{{ $arena->id }}"
        class="peer hidden"
        {{ old('arena_id') == $arena->id ? 'checked' : '' }}>
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold
        border-2 border-gray-200 rounded-lg cursor-pointer transition-all
        peer-checked:border-orange-400 peer-checked:bg-orange-50 peer-checked:text-orange-700
        hover:border-orange-300">
        <i class="fas fa-map-marker-alt text-[10px]"></i>
        {{ $arena->name }}   {{-- ← dalam tag, bukan attribute --}}
    </span>
</label>
@endforeach

{{-- Fallback jika arena kosong --}}
@if($arenas->isEmpty())
<p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
    <i class="fas fa-exclamation-triangle mr-1"></i>
    Belum ada arena. <a href="{{ route('admin.arenas.create') }}" class="underline font-bold">Tambah arena dulu</a>
</p>
@endif
                    @error('arena_id')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @error('lapangan')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-600 mb-1 block">
                        Waktu Mulai <span class="text-red-400">*</span>
                    </label>
                    <input type="datetime-local" name="start_time"
                        value="{{ old('start_time') }}"
                        class="input input-bordered w-full focus:outline-orange-400 @error('start_time') input-error @enderror">
                    @error('start_time')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-600 mb-1 block">
                        Durasi per Atlet (menit) <span class="text-red-400">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="range" name="duration_per_athlete" id="durationRange"
                            min="1" max="15" value="{{ old('duration_per_athlete', 4) }}"
                            class="range range-xs range-orange flex-1"
                            oninput="document.getElementById('durationVal').textContent = this.value">
                        <span id="durationVal"
                            class="w-8 text-center font-black text-orange-600 text-lg">
                            {{ old('duration_per_athlete', 4) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Geser untuk pilih 1–15 menit</p>
                </div>
            </div>
        </div>

        {{-- Estimasi (realtime) --}}
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4" id="estimasiBox">
            <p class="text-xs font-bold text-orange-600 uppercase tracking-wider mb-2">
                <i class="fas fa-calculator mr-1"></i> Estimasi Sesi
            </p>
            <p class="text-sm text-orange-700">
                Pilih kategori dan waktu mulai untuk melihat estimasi.
            </p>
        </div>

        {{-- Catatan --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <label class="text-sm font-semibold text-gray-600 mb-1 block">Catatan (opsional)</label>
            <textarea name="notes" rows="2"
                placeholder="Catatan untuk sesi ini..."
                class="textarea textarea-bordered w-full focus:outline-orange-400 text-sm">{{ old('notes') }}</textarea>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.sessions.index') }}" class="btn btn-ghost text-gray-500 btn-sm">Batal</a>
            <button type="submit"
                class="btn btn-sm bg-orange-500 hover:bg-orange-600 text-white border-none gap-2">
                <i class="fas fa-save"></i>
                Buat Sesi & Generate Atlet
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
// Estimasi realtime sederhana (hanya info, atlet dari DB)
const startInput = document.querySelector('[name=start_time]');
const durationInput = document.getElementById('durationRange');

function updateEstimasi() {
    const start = startInput.value;
    const dur   = parseInt(durationInput.value);
    const box   = document.getElementById('estimasiBox');

    if (!start) {
        box.innerHTML = `<p class="text-xs font-bold text-orange-600 uppercase tracking-wider mb-2"><i class="fas fa-calculator mr-1"></i> Estimasi Sesi</p><p class="text-sm text-orange-700">Pilih waktu mulai untuk melihat estimasi.</p>`;
        return;
    }

    const startDate = new Date(start);
    box.innerHTML = `
        <p class="text-xs font-bold text-orange-600 uppercase tracking-wider mb-2">
            <i class="fas fa-calculator mr-1"></i> Estimasi Sesi
        </p>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div>
                <p class="text-xs text-orange-500">Mulai</p>
                <p class="font-black text-orange-700">${startDate.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'})}</p>
            </div>
            <div>
                <p class="text-xs text-orange-500">Per Atlet</p>
                <p class="font-black text-orange-700">${dur} menit</p>
            </div>
            <div>
                <p class="text-xs text-orange-500">Selesai*</p>
                <p class="font-black text-orange-700">tergantung jumlah atlet</p>
            </div>
        </div>
        <p class="text-xs text-orange-400 mt-2">* Estimasi akan dihitung setelah atlet di-generate</p>
    `;
}

startInput?.addEventListener('input', updateEstimasi);
durationInput?.addEventListener('input', updateEstimasi);
</script>
@endpush
