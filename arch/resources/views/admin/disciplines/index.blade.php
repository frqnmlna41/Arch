@extends('layouts.admin')

@section('title', 'Discipline Management')

@section('content')
    <div class="dc-page">

        {{-- ══════════════════════════════════════
        PAGE HEADER
    ══════════════════════════════════════ --}}
        <div class="dc-header">
            <div class="dc-header__left">
                <div class="dc-header__badge">DISCIPLINE REGISTRY</div>
                <h1 class="dc-header__title">Disciplines<span class="dc-header__dot">.</span></h1>
                <p class="dc-header__sub">Kelola semua disiplin olahraga yang tersedia.</p>
            </div>
            <div class="dc-header__right">
                <button class="dc-btn dc-btn--primary" data-bs-toggle="modal" data-bs-target="#createModal">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Tambah Discipline
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════
        STAT CARDS
    ══════════════════════════════════════ --}}
        <div class="dc-stats">
            <div class="dc-stat-card dc-stat-card--blue">
                <div class="dc-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                    </svg>
                </div>
                <div class="dc-stat-card__body">
                    <span class="dc-stat-card__label">Total Disciplines</span>
                    <span class="dc-stat-card__value">{{ $disciplines->total() }}</span>
                </div>
            </div>
            <div class="dc-stat-card dc-stat-card--green">
                <div class="dc-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>
                <div class="dc-stat-card__body">
                    <span class="dc-stat-card__label">Aktif</span>
                    <span class="dc-stat-card__value">{{ $disciplines->where('is_active', true)->count() }}</span>
                </div>
            </div>
            <div class="dc-stat-card dc-stat-card--purple">
                <div class="dc-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <polygon points="10 8 16 12 10 16 10 8" />
                    </svg>
                </div>
                <div class="dc-stat-card__body">
                    <span class="dc-stat-card__label">Performance</span>
                    <span class="dc-stat-card__value">{{ $disciplines->where('type', 'performance')->count() }}</span>
                </div>
            </div>
            <div class="dc-stat-card dc-stat-card--red">
                <div class="dc-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                            d="M14.5 10c-.83 0-1.5-.67-1.5-1.5v-5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5v5c0 .83-.67 1.5-1.5 1.5z" />
                        <path d="M20.5 10H19V8.5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z" />
                        <path
                            d="M9.5 14c.83 0 1.5.67 1.5 1.5v5c0 .83-.67 1.5-1.5 1.5S8 21.33 8 20.5v-5c0-.83.67-1.5 1.5-1.5z" />
                        <path d="M3.5 14H5v1.5c0 .83-.67 1.5-1.5 1.5S2 16.33 2 15.5 2.67 14 3.5 14z" />
                        <path
                            d="M14 14.5c0-.83.67-1.5 1.5-1.5h5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-5c-.83 0-1.5-.67-1.5-1.5z" />
                        <path d="M15.5 19H14v1.5c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5-.67-1.5-1.5-1.5z" />
                        <path
                            d="M10 9.5C10 8.67 9.33 8 8.5 8h-5C2.67 8 2 8.67 2 9.5S2.67 11 3.5 11h5c.83 0 1.5-.67 1.5-1.5z" />
                        <path d="M8.5 5H10V3.5C10 2.67 9.33 2 8.5 2S7 2.67 7 3.5 7.67 5 8.5 5z" />
                    </svg>
                </div>
                <div class="dc-stat-card__body">
                    <span class="dc-stat-card__label">Duel</span>
                    <span class="dc-stat-card__value">{{ $disciplines->where('type', 'duel')->count() }}</span>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════
        FILTER BAR
    ══════════════════════════════════════ --}}
        <div class="dc-filter-card">
            <form method="GET" action="{{ route('admin.disciplines.index') }}" class="dc-filter-form" id="filterForm">
                <div class="dc-filter-group">
                    <label class="dc-filter-label">Cari Nama</label>
                    <div class="dc-input-icon">
                        <svg class="dc-input-icon__icon" width="15" height="15" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <input type="text" name="search" class="dc-input dc-input--icon" placeholder="Nama discipline…"
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="dc-filter-group">
                    <label class="dc-filter-label">Sport</label>
                    <select name="sport_id" class="dc-input dc-input--select">
                        <option value="">Semua Sport</option>
                        @foreach ($sports as $sport)
                            <option value="{{ $sport->id }}" {{ request('sport_id') == $sport->id ? 'selected' : '' }}>
                                {{ $sport->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="dc-filter-group">
                    <label class="dc-filter-label">Type</label>
                    <select name="type" class="dc-input dc-input--select">
                        <option value="">Semua Type</option>
                        <option value="performance" {{ request('type') === 'performance' ? 'selected' : '' }}>Performance
                        </option>
                        <option value="duel" {{ request('type') === 'duel' ? 'selected' : '' }}>Duel</option>
                    </select>
                </div>
                <div class="dc-filter-group">
                    <label class="dc-filter-label">Match Type</label>
                    <select name="match_type" class="dc-input dc-input--select">
                        <option value="">Semua</option>
                        <option value="sanda" {{ request('match_type') === 'sanda' ? 'selected' : '' }}>Sanda</option>
                        <option value="sparring" {{ request('match_type') === 'sparring' ? 'selected' : '' }}>Sparring
                        </option>
                        <option value="solo" {{ request('match_type') === 'solo' ? 'selected' : '' }}>Solo</option>
                    </select>
                </div>
                <div class="dc-filter-group dc-filter-group--check">
                    <label class="dc-toggle">
                        <input type="checkbox" name="active" value="1" {{ request('active') ? 'checked' : '' }}
                            onchange="document.getElementById('filterForm').submit()">
                        <span class="dc-toggle__slider"></span>
                        <span class="dc-toggle__label">Aktif saja</span>
                    </label>
                </div>
                <div class="dc-filter-actions">
                    <button type="submit" class="dc-btn dc-btn--primary dc-btn--sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.disciplines.index') }}" class="dc-btn dc-btn--ghost dc-btn--sm">Reset</a>
                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════
        FLASH MESSAGES
    ══════════════════════════════════════ --}}
        @if (session('success'))
            <div class="dc-alert dc-alert--success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="dc-alert dc-alert--error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="15" y1="9" x2="9" y2="15" />
                    <line x1="9" y1="9" x2="15" y2="15" />
                </svg>
                {{ session('error') }}
            </div>
        @endif
        <div id="dc-flash" class="dc-alert" style="display:none;"></div>

        {{-- ══════════════════════════════════════
        TABLE
    ══════════════════════════════════════ --}}
        <div class="dc-table-card">
            <div class="dc-table-header">
                <span class="dc-table-header__count">
                    Showing <strong>{{ $disciplines->firstItem() }}–{{ $disciplines->lastItem() }}</strong> of
                    <strong>{{ $disciplines->total() }}</strong> disciplines
                </span>
                <div class="dc-table-header__perpage">
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

            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead>
                        <tr>
                            <th class="dc-table__th dc-table__th--num">#</th>
                            <th class="dc-table__th">Nama</th>
                            <th class="dc-table__th">Sport</th>
                            <th class="dc-table__th dc-table__th--center">Type</th>
                            <th class="dc-table__th dc-table__th--center">Match Type</th>
                            <th class="dc-table__th dc-table__th--center">Kat. Usia</th>
                            <th class="dc-table__th dc-table__th--center">Status</th>
                            <th class="dc-table__th dc-table__th--center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($disciplines as $i => $discipline)
                            <tr class="dc-table__row">
                                <td class="dc-table__td dc-table__td--num">{{ $disciplines->firstItem() + $i }}</td>

                                {{-- Nama --}}
                                <td class="dc-table__td">
                                    <span class="dc-name">{{ $discipline->name }}</span>
                                    @if ($discipline->description)
                                        <span class="dc-desc">{{ Str::limit($discipline->description, 60) }}</span>
                                    @endif
                                </td>

                                {{-- Sport --}}
                                <td class="dc-table__td">
                                    @if ($discipline->sport)
                                        <span class="dc-chip dc-chip--sport">{{ $discipline->sport->name }}</span>
                                    @else
                                        <span class="dc-na">—</span>
                                    @endif
                                </td>

                                {{-- Type --}}
                                <td class="dc-table__td dc-table__td--center">
                                    @if ($discipline->type === 'performance')
                                        <span class="dc-type dc-type--performance">Performance</span>
                                    @elseif($discipline->type === 'duel')
                                        <span class="dc-type dc-type--duel">Duel</span>
                                    @else
                                        <span class="dc-na">—</span>
                                    @endif
                                </td>

                                {{-- Match Type --}}
                                <td class="dc-table__td dc-table__td--center">
                                    @if ($discipline->match_type)
                                        <span class="dc-match dc-match--{{ $discipline->match_type }}">
                                            {{ ucfirst($discipline->match_type) }}
                                        </span>
                                    @else
                                        <span class="dc-na">—</span>
                                    @endif
                                </td>

                                {{-- Kategori Usia --}}
                                <td class="dc-table__td dc-table__td--center">
                                    <span class="dc-badge">{{ $discipline->age_categories_count }}</span>
                                </td>

                                {{-- Status --}}
                                <td class="dc-table__td dc-table__td--center">
                                    @if ($discipline->is_active)
                                        <span class="dc-status dc-status--active">Aktif</span>
                                    @else
                                        <span class="dc-status dc-status--inactive">Nonaktif</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="dc-table__td dc-table__td--center">
                                    <div class="dc-actions">
                                        <button class="dc-action-btn dc-action-btn--view btn-show"
                                            data-id="{{ $discipline->id }}" title="Detail">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </button>
                                        <button class="dc-action-btn dc-action-btn--edit btn-edit"
                                            data-discipline="{{ json_encode([
                                                'id' => $discipline->id,
                                                'name' => $discipline->name,
                                                'sport_id' => $discipline->sport_id,
                                                'type' => $discipline->type,
                                                'match_type' => $discipline->match_type,
                                                'description' => $discipline->description,
                                                'is_active' => $discipline->is_active,
                                                'age_categories' => $discipline->ageCategories->pluck('id'),
                                            ]) }}"
                                            title="Edit">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </button>
                                        <button class="dc-action-btn dc-action-btn--delete btn-delete"
                                            data-id="{{ $discipline->id }}" data-name="{{ $discipline->name }}"
                                            title="Hapus">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="dc-table__empty">
                                    <div class="dc-empty-state">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                                        </svg>
                                        <p>Belum ada discipline.</p>
                                        <button class="dc-btn dc-btn--primary dc-btn--sm" data-bs-toggle="modal"
                                            data-bs-target="#createModal">
                                            Tambah Sekarang
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($disciplines->hasPages())
                <div class="dc-pagination">
                    {{ $disciplines->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            @endif
        </div>

    </div>

    {{-- ══════════════════════════════════════
    MODAL CREATE
══════════════════════════════════════ --}}
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="dc-modal-content">
                <div class="dc-modal__header">
                    <h2 class="dc-modal__title">Tambah Discipline</h2>
                    <button class="dc-modal__close" data-bs-dismiss="modal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>
                <div class="dc-modal__body">
                    @include('admin.disciplines._form', ['mode' => 'create'])
                </div>
                <div class="dc-modal__footer">
                    <button class="dc-btn dc-btn--ghost" data-bs-dismiss="modal">Batal</button>
                    <button class="dc-btn dc-btn--primary" onclick="submitCreate()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
    MODAL EDIT
══════════════════════════════════════ --}}
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="dc-modal-content">
                <div class="dc-modal__header">
                    <h2 class="dc-modal__title">Edit Discipline</h2>
                    <button class="dc-modal__close" data-bs-dismiss="modal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>
                <div class="dc-modal__body" id="editModalBody">
                    <div class="dc-loading-state" style="padding:2rem 0;">
                        <div class="dc-spinner"></div>
                    </div>
                </div>
                <div class="dc-modal__footer">
                    <button class="dc-btn dc-btn--ghost" data-bs-dismiss="modal">Batal</button>
                    <button class="dc-btn dc-btn--primary" onclick="submitEdit()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        Update
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
    MODAL DETAIL
══════════════════════════════════════ --}}
    <div class="modal fade" id="showModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="dc-modal-content">
                <div class="dc-modal__header">
                    <h2 class="dc-modal__title">Detail Discipline</h2>
                    <button class="dc-modal__close" data-bs-dismiss="modal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>
                <div class="dc-modal__body" id="showModalBody">
                    <div class="dc-loading-state" style="padding:2rem 0;">
                        <div class="dc-spinner"></div>
                    </div>
                </div>
                <div class="dc-modal__footer">
                    <button class="dc-btn dc-btn--ghost" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        :root {
            --dc-blue: #178EF5;
            --dc-blue-bg: #EBF5FF;
            --dc-green: #16A34A;
            --dc-green-bg: #DCFCE7;
            --dc-purple: #7C3AED;
            --dc-purple-bg: #EDE9FE;
            --dc-red: #DC2626;
            --dc-red-bg: #FEE2E2;
            --dc-amber: #D97706;
            --dc-amber-bg: #FEF3C7;
            --dc-border: rgba(0, 0, 0, .08);
            --dc-radius: 10px;
            --dc-radius-sm: 6px;
        }

        .dc-page {
            padding: 1.75rem 2rem;
            max-width: 1400px;
        }

        /* Header */
        .dc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 1.75rem;
        }

        .dc-header__badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            color: #6B7280;
            background: #F3F4F6;
            border: 1px solid var(--dc-border);
            border-radius: 4px;
            padding: 3px 8px;
            margin-bottom: .5rem;
        }

        .dc-header__title {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -.03em;
            color: #111827;
            margin: 0 0 .25rem;
            line-height: 1;
        }

        .dc-header__dot {
            color: var(--dc-blue);
        }

        .dc-header__sub {
            font-size: .875rem;
            color: #6B7280;
            margin: 0;
        }

        /* Buttons */
        .dc-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: var(--dc-radius-sm);
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all .15s;
            line-height: 1;
        }

        .dc-btn--primary {
            background: var(--dc-blue);
            color: #fff;
        }

        .dc-btn--primary:hover {
            background: #0E7FE0;
        }

        .dc-btn--ghost {
            background: transparent;
            color: #374151;
            border: 1px solid var(--dc-border);
        }

        .dc-btn--ghost:hover {
            background: #F9FAFB;
        }

        .dc-btn--sm {
            padding: 6px 12px;
            font-size: .8125rem;
        }

        /* Stat Cards */
        .dc-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .dc-stat-card {
            display: flex;
            align-items: center;
            gap: 14px;
            background: #fff;
            border: 1px solid var(--dc-border);
            border-radius: var(--dc-radius);
            padding: 1rem 1.25rem;
        }

        .dc-stat-card__icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .dc-stat-card--blue .dc-stat-card__icon {
            background: var(--dc-blue-bg);
            color: var(--dc-blue);
        }

        .dc-stat-card--green .dc-stat-card__icon {
            background: var(--dc-green-bg);
            color: var(--dc-green);
        }

        .dc-stat-card--purple .dc-stat-card__icon {
            background: var(--dc-purple-bg);
            color: var(--dc-purple);
        }

        .dc-stat-card--red .dc-stat-card__icon {
            background: var(--dc-red-bg);
            color: var(--dc-red);
        }

        .dc-stat-card__body {
            display: flex;
            flex-direction: column;
        }

        .dc-stat-card__label {
            font-size: .75rem;
            font-weight: 500;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .dc-stat-card__value {
            font-size: 1.625rem;
            font-weight: 800;
            color: #111827;
            line-height: 1.2;
        }

        /* Filter */
        .dc-filter-card {
            background: #fff;
            border: 1px solid var(--dc-border);
            border-radius: var(--dc-radius);
            padding: .875rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        .dc-filter-form {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .dc-filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .dc-filter-group--check {
            flex-direction: row;
            align-items: center;
            padding-top: 18px;
        }

        .dc-filter-label {
            font-size: .75rem;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .dc-filter-actions {
            display: flex;
            align-items: flex-end;
            gap: .5rem;
            padding-top: 18px;
        }

        .dc-input-icon {
            position: relative;
        }

        .dc-input-icon__icon {
            position: absolute;
            left: 9px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            pointer-events: none;
        }

        .dc-input--icon {
            padding-left: 30px;
        }

        /* Inputs */
        .dc-input {
            height: 36px;
            border: 1px solid #E5E7EB;
            border-radius: var(--dc-radius-sm);
            padding: 0 10px;
            font-size: .875rem;
            color: #111827;
            background: #fff;
            transition: border-color .15s;
            outline: none;
        }

        .dc-input:focus {
            border-color: var(--dc-blue);
            box-shadow: 0 0 0 3px rgba(23, 142, 245, .1);
        }

        .dc-input--select {
            padding-right: 28px;
            cursor: pointer;
        }

        /* Textarea override */
        .dc-textarea {
            width: 100%;
            border: 1px solid #E5E7EB;
            border-radius: var(--dc-radius-sm);
            padding: 8px 10px;
            font-size: .875rem;
            color: #111827;
            background: #fff;
            transition: border-color .15s;
            outline: none;
            resize: vertical;
            font-family: inherit;
        }

        .dc-textarea:focus {
            border-color: var(--dc-blue);
            box-shadow: 0 0 0 3px rgba(23, 142, 245, .1);
        }

        /* Toggle */
        .dc-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .dc-toggle input {
            display: none;
        }

        .dc-toggle__slider {
            position: relative;
            width: 36px;
            height: 20px;
            background: #D1D5DB;
            border-radius: 999px;
            transition: background .2s;
            flex-shrink: 0;
        }

        .dc-toggle__slider::after {
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

        .dc-toggle input:checked~.dc-toggle__slider {
            background: var(--dc-blue);
        }

        .dc-toggle input:checked~.dc-toggle__slider::after {
            transform: translateX(16px);
        }

        .dc-toggle__label {
            font-size: .875rem;
            color: #374151;
            font-weight: 500;
        }

        /* Alert */
        .dc-alert {
            display: flex;
            align-items: center;
            gap: .625rem;
            padding: .75rem 1rem;
            border-radius: var(--dc-radius-sm);
            font-size: .875rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .dc-alert--success {
            background: var(--dc-green-bg);
            color: #15803D;
            border: 1px solid #BBF7D0;
        }

        .dc-alert--error {
            background: var(--dc-red-bg);
            color: #B91C1C;
            border: 1px solid #FECACA;
        }

        /* Table */
        .dc-table-card {
            background: #fff;
            border: 1px solid var(--dc-border);
            border-radius: var(--dc-radius);
            overflow: hidden;
        }

        .dc-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .875rem 1.25rem;
            border-bottom: 1px solid var(--dc-border);
        }

        .dc-table-header__count {
            font-size: .8125rem;
            color: #6B7280;
        }

        .dc-table-header__count strong {
            color: #111827;
        }

        .dc-table-header__perpage {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .8125rem;
            color: #6B7280;
        }

        .dc-table-header__perpage select {
            height: 28px;
            padding: 0 8px;
            border: 1px solid #E5E7EB;
            border-radius: 4px;
            font-size: .8125rem;
        }

        .dc-table-wrap {
            overflow-x: auto;
        }

        .dc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .dc-table__th {
            padding: .75rem 1rem;
            text-align: left;
            font-size: .75rem;
            font-weight: 700;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom: 1px solid var(--dc-border);
            white-space: nowrap;
            background: #FAFAFA;
        }

        .dc-table__th--num,
        .dc-table__th--center {
            text-align: center;
        }

        .dc-table__td {
            padding: .75rem 1rem;
            font-size: .875rem;
            color: #374151;
            border-bottom: 1px solid #F3F4F6;
            vertical-align: middle;
        }

        .dc-table__td--num,
        .dc-table__td--center {
            text-align: center;
        }

        .dc-table__row:hover td {
            background: #F9FAFB;
        }

        .dc-table__row:last-child td {
            border-bottom: none;
        }

        .dc-table__empty {
            padding: 3rem;
            text-align: center;
            color: #9CA3AF;
        }

        /* Cell elements */
        .dc-name {
            display: block;
            font-weight: 600;
            color: #111827;
        }

        .dc-desc {
            display: block;
            font-size: .75rem;
            color: #9CA3AF;
            margin-top: 2px;
        }

        .dc-na {
            color: #D1D5DB;
        }

        .dc-chip {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: .75rem;
            font-weight: 600;
        }

        .dc-chip--sport {
            background: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
        }

        .dc-badge {
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

        /* Type badges */
        .dc-type {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: .75rem;
            font-weight: 700;
        }

        .dc-type--performance {
            background: var(--dc-purple-bg);
            color: var(--dc-purple);
        }

        .dc-type--duel {
            background: var(--dc-red-bg);
            color: var(--dc-red);
        }

        /* Match type badges */
        .dc-match {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: .75rem;
            font-weight: 600;
            background: #F3F4F6;
            color: #374151;
        }

        .dc-match--sanda {
            background: #FEF3C7;
            color: #92400E;
        }

        .dc-match--sparring {
            background: #E0E7FF;
            color: #3730A3;
        }

        .dc-match--solo {
            background: #DCFCE7;
            color: #166534;
        }

        /* Status */
        .dc-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
        }

        .dc-status::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .dc-status--active {
            background: var(--dc-green-bg);
            color: #15803D;
        }

        .dc-status--active::before {
            background: #16A34A;
        }

        .dc-status--inactive {
            background: #F3F4F6;
            color: #6B7280;
        }

        .dc-status--inactive::before {
            background: #9CA3AF;
        }

        /* Action buttons */
        .dc-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .dc-action-btn {
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
        }

        .dc-action-btn--view:hover {
            background: #F3F4F6;
            color: #111827;
        }

        .dc-action-btn--edit:hover {
            background: var(--dc-blue-bg);
            color: var(--dc-blue);
        }

        .dc-action-btn--delete:hover {
            background: var(--dc-red-bg);
            color: var(--dc-red);
        }

        .dc-action-btn {
            color: #6B7280;
        }

        /* Pagination */
        .dc-pagination {
            padding: .875rem 1.25rem;
            border-top: 1px solid var(--dc-border);
        }

        /* Empty state */
        .dc-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .75rem;
            color: #9CA3AF;
            padding: 2rem 0;
        }

        .dc-empty-state p {
            margin: 0;
            font-size: .9375rem;
        }

        /* Loading */
        .dc-loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .75rem;
        }

        .dc-spinner {
            width: 28px;
            height: 28px;
            border: 3px solid #E5E7EB;
            border-top-color: var(--dc-blue);
            border-radius: 50%;
            animation: dc-spin .7s linear infinite;
        }

        @keyframes dc-spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Modal — override Bootstrap modal-content */
        .dc-modal-content {
            background: #fff;
            border-radius: 14px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .15);
            overflow: hidden;
        }

        .dc-modal__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--dc-border);
        }

        .dc-modal__title {
            font-size: 1.0625rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        .dc-modal__close {
            background: none;
            border: none;
            cursor: pointer;
            color: #9CA3AF;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            flex-shrink: 0;
        }

        .dc-modal__close:hover {
            background: #F3F4F6;
            color: #374151;
        }

        .dc-modal__body {
            padding: 1.5rem;
        }

        .dc-modal__footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--dc-border);
            display: flex;
            justify-content: flex-end;
            gap: .625rem;
        }

        /* Modal form layout */
        .dc-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .dc-form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 1rem;
        }

        .dc-form-group:last-child {
            margin-bottom: 0;
        }

        .dc-form-label {
            font-size: .8125rem;
            font-weight: 600;
            color: #374151;
        }

        .dc-required {
            color: var(--dc-red);
        }

        .dc-form-hint {
            font-size: .75rem;
            color: #9CA3AF;
            margin-top: 3px;
        }

        .dc-form-group .dc-input,
        .dc-form-group .dc-textarea {
            width: 100%;
            box-sizing: border-box;
        }

        /* Detail table inside modal */
        .dc-detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .875rem;
        }

        .dc-detail-table th {
            width: 140px;
            padding: 9px 12px;
            color: #6B7280;
            font-weight: 600;
            text-align: left;
            border-bottom: 1px solid #F3F4F6;
            vertical-align: top;
        }

        .dc-detail-table td {
            padding: 9px 12px;
            color: #111827;
            border-bottom: 1px solid #F3F4F6;
        }

        .dc-detail-table tr:last-child th,
        .dc-detail-table tr:last-child td {
            border-bottom: none;
        }

        @media (max-width:900px) {
            .dc-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .dc-form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width:600px) {
            .dc-page {
                padding: 1rem;
            }

            .dc-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        /* ─── Flash helper ───────────────────── */
        function showFlash(type, msg) {
            const el = document.getElementById('dc-flash');
            el.className = `dc-alert dc-alert--${type}`;
            const icon = type === 'success' ?
                '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>' :
                '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>';
            el.innerHTML =
                `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">${icon}</svg>${escHtml(msg)}`;
            el.style.display = 'flex';
            el.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
            setTimeout(() => {
                el.style.display = 'none';
            }, 4500);
        }

        function escHtml(s) {
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        /* ─── SHOW DETAIL ────────────────────── */
        document.querySelectorAll('.btn-show').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const body = document.getElementById('showModalBody');
                body.innerHTML =
                    '<div class="dc-loading-state" style="padding:2rem 0;"><div class="dc-spinner"></div></div>';
                new bootstrap.Modal(document.getElementById('showModal')).show();

                fetch(`/admin/disciplines/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status !== 'success') {
                            body.innerHTML = `<p class="text-danger">${res.message}</p>`;
                            return;
                        }
                        const d = res.data;

                        const typeBadge = d.type === 'performance' ?
                            `<span class="dc-type dc-type--performance">Performance</span>` :
                            d.type === 'duel' ?
                            `<span class="dc-type dc-type--duel">Duel</span>` :
                            (d.type ?? '—');

                        const matchBadge = d.match_type ?
                            `<span class="dc-match dc-match--${d.match_type}">${d.match_type.charAt(0).toUpperCase()+d.match_type.slice(1)}</span>` :
                            '—';

                        const ageList = d.age_categories?.length ?
                            d.age_categories.map(a =>
                                `<span class="dc-chip dc-chip--sport" style="margin:2px;">${escHtml(a.name)}</span>`
                                ).join('') :
                            '—';

                        const statusBadge = d.is_active ?
                            `<span class="dc-status dc-status--active">Aktif</span>` :
                            `<span class="dc-status dc-status--inactive">Nonaktif</span>`;

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
                    .catch(() => {
                        body.innerHTML =
                        '<p style="color:#DC2626;padding:1rem;">Gagal memuat data.</p>';
                    });
            });
        });

        /* ─── EDIT ───────────────────────────── */
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const d = JSON.parse(this.dataset.discipline);
                document.getElementById('editModalBody').innerHTML = buildEditForm(d);
                new bootstrap.Modal(document.getElementById('editModal')).show();
            });
        });

        function buildEditForm(d) {
            const sports = @json($sports);
            const ageCategories = @json($ageCategories);
            const selectedAges = d.age_categories ?? [];

            const sportOpts = sports.map(s =>
                `<option value="${s.id}" ${d.sport_id == s.id ? 'selected' : ''}>${escHtml(s.name)}</option>`
            ).join('');

            const ageOpts = ageCategories.map(a =>
                `<option value="${a.id}" ${selectedAges.includes(a.id) ? 'selected' : ''}>${escHtml(a.name)}</option>`
            ).join('');

            const matchTypes = ['sanda', 'sparring', 'solo'];
            const typeOpts = ['performance', 'duel'];

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
            const id = document.getElementById('editId').value;
            const ids = Array.from(document.getElementById('editAgeCategories').selectedOptions).map(o => o.value);

            fetch(`/admin/disciplines/${id}`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name: document.getElementById('editName').value,
                        sport_id: document.getElementById('editSportId').value,
                        type: document.getElementById('editType').value,
                        match_type: document.getElementById('editMatchType').value,
                        description: document.getElementById('editDescription').value,
                        is_active: document.getElementById('editIsActive').checked,
                        age_category_ids: ids,
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
            const ids = Array.from(form.querySelectorAll('[name="age_category_ids[]"] option:checked')).map(o => o.value);

            fetch('/admin/disciplines', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name: form.querySelector('[name=name]').value,
                        sport_id: form.querySelector('[name=sport_id]').value,
                        type: form.querySelector('[name=type]').value,
                        match_type: form.querySelector('[name=match_type]').value,
                        description: form.querySelector('[name=description]').value,
                        is_active: form.querySelector('[name=is_active]')?.checked ?? true,
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
            btn.addEventListener('click', function() {
                if (!confirm(`Hapus discipline "${this.dataset.name}"?\nAksi ini tidak dapat dibatalkan.`))
                    return;

                fetch(`/admin/disciplines/${this.dataset.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        showFlash(data.status === 'success' ? 'success' : 'error', data.message);
                        if (data.status === 'success') setTimeout(() => location.reload(), 1200);
                    })
                    .catch(() => showFlash('error', 'Terjadi kesalahan. Silakan coba lagi.'));
            });
        });
    </script>
@endpush
