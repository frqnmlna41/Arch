@extends('layouts.coach')

@section('title', 'Athlete Detail')

@section('content')
    <div class="ath-page">

        {{-- HEADER --}}
        <div class="ath-header">
            <div class="ath-header__left">
                <div class="ath-header__badge">ATHLETE PROFILE</div>
                <h1 class="ath-header__title">
                    {{ $athlete->name }}<span class="ath-header__dot">.</span>
                </h1>
                <p class="ath-header__sub">Complete athlete information and history.</p>
            </div>
            <div class="ath-header__right">
                <a href="{{ route('coach.athletes.edit', $athlete) }}" class="ath-btn ath-btn--primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                    Edit
                </a>
                <a href="{{ route('coach.athletes.index') }}" class="ath-btn ath-btn--ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    Back
                </a>
            </div>
        </div>

        {{-- STAT CARDS --}}
        <div class="ath-stats">
            <div class="ath-stat-card ath-stat-card--blue">
                <div class="ath-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div class="ath-stat-card__body">
                    <span class="ath-stat-card__label">Age</span>
                    <span class="ath-stat-card__value">{{ $athlete->age ?? '—' }}</span>
                </div>
            </div>
            <div class="ath-stat-card ath-stat-card--green">
                <div class="ath-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                    </svg>
                </div>
                <div class="ath-stat-card__body">
                    <span class="ath-stat-card__label">Events Joined</span>
                    <span class="ath-stat-card__value">{{ $athlete->eventParticipants->count() }}</span>
                    <!-- <span class="ath-stat-card__value">{{ $athlete->eventParticipants->count() }}</span> -->
                </div>
            </div>
            <div class="ath-stat-card ath-stat-card--amber">
                <div class="ath-stat-card__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <circle cx="12" cy="8" r="6" />
                        <path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11" />
                    </svg>
                </div>
                <div class="ath-stat-card__body">
                    <span class="ath-stat-card__label">Achievements</span>
                    <span class="ath-stat-card__value">{{ $athlete->winners->count() }}</span>
                </div>
            </div>
            <div class="ath-stat-card {{ $athlete->is_active ? 'ath-stat-card--green' : 'ath-stat-card--red' }}">
                <div class="ath-stat-card__icon">
                    @if ($athlete->is_active)
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    @else
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                        </svg>
                    @endif
                </div>
                <div class="ath-stat-card__body">
                    <span class="ath-stat-card__label">Status</span>
                    <span class="ath-stat-card__value">{{ $athlete->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
            </div>
        </div>

        {{-- FLASH MESSAGES --}}
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

        {{-- PROFILE CARD --}}
        <div class="ath-table-card">
            <div class="ath-table-header">
                <span>Profile Overview</span>
            </div>
            <div class="ath-show-profile">
                <div class="ath-show-profile__avatar">
                    @if ($athlete->photo)
                        <img src="{{ Storage::url($athlete->photo) }}" alt="{{ $athlete->name }}">
                    @else
                        <div class="ath-avatar ath-avatar--placeholder ath-avatar--lg">
                            {{ strtoupper(substr($athlete->name, 0, 2)) }}
                        </div>
                    @endif
                </div>
                <div class="ath-show-profile__body">
                    <div class="ath-show-profile__top">
                        <div>
                            <h2 class="ath-show-profile__name">{{ $athlete->name }}</h2>
                            <div class="ath-profile-meta">
                                @if ($athlete->coach)
                                    <span class="ath-chip ath-chip--blue">{{ $athlete->coach->name }}</span>
                                @else
                                    <span class="ath-chip">No Coach</span>
                                @endif
                                <span
                                    class="ath-status {{ $athlete->is_active ? 'ath-status--active' : 'ath-status--inactive' }}">
                                    {{ $athlete->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if ($athlete->gender === 'male')
                                    <span class="ath-gender ath-gender--male">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
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
                            </div>
                        </div>
                    </div>

                    <div class="ath-show-grid">
                        <div class="ath-show-field">
                            <span class="ath-show-field__label">Club / Perguruan</span>
                            <span class="ath-show-field__value">{{ $athlete->perguruan->name ?? '—' }}</span>
                        </div>
                        <div class="ath-show-field">
                            <span class="ath-show-field__label">NIK</span>
                            <span class="ath-show-field__value">{{ $athlete->id_card_number ?? '—' }}</span>
                        </div>
                        <div class="ath-show-field">
                            <span class="ath-show-field__label">Date of Birth</span>
                            <span class="ath-show-field__value">
                                {{ $athlete->birth_date ? \Carbon\Carbon::parse($athlete->birth_date)->format('d M Y') : '—' }}
                            </span>
                        </div>
                        <div class="ath-show-field">
                            <span class="ath-show-field__label">Age Category</span>
                            <span class="ath-show-field__value">
                                {{ $athlete->age_categories->name ?? '—' }}
                            </span>
                        </div>
                        <div class="ath-show-field">
                            <span class="ath-show-field__label">Weight</span>
                            <span
                                class="ath-show-field__value">{{ $athlete->weight ? $athlete->weight . ' kg' : '—' }}</span>
                        </div>
                        <div class="ath-show-field">
                            <span class="ath-show-field__label">Height</span>
                            <span
                                class="ath-show-field__value">{{ $athlete->height ? $athlete->height . ' cm' : '—' }}</span>
                        </div>
                        <div class="ath-show-field">
                            <span class="ath-show-field__label">Phone</span>
                            <span class="ath-show-field__value">{{ $athlete->phone ?? '—' }}</span>
                        </div>
                        <div class="ath-show-field">
                            <span class="ath-show-field__label">Address</span>
                            <span class="ath-show-field__value">{{ $athlete->address ?? '—' }}</span>
                        </div>
                        <div class="ath-show-field">
                            <span class="ath-show-field__label">Registered</span>
                            <span class="ath-show-field__value">{{ $athlete->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="ath-show-field">
                            <span class="ath-show-field__label">Last Updated</span>
                            <span class="ath-show-field__value">{{ $athlete->updated_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- EVENT PARTICIPATION --}}
        <div class="ath-table-card">
            <div class="ath-table-header">
                <span>
                    Event Participation
                    <span class="ath-badge">{{ $athlete->registrations->count() }}</span>
                </span>
            </div>
            <div class="ath-table-wrap">
                <table class="ath-table">
                    <thead>
                        <tr>
                            <th class="ath-table__th ath-table__th--num">#</th>
                            <th class="ath-table__th">Event</th>
                            <th class="ath-table__th">Discipline</th>
                            <th class="ath-table__th ath-table__th--center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($athlete->eventParticipants as $i => $ec)
                            <tr class="ath-table__row">
                                <td class="ath-table__td ath-table__td--num">{{ $i + 1 }}</td>
                                <td class="ath-table__td">{{ $ec->event->name ?? '-' }}</td>
                                <td class="ath-table__td">{{ $ec->registration->discipline->name ?? '-' }}</td>
                                <td class="ath-table__td ath-table__td--center">
                                    @php
                                        $status = $ec->status ?? 'pending';
                                        $statusClass = match ($status) {
                                            'verified' => 'ath-status--active',
                                            'rejected', 'disqualified' => 'ath-status--inactive',
                                            default => 'ath-status--pending',
                                        };
                                    @endphp
                                    <span class="ath-status {{ $statusClass }}">{{ ucfirst($status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="ath-table__empty">
                                    <div class="ath-empty-state">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                        <p>No event participation yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ACHIEVEMENTS --}}
        <div class="ath-table-card">
            <div class="ath-table-header">
                <span>
                    Achievements
                    <span class="ath-badge">{{ $athlete->winners->count() }}</span>
                </span>
            </div>
            <div class="ath-table-wrap">
                <table class="ath-table">
                    <thead>
                        <tr>
                            <th class="ath-table__th ath-table__th--num">#</th>
                            <th class="ath-table__th">Event</th>
                            <th class="ath-table__th">Category</th>
                            <th class="ath-table__th ath-table__th--center">Rank</th>
                            <th class="ath-table__th">Certificate No.</th>
                            <th class="ath-table__th">Issued Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($athlete->winners as $i => $win)
                            <tr class="ath-table__row">
                                <td class="ath-table__td ath-table__td--num">{{ $i + 1 }}</td>
                                <td class="ath-table__td">{{ $win->eventCategory->event->name ?? '-' }}</td>
                                <td class="ath-table__td">{{ $win->eventCategory->discipline->name ?? '-' }}</td>
                                <td class="ath-table__td ath-table__td--center">
                                    @php
                                        $medal = match ($win->rank) {
                                            1 => ['label' => 'Gold', 'class' => 'ath-medal ath-medal--gold'],
                                            2 => ['label' => 'Silver', 'class' => 'ath-medal ath-medal--silver'],
                                            3 => ['label' => 'Bronze', 'class' => 'ath-medal ath-medal--bronze'],
                                            default => ['label' => '#' . $win->rank, 'class' => 'ath-badge'],
                                        };
                                    @endphp
                                    <span class="{{ $medal['class'] }}">{{ $medal['label'] }}</span>
                                </td>
                                <td class="ath-table__td">
                                    <span
                                        class="ath-cert-num">{{ $win->certificates->first()->certificate_number ?? '—' }}</span>
                                </td>
                                <td class="ath-table__td">
                                    {{ $win->certificates->first()?->issued_at
                                        ? \Carbon\Carbon::parse($win->certificates->first()->issued_at)->format('d M Y')
                                        : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="ath-table__empty">
                                    <div class="ath-empty-state">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <circle cx="12" cy="8" r="6" />
                                            <path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11" />
                                        </svg>
                                        <p>No achievements yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- DANGER ZONE --}}
        <div class="ath-danger-zone">
            <div class="ath-danger-zone__info">
                <strong>Delete Athlete</strong>
                <p>Permanently remove this athlete and all associated data. This action cannot be undone.</p>
            </div>
            <form method="POST" action="{{ route('coach.athletes.destroy', $athlete) }}"
                onsubmit="return confirm('Delete {{ addslashes($athlete->name) }} permanently? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="ath-btn ath-btn--danger">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                        <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                    </svg>
                    Delete Athlete
                </button>
            </form>
        </div>

    </div>
@endsection

{{-- <pre>
{{ dd($athlete->toArray()) }}
</pre> --}}
