@extends('layouts.coach')

@section('title', 'My Invoices')

@section('content')
    <div class="inv-page">

        {{-- ══════════════════════════════════════
            PAGE HEADER
        ══════════════════════════════════════ --}}
        <div class="inv-header">
            <div class="inv-header__left">
                <div class="inv-header__badge">BILLING</div>
                <h1 class="inv-header__title">My Invoices<span class="inv-header__dot">.</span></h1>
                <p class="inv-header__sub">Registration invoices issued to you by the administrator.</p>
            </div>
        </div>

        {{-- ══════════════════════════════════════
            STAT CARDS
        ══════════════════════════════════════ --}}
        <div class="inv-stats">
            <div class="inv-stat-card inv-stat-card--blue">
                <div class="inv-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                </div>
                <div class="inv-stat-card__body">
                    <span class="inv-stat-card__label">Total Invoices</span>
                    <span class="inv-stat-card__value">{{ $stats['total'] }}</span>
                </div>
            </div>
            <div class="inv-stat-card inv-stat-card--purple">
                <div class="inv-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13" />
                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                    </svg>
                </div>
                <div class="inv-stat-card__body">
                    <span class="inv-stat-card__label">Awaiting Payment</span>
                    <span class="inv-stat-card__value">{{ $stats['sent'] }}</span>
                </div>
            </div>
            <div class="inv-stat-card inv-stat-card--green">
                <div class="inv-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>
                <div class="inv-stat-card__body">
                    <span class="inv-stat-card__label">Paid</span>
                    <span class="inv-stat-card__value">{{ $stats['paid'] }}</span>
                </div>
            </div>
            <div class="inv-stat-card inv-stat-card--red">
                <div class="inv-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                </div>
                <div class="inv-stat-card__body">
                    <span class="inv-stat-card__label">Overdue</span>
                    <span class="inv-stat-card__value">{{ $stats['overdue'] }}</span>
                </div>
            </div>
            <div class="inv-stat-card inv-stat-card--teal">
                <div class="inv-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23" />
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </div>
                <div class="inv-stat-card__body">
                    <span class="inv-stat-card__label">Total Billed</span>
                    <span class="inv-stat-card__value inv-stat-card__value--sm">
                        Rp {{ number_format($stats['total_billed'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Overdue Banner --}}
        @if ($stats['overdue'] > 0)
            <div class="inv-overdue-banner">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <div>
                    <strong>You have {{ $stats['overdue'] }} overdue invoice{{ $stats['overdue'] > 1 ? 's' : '' }}.</strong>
                    Please contact the administrator to arrange payment as soon as possible.
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════
            FILTER BAR
        ══════════════════════════════════════ --}}
        <div class="inv-filter-card">
            <form method="GET" action="{{ route('coach.invoices.index') }}" class="inv-filter-form" id="filterForm">
                <div class="inv-filter-group">
                    <label class="inv-filter-label">Search</label>
                    <div class="inv-input-icon">
                        <svg class="inv-input-icon__icon" width="15" height="15" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <input type="text" name="search" class="inv-input" placeholder="Invoice number…"
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="inv-filter-group">
                    <label class="inv-filter-label">Status</label>
                    <select name="status" class="inv-input inv-input--select"
                        onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled
                        </option>
                    </select>
                </div>
                <div class="inv-filter-actions">
                    <button type="submit" class="inv-btn inv-btn--primary inv-btn--sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('coach.invoices.index') }}" class="inv-btn inv-btn--ghost inv-btn--sm">Reset</a>
                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════
            FLASH MESSAGES
        ══════════════════════════════════════ --}}
        @if (session('success'))
            <div class="inv-alert inv-alert--success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- ══════════════════════════════════════
            TABLE
        ══════════════════════════════════════ --}}
        <div class="inv-table-card">
            <div class="inv-table-header">
                <span class="inv-table-header__count">
                    Showing <strong>{{ $invoices->firstItem() }}–{{ $invoices->lastItem() }}</strong> of
                    <strong>{{ $invoices->total() }}</strong> invoices
                </span>
                <div class="inv-table-header__perpage">
                    <label>Rows:</label>
                    <select
                        onchange="window.location='?per_page='+this.value+'&{{ http_build_query(request()->except('per_page', 'page')) }}'">
                        @foreach ([10, 15, 25, 50] as $pp)
                            <option value="{{ $pp }}" {{ request('per_page', 15) == $pp ? 'selected' : '' }}>
                                {{ $pp }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="inv-table-wrap">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th class="inv-table__th inv-table__th--num">#</th>
                            <th class="inv-table__th">Invoice</th>
                            <th class="inv-table__th inv-table__th--center">Athletes</th>
                            <th class="inv-table__th inv-table__th--right">Amount</th>
                            <th class="inv-table__th">Due Date</th>
                            <th class="inv-table__th inv-table__th--center">Status</th>
                            <th class="inv-table__th inv-table__th--center">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $i => $invoice)
                            <!-- <tr
                                class="inv-table__row {{ $invoice->status === 'sent' && $invoice->due_date->isPast() ? 'inv-table__row--overdue' : '' }}">
                                 -->
                                <tr class="inv-table__row {{ $invoice->status === 'sent' && $invoice->due_date?->isPast() ? 'inv-table__row--overdue' : '' }}">
                                <td class="inv-table__td inv-table__td--num">{{ $invoices->firstItem() + $i }}</td>

                                {{-- Invoice Info --}}
                                <td class="inv-table__td">
                                    <div class="inv-invoice-cell">
                                        <a href="{{ route('coach.invoices.show', $invoice) }}"
                                            class="inv-invoice-cell__number">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                        <span class="inv-invoice-cell__date">
                                            Issued {{ $invoice->created_at->format('d M Y') }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Athletes --}}
                                <td class="inv-table__td inv-table__td--center">
                                    <span class="inv-badge">{{ $invoice->items_count }}</span>
                                </td>

                                {{-- Amount --}}
                                <td class="inv-table__td inv-table__td--right">
                                    <span class="inv-amount">Rp
                                        {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                </td>

                                {{-- Due Date --}}
                                   <td class="inv-table__td">
        @php $isOverdue = $invoice->status === 'sent' && $invoice->due_date?->isPast(); @endphp
        <span class="{{ $isOverdue ? 'inv-overdue' : 'inv-date' }}">
            @if ($isOverdue)
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
            @endif
            {{ $invoice->due_date?->format('d M Y') ?? '-' }}
        </span>
    </td>

                                {{-- Status --}}
                                <td class="inv-table__td inv-table__td--center">
                                    @switch($invoice->status)
                                        @case('draft')
                                            <span class="inv-status inv-status--draft">Draft</span>
                                        @break

                                        @case('sent')
                                            <span class="inv-status inv-status--sent">Awaiting Payment</span>
                                        @break

                                        @case('paid')
                                            <span class="inv-status inv-status--paid">Paid</span>
                                        @break

                                        @case('cancelled')
                                            <span class="inv-status inv-status--cancelled">Cancelled</span>
                                        @break
                                    @endswitch
                                </td>

                                {{-- View Only --}}
                                <td class="inv-table__td inv-table__td--center">
                                    <a href="{{ route('coach.invoices.show', $invoice) }}"
                                        class="inv-action-btn inv-action-btn--view" title="View Invoice">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        View
                                    </a>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="inv-table__empty">
                                        <div class="inv-empty-state">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.5">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                <polyline points="14 2 14 8 20 8" />
                                            </svg>
                                            <p>No invoices have been issued to you yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($invoices->hasPages())
                    <div class="inv-pagination">
                        {{ $invoices->appends(request()->query())->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>

        </div>
    @endsection

    @push('styles')
        <style>
            .inv-page {
                padding: 2rem;
                max-width: 1200px;
                margin: 0 auto;
            }

            .inv-header {
                margin-bottom: 1.75rem;
            }

            .inv-header__badge {
                display: inline-block;
                font-size: .625rem;
                font-weight: 700;
                letter-spacing: .12em;
                text-transform: uppercase;
                color: #6366f1;
                background: #eef2ff;
                border-radius: 4px;
                padding: 3px 8px;
                margin-bottom: .5rem;
            }

            .inv-header__title {
                font-size: 2.25rem;
                font-weight: 800;
                letter-spacing: -.03em;
                color: #0f172a;
                margin: 0 0 .25rem;
                line-height: 1;
            }

            .inv-header__dot {
                color: #6366f1;
            }

            .inv-header__sub {
                color: #64748b;
                font-size: .875rem;
                margin: 0;
            }

            /* Stats */
            .inv-stats {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                gap: 1rem;
                margin-bottom: 1.25rem;
            }

            @media (max-width: 900px) {
                .inv-stats {
                    grid-template-columns: repeat(3, 1fr);
                }
            }

            @media (max-width: 600px) {
                .inv-stats {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            .inv-stat-card {
                display: flex;
                align-items: center;
                gap: .875rem;
                padding: 1rem 1.125rem;
                border-radius: 12px;
                border: 1px solid transparent;
            }

            .inv-stat-card__icon {
                flex-shrink: 0;
                width: 42px;
                height: 42px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .inv-stat-card__label {
                display: block;
                font-size: .68rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .08em;
            }

            .inv-stat-card__value {
                display: block;
                font-size: 1.6rem;
                font-weight: 800;
                letter-spacing: -.02em;
                line-height: 1.1;
            }

            .inv-stat-card__value--sm {
                font-size: .95rem;
            }

            .inv-stat-card--blue {
                background: #eff6ff;
                border-color: #bfdbfe;
            }

            .inv-stat-card--blue .inv-stat-card__icon {
                background: #dbeafe;
                color: #1d4ed8;
            }

            .inv-stat-card--blue .inv-stat-card__label {
                color: #1d4ed8;
            }

            .inv-stat-card--blue .inv-stat-card__value {
                color: #1e40af;
            }

            .inv-stat-card--purple {
                background: #f5f3ff;
                border-color: #ddd6fe;
            }

            .inv-stat-card--purple .inv-stat-card__icon {
                background: #ede9fe;
                color: #7c3aed;
            }

            .inv-stat-card--purple .inv-stat-card__label {
                color: #7c3aed;
            }

            .inv-stat-card--purple .inv-stat-card__value {
                color: #6d28d9;
            }

            .inv-stat-card--green {
                background: #f0fdf4;
                border-color: #bbf7d0;
            }

            .inv-stat-card--green .inv-stat-card__icon {
                background: #dcfce7;
                color: #16a34a;
            }

            .inv-stat-card--green .inv-stat-card__label {
                color: #16a34a;
            }

            .inv-stat-card--green .inv-stat-card__value {
                color: #15803d;
            }

            .inv-stat-card--red {
                background: #fff1f2;
                border-color: #fecdd3;
            }

            .inv-stat-card--red .inv-stat-card__icon {
                background: #ffe4e6;
                color: #dc2626;
            }

            .inv-stat-card--red .inv-stat-card__label {
                color: #dc2626;
            }

            .inv-stat-card--red .inv-stat-card__value {
                color: #b91c1c;
            }

            .inv-stat-card--teal {
                background: #f0fdfa;
                border-color: #99f6e4;
            }

            .inv-stat-card--teal .inv-stat-card__icon {
                background: #ccfbf1;
                color: #0d9488;
            }

            .inv-stat-card--teal .inv-stat-card__label {
                color: #0d9488;
            }

            .inv-stat-card--teal .inv-stat-card__value {
                color: #0f766e;
            }

            /* Overdue Banner */
            .inv-overdue-banner {
                display: flex;
                align-items: flex-start;
                gap: .875rem;
                padding: .875rem 1.125rem;
                background: #fff1f2;
                border: 1px solid #fecdd3;
                border-radius: 10px;
                color: #b91c1c;
                font-size: .875rem;
                margin-bottom: 1.25rem;
            }

            .inv-overdue-banner svg {
                flex-shrink: 0;
                margin-top: 1px;
            }

            /* Filter */
            .inv-filter-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 1.125rem 1.25rem;
                margin-bottom: 1.25rem;
            }

            .inv-filter-form {
                display: flex;
                align-items: flex-end;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .inv-filter-group {
                display: flex;
                flex-direction: column;
                gap: .35rem;
                flex: 1;
                min-width: 160px;
            }

            .inv-filter-label {
                font-size: .7rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .08em;
                color: #94a3b8;
            }

            .inv-filter-actions {
                display: flex;
                gap: .5rem;
                align-items: center;
            }

            .inv-input {
                height: 38px;
                padding: 0 .75rem;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                font-size: .875rem;
                color: #0f172a;
                background: #f8fafc;
                transition: border-color .15s, box-shadow .15s;
                width: 100%;
            }

            .inv-input:focus {
                outline: none;
                border-color: #818cf8;
                box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
                background: #fff;
            }

            .inv-input--select {
                cursor: pointer;
            }

            .inv-input-icon {
                position: relative;
            }

            .inv-input-icon .inv-input {
                padding-left: 2.25rem;
            }

            .inv-input-icon__icon {
                position: absolute;
                left: .75rem;
                top: 50%;
                transform: translateY(-50%);
                color: #94a3b8;
                pointer-events: none;
            }

            /* Buttons */
            .inv-btn {
                display: inline-flex;
                align-items: center;
                gap: .5rem;
                padding: .5rem 1.125rem;
                border-radius: 8px;
                font-size: .875rem;
                font-weight: 600;
                text-decoration: none;
                border: none;
                cursor: pointer;
                transition: all .15s;
                white-space: nowrap;
            }

            .inv-btn--primary {
                background: #6366f1;
                color: #fff;
            }

            .inv-btn--primary:hover {
                background: #4f46e5;
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

            .inv-btn--sm {
                padding: .375rem .875rem;
                font-size: .8rem;
            }

            /* Alert */
            .inv-alert {
                display: flex;
                align-items: center;
                gap: .625rem;
                padding: .75rem 1rem;
                border-radius: 8px;
                font-size: .875rem;
                margin-bottom: 1rem;
            }

            .inv-alert--success {
                background: #f0fdf4;
                color: #16a34a;
                border: 1px solid #bbf7d0;
            }

            /* Table */
            .inv-table-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                overflow: hidden;
            }

            .inv-table-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1rem 1.25rem;
                border-bottom: 1px solid #f1f5f9;
            }

            .inv-table-header__count {
                font-size: .8rem;
                color: #64748b;
            }

            .inv-table-header__perpage {
                display: flex;
                align-items: center;
                gap: .5rem;
                font-size: .8rem;
                color: #64748b;
            }

            .inv-table-header__perpage select {
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 2px 6px;
                font-size: .8rem;
            }

            .inv-table-wrap {
                overflow-x: auto;
            }

            .inv-table {
                width: 100%;
                border-collapse: collapse;
            }

            .inv-table__th {
                padding: .7rem 1rem;
                font-size: .7rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .08em;
                color: #94a3b8;
                background: #f8fafc;
                border-bottom: 1px solid #e2e8f0;
                white-space: nowrap;
                text-align: left;
            }

            .inv-table__th--num {
                width: 48px;
            }

            .inv-table__th--center {
                text-align: center;
            }

            .inv-table__th--right {
                text-align: right;
            }

            .inv-table__td {
                padding: .875rem 1rem;
                font-size: .875rem;
                color: #334155;
                border-bottom: 1px solid #f1f5f9;
                vertical-align: middle;
            }

            .inv-table__td--num {
                color: #94a3b8;
                font-size: .8rem;
            }

            .inv-table__td--center {
                text-align: center;
            }

            .inv-table__td--right {
                text-align: right;
            }

            .inv-table__row:hover {
                background: #f8fafc;
            }

            .inv-table__row--overdue {
                background: #fff9f9;
            }

            .inv-table__row:last-child .inv-table__td {
                border-bottom: none;
            }

            .inv-table__empty {
                text-align: center;
                padding: 3rem 1rem;
            }

            .inv-invoice-cell {
                display: flex;
                flex-direction: column;
                gap: 2px;
            }

            .inv-invoice-cell__number {
                font-weight: 700;
                color: #4f46e5;
                font-size: .875rem;
                text-decoration: none;
                font-family: 'Courier New', monospace;
                letter-spacing: .02em;
            }

            .inv-invoice-cell__number:hover {
                text-decoration: underline;
            }

            .inv-invoice-cell__date {
                font-size: .75rem;
                color: #94a3b8;
            }

            .inv-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 24px;
                height: 24px;
                padding: 0 7px;
                background: #f1f5f9;
                color: #475569;
                border-radius: 20px;
                font-size: .75rem;
                font-weight: 600;
            }

            .inv-amount {
                font-weight: 700;
                color: #0f172a;
                font-variant-numeric: tabular-nums;
            }

            .inv-date {
                font-size: .8rem;
                color: #64748b;
            }

            .inv-overdue {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                font-size: .8rem;
                color: #dc2626;
                font-weight: 600;
            }

            .inv-status {
                display: inline-flex;
                align-items: center;
                padding: 4px 10px;
                border-radius: 20px;
                font-size: .7rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .06em;
            }

            .inv-status--draft {
                background: #f1f5f9;
                color: #475569;
            }

            .inv-status--sent {
                background: #ede9fe;
                color: #7c3aed;
            }

            .inv-status--paid {
                background: #dcfce7;
                color: #16a34a;
            }

            .inv-status--cancelled {
                background: #fff1f2;
                color: #dc2626;
            }

            .inv-action-btn {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 5px 12px;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
                background: transparent;
                color: #64748b;
                font-size: .78rem;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
                transition: all .15s;
            }

            .inv-action-btn--view:hover {
                background: #eff6ff;
                color: #1d4ed8;
                border-color: #bfdbfe;
            }

            .inv-empty-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: .75rem;
                color: #cbd5e1;
            }

            .inv-empty-state p {
                margin: 0;
                color: #94a3b8;
                font-size: .875rem;
            }

            .inv-pagination {
                padding: 1rem 1.25rem;
                border-top: 1px solid #f1f5f9;
            }
        </style>
    @endpush
