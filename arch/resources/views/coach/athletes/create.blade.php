@extends('layouts.coach')

@section('content')
    <div class="ath-page">

        {{-- ══════════════════════════════════════
            PAGE HEADER
        ══════════════════════════════════════ --}}
        <div class="ath-header">
            <div class="ath-header__left">
                <div class="ath-header__badge">ATHLETE REGISTRY</div>
                <h1 class="ath-header__title">Create Athlete<span class="ath-header__dot">.</span></h1>
                <p class="ath-header__sub">Register a new athlete and assign their disciplines.</p>
            </div>
            <div class="ath-header__right">
                <a href="{{ route('coach.athletes.index') }}" class="ath-btn ath-btn--ghost">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    Back to Athletes
                </a>
            </div>
        </div>

        {{-- ══════════════════════════════════════
            FLASH MESSAGES
        ══════════════════════════════════════ --}}
        @if (session('success'))
            <div class="ath-alert ath-alert--success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="ath-alert ath-alert--error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="15" y1="9" x2="9" y2="15" />
                    <line x1="9" y1="9" x2="15" y2="15" />
                </svg>
                Please fix the errors below before submitting.
            </div>
        @endif

        {{-- ══════════════════════════════════════
            FORM
        ══════════════════════════════════════ --}}
        <form action="{{ route('coach.athletes.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="ath-form-grid">

                {{-- LEFT COLUMN --}}
                <div class="ath-form-col">

                    {{-- BASIC INFO CARD --}}
                    <div class="ath-table-card">
                        <div class="ath-form-card-header">
                            <div class="ath-form-card-header__icon ath-form-card-header__icon--blue">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </div>
                            <span>Basic Information</span>
                        </div>

                        <div class="ath-form-body">

                            {{-- Name --}}
                            <div class="ath-field">
                                <label class="ath-field__label">
                                    Name <span class="ath-field__required">*</span>
                                </label>
                                <input type="text" name="name"
                                    class="ath-input @error('name') ath-input--error @enderror"
                                    placeholder="Full athlete name…" value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="ath-field__error">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Birth Date --}}
                            <div class="ath-field">
                                <label class="ath-field__label">
                                    Birth Date <span class="ath-field__required">*</span>
                                </label>
                                <input type="date" name="birth_date"
                                    class="ath-input @error('birth_date') ath-input--error @enderror"
                                    value="{{ old('birth_date') }}" required>
                                @error('birth_date')
                                    <span class="ath-field__error">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Gender --}}
                            <div class="ath-field">
                                <label class="ath-field__label">Gender</label>
                                <select name="gender" class="ath-input ath-input--select">
                                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>
                                        Male
                                    </option>
                                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>
                                        Female
                                    </option>
                                </select>
                            </div>

                            {{-- Club --}}
                            {{-- <div class="ath-field">
                                <label class="ath-field__label">Club</label>
                                <input type="text" name="club"
                                    class="ath-input @error('club') ath-input--error @enderror" placeholder="Club name…"
                                    value="{{ old('club') }}">
                                @error('club')
                                    <span class="ath-field__error">{{ $message }}</span>
                                @enderror
                            </div> --}}
                            <div class="ath-field">
                                <label class="ath-field__label">Perguruan</label>

                                @if ($perguruan)
                                    {{-- Tampilkan nama, kirim id via hidden input --}}
                                    <input type="text" class="ath-input" value="{{ $perguruan->name }}" readonly
                                        style="background: #f3f4f6; cursor: not-allowed;">
                                    <input type="hidden" name="perguruan_id" value="{{ $perguruan->id }}">
                                    <span style="font-size:0.78rem; color:#6b7280; margin-top:0.2rem;">
                                        Otomatis dari akun coach Anda.
                                    </span>
                                @else
                                    <input type="text" class="ath-input ath-input--error" value="Perguruan belum diatur"
                                        readonly>
                                    <span class="ath-field__error">
                                        Akun Anda belum memiliki perguruan. Hubungi admin.
                                    </span>
                                @endif
                            </div>
                            {{-- Photo --}}
                            <div class="ath-field">
                                <label class="ath-field__label">Photo</label>
                                <div class="ath-file-upload" id="dropzone">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5" class="ath-file-upload__icon">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <polyline points="21 15 16 10 5 21" />
                                    </svg>
                                    <p class="ath-file-upload__text">
                                        <span class="ath-file-upload__link">Click to upload</span> or drag & drop
                                    </p>
                                    <p class="ath-file-upload__hint">PNG, JPG, WEBP up to 2MB</p>
                                    <input type="file" name="photo" id="photoInput" class="ath-file-upload__input"
                                        accept="image/*">
                                </div>
                                <div id="photoPreview" class="ath-photo-preview" style="display:none;">
                                    <img id="previewImg" src="" alt="Preview">
                                    <button type="button" id="removePhoto" class="ath-photo-preview__remove">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5">
                                            <line x1="18" y1="6" x2="6" y2="18" />
                                            <line x1="6" y1="6" x2="18" y2="18" />
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                                @error('photo')
                                    <span class="ath-field__error">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="ath-form-col">

                    {{-- DISCIPLINES CARD --}}
                    <div class="ath-table-card">
                        <div class="ath-form-card-header">
                            <div class="ath-form-card-header__icon ath-form-card-header__icon--green">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z" />
                                    <path d="M2 17l10 5 10-5" />
                                    <path d="M2 12l10 5 10-5" />
                                </svg>
                            </div>
                            <span>Disciplines</span>
                        </div>

                        <div class="ath-form-body">

                            <div id="discipline-wrapper">

                                <div class="ath-discipline-item">

                                    <div class="ath-field">
                                        <label class="ath-field__label">
                                            Discipline <span class="ath-field__required">*</span>
                                        </label>

                                        <select name="disciplines[0][discipline_id]" class="ath-input" required>
                                            <option value="">— Select Discipline —</option>
                                            @foreach ($disciplines as $discipline)
                                                <option value="{{ $discipline->id }}"
                                                    data-sport="{{ $discipline->sport_id }}">
                                                    {{ $discipline->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="ath-field">
                                        <label class="ath-field__label">
                                            Age Category <span class="ath-field__required">*</span>
                                        </label>

                                        <select name="disciplines[0][age_category_id]" class="ath-input" required>
                                            <option value="">— Select Age Category —</option>
                                            @foreach ($ageCategories as $category)
                                                <option value="{{ $category->id }}"
                                                    data-sport="{{ $category->sport_id }}">
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>

                            </div>

                            {{-- <button type="button" id="add-discipline"
                                class="ath-btn ath-btn--ghost ath-btn--sm ath-btn--full">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                Add Another Discipline
                            </button> --}}
                            <button type="button" id="add-discipline" class="ath-btn ath-btn--ghost ath-btn--full">
                                + Add Discipline
                            </button>
                        </div>
                    </div>

                    {{-- SUBMIT --}}
                    <div class="ath-form-actions">
                        <a href="{{ route('coach.athletes.index') }}" class="ath-btn ath-btn--ghost">Cancel</a>
                        <button type="submit" class="ath-btn ath-btn--primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                            Save Athlete
                        </button>
                    </div>

                </div>
            </div>

        </form>
    </div>

    {{-- ══════════════════════════════════════
        PAGE-SPECIFIC STYLES
    ══════════════════════════════════════ --}}
    @push('styles')
        <style>
            /* ── Form Layout ── */
            .ath-form-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
                align-items: start;
            }

            @media (max-width: 900px) {
                .ath-form-grid {
                    grid-template-columns: 1fr;
                }
            }

            .ath-form-col {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            /* ── Card Header ── */
            .ath-form-card-header {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1.1rem 1.5rem;
                border-bottom: 1px solid var(--ath-border, #e5e7eb);
                font-weight: 600;
                font-size: 0.9rem;
                letter-spacing: 0.01em;
            }

            .ath-form-card-header__icon {
                width: 34px;
                height: 34px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .ath-form-card-header__icon--blue {
                background: #eff6ff;
                color: #2563eb;
            }

            .ath-form-card-header__icon--green {
                background: #f0fdf4;
                color: #16a34a;
            }

            /* ── Form Body ── */
            .ath-form-body {
                padding: 1.5rem;
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
            }

            /* ── Field ── */
            .ath-field {
                display: flex;
                flex-direction: column;
                gap: 0.4rem;
            }

            .ath-field__label {
                font-size: 0.82rem;
                font-weight: 600;
                color: var(--ath-text-secondary, #6b7280);
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .ath-field__required {
                color: #ef4444;
            }

            .ath-field__error {
                font-size: 0.8rem;
                color: #ef4444;
                margin-top: 0.2rem;
            }

            .ath-input--error {
                border-color: #ef4444 !important;
            }

            /* ── File Upload ── */
            .ath-file-upload {
                border: 2px dashed var(--ath-border, #e5e7eb);
                border-radius: 10px;
                padding: 2rem 1rem;
                text-align: center;
                cursor: pointer;
                position: relative;
                transition: border-color 0.2s, background 0.2s;
            }

            .ath-file-upload:hover {
                border-color: #2563eb;
                background: #eff6ff;
            }

            .ath-file-upload__icon {
                color: var(--ath-text-muted, #9ca3af);
                margin: 0 auto 0.75rem;
            }

            .ath-file-upload__text {
                font-size: 0.875rem;
                color: var(--ath-text-secondary, #6b7280);
                margin: 0 0 0.25rem;
            }

            .ath-file-upload__link {
                color: #2563eb;
                font-weight: 600;
            }

            .ath-file-upload__hint {
                font-size: 0.78rem;
                color: var(--ath-text-muted, #9ca3af);
                margin: 0;
            }

            .ath-file-upload__input {
                position: absolute;
                inset: 0;
                opacity: 0;
                cursor: pointer;
                width: 100%;
                height: 100%;
            }

            /* ── Photo Preview ── */
            .ath-photo-preview {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 0.75rem 1rem;
                background: #f9fafb;
                border-radius: 10px;
                border: 1px solid #e5e7eb;
            }

            .ath-photo-preview img {
                width: 60px;
                height: 60px;
                object-fit: cover;
                border-radius: 8px;
                border: 2px solid #e5e7eb;
            }

            .ath-photo-preview__remove {
                display: flex;
                align-items: center;
                gap: 0.35rem;
                font-size: 0.8rem;
                color: #ef4444;
                font-weight: 600;
                background: none;
                border: none;
                cursor: pointer;
                padding: 0;
            }

            .ath-photo-preview__remove:hover {
                text-decoration: underline;
            }

            /* ── Discipline Item ── */
            .ath-discipline-item {
                border: 1px solid var(--ath-border, #e5e7eb);
                border-radius: 10px;
                padding: 1rem;
                margin-bottom: 1rem;
                background: var(--ath-row-hover, #f9fafb);
                display: flex;
                flex-direction: column;
                gap: 1rem;
                transition: border-color 0.2s;
            }

            .ath-discipline-item:hover {
                border-color: #2563eb44;
            }

            .ath-discipline-item__header {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .ath-discipline-item__num {
                display: flex;
                align-items: center;
                gap: 0.4rem;
                font-size: 0.8rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #2563eb;
            }

            /* ── Full-width button variant ── */
            .ath-btn--full {
                width: 100%;
                justify-content: center;
            }

            /* ── Form Actions ── */
            .ath-form-actions {
                display: flex;
                gap: 0.75rem;
                justify-content: flex-end;
            }
        </style>
    @endpush

    {{-- ══════════════════════════════════════
        JS
    ══════════════════════════════════════ --}}
    @push('scripts')
        <script>
            let disciplineIndex = 1;

            /*
            |--------------------------------------------------------------------------
            | FILTER AGE CATEGORY BERDASARKAN SPORT
            |--------------------------------------------------------------------------
            */
            document.addEventListener('change', function(e) {

                if (!e.target.name.includes('discipline_id')) return;

                let disciplineSelect = e.target;
                let selectedOption = disciplineSelect.options[disciplineSelect.selectedIndex];

                let sportId = selectedOption.getAttribute('data-sport');
                if (!sportId) return;

                let parent = disciplineSelect.closest('.ath-discipline-item');
                let ageSelect = parent.querySelector('select[name*="age_category_id"]');

                ageSelect.value = "";

                Array.from(ageSelect.options).forEach(option => {

                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = option.getAttribute('data-sport') !== sportId;
                });
            });


            /*
            |--------------------------------------------------------------------------
            | ADD DISCIPLINE (DYNAMIC)
            |--------------------------------------------------------------------------
            */
            document.getElementById('add-discipline').addEventListener('click', function() {

                const wrapper = document.getElementById('discipline-wrapper');

                const html = `
    <div class="ath-discipline-item">

        <div class="ath-field">
            <label class="ath-field__label">Discipline</label>
            <select name="disciplines[${disciplineIndex}][discipline_id]" class="ath-input" required>
                <option value="">— Select Discipline —</option>
                @foreach ($disciplines as $discipline)
                    <option value="{{ $discipline->id }}"
                        data-sport="{{ $discipline->sport_id }}">
                        {{ $discipline->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ath-field">
            <label class="ath-field__label">Age Category</label>
            <select name="disciplines[${disciplineIndex}][age_category_id]" class="ath-input" required>
                <option value="">— Select Age Category —</option>
                @foreach ($ageCategories as $category)
                    <option value="{{ $category->id }}"
                        data-sport="{{ $category->sport_id }}">
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="button" class="remove-item">Remove</button>

    </div>
    `;

                wrapper.insertAdjacentHTML('beforeend', html);
                disciplineIndex++;
            });


            /*
            |--------------------------------------------------------------------------
            | REMOVE DISCIPLINE
            |--------------------------------------------------------------------------
            */
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-item')) {

                    const items = document.querySelectorAll('.ath-discipline-item');

                    if (items.length > 1) {
                        e.target.closest('.ath-discipline-item').remove();
                    }
                }
            });


            /*
            |--------------------------------------------------------------------------
            | INIT FILTER (AUTO APPLY)
            |--------------------------------------------------------------------------
            */
            document.querySelectorAll('.ath-discipline-item').forEach(item => {
                let select = item.querySelector('select[name*="discipline_id"]');
                if (select.value) {
                    select.dispatchEvent(new Event('change'));
                }
            });
        </script>
    @endpush
@endsection
