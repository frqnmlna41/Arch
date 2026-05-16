<form id="createForm">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nama <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="Contoh: Sanda Putra" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Sport <span class="text-danger">*</span></label>
            <select name="sport_id" class="form-select" required>
                <option value="">-- Pilih Sport --</option>
                @foreach ($sports as $sport)
                    <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Type <span class="text-danger">*</span></label>
            <select name="type" class="form-select" required>
                <option value="">-- Pilih Type --</option>
                <option value="performance">Performance</option>
                <option value="duel">Duel</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Match Type</label>
            <select name="match_type" class="form-select">
                <option value="">-- Pilih Match Type --</option>
                <option value="sanda">Sanda</option>
                <option value="sparring">Sparring</option>
                <option value="solo">Solo</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi singkat discipline..."></textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Kategori Usia</label>
            <select name="age_category_ids[]" class="form-select" multiple>
                @foreach ($ageCategories as $ac)
                    <option value="{{ $ac->id }}">{{ $ac->name }}</option>
                @endforeach
            </select>
            <small class="text-muted">Tahan Ctrl untuk pilih lebih dari satu</small>
        </div>
        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" id="createIsActive" checked>
                <label class="form-check-label" for="createIsActive">Aktif</label>
            </div>
        </div>
    </div>
</form>
