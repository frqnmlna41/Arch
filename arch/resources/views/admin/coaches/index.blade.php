@extends('layouts.admin')

@section('title', 'Coaches Management')

@section('content')
    <div class="ath-page">

        {{-- HEADER --}}
        <div class="ath-header">
            <div class="ath-header__left">
                <div class="ath-header__badge">COACH REGISTRY</div>
                <h1 class="ath-header__title">Coaches<span class="ath-header__dot">.</span></h1>
                <p class="ath-header__sub">Manage all registered coaches.</p>
            </div>
            <div class="ath-header__right">
                <a href="{{ route('admin.coaches.create') }}" class="ath-btn ath-btn--primary">
                    + Add Coach
                </a>
            </div>
        </div>

        {{-- STATS --}}
        <div class="ath-stats">
            <div class="ath-stat-card ath-stat-card--blue">
                <div class="ath-stat-card__body">
                    <span class="ath-stat-card__label">Total Coaches</span>
                    <span class="ath-stat-card__value">{{ $coaches->total() }}</span>
                </div>
            </div>

            <div class="ath-stat-card ath-stat-card--green">
                <div class="ath-stat-card__body">
                    <span class="ath-stat-card__label">Active</span>
                    <span class="ath-stat-card__value">{{ $activeCount }}</span>
                </div>
            </div>

            <div class="ath-stat-card ath-stat-card--red">
                <div class="ath-stat-card__body">
                    <span class="ath-stat-card__label">Inactive</span>
                    <span class="ath-stat-card__value">{{ $inactiveCount }}</span>
                </div>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="ath-filter-card">
            <form method="GET" action="{{ route('admin.coaches.index') }}" class="ath-filter-form">
                <div class="ath-filter-group">
                    <label class="ath-filter-label">Search</label>
                    <input type="text" name="search" class="ath-input" placeholder="Coach name..."
                        value="{{ request('search') }}">
                </div>

                <div class="ath-filter-actions">
                    <button class="ath-btn ath-btn--primary ath-btn--sm">Filter</button>
                    <a href="{{ route('admin.coaches.index') }}" class="ath-btn ath-btn--ghost ath-btn--sm">Reset</a>
                </div>
            </form>
        </div>

        {{-- TABLE --}}
        <div class="ath-table-card">
            <div class="ath-table-header">
                <span class="ath-table-header__count">
                    Showing {{ $coaches->firstItem() }}–{{ $coaches->lastItem() }}
                    of {{ $coaches->total() }}
                </span>
            </div>
            <div class="ath-table-wrap">
                <table class="ath-table">
                    <thead>
                        <tr>
                            <th class="ath-table__th ath-table__th--num">#</th>
                            <th class="ath-table__th">Coach</th>
                            <th class="ath-table__th">Email</th>
                            <th class="ath-table__th">Phone</th>
                            <th class="ath-table__th ath-table__th--center">Status</th>
                            <th class="ath-table__th ath-table__th--center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coaches as $i => $coach)
                            <tr class="ath-table__row">
                                <td class="ath-table__td ath-table__td--num">{{ $coaches->firstItem() + $i }}</td>
                                {{-- <td class="ath-table__td--num">{{ $coaches->firstItem() + $i }}</td> --}}

                                {{-- Athlete Info --}}
                                <td class="ath-table__td">
                                    <div class="ath-athlete-cell">

                                        <div class="ath-avatar ath-avatar--placeholder">
                                            {{ strtoupper(substr($coach->name, 0, 2)) }}
                                        </div>

                                        <div class="ath-athlete-cell__info">
                                            <span class="ath-athlete-cell__name">{{ $coach->name }}</span>
                                            @if ($coach->nik ?? null)
                                                <span class="ath-athlete-cell__nik">{{ $coach->nik }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                {{-- Club --}}
                                {{-- <td class="ath-table__td">{{ $coach->club ?? '—' }}</td> --}}
                                <td class="ath-table__td">{{ $coach->email ?? '—' }}</td>
                                <td class="ath-table__td">{{ $coach->phone ?? '—' }}</td>

                                {{-- Age --}}
                                {{-- <td class="ath-table__td ath-table__td--center">
                                    {{ $athlete->age ?? '—' }}
                                </td> --}}

                                {{-- Events --}}
                                {{-- <td class="ath-table__td ath-table__td--center">
                                    <span class="ath-badge">{{ $athlete->event_participants_count }}</span>
                                </td> --}}

                                {{-- Status --}}
                                <td class="ath-table__td ath-table__td--center">
                                    @if ($coach->is_active)
                                        <span class="ath-status ath-status--active">Active</span>
                                    @else
                                        <span class="ath-status ath-status--inactive">Inactive</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="ath-table__td ath-table__td--center">
                                    <div class="ath-actions">
                                        <a href="{{ route('admin.coaches.show', $coach) }}"
                                            class="ath-action-btn ath-action-btn--view" title="View Detail">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.coaches.edit', $coach) }}"
                                            class="ath-action-btn ath-action-btn--edit" title="Edit">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.coaches.destroy', $coach) }}"
                                            onsubmit="return confirmDelete('{{ addslashes($coach->name) }}')"
                                            style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ath-action-btn ath-action-btn--delete"
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
                                <td colspan="9" class="ath-table__empty">
                                    <div class="ath-empty-state">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                            <circle cx="9" cy="7" r="4" />
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                        </svg>
                                        <p>No coaches found.</p>
                                        <a href="{{ route('admin.coaches.create') }}"
                                            class="ath-btn ath-btn--primary ath-btn--sm">Add First Coach</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    {{-- <tbody>
                        @forelse($coaches as $i => $coach)
                            <tr class="ath-table__row">
                                <td class="ath-table__td--num">{{ $coaches->firstItem() + $i }}</td>

                                <td class="ath-table__td">{{ $coach->name }}</td>
                                <td>{{ $coach->email }}</td>
                                <td>{{ $coach->phone ?? '-' }}</td>

                                <td class="text-center">
                                    @if ($coach->is_active)
                                        <span class="ath-status ath-status--active">Active</span>
                                    @else
                                        <span class="ath-status ath-status--inactive">Inactive</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('admin.coaches.show', $coach) }}" class="ath-action-btn">View</a>
                                    <a href="{{ route('admin.coaches.edit', $coach) }}" class="ath-action-btn">Edit</a>

                                    <form method="POST" action="{{ route('admin.coaches.destroy', $coach) }}"
                                        style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ath-action-btn ath-action-btn--delete">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="ath-table__empty">
                                    No coaches found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody> --}}
                </table>
            </div>


            {{-- PAGINATION --}}
            <div class="ath-pagination">
                {{ $coaches->links() }}
            </div>
        </div>

    </div>
@endsection
