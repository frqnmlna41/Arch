    @extends('layouts.admin')

    @section('title', 'Athletes Management')

    @section('content')
        <div class="ath-page">

            {{-- ══════════════════════════════════════
            PAGE HEADER
        ══════════════════════════════════════ --}}
            <div class="ath-header">
                <div class="ath-header__left">
                    <div class="ath-header__badge">ATHLETE REGISTRY</div>
                    <h1 class="ath-header__title">Athletes<span class="ath-header__dot">.</span></h1>
                    <p class="ath-header__sub">Manage all registered athletes across all coaches.</p>
                </div>
                <div class="ath-header__right">
                    <a href="{{ route('admin.athletes.create') }}" class="ath-btn ath-btn--primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Add Athlete
                    </a>
                </div>
            </div>

            {{-- ══════════════════════════════════════
            STAT CARDS
        ══════════════════════════════════════ --}}
            <div class="ath-stats">
                <div class="ath-stat-card ath-stat-card--blue">
                    <div class="ath-stat-card__icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <div class="ath-stat-card__body">
                        <span class="ath-stat-card__label">Total Athletes</span>
                        <span class="ath-stat-card__value">{{ $athletes->total() }}</span>
                    </div>
                </div>
                <div class="ath-stat-card ath-stat-card--green">
                    <div class="ath-stat-card__icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    </div>
                    <div class="ath-stat-card__body">
                        <span class="ath-stat-card__label">Active</span>
                        <span class="ath-stat-card__value">{{ $athletes->where('is_active', true)->count() }}</span>
                    </div>
                </div>
                <div class="ath-stat-card ath-stat-card--amber">
                    <div class="ath-stat-card__icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <div class="ath-stat-card__body">
                        <span class="ath-stat-card__label">This Page</span>
                        <span class="ath-stat-card__value">{{ $athletes->count() }}</span>
                    </div>
                </div>
                <div class="ath-stat-card ath-stat-card--red">
                    <div class="ath-stat-card__icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                        </svg>
                    </div>
                    <div class="ath-stat-card__body">
                        <span class="ath-stat-card__label">Inactive</span>
                        <span class="ath-stat-card__value">{{ $athletes->where('is_active', false)->count() }}</span>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════
            FILTER BAR
        ══════════════════════════════════════ --}}
            <div class="ath-filter-card">
                <form method="GET" action="{{ route('admin.athletes.index') }}" class="ath-filter-form" id="filterForm">
                    <div class="ath-filter-group">
                        <label class="ath-filter-label">Search</label>
                        <div class="ath-input-icon">
                            <svg class="ath-input-icon__icon" width="15" height="15" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input type="text" name="search" class="ath-input" placeholder="Athlete name…"
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="ath-filter-group">
                        <label class="ath-filter-label">Gender</label>
                        <select name="gender" class="ath-input ath-input--select">
                            <option value="">All</option>
                            <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="ath-filter-group">
                        <label class="ath-filter-label">Club</label>
                        <input type="text" name="club" class="ath-input" placeholder="Club name…"
                            value="{{ request('club') }}">
                    </div>
                    <div class="ath-filter-group ath-filter-group--check">
                        <label class="ath-toggle">
                            <input type="checkbox" name="active" value="1"
                                {{ request('active') ? 'checked' : '' }}
                                onchange="document.getElementById('filterForm').submit()">
                            <span class="ath-toggle__slider"></span>
                            <span class="ath-toggle__label">Active only</span>
                        </label>
                    </div>
                    <div class="ath-filter-actions">
                        <button type="submit" class="ath-btn ath-btn--primary ath-btn--sm">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('admin.athletes.index') }}"
                            class="ath-btn ath-btn--ghost ath-btn--sm">Reset</a>
                    </div>
                </form>
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
            @if (session('error'))
                <div class="ath-alert ath-alert--error">
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
            <div class="ath-table-card">
                <div class="ath-table-header">
                    <span class="ath-table-header__count">
                        Showing <strong>{{ $athletes->firstItem() }}–{{ $athletes->lastItem() }}</strong> of
                        <strong>{{ $athletes->total() }}</strong> athletes
                    </span>
                    <div class="ath-table-header__perpage">
                        <label>Rows:</label>
                        <select
                            onchange="window.location='?per_page='+this.value+'&{{ http_build_query(request()->except('per_page', 'page')) }}'">
                            @foreach ([10, 15, 25, 50] as $pp)
                                <option value="{{ $pp }}"
                                    {{ request('per_page', 15) == $pp ? 'selected' : '' }}>
                                    {{ $pp }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ath-table-wrap">
                    <table class="ath-table">
                        <thead>
                            <tr>
                                <th class="ath-table__th ath-table__th--num">#</th>
                                <th class="ath-table__th">Athlete</th>
                                <th class="ath-table__th">Coach</th>
                                <th class="ath-table__th ath-table__th--center">Gender</th>
                                <th class="ath-table__th">Club</th>
                                <th class="ath-table__th ath-table__th--center">Age</th>
                                <th class="ath-table__th ath-table__th--center">Events</th>
                                <th class="ath-table__th ath-table__th--center">Status</th>
                                <th class="ath-table__th ath-table__th--center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($athletes as $i => $athlete)
                                <tr class="ath-table__row">
                                    <td class="ath-table__td ath-table__td--num">{{ $athletes->firstItem() + $i }}</td>

                                    {{-- Athlete Info --}}
                                    <td class="ath-table__td">
                                        <div class="ath-athlete-cell">
                                            @if ($athlete->photo)
                                                <img src="{{ Storage::url($athlete->photo) }}"
                                                    alt="{{ $athlete->name }}" class="ath-avatar">
                                            @else
                                                <div class="ath-avatar ath-avatar--placeholder">
                                                    {{ strtoupper(substr($athlete->name, 0, 2)) }}
                                                </div>
                                            @endif
                                            <div class="ath-athlete-cell__info">
                                                <span class="ath-athlete-cell__name">{{ $athlete->name }}</span>
                                                @if ($athlete->nik ?? null)
                                                    <span class="ath-athlete-cell__nik">{{ $athlete->nik }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Coach --}}
                                    <td class="ath-table__td">
                                        @if ($athlete->coach)
                                            <span class="ath-chip ath-chip--blue">{{ $athlete->coach->name }}</span>
                                        @else
                                            <span class="ath-na">—</span>
                                        @endif
                                    </td>

                                    {{-- Gender --}}
                                    <td class="ath-table__td ath-table__td--center">
                                        @if ($athlete->gender === 'male')
                                            <span class="ath-gender ath-gender--male">
                                                <svg width="12" height="12" viewBox="0 0 24 24"
                                                    fill="currentColor">
                                                    <path
                                                        d="M16 2v2h3.586l-5 5A7 7 0 1 0 19.071 14H22v-2h-3v-3h-2v3h-1.071A7.003 7.003 0 0 0 9 5a7 7 0 0 0-1 .071L13 0z" />
                                                    <circle cx="9" cy="15" r="5" fill="none"
                                                        stroke="currentColor" stroke-width="2" />
                                                </svg>
                                                Male
                                            </span>
                                        @else
                                            <span class="ath-gender ath-gender--female">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="8" r="5" />
                                                    <line x1="12" y1="13" x2="12" y2="21" />
                                                    <line x1="9" y1="18" x2="15" y2="18" />
                                                </svg>
                                                Female
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Club --}}
                                    <td class="ath-table__td">{{ $athlete->club ?? '—' }}</td>

                                    {{-- Age --}}
                                    <td class="ath-table__td ath-table__td--center">
                                        {{ $athlete->age ?? '—' }}
                                    </td>

                                    {{-- Events --}}
                                    <td class="ath-table__td ath-table__td--center">
                                        <span class="ath-badge">{{ $athlete->event_participants_count }}</span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="ath-table__td ath-table__td--center">
                                        @if ($athlete->is_active)
                                            <span class="ath-status ath-status--active">Active</span>
                                        @else
                                            <span class="ath-status ath-status--inactive">Inactive</span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="ath-table__td ath-table__td--center">
                                        <div class="ath-actions">
                                            <a href="{{ route('admin.athletes.show', $athlete) }}"
                                                class="ath-action-btn ath-action-btn--view" title="View Detail">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.athletes.edit', $athlete) }}"
                                                class="ath-action-btn ath-action-btn--edit" title="Edit">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route('admin.athletes.destroy', $athlete) }}"
                                                onsubmit="return confirmDelete('{{ addslashes($athlete->name) }}')"
                                                style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ath-action-btn ath-action-btn--delete"
                                                    title="Delete">
                                                    <svg width="14" height="14" viewBox="0 0 24 24"
                                                        fill="none" stroke="currentColor" stroke-width="2">
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
                                    <td colspan="9" class="ath-table__empty">
                                        <div class="ath-empty-state">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.5">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                                <circle cx="9" cy="7" r="4" />
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                            </svg>
                                            <p>No athletes found.</p>
                                            <a href="{{ route('admin.athletes.store') }}"
                                                class="ath-btn ath-btn--primary ath-btn--sm">Add First Athlete</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($athletes->hasPages())
                    <div class="ath-pagination">
                        {{ $athletes->appends(request()->query())->links('pagination::tailwind') }}
                        {{-- {{ $athletes->appends(request()->query())->links('vendor.pagination.athletes') }} --}}
                    </div>
                @endif
            </div>

        </div>
    @endsection

    @push('styles')
    @endpush

    @push('scripts')
        <script>
            function confirmDelete(name) {
                return confirm('Are you sure you want to delete athlete "' + name + '"?\nThis action cannot be undone.');
            }
        </script>
    @endpush
