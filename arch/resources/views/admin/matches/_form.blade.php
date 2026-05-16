{{-- @foreach ($fields as $field)
    <div class="mb-3">
        <label class="form-label">{{ $field['label'] }}</label>
        <input type="{{ $field['type'] ?? 'text' }}"
            class="form-control {{ $errors->has($field['name']) ? 'is-invalid' : '' }}" name="{{ $field['name'] }}"
            value="{{ old($field['name']) }}" {{ $field['required'] ?? '' }}>
        @error($field['name'])
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endforeach --}}
<form id="createMatchForm">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Event <span class="text-danger">*</span></label>
            <select name="event_id" class="form-select" required>
                <option value="">-- Pilih Event --</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                        {{ $event->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Disiplin <span class="text-danger">*</span></label>
            <select name="discipline_id" class="form-select" required>
                <option value="">-- Pilih Disiplin --</option>
                @foreach ($disciplines as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Kategori Usia <span class="text-danger">*</span></label>
            <select name="age_category_id" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                @foreach ($ageCategories as $ac)
                    <option value="{{ $ac->id }}">{{ $ac->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Arena</label>
            <select name="arena_id" class="form-select">
                <option value="">-- Pilih Arena --</option>
                @foreach ($arenas as $arena)
                    <option value="{{ $arena->id }}">{{ $arena->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Atlet 1 <span class="text-danger">*</span></label>
            <select name="athlete1_id" class="form-select" required>
                <option value="">-- Pilih Atlet --</option>
                @foreach ($athletes as $athlete)
                    <option value="{{ $athlete->id }}">{{ $athlete->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Atlet 2 <small class="text-muted">(kosong = BYE)</small></label>
            <select name="athlete2_id" class="form-select">
                <option value="">-- Pilih Atlet --</option>
                @foreach ($athletes as $athlete)
                    <option value="{{ $athlete->id }}">{{ $athlete->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Ronde <span class="text-danger">*</span></label>
            <select name="round" class="form-select" required>
                <option value="pool">Pool</option>
                <option value="quarter_final">Quarter Final</option>
                <option value="semi_final">Semi Final</option>
                <option value="final">Final</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal</label>
            <input type="date" name="match_date" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Waktu</label>
            <input type="time" name="match_time" class="form-control">
        </div>
    </div>
</form>
