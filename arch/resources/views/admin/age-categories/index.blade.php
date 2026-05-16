@extends('layouts.admin')

@section('title', 'Age Categories Management')

@section('content')
    <div class="ac-page">

        {{-- ══════════════════════════════════════
        PAGE HEADER
    ══════════════════════════════════════ --}}
        <div class="ac-header">
            <div class="ac-header__left">
                <div class="ac-header__badge">CATEGORY REGISTRY</div>
                <h1 class="ac-header__title">Age Categories<span class="ac-header__dot">.</span></h1>
                <p class="ac-header__sub">Manage all age categories per sport discipline.</p>
            </div>
            <div class="ac-header__right">
                <a href="{{ route('admin.age-categories.create') }}" class="ac-btn ac-btn--primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Add Category
                </a>
            </div>
        </div>

        {{-- ══════════════════════════════════════
        STAT CARDS
    ══════════════════════════════════════ --}}
        <div class="ac-stats">
            <div class="ac-stat-card ac-stat-card--blue">
                <div class="ac-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </div>
                <div class="ac-stat-card__body">
                    <span class="ac-stat-card__label">Total Categories</span>
                    <span class="ac-stat-card__value">{{ $categories->total() }}</span>
                </div>
            </div>
            <div class="ac-stat-card ac-stat-card--green">
                <div class="ac-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>
                <div class="ac-stat-card__body">
                    <span class="ac-stat-card__label">Active</span>
                    <span class="ac-stat-card__value">{{ $categories->where('is_active', true)->count() }}</span>
                </div>
            </div>
            <div class="ac-stat-card ac-stat-card--amber">
                <div class="ac-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <ellipse cx="12" cy="5" rx="9" ry="3" />
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                    </svg>
                </div>
                <div class="ac-stat-card__body">
                    <span class="ac-stat-card__label">Sports</span>
                    <span class="ac-stat-card__value">{{ $sports->count() }}</span>
                </div>
            </div>
            <div class="ac-stat-card ac-stat-card--red">
                <div class="ac-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                    </svg>
                </div>
                <div class="ac-stat-card__body">
                    <span class="ac-stat-card__label">Inactive</span>
                    <span class="ac-stat-card__value">{{ $categories->where('is_active', false)->count() }}</span>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════
        FILTER BAR
    ══════════════════════════════════════ --}}
        <div class="ac-filter-card">
            <form method="GET" action="{{ route('admin.age-categories.index') }}" class="ac-filter-form" id="filterForm">
                <div class="ac-filter-group">
                    <label class="ac-filter-label">Sport</label>
                    <select name="sport_id" class="ac-input ac-input--select">
                        <option value="">All Sports</option>
                        @foreach ($sports as $sport)
                            <option value="{{ $sport->id }}" {{ request('sport_id') == $sport->id ? 'selected' : '' }}>
                                {{ $sport->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ac-filter-group ac-filter-group--check">
                    <label class="ac-toggle">
                        <input type="checkbox" name="active" value="1" {{ request('active') ? 'checked' : '' }}
                            onchange="document.getElementById('filterForm').submit()">
                        <span class="ac-toggle__slider"></span>
                        <span class="ac-toggle__label">Active only</span>
                    </label>
                </div>
                <div class="ac-filter-actions">
                    <button type="submit" class="ac-btn ac-btn--primary ac-btn--sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.age-categories.index') }}" class="ac-btn ac-btn--ghost ac-btn--sm">Reset</a>
                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════
        FLASH MESSAGES
    ══════════════════════════════════════ --}}
        @if (session('success'))
            <div class="ac-alert ac-alert--success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="ac-alert ac-alert--error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="15" y1="9" x2="9" y2="15" />
                    <line x1="9" y1="9" x2="15" y2="15" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- ══════════════════════════════════════
        TABLE
    ══════════════════════════════════════ --}}
        <div class="ac-table-card">
            <div class="ac-table-header">
                <span class="ac-table-header__count">
                    Showing <strong>{{ $categories->firstItem() }}–{{ $categories->lastItem() }}</strong> of
                    <strong>{{ $categories->total() }}</strong> categories
                </span>
                <div class="ac-table-header__perpage">
                    <label>Rows:</label>
                    <select
                        onchange="window.location='?per_page='+this.value+'&{{ http_build_query(request()->except('per_page', 'page')) }}'">
                        @foreach ([10, 15, 25, 50] as $pp)
                            <option value="{{ $pp }}" {{ request('per_page', 15) == $pp ? 'selected' : '' }}>
                                {{ $pp }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead>
                        <tr>
                            <th class="ac-table__th ac-table__th--num">#</th>
                            <th class="ac-table__th">Category Name</th>
                            <th class="ac-table__th">Sport</th>
                            <th class="ac-table__th ac-table__th--center">Min Age</th>
                            <th class="ac-table__th ac-table__th--center">Max Age</th>
                            <th class="ac-table__th ac-table__th--center">Age Range</th>
                            <th class="ac-table__th ac-table__th--center">Disciplines</th>
                            <th class="ac-table__th ac-table__th--center">Status</th>
                            <th class="ac-table__th ac-table__th--center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $i => $category)
                            @php $isOpen = $category->max_age >= 999; @endphp
                            <tr class="ac-table__row">
                                <td class="ac-table__td ac-table__td--num">{{ $categories->firstItem() + $i }}</td>

                                {{-- Category Name --}}
                                <td class="ac-table__td">
                                    <span class="ac-name">{{ $category->name }}</span>
                                </td>

                                {{-- Sport --}}
                                <td class="ac-table__td">
                                    @if ($category->sport)
                                        <span class="ac-chip ac-chip--sport">{{ $category->sport->name }}</span>
                                    @else
                                        <span class="ac-na">—</span>
                                    @endif
                                </td>

                                {{-- Min Age --}}
                                <td class="ac-table__td ac-table__td--center">
                                    {{ $category->min_age }}
                                </td>

                                {{-- Max Age --}}
                                <td class="ac-table__td ac-table__td--center">
                                    @if ($isOpen)
                                        <span class="ac-na">∞</span>
                                    @else
                                        {{ $category->max_age }}
                                    @endif
                                </td>

                                {{-- Age Range --}}
                                <td class="ac-table__td ac-table__td--center">
                                    @if ($isOpen)
                                        <span class="ac-range-badge ac-range-badge--open">{{ $category->min_age }}+</span>
                                    @else
                                        <span class="ac-range-badge">{{ $category->min_age }} –
                                            {{ $category->max_age }}</span>
                                    @endif
                                </td>

                                {{-- Disciplines --}}
                                <td class="ac-table__td ac-table__td--center">
                                    <span class="ac-badge">{{ $category->disciplines_count }}</span>
                                </td>

                                {{-- Status --}}
                                <td class="ac-table__td ac-table__td--center">
                                    @if ($category->is_active)
                                        <span class="ac-status ac-status--active">Active</span>
                                    @else
                                        <span class="ac-status ac-status--inactive">Inactive</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="ac-table__td ac-table__td--center">
                                    <div class="ac-actions">
                                        <a href="{{ route('admin.age-categories.show', $category) }}"
                                            class="ac-action-btn ac-action-btn--view" title="View Detail">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.age-categories.edit', $category) }}"
                                            class="ac-action-btn ac-action-btn--edit" title="Edit">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </a>
                                        <form method="POST"
                                            action="{{ route('admin.age-categories.destroy', $category) }}"
                                            onsubmit="return confirmDelete('{{ addslashes($category->name) }}')"
                                            style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ac-action-btn ac-action-btn--delete"
                                                title="Delete">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6" />
                                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                    <path d="M10 11v6" />
                                                    <path d="M14 11v6" />
                                                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="ac-table__empty">
                                    <div class="ac-empty-state">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                        <p>No age categories found.</p>
                                        <a href="{{ route('admin.age-categories.create') }}"
                                            class="ac-btn ac-btn--primary ac-btn--sm">Add First Category</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($categories->hasPages())
                <div class="ac-pagination">
                    {{ $categories->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            @endif
        </div>

    </div>
@endsection

@push('styles')
    <style>
        :root {
            --ac-blue: #178EF5;
            --ac-blue-bg: #EBF5FF;
            --ac-green: #16A34A;
            --ac-green-bg: #DCFCE7;
            --ac-amber: #D97706;
            --ac-amber-bg: #FEF3C7;
            --ac-red: #DC2626;
            --ac-red-bg: #FEE2E2;
            --ac-border: rgba(0, 0, 0, .08);
            --ac-radius: 10px;
            --ac-radius-sm: 6px;
        }

        .ac-page {
            padding: 1.75rem 2rem;
            max-width: 1400px;
        }

        /* Header */
        .ac-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 1.75rem;
        }

        .ac-header__badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            color: #6B7280;
            background: #F3F4F6;
            border: 1px solid var(--ac-border);
            border-radius: 4px;
            padding: 3px 8px;
            margin-bottom: .5rem;
        }

        .ac-header__title {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -.03em;
            color: #111827;
            margin: 0 0 .25rem;
            line-height: 1;
        }

        .ac-header__dot {
            color: var(--ac-blue);
        }

        .ac-header__sub {
            font-size: .875rem;
            color: #6B7280;
            margin: 0;
        }

        /* Buttons */
        .ac-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: var(--ac-radius-sm);
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all .15s;
        }

        .ac-btn--primary {
            background: var(--ac-blue);
            color: #fff;
        }

        .ac-btn--primary:hover {
            background: #0E7FE0;
        }

        .ac-btn--ghost {
            background: transparent;
            color: #374151;
            border: 1px solid var(--ac-border);
        }

        .ac-btn--ghost:hover {
            background: #F9FAFB;
        }

        .ac-btn--sm {
            padding: 6px 12px;
            font-size: .8125rem;
        }

        /* Stat Cards */
        .ac-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .ac-stat-card {
            display: flex;
            align-items: center;
            gap: 14px;
            background: #fff;
            border: 1px solid var(--ac-border);
            border-radius: var(--ac-radius);
            padding: 1rem 1.25rem;
        }

        .ac-stat-card__icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ac-stat-card--blue .ac-stat-card__icon {
            background: var(--ac-blue-bg);
            color: var(--ac-blue);
        }

        .ac-stat-card--green .ac-stat-card__icon {
            background: var(--ac-green-bg);
            color: var(--ac-green);
        }

        .ac-stat-card--amber .ac-stat-card__icon {
            background: var(--ac-amber-bg);
            color: var(--ac-amber);
        }

        .ac-stat-card--red .ac-stat-card__icon {
            background: var(--ac-red-bg);
            color: var(--ac-red);
        }

        .ac-stat-card__body {
            display: flex;
            flex-direction: column;
        }

        .ac-stat-card__label {
            font-size: .75rem;
            font-weight: 500;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .ac-stat-card__value {
            font-size: 1.625rem;
            font-weight: 800;
            color: #111827;
            line-height: 1.2;
        }

        /* Filter */
        .ac-filter-card {
            background: #fff;
            border: 1px solid var(--ac-border);
            border-radius: var(--ac-radius);
            padding: .875rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        .ac-filter-form {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .ac-filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .ac-filter-group--check {
            flex-direction: row;
            align-items: center;
            padding-top: 18px;
        }

        .ac-filter-label {
            font-size: .75rem;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .ac-filter-actions {
            display: flex;
            align-items: flex-end;
            gap: .5rem;
            padding-top: 18px;
        }

        /* Inputs */
        .ac-input {
            height: 36px;
            border: 1px solid #E5E7EB;
            border-radius: var(--ac-radius-sm);
            padding: 0 10px;
            font-size: .875rem;
            color: #111827;
            background: #fff;
            transition: border-color .15s;
            outline: none;
        }

        .ac-input:focus {
            border-color: var(--ac-blue);
            box-shadow: 0 0 0 3px rgba(23, 142, 245, .1);
        }

        .ac-input--select {
            padding-right: 28px;
            cursor: pointer;
        }

        /* Toggle */
        .ac-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .ac-toggle input {
            display: none;
        }

        .ac-toggle__slider {
            position: relative;
            width: 36px;
            height: 20px;
            background: #D1D5DB;
            border-radius: 999px;
            transition: background .2s;
            flex-shrink: 0;
        }

        .ac-toggle__slider::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            background: #fff;
            border-radius: 50%;
            transition: transform .2s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
        }

        .ac-toggle input:checked~.ac-toggle__slider {
            background: var(--ac-blue);
        }

        .ac-toggle input:checked~.ac-toggle__slider::after {
            transform: translateX(16px);
        }

        .ac-toggle__label {
            font-size: .875rem;
            color: #374151;
            font-weight: 500;
        }

        /* Alert */
        .ac-alert {
            display: flex;
            align-items: center;
            gap: .625rem;
            padding: .75rem 1rem;
            border-radius: var(--ac-radius-sm);
            font-size: .875rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .ac-alert--success {
            background: var(--ac-green-bg);
            color: #15803D;
            border: 1px solid #BBF7D0;
        }

        .ac-alert--error {
            background: var(--ac-red-bg);
            color: #B91C1C;
            border: 1px solid #FECACA;
        }

        /* Table Card */
        .ac-table-card {
            background: #fff;
            border: 1px solid var(--ac-border);
            border-radius: var(--ac-radius);
            overflow: hidden;
        }

        .ac-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .875rem 1.25rem;
            border-bottom: 1px solid var(--ac-border);
        }

        .ac-table-header__count {
            font-size: .8125rem;
            color: #6B7280;
        }

        .ac-table-header__count strong {
            color: #111827;
        }

        .ac-table-header__perpage {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .8125rem;
            color: #6B7280;
        }

        .ac-table-header__perpage select {
            height: 28px;
            padding: 0 8px;
            border: 1px solid #E5E7EB;
            border-radius: 4px;
            font-size: .8125rem;
        }

        .ac-table-wrap {
            overflow-x: auto;
        }

        .ac-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ac-table__th {
            padding: .75rem 1rem;
            text-align: left;
            font-size: .75rem;
            font-weight: 700;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom: 1px solid var(--ac-border);
            white-space: nowrap;
            background: #FAFAFA;
        }

        .ac-table__th--num,
        .ac-table__th--center {
            text-align: center;
        }

        .ac-table__td {
            padding: .75rem 1rem;
            font-size: .875rem;
            color: #374151;
            border-bottom: 1px solid #F3F4F6;
        }

        .ac-table__td--num,
        .ac-table__td--center {
            text-align: center;
        }

        .ac-table__row:hover td {
            background: #F9FAFB;
        }

        .ac-table__row:last-child td {
            border-bottom: none;
        }

        .ac-table__empty {
            padding: 3rem;
            text-align: center;
            color: #9CA3AF;
        }

        /* Chips & Badges */
        .ac-name {
            font-weight: 600;
            color: #111827;
        }

        .ac-chip {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: .75rem;
            font-weight: 600;
        }

        .ac-chip--sport {
            background: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
        }

        .ac-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            padding: 0 6px;
            background: #F3F4F6;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            color: #374151;
        }

        .ac-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
        }

        .ac-status::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .ac-status--active {
            background: var(--ac-green-bg);
            color: #15803D;
        }

        .ac-status--active::before {
            background: #16A34A;
        }

        .ac-status--inactive {
            background: #F3F4F6;
            color: #6B7280;
        }

        .ac-status--inactive::before {
            background: #9CA3AF;
        }

        .ac-range-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: .8125rem;
            font-weight: 700;
            background: #F3F4F6;
            color: #374151;
            font-variant-numeric: tabular-nums;
        }

        .ac-range-badge--open {
            background: #FEF9C3;
            color: #A16207;
        }

        .ac-na {
            color: #D1D5DB;
        }

        /* Actions */
        .ac-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .ac-action-btn {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all .15s;
            background: transparent;
            text-decoration: none;
        }

        .ac-action-btn--view {
            color: #6B7280;
        }

        .ac-action-btn--view:hover {
            background: #F3F4F6;
            color: #111827;
        }

        .ac-action-btn--edit {
            color: #6B7280;
        }

        .ac-action-btn--edit:hover {
            background: #EFF6FF;
            color: var(--ac-blue);
        }

        .ac-action-btn--delete {
            color: #6B7280;
        }

        .ac-action-btn--delete:hover {
            background: #FEF2F2;
            color: var(--ac-red);
        }

        /* Pagination */
        .ac-pagination {
            padding: .875rem 1.25rem;
            border-top: 1px solid var(--ac-border);
        }

        /* Empty state */
        .ac-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .75rem;
            color: #9CA3AF;
            padding: 2rem 0;
        }

        .ac-empty-state p {
            margin: 0;
            font-size: .9375rem;
        }

        @media (max-width:900px) {
            .ac-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width:600px) {
            .ac-page {
                padding: 1rem;
            }

            .ac-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function confirmDelete(name) {
            return confirm('Are you sure you want to delete age category "' + name + '"?\nThis action cannot be undone.');
        }
    </script>
@endpush
