import './bootstrap';
import 'htmx.org';
        function confirmDelete(name) {
            return confirm('Are you sure you want to delete age category "' + name + '"?\nThis action cannot be undone.');
        }
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ─── Flash helper ───────────────────── */
function showFlash(type, msg) {
    const el = document.getElementById('dc-flash');
    el.className = `dc-alert dc-alert--${type}`;
    const icon = type === 'success'
        ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'
        : '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>';
    el.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">${icon}</svg>${escHtml(msg)}`;
    el.style.display = 'flex';
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    setTimeout(() => { el.style.display = 'none'; }, 4500);
}
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ─── SHOW DETAIL ────────────────────── */
document.querySelectorAll('.btn-show').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        const body = document.getElementById('showModalBody');
        body.innerHTML = '<div class="dc-loading-state" style="padding:2rem 0;"><div class="dc-spinner"></div></div>';
        new bootstrap.Modal(document.getElementById('showModal')).show();

        fetch(`/admin/disciplines/${id}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(res => {
            if (res.status !== 'success') { body.innerHTML = `<p class="text-danger">${res.message}</p>`; return; }
            const d = res.data;

            const typeBadge = d.type === 'performance'
                ? `<span class="dc-type dc-type--performance">Performance</span>`
                : d.type === 'duel'
                    ? `<span class="dc-type dc-type--duel">Duel</span>`
                    : (d.type ?? '—');

            const matchBadge = d.match_type
                ? `<span class="dc-match dc-match--${d.match_type}">${d.match_type.charAt(0).toUpperCase()+d.match_type.slice(1)}</span>`
                : '—';

            const ageList = d.age_categories?.length
                ? d.age_categories.map(a => `<span class="dc-chip dc-chip--sport" style="margin:2px;">${escHtml(a.name)}</span>`).join('')
                : '—';

            const statusBadge = d.is_active
                ? `<span class="dc-status dc-status--active">Aktif</span>`
                : `<span class="dc-status dc-status--inactive">Nonaktif</span>`;

            body.innerHTML = `
                <table class="dc-detail-table">
                    <tr><th>Nama</th><td><strong>${escHtml(d.name)}</strong></td></tr>
                    <tr><th>Sport</th><td>${d.sport ? `<span class="dc-chip dc-chip--sport">${escHtml(d.sport.name)}</span>` : '—'}</td></tr>
                    <tr><th>Type</th><td>${typeBadge}</td></tr>
                    <tr><th>Match Type</th><td>${matchBadge}</td></tr>
                    <tr><th>Deskripsi</th><td>${d.description ? escHtml(d.description) : '<span style="color:#D1D5DB;">—</span>'}</td></tr>
                    <tr><th>Kategori Usia</th><td>${ageList}</td></tr>
                    <tr><th>Status</th><td>${statusBadge}</td></tr>
                </table>`;
        })
        .catch(() => { body.innerHTML = '<p style="color:#DC2626;padding:1rem;">Gagal memuat data.</p>'; });
    });
});

/* ─── EDIT ───────────────────────────── */
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function () {
        const d = JSON.parse(this.dataset.discipline);
        document.getElementById('editModalBody').innerHTML = buildEditForm(d);
        new bootstrap.Modal(document.getElementById('editModal')).show();
    });
});

function buildEditForm(d) {
    const sports        = @json($sports);
    const ageCategories = @json($ageCategories);
    const selectedAges  = d.age_categories ?? [];

    const sportOpts = sports.map(s =>
        `<option value="${s.id}" ${d.sport_id == s.id ? 'selected' : ''}>${escHtml(s.name)}</option>`
    ).join('');

    const ageOpts = ageCategories.map(a =>
        `<option value="${a.id}" ${selectedAges.includes(a.id) ? 'selected' : ''}>${escHtml(a.name)}</option>`
    ).join('');

    const matchTypes = ['sanda','sparring','solo'];
    const typeOpts   = ['performance','duel'];

    return `
        <input type="hidden" id="editId" value="${d.id}">
        <div class="dc-form-row">
            <div class="dc-form-group">
                <label class="dc-form-label">Nama <span class="dc-required">*</span></label>
                <input type="text" id="editName" class="dc-input" value="${escHtml(d.name)}">
            </div>
            <div class="dc-form-group">
                <label class="dc-form-label">Sport <span class="dc-required">*</span></label>
                <select id="editSportId" class="dc-input dc-input--select">
                    <option value="">— Pilih Sport —</option>${sportOpts}
                </select>
            </div>
        </div>
        <div class="dc-form-row">
            <div class="dc-form-group">
                <label class="dc-form-label">Type <span class="dc-required">*</span></label>
                <select id="editType" class="dc-input dc-input--select">
                    ${typeOpts.map(t => `<option value="${t}" ${d.type===t?'selected':''}>${t.charAt(0).toUpperCase()+t.slice(1)}</option>`).join('')}
                </select>
            </div>
            <div class="dc-form-group">
                <label class="dc-form-label">Match Type</label>
                <select id="editMatchType" class="dc-input dc-input--select">
                    <option value="">— Pilih —</option>
                    ${matchTypes.map(t => `<option value="${t}" ${d.match_type===t?'selected':''}>${t.charAt(0).toUpperCase()+t.slice(1)}</option>`).join('')}
                </select>
            </div>
        </div>
        <div class="dc-form-group">
            <label class="dc-form-label">Deskripsi</label>
            <textarea id="editDescription" class="dc-textarea" rows="3">${escHtml(d.description ?? '')}</textarea>
        </div>
        <div class="dc-form-group">
            <label class="dc-form-label">Kategori Usia</label>
            <select id="editAgeCategories" class="dc-input" style="height:auto;padding:6px 10px;" multiple size="5">
                ${ageOpts}
            </select>
            <span class="dc-form-hint">Tahan Ctrl / Cmd untuk pilih lebih dari satu</span>
        </div>
        <div class="dc-form-group">
            <label class="dc-toggle">
                <input type="checkbox" id="editIsActive" ${d.is_active ? 'checked' : ''}>
                <span class="dc-toggle__slider"></span>
                <span class="dc-toggle__label">Aktif</span>
            </label>
        </div>`;
}

function submitEdit() {
    const id  = document.getElementById('editId').value;
    const ids = Array.from(document.getElementById('editAgeCategories').selectedOptions).map(o => o.value);

    fetch(`/admin/disciplines/${id}`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name:              document.getElementById('editName').value,
            sport_id:          document.getElementById('editSportId').value,
            type:              document.getElementById('editType').value,
            match_type:        document.getElementById('editMatchType').value,
            description:       document.getElementById('editDescription').value,
            is_active:         document.getElementById('editIsActive').checked,
            age_category_ids:  ids,
        })
    })
    .then(r => r.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('editModal'))?.hide();
        showFlash(data.status === 'success' ? 'success' : 'error', data.message);
        if (data.status === 'success') setTimeout(() => location.reload(), 1200);
    })
    .catch(() => showFlash('error', 'Terjadi kesalahan. Silakan coba lagi.'));
}

/* ─── CREATE ─────────────────────────── */
function submitCreate() {
    const form = document.getElementById('createForm');
    const ids  = Array.from(form.querySelectorAll('[name="age_category_ids[]"] option:checked')).map(o => o.value);

    fetch('/admin/disciplines', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name:             form.querySelector('[name=name]').value,
            sport_id:         form.querySelector('[name=sport_id]').value,
            type:             form.querySelector('[name=type]').value,
            match_type:       form.querySelector('[name=match_type]').value,
            description:      form.querySelector('[name=description]').value,
            is_active:        form.querySelector('[name=is_active]')?.checked ?? true,
            age_category_ids: ids,
        })
    })
    .then(r => r.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('createModal'))?.hide();
        showFlash(data.status === 'success' ? 'success' : 'error', data.message);
        if (data.status === 'success') setTimeout(() => location.reload(), 1200);
    })
    .catch(() => showFlash('error', 'Terjadi kesalahan. Silakan coba lagi.'));
}

/* ─── DELETE ─────────────────────────── */
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function () {
        if (!confirm(`Hapus discipline "${this.dataset.name}"?\nAksi ini tidak dapat dibatalkan.`)) return;

        fetch(`/admin/disciplines/${this.dataset.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            showFlash(data.status === 'success' ? 'success' : 'error', data.message);
            if (data.status === 'success') setTimeout(() => location.reload(), 1200);
        })
        .catch(() => showFlash('error', 'Terjadi kesalahan. Silakan coba lagi.'));
    });
});
