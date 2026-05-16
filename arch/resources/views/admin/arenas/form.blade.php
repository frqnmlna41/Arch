{{-- resources/views/admin/arenas/_form.blade.php --}}
{{-- Digunakan oleh create.blade.php dan edit.blade.php --}}

@php $arena = $arena ?? null; @endphp

{{-- Nama & Status --}}
<div class="bg-white border border-gray-100 rounded-xl p-5 space-y-4">
    <p class="font-bold text-gray-700 text-sm uppercase tracking-wider">Informasi Arena</p>

    <div>
        <label class="text-sm font-semibold text-gray-600 mb-1 block">
            Nama Arena <span class="text-red-400">*</span>
        </label>
        <input type="text" name="name"
            value="{{ old('name', $arena?->name) }}"
            placeholder="Contoh: Gelanggang Olahraga Serbaguna"
            class="input input-bordered w-full focus:outline-orange-400 @error('name') input-error @enderror">
        @error('name')
            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-gray-600 mb-1 block">Lokasi / Alamat</label>
        <input type="text" name="location"
            value="{{ old('location', $arena?->location) }}"
            placeholder="Contoh: Jl. Sudirman No. 1, Jakarta"
            class="input input-bordered w-full focus:outline-orange-400 @error('location') input-error @enderror">
        @error('location')
            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-semibold text-gray-600 mb-1 block">Kapasitas (orang)</label>
            <input type="number" name="capacity"
                value="{{ old('capacity', $arena?->capacity) }}"
                placeholder="0"
                min="0"
                class="input input-bordered w-full focus:outline-orange-400 @error('capacity') input-error @enderror">
            @error('capacity')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-600 mb-2 block">Status</label>
            <div class="flex gap-3">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="is_active" value="1" class="peer hidden"
                        {{ old('is_active', $arena?->is_active ?? 1) == 1 ? 'checked' : '' }}>
                    <div class="border-2 border-gray-200 rounded-xl p-3 text-center transition-all
                        peer-checked:border-green-400 peer-checked:bg-green-50">
                        <i class="fas fa-check-circle text-green-400 text-lg mb-1"></i>
                        <p class="text-xs font-bold text-gray-700">Aktif</p>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="is_active" value="0" class="peer hidden"
                        {{ old('is_active', $arena?->is_active ?? 1) == 0 ? 'checked' : '' }}>
                    <div class="border-2 border-gray-200 rounded-xl p-3 text-center transition-all
                        peer-checked:border-gray-400 peer-checked:bg-gray-50">
                        <i class="fas fa-times-circle text-gray-400 text-lg mb-1"></i>
                        <p class="text-xs font-bold text-gray-700">Non-aktif</p>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <div>
        <label class="text-sm font-semibold text-gray-600 mb-1 block">Deskripsi (opsional)</label>
        <textarea name="description" rows="3"
            placeholder="Keterangan tambahan tentang arena ini..."
            class="textarea textarea-bordered w-full focus:outline-orange-400 text-sm @error('description') textarea-error @enderror">{{ old('description', $arena?->description) }}</textarea>
        @error('description')
            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Fasilitas (opsional, jika ada di model) --}}
@if(isset($arena) && $arena->facilities)
<div class="bg-white border border-gray-100 rounded-xl p-5">
    <p class="font-bold text-gray-700 text-sm uppercase tracking-wider mb-3">Fasilitas</p>
    <div class="flex flex-wrap gap-2">
        @foreach(['Parkir', 'Toilet', 'Kantin', 'Tribun', 'AC', 'Sound System', 'Pencahayaan'] as $fas)
        <label class="cursor-pointer">
            <input type="checkbox" name="facilities[]" value="{{ $fas }}" class="peer hidden"
                {{ in_array($fas, (array) old('facilities', $arena?->facilities ?? [])) ? 'checked' : '' }}>
            <span class="inline-block px-3 py-1.5 text-xs font-semibold border-2 border-gray-200 rounded-lg
                peer-checked:border-orange-400 peer-checked:bg-orange-50 peer-checked:text-orange-700
                hover:border-orange-300 transition-all cursor-pointer">
                {{ $fas }}
            </span>
        </label>
        @endforeach
    </div>
</div>
@endif