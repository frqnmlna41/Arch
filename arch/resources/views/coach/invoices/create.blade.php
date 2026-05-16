@extends('layouts.admin')

@section('title', isset($invoice) ? 'Edit Invoice ' . $invoice->invoice_number : 'Create Invoice')

@section('content')
    <div class="inv-page">

        {{-- ══════════════════════════════════════
            PAGE HEADER
        ══════════════════════════════════════ --}}
        <div class="inv-header">
            <div class="inv-header__left">
                <div class="inv-header__breadcrumb">
                    <a href="{{ route('admin.invoices.index') }}" class="inv-header__breadcrumb-link">Invoices</a>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                    <span>{{ isset($invoice) ? 'Edit' : 'Create' }}</span>
                </div>
                <div class="inv-header__badge">{{ isset($invoice) ? 'EDIT INVOICE' : 'NEW INVOICE' }}</div>
                <h1 class="inv-header__title">
                    {{ isset($invoice) ? $invoice->invoice_number : 'New Invoice' }}<span
                        class="inv-header__dot">.</span>
                </h1>
                <p class="inv-header__sub">
                    {{ isset($invoice) ? 'Update due date or notes.' : 'Create a registration invoice for a coach.' }}
                </p>
            </div>
        </div>

        {{-- ══════════════════════════════════════
            FORM
        ══════════════════════════════════════ --}}
        <form method="POST"
            action="{{ isset($invoice) ? route('admin.invoices.update', $invoice) : route('admin.invoices.store') }}"
            id="invoiceForm">
            @csrf
            @if (isset($invoice))
                @method('PUT')
            @endif

            <div class="inv-form-grid">
                {{-- ── LEFT: Main Form ──────────────────────────────── --}}
                <div class="inv-form-main">

                    {{-- Coach Selection (create only) --}}
                    @unless(isset($invoice))
                        <div class="inv-form-card">
                            <div class="inv-form-card__header">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                Select Coach
                            </div>
                            <div class="inv-form-card__body">
                                <div class="inv-form-row">
                                    <div class="inv-form-group">
                                        <label class="inv-form-label" for="coach_id">Coach <span
                                                class="inv-form-required">*</span></label>
                                        <select name="coach_id" id="coach_id"
                                            class="inv-input inv-input--select @error('coach_id') is-invalid @enderror"
                                            onchange="loadCoachData(this.value)" required>
                                            <option value="">— Select a coach —</option>
                                            @foreach ($coaches as $coach)
                                                <option value="{{ $coach->id }}"
                                                    {{ (old('coach_id') ?? $selectedCoach?->id) == $coach->id ? 'selected' : '' }}>
                                                    {{ $coach->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('coach_id')
                                            <p class="inv-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endunless

                    {{-- Invoice Details --}}
                    <div class="inv-form-card">
                        <div class="inv-form-card__header">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            Invoice Details
                        </div>
                        <div class="inv-form-card__body">
                            <div class="inv-form-row inv-form-row--2">
                                <div class="inv-form-group">
                                    <label class="inv-form-label" for="due_date">Due Date <span
                                            class="inv-form-required">*</span></label>
                                    <input type="date" name="due_date" id="due_date"
                                        class="inv-input @error('due_date') is-invalid @enderror"
                                        value="{{ old('due_date', isset($invoice) ? $invoice->due_date->format('Y-m-d') : '') }}"
                                        min="{{ now()->addDay()->format('Y-m-d') }}" required>
                                    @error('due_date')
                                        <p class="inv-form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="inv-form-row">
                                <div class="inv-form-group">
                                    <label class="inv-form-label" for="notes">Notes</label>
                                    <textarea name="notes" id="notes" rows="4"
                                        class="inv-input inv-input--textarea @error('notes') is-invalid @enderror"
                                        placeholder="Additional notes for the coach…">{{ old('notes', $invoice->notes ?? '') }}</textarea>
                                    @error('notes')
                                        <p class="inv-form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Items (create only) --}}
                    @unless(isset($invoice))
                        <div class="inv-form-card" id="itemsSection" style="{{ $selectedCoach ? '' : 'display:none' }}">
                            <div class="inv-form-card__header">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <line x1="8" y1="6" x2="21" y2="6" />
                                    <line x1="8" y1="12" x2="21" y2="12" />
                                    <line x1="8" y1="18" x2="21" y2="18" />
                                    <line x1="3" y1="6" x2="3.01" y2="6" />
                                    <line x1="3" y1="12" x2="3.01" y2="12" />
                                    <line x1="3" y1="18" x2="3.01" y2="18" />
                                </svg>
                                Invoice Items
                                <span class="inv-form-card__header-hint">Minimum 1 item required</span>
                            </div>
                            <div class="inv-form-card__body">

                                @error('items')
                                    <div class="inv-alert inv-alert--error" style="margin-bottom:1rem">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10" />
                                            <line x1="15" y1="9" x2="9" y2="15" />
                                            <line x1="9" y1="9" x2="15" y2="15" />
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror

                                {{-- Items List --}}
                                <div id="itemsList" class="inv-items-list">
                                    {{-- Populated dynamically --}}
                                </div>

                                {{-- Add Item Row --}}
                                <div class="inv-add-row" id="addItemRow">
                                    <div class="inv-add-row__fields">
                                        <div class="inv-form-group">
                                            <label class="inv-form-label">Athlete</label>
                                            <select id="newAthlete" class="inv-input inv-input--select">
                                                <option value="">Select athlete…</option>
                                                @foreach ($athletes as $athlete)
                                                    <option value="{{ $athlete->id }}"
                                                        data-name="{{ $athlete->name }}">
                                                        {{ $athlete->name }}
                                                        @if ($athlete->perguruan)
                                                            ({{ $athlete->perguruan->name }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="inv-form-group">
                                            <label class="inv-form-label">Event Category</label>
                                            <select id="newCategory" class="inv-input inv-input--select">
                                                <option value="">Select category…</option>
                                                @foreach ($eventCategories as $cat)
                                                    <option value="{{ $cat->id }}"
                                                        data-name="{{ $cat->event->name ?? '' }} — {{ $cat->discipline->name ?? '' }} ({{ $cat->ageCategory->name ?? '' }})">
                                                        {{ $cat->event->name ?? '—' }} / {{ $cat->discipline->name ?? '—' }}
                                                        / {{ $cat->ageCategory->name ?? '—' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="inv-form-group inv-form-group--sm">
                                            <label class="inv-form-label">Price (Rp)</label>
                                            <input type="number" id="newPrice" class="inv-input" min="0"
                                                step="1000" placeholder="0">
                                        </div>
                                        <div class="inv-form-group inv-form-group--action">
                                            <button type="button" onclick="addItem()"
                                                class="inv-btn inv-btn--secondary">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2.5">
                                                    <line x1="12" y1="5" x2="12" y2="19" />
                                                    <line x1="5" y1="12" x2="19" y2="12" />
                                                </svg>
                                                Add
                                            </button>
                                        </div>
                                    </div>
                                    <p id="addItemError" class="inv-form-error" style="display:none"></p>
                                </div>
                            </div>
                        </div>
                    @endunless

                </div>

                {{-- ── RIGHT: Summary ───────────────────────────────── --}}
                <div class="inv-form-aside">
                    <div class="inv-summary-card">
                        <div class="inv-summary-card__header">Summary</div>
                        <div class="inv-summary-rows" id="summaryRows">
                            <div class="inv-summary-row">
                                <span>Items</span>
                                <strong id="summaryItemCount">0</strong>
                            </div>
                            <div class="inv-summary-row">
                                <span>Athletes</span>
                                <strong id="summaryAthleteCount">0</strong>
                            </div>
                        </div>
                        <div class="inv-summary-total">
                            <span>Total</span>
                            <span id="summaryTotal">Rp 0</span>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="inv-form-submit-card">
                        <button type="submit" class="inv-btn inv-btn--primary inv-btn--block" id="submitBtn">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z" />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                            {{ isset($invoice) ? 'Save Changes' : 'Create Invoice' }}
                        </button>
                        <a href="{{ isset($invoice) ? route('admin.invoices.show', $invoice) : route('admin.invoices.index') }}"
                            class="inv-btn inv-btn--ghost inv-btn--block">
                            Cancel
                        </a>
                    </div>

                    {{-- Tips --}}
                    <div class="inv-tips-card">
                        <div class="inv-tips-card__title">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            Tips
                        </div>
                        <ul class="inv-tips-list">
                            <li>Each invoice is for one coach only.</li>
                            <li>Each athlete + category can only appear once per invoice.</li>
                            <li>Invoice can only be edited while in <strong>Draft</strong> status.</li>
                            <li>Once sent, coach will be notified by email.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>

    </div>
@endsection

@push('styles')
    <style>
        .inv-page {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ── HEADER ───────────────────────────────────────────── */
        .inv-header {
            margin-bottom: 1.75rem;
        }

        .inv-header__breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            color: #94a3b8;
            margin-bottom: 0.5rem;
        }

        .inv-header__breadcrumb-link {
            color: #6366f1;
            text-decoration: none;
        }

        .inv-header__breadcrumb-link:hover {
            text-decoration: underline;
        }

        .inv-header__badge {
            display: inline-block;
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6366f1;
            background: #eef2ff;
            border-radius: 4px;
            padding: 3px 8px;
            margin-bottom: 0.4rem;
        }

        .inv-header__title {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
            margin: 0 0 0.25rem;
            line-height: 1;
        }

        .inv-header__dot {
            color: #6366f1;
        }

        .inv-header__sub {
            color: #64748b;
            font-size: 0.875rem;
            margin: 0;
        }

        /* ── FORM GRID ────────────────────────────────────────── */
        .inv-form-grid {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 1.25rem;
            align-items: start;
        }

        @media (max-width: 900px) {
            .inv-form-grid {
                grid-template-columns: 1fr;
            }
        }

        .inv-form-main {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .inv-form-aside {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        /* ── FORM CARD ────────────────────────────────────────── */
        .inv-form-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .inv-form-card__header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.25rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        .inv-form-card__header-hint {
            margin-left: auto;
            font-size: 0.7rem;
            font-weight: 400;
            color: #94a3b8;
            text-transform: none;
            letter-spacing: 0;
        }

        .inv-form-card__body {
            padding: 1.25rem;
        }

        .inv-form-row {
            margin-bottom: 1rem;
        }

        .inv-form-row:last-child {
            margin-bottom: 0;
        }

        .inv-form-row--2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* ── FORM ELEMENTS ────────────────────────────────────── */
        .inv-form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            flex: 1;
        }

        .inv-form-group--sm {
            max-width: 150px;
        }

        .inv-form-group--action {
            flex: 0;
            min-width: auto;
            justify-content: flex-end;
        }

        .inv-form-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        .inv-form-required {
            color: #ef4444;
        }

        .inv-form-error {
            font-size: 0.75rem;
            color: #dc2626;
            margin: 0.35rem 0 0;
        }

        .inv-input {
            height: 38px;
            padding: 0 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #0f172a;
            background: #f8fafc;
            transition: border-color 0.15s, box-shadow 0.15s;
            width: 100%;
        }

        .inv-input:focus {
            outline: none;
            border-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            background: #fff;
        }

        .inv-input.is-invalid {
            border-color: #f87171;
        }

        .inv-input--textarea {
            height: auto;
            padding: 0.625rem 0.75rem;
            resize: vertical;
        }

        .inv-input--select {
            cursor: pointer;
        }

        /* ── ITEMS LIST ───────────────────────────────────────── */
        .inv-items-list {
            margin-bottom: 1rem;
        }

        .inv-item-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .inv-item-row__info {
            flex: 1;
            min-width: 0;
        }

        .inv-item-row__name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .inv-item-row__cat {
            font-size: 0.75rem;
            color: #64748b;
        }

        .inv-item-row__price {
            font-size: 0.875rem;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .inv-item-row__remove {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid #fecdd3;
            background: transparent;
            color: #ef4444;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.15s;
        }

        .inv-item-row__remove:hover {
            background: #fff1f2;
        }

        /* ── ADD ITEM ROW ─────────────────────────────────────── */
        .inv-add-row {
            padding: 0.875rem;
            background: #f0fdf4;
            border: 1px dashed #86efac;
            border-radius: 8px;
        }

        .inv-add-row__fields {
            display: flex;
            gap: 0.75rem;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        /* ── ALERT ────────────────────────────────────────────── */
        .inv-alert {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .inv-alert--error {
            background: #fff1f2;
            color: #dc2626;
            border: 1px solid #fecdd3;
        }

        /* ── BUTTONS ──────────────────────────────────────────── */
        .inv-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1.125rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
        }

        .inv-btn--primary {
            background: #6366f1;
            color: #fff;
        }

        .inv-btn--primary:hover {
            background: #4f46e5;
        }

        .inv-btn--secondary {
            background: #0f172a;
            color: #fff;
        }

        .inv-btn--secondary:hover {
            background: #1e293b;
        }

        .inv-btn--ghost {
            background: transparent;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .inv-btn--ghost:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .inv-btn--block {
            width: 100%;
        }

        .inv-btn--sm {
            padding: 0.375rem 0.875rem;
            font-size: 0.8rem;
        }

        /* ── SUMMARY CARD ─────────────────────────────────────── */
        .inv-summary-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .inv-summary-card__header {
            padding: 0.875rem 1.25rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        .inv-summary-rows {
            padding: 0.875rem 1.25rem;
        }

        .inv-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            font-size: 0.875rem;
            color: #64748b;
            border-bottom: 1px solid #f1f5f9;
        }

        .inv-summary-row:last-child {
            border-bottom: none;
        }

        .inv-summary-row strong {
            color: #0f172a;
            font-weight: 700;
        }

        .inv-summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            background: #0f172a;
            color: #fff;
            font-weight: 800;
            font-size: 1rem;
        }

        /* ── SUBMIT CARD ──────────────────────────────────────── */
        .inv-form-submit-card {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        /* ── TIPS CARD ────────────────────────────────────────── */
        .inv-tips-card {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 1rem;
        }

        .inv-tips-card__title {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #b45309;
            margin-bottom: 0.625rem;
        }

        .inv-tips-list {
            margin: 0;
            padding: 0 0 0 1.125rem;
            font-size: 0.78rem;
            color: #78350f;
            line-height: 1.6;
        }

        .inv-tips-list li {
            margin-bottom: 0.25rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // ── Item management (create form only) ──────────────
        const items = [];

        function formatRp(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        }

        function updateSummary() {
            document.getElementById('summaryItemCount').textContent = items.length;
            const uniqueAthletes = [...new Set(items.map(i => i.athleteId))].length;
            document.getElementById('summaryAthleteCount').textContent = uniqueAthletes;
            const total = items.reduce((s, i) => s + Number(i.price), 0);
            document.getElementById('summaryTotal').textContent = formatRp(total);
        }

        function renderItems() {
            const list = document.getElementById('itemsList');
            if (!list) return;

            list.innerHTML = items.map((item, idx) => `
                <div class="inv-item-row">
                    <div class="inv-item-row__info">
                        <div class="inv-item-row__name">${item.athleteName}</div>
                        <div class="inv-item-row__cat">${item.categoryName}</div>
                    </div>
                    <div class="inv-item-row__price">${formatRp(item.price)}</div>
                    <button type="button" class="inv-item-row__remove" onclick="removeItem(${idx})">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                    <input type="hidden" name="items[${idx}][athlete_id]" value="${item.athleteId}">
                    <input type="hidden" name="items[${idx}][event_category_id]" value="${item.categoryId}">
                    <input type="hidden" name="items[${idx}][price]" value="${item.price}">
                </div>
            `).join('');

            updateSummary();
        }

        function addItem() {
            const athleteSel = document.getElementById('newAthlete');
            const catSel = document.getElementById('newCategory');
            const priceInput = document.getElementById('newPrice');
            const errorEl = document.getElementById('addItemError');

            const athleteId = athleteSel.value;
            const categoryId = catSel.value;
            const price = priceInput.value;

            errorEl.style.display = 'none';

            if (!athleteId) {
                errorEl.textContent = 'Please select an athlete.';
                errorEl.style.display = 'block';
                return;
            }
            if (!categoryId) {
                errorEl.textContent = 'Please select an event category.';
                errorEl.style.display = 'block';
                return;
            }
            if (!price || price < 0) {
                errorEl.textContent = 'Please enter a valid price.';
                errorEl.style.display = 'block';
                return;
            }

            // Duplicate check
            const dup = items.find(i => i.athleteId === athleteId && i.categoryId === categoryId);
            if (dup) {
                errorEl.textContent = 'This athlete + category combination is already added.';
                errorEl.style.display = 'block';
                return;
            }

            items.push({
                athleteId,
                athleteName: athleteSel.options[athleteSel.selectedIndex].dataset.name,
                categoryId,
                categoryName: catSel.options[catSel.selectedIndex].dataset.name,
                price: Number(price),
            });

            // Reset fields
            athleteSel.value = '';
            catSel.value = '';
            priceInput.value = '';

            renderItems();
        }

        function removeItem(idx) {
            items.splice(idx, 1);
            renderItems();
        }

        // Load coach data (for create form — pre-select from URL)
        function loadCoachData(coachId) {
            if (!coachId) {
                document.getElementById('itemsSection').style.display = 'none';
                return;
            }
            window.location.href = '{{ route('admin.invoices.create') }}?coach_id=' + coachId;
        }

        // Init summary on load
        updateSummary();
    </script>
@endpush
