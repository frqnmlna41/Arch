@extends('layouts.admin')

@section('title', 'Jadwal Tanding')

@section('content')

<div class="mx-page">

    {{-- ═══════════════════════════════════════
         PAGE HEADER
    ═══════════════════════════════════════ --}}
    <div class="mx-header">
        <div class="mx-header__left">
            <span class="mx-eyebrow">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                COMPETITION MANAGEMENT
            </span>
            <h1 class="mx-title">Jadwal Tanding<span class="mx-accent">.</span></h1>
            <p class="mx-sub">Kelola seluruh pertandingan, sesi, dan urutan tampil atlet.</p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         STAT STRIP
    ═══════════════════════════════════════ --}}
    <div class="mx-stats">
        @php
            $allMatches = $matches;
            $statTotal      = $matches->total();
            $statScheduled  = $matches->getCollection()->where('status', 'scheduled')->count();
            $statOngoing    = $matches->getCollection()->where('status', 'ongoing')->count();
            $statCompleted  = $matches->getCollection()->where('status', 'completed')->count();
            $statCancelled  = $matches->getCollection()->where('status', 'cancelled')->count();
        @endphp
        <div class="mx-stat mx-stat--all">
            <div class="mx-stat__num">{{ $statTotal }}</div>
            <div class="mx-stat__lbl">Total</div>
        </div>
        <div class="mx-stat mx-stat--scheduled">
            <div class="mx-stat__num">{{ $statScheduled }}</div>
            <div class="mx-stat__lbl">Scheduled</div>
        </div>
        <div class="mx-stat mx-stat--ongoing">
            <div class="mx-stat__num">{{ $statOngoing }}</div>
            <div class="mx-stat__lbl">Ongoing</div>
        </div>
        <div class="mx-stat mx-stat--completed">
            <div class="mx-stat__num">{{ $statCompleted }}</div>
            <div class="mx-stat__lbl">Completed</div>
        </div>
        <div class="mx-stat mx-stat--cancelled">
            <div class="mx-stat__num">{{ $statCancelled }}</div>
            <div class="mx-stat__lbl">Cancelled</div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         ALERTS
    ═══════════════════════════════════════ --}}
    @if(session('success'))
        <div class="mx-alert mx-alert--success" id="mx-alert">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
            <button class="mx-alert__close" onclick="this.closest('.mx-alert').remove()">×</button>
        </div>
    @endif
    @if(session('error'))
        <div class="mx-alert mx-alert--error" id="mx-alert">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            {{ session('error') }}
            <button class="mx-alert__close" onclick="this.closest('.mx-alert').remove()">×</button>
        </div>
    @endif

    {{-- ═══════════════════════════════════════
         FILTER PANEL
    ═══════════════════════════════════════ --}}
    <div class="mx-panel" id="filterPanel">
        <div class="mx-panel__head" onclick="togglePanel('filterPanel')">
            <div class="mx-panel__head-left">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                <span>Filter Pertandingan</span>
                @if(request()->hasAny(['event_category_id','competition_session_id','status','arena_id','gender']))
                    <span class="mx-panel__active-badge">Aktif</span>
                @endif
            </div>
            <svg class="mx-panel__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="mx-panel__body">
            <form method="GET" action="{{ route('admin.matches.index') }}" id="filterForm">
                <div class="mx-filter-grid">

                    {{-- Kategori Event --}}
                    <div class="mx-field">
                        <label class="mx-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Kategori Event
                        </label>
                        <select name="event_category_id" class="mx-select">
                            <option value="">Semua Kategori</option>
                            @foreach ($eventCategories as $cat)
                                <option value="{{ $cat->id }}" {{ request('event_category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->discipline->name ?? '—' }} – {{ $cat->ageCategory->name ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sesi Pertandingan --}}
                    <div class="mx-field">
                        <label class="mx-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Sesi Pertandingan
                        </label>
                        <select name="competition_session_id" class="mx-select">
                            <option value="">Semua Sesi</option>
                            @foreach ($sessions as $ses)
                                <option value="{{ $ses->id }}" {{ request('competition_session_id') == $ses->id ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($ses->start_time)->format('d M Y H:i') }} · {{ $ses->eventCategory->discipline->name ?? '—' }} ({{ ucfirst($ses->gender) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="mx-field">
                        <label class="mx-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Status
                        </label>
                        <select name="status" class="mx-select">
                            <option value="">Semua Status</option>
                            <option value="scheduled"  {{ request('status') === 'scheduled'  ? 'selected' : '' }}>Scheduled</option>
                            <option value="ongoing"    {{ request('status') === 'ongoing'    ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed"  {{ request('status') === 'completed'  ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled"  {{ request('status') === 'cancelled'  ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    {{-- Gender --}}
                    <div class="mx-field">
                        <label class="mx-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                            Gender
                        </label>
                        <select name="gender" class="mx-select">
                            <option value="">Semua Gender</option>
                            <option value="male"   {{ request('gender') === 'male'   ? 'selected' : '' }}>Putra</option>
                            <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Putri</option>
                        </select>
                    </div>

                    {{-- Arena --}}
                    <div class="mx-field">
                        <label class="mx-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                            Arena
                        </label>
                        <select name="arena_id" class="mx-select">
                            <option value="">Semua Arena</option>
                            @foreach ($sessions->pluck('arena')->filter()->unique('id') as $arena)
                                <option value="{{ $arena->id }}" {{ request('arena_id') == $arena->id ? 'selected' : '' }}>
                                    {{ $arena->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="mx-filter-actions">
                    <button type="submit" class="mx-btn mx-btn--primary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Terapkan Filter
                    </button>
                    <a href="{{ route('admin.matches.index') }}" class="mx-btn mx-btn--ghost">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.47"/></svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         VALIDASI & GENERATE JADWAL
    ═══════════════════════════════════════ --}}
    <div class="mx-panel mx-panel--warning" id="generatePanel">
        <div class="mx-panel__head" onclick="togglePanel('generatePanel')">
            <div class="mx-panel__head-left">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                <span>Validasi & Jadwalkan Sesi</span>
            </div>
            <svg class="mx-panel__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="mx-panel__body">
            <p class="mx-generate-hint">Pilih sesi lalu klik <strong>Validasi / Jadwalkan</strong> untuk memvalidasi urutan tampil atlet pada sesi tersebut.</p>
            <div class="mx-generate-row" id="generateForm">
                @csrf
                <div class="mx-field mx-field--grow">
                    <label class="mx-label">Sesi Pertandingan</label>
                    <select name="competition_session_id" id="genSessionId" class="mx-select">
                        <option value="">— Pilih Sesi —</option>
                        @foreach ($sessions as $ses)
                            <option value="{{ $ses->id }}">
                                {{ \Carbon\Carbon::parse($ses->start_time)->format('d M Y H:i') }} · {{ $ses->eventCategory->discipline->name ?? '—' }} ({{ ucfirst($ses->gender) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="button" onclick="generateBracket()" class="mx-btn mx-btn--warning" id="genBtn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    <span id="genBtnText">Validasi / Jadwalkan</span>
                </button>
            </div>
            {{-- Generate Result --}}
            <div id="genResult" class="mx-gen-result" style="display:none;"></div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         TABLE CARD
    ═══════════════════════════════════════ --}}
    <div class="mx-card">
        <div class="mx-card__head">
            <div class="mx-card__head-left">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
                <span>Daftar Pertandingan</span>
            </div>
            <div class="mx-card__head-right">
                <span class="mx-total-badge">{{ $matches->total() }} jadwal</span>
                <div class="mx-perpage">
                    <label>Baris:</label>
                    <select onchange="window.location='?per_page='+this.value+'&{{ http_build_query(request()->except('per_page','page')) }}'">
                        @foreach([10, 15, 25, 50] as $pp)
                            <option value="{{ $pp }}" {{ request('per_page', 15) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if($matches->isEmpty())
            <div class="mx-empty">
                <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <p>Belum ada pertandingan ditemukan.</p>
                <span>Coba ubah filter atau validasi sesi terlebih dahulu.</span>
            </div>
        @else
            <div class="mx-table-wrap">
                <table class="mx-table">
                    <thead>
                        <tr>
                            <th class="mx-th--num">No</th>
                            <th>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                Sesi Mulai
                            </th>
                            <th>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 10h16M4 14h8"/></svg>
                                Kategori
                            </th>
                            <th>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Atlet
                            </th>
                            <th>Perguruan</th>
                            <th class="mx-th--center">Urutan</th>
                            <th class="mx-th--center">Est. Tampil</th>
                            <th class="mx-th--center">Arena</th>
                            <th class="mx-th--center">Status</th>
                            <th class="mx-th--center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matches as $i => $match)
                        <tr class="mx-tr" data-status="{{ $match->status }}">
                            <td class="mx-td--num">{{ $matches->firstItem() + $i }}</td>

                            {{-- Sesi --}}
                            <td>
                                @if($match->session)
                                    <div class="mx-cell-session">
                                        <span class="mx-cell-session__date">{{ \Carbon\Carbon::parse($match->session->start_time)->format('d M Y') }}</span>
                                        <span class="mx-cell-session__time">{{ \Carbon\Carbon::parse($match->session->start_time)->format('H:i') }}</span>
                                    </div>
                                @else
                                    <span class="mx-na">—</span>
                                @endif
                            </td>

                            {{-- Kategori --}}
                            <td>
                                @if($match->session?->eventCategory)
                                    <div class="mx-cell-cat">
                                        <span class="mx-cell-cat__disc">{{ $match->session->eventCategory->discipline->name ?? '—' }}</span>
                                        <span class="mx-cell-cat__age">{{ $match->session->eventCategory->ageCategory->name ?? '—' }} · {{ ucfirst($match->session->gender) }}</span>
                                    </div>
                                @else
                                    <span class="mx-na">—</span>
                                @endif
                            </td>

                            {{-- Atlet --}}
                            <td>
                                <div class="mx-cell-athlete">
                                    <div class="mx-avatar">{{ strtoupper(substr($match->athlete?->name ?? '?', 0, 2)) }}</div>
                                    <span>{{ $match->athlete?->name ?? '—' }}</span>
                                </div>
                            </td>

                            {{-- Perguruan --}}
                            <td>
                                @if($match->athlete?->perguruan?->name)
                                    <span class="mx-chip">{{ $match->athlete->perguruan->name }}</span>
                                @else
                                    <span class="mx-na">—</span>
                                @endif
                            </td>

                            {{-- Urutan --}}
                            <td class="mx-td--center">
                                @if($match->appearance_order)
                                    <span class="mx-order-badge">{{ $match->appearance_order }}</span>
                                @else
                                    <span class="mx-na">—</span>
                                @endif
                            </td>

                            {{-- Estimasi Tampil --}}
                            <td class="mx-td--center">
                                @if($match->session && $match->appearance_order)
                                    @php
                                        $mins = ($match->appearance_order - 1) * $match->session->duration_per_athlete;
                                        $est  = \Carbon\Carbon::parse($match->session->start_time)->addMinutes($mins);
                                    @endphp
                                    <span class="mx-time-chip">{{ $est->format('H:i') }}</span>
                                @else
                                    <span class="mx-na">—</span>
                                @endif
                            </td>

                            {{-- Arena --}}
                            <td class="mx-td--center">
                                @if($match->session?->arena)
                                    <span class="mx-arena-chip">{{ $match->session->arena->name }}</span>
                                @else
                                    <span class="mx-na">Belum diatur</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="mx-td--center">
                                <span class="mx-status mx-status--{{ $match->status ?? 'unknown' }}">
                                    <span class="mx-status__dot"></span>
                                    {{ ucfirst($match->status ?? '—') }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="mx-td--center">
                                <div class="mx-actions">
                                    @if(!in_array($match->status, ['ongoing', 'completed']))
                                        <button class="mx-action-btn mx-action-btn--edit btn-edit"
                                            data-id="{{ $match->id }}"
                                            data-status="{{ $match->status }}"
                                            title="Edit Status">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <button class="mx-action-btn mx-action-btn--delete btn-delete"
                                            data-id="{{ $match->id }}"
                                            data-name="{{ $match->athlete?->name ?? 'ini' }}"
                                            title="Hapus">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        </button>
                                    @else
                                        <span class="mx-na" style="font-size:.72rem">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($matches->hasPages())
            <div class="mx-pagination">
                {{ $matches->appends(request()->query())->links() }}
            </div>
            @endif
        @endif
    </div>

</div>

{{-- ═══════════════════════════════════════
     MODAL — EDIT STATUS
═══════════════════════════════════════ --}}
<div class="mx-modal-overlay" id="editModal">
    <div class="mx-modal">
        <div class="mx-modal__head">
            <div class="mx-modal__head-left">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Status Pertandingan
            </div>
            <button class="mx-modal__close" onclick="closeModal('editModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="mx-modal__body">
            <input type="hidden" id="editMatchId">
            <div class="mx-field" style="margin-bottom:0">
                <label class="mx-label">Status Pertandingan</label>
                <select id="editStatus" class="mx-select">
                    <option value="scheduled">Scheduled</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            {{-- Status preview --}}
            <div id="statusPreview" class="mx-status-preview"></div>
        </div>
        <div class="mx-modal__foot">
            <button class="mx-btn mx-btn--ghost" onclick="closeModal('editModal')">Batal</button>
            <button class="mx-btn mx-btn--primary" onclick="submitEditMatch()" id="submitEditBtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Update Status
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════
     MODAL — KONFIRMASI DELETE
═══════════════════════════════════════ --}}
<div class="mx-modal-overlay" id="deleteModal">
    <div class="mx-modal mx-modal--sm">
        <div class="mx-modal__head mx-modal__head--danger">
            <div class="mx-modal__head-left">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                Konfirmasi Hapus
            </div>
            <button class="mx-modal__close" onclick="closeModal('deleteModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="mx-modal__body">
            <input type="hidden" id="deleteMatchId">
            <div class="mx-delete-confirm">
                <div class="mx-delete-confirm__icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <p>Hapus pertandingan atlet <strong id="deleteMatchName"></strong>?</p>
                <span>Tindakan ini tidak dapat dibatalkan.</span>
            </div>
        </div>
        <div class="mx-modal__foot">
            <button class="mx-btn mx-btn--ghost" onclick="closeModal('deleteModal')">Batal</button>
            <button class="mx-btn mx-btn--danger" onclick="submitDelete()" id="submitDeleteBtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,700;1,9..144,400&display=swap');

:root {
    --mx-bg:          #f0f2f5;
    --mx-surface:     #ffffff;
    --mx-border:      #e4e7ec;
    --mx-border-2:    #d0d5dd;
    --mx-text:        #101828;
    --mx-text-2:      #667085;
    --mx-text-3:      #98a2b3;

    --mx-blue:        #1570ef;
    --mx-blue-2:      #eff4ff;
    --mx-blue-3:      #b2ccff;
    --mx-green:       #079455;
    --mx-green-2:     #ecfdf3;
    --mx-amber:       #dc6803;
    --mx-amber-2:     #fffaeb;
    --mx-amber-3:     #fedf89;
    --mx-red:         #d92d20;
    --mx-red-2:       #fef3f2;
    --mx-red-3:       #fecdca;
    --mx-violet:      #6938ef;
    --mx-violet-2:    #f4f3ff;

    --mx-radius:      12px;
    --mx-radius-sm:   8px;
    --mx-radius-xs:   5px;
    --mx-shadow:      0 1px 2px rgba(16,24,40,.06), 0 1px 3px rgba(16,24,40,.1);
    --mx-shadow-md:   0 4px 16px rgba(16,24,40,.1);
    --mx-shadow-lg:   0 12px 48px rgba(16,24,40,.18);
}

/* ── BASE ────────────────────── */
.mx-page, .mx-page * { box-sizing: border-box; }
.mx-page { font-family: 'Plus Jakarta Sans', sans-serif; }

.mx-page {
    padding: 2rem 2.5rem;
    background: var(--mx-bg);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* ── HEADER ─────────────────── */
.mx-header { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem; }
.mx-eyebrow {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .65rem; font-weight: 700; letter-spacing: .12em; color: var(--mx-blue);
    background: var(--mx-blue-2); border: 1px solid var(--mx-blue-3);
    padding: .25rem .65rem; border-radius: 99px;
    margin-bottom: .4rem;
}
.mx-title {
    font-family: 'Fraunces', serif;
    font-size: 2.4rem; line-height: 1;
    color: var(--mx-text); margin: 0 0 .25rem;
    font-weight: 700;
}
.mx-accent { color: var(--mx-blue); }
.mx-sub { font-size: .875rem; color: var(--mx-text-2); margin: 0; }

/* ── STATS ──────────────────── */
.mx-stats {
    display: flex; gap: 0;
    background: var(--mx-surface);
    border: 1.5px solid var(--mx-border);
    border-radius: var(--mx-radius);
    box-shadow: var(--mx-shadow);
    overflow: hidden;
}
.mx-stat {
    flex: 1; padding: 1rem 1.25rem;
    border-right: 1.5px solid var(--mx-border);
    display: flex; flex-direction: column; gap: .2rem;
    position: relative;
    transition: background .15s ease;
}
.mx-stat:last-child { border-right: none; }
.mx-stat:hover { background: var(--mx-bg); }
.mx-stat::before {
    content: '';
    position: absolute; bottom: 0; left: 1.25rem; right: 1.25rem;
    height: 3px; border-radius: 99px;
}
.mx-stat--all::before       { background: var(--mx-blue); }
.mx-stat--scheduled::before { background: var(--mx-blue); }
.mx-stat--ongoing::before   { background: var(--mx-amber); }
.mx-stat--completed::before { background: var(--mx-green); }
.mx-stat--cancelled::before { background: var(--mx-red); }

.mx-stat__num { font-size: 1.8rem; font-weight: 700; color: var(--mx-text); line-height: 1; }
.mx-stat__lbl { font-size: .72rem; color: var(--mx-text-2); font-weight: 500; }

/* ── ALERTS ─────────────────── */
.mx-alert {
    display: flex; align-items: center; gap: .6rem;
    padding: .7rem 1rem; border-radius: var(--mx-radius-sm);
    font-size: .84rem; font-weight: 500;
    animation: slideDown .25s ease;
}
.mx-alert--success { background: var(--mx-green-2); color: var(--mx-green); border: 1px solid #abefc6; }
.mx-alert--error   { background: var(--mx-red-2);   color: var(--mx-red);   border: 1px solid var(--mx-red-3); }
.mx-alert__close { margin-left: auto; background: none; border: none; cursor: pointer; font-size: 1.2rem; line-height: 1; color: inherit; opacity: .7; }
.mx-alert__close:hover { opacity: 1; }

/* ── COLLAPSIBLE PANELS ─────── */
.mx-panel {
    background: var(--mx-surface);
    border: 1.5px solid var(--mx-border);
    border-radius: var(--mx-radius);
    box-shadow: var(--mx-shadow);
    overflow: hidden;
}
.mx-panel--warning { border-color: var(--mx-amber-3); }
.mx-panel__head {
    display: flex; align-items: center; justify-content: space-between;
    padding: .9rem 1.25rem;
    cursor: pointer;
    user-select: none;
    transition: background .15s;
    border-bottom: 1.5px solid var(--mx-border);
}
.mx-panel--warning .mx-panel__head { border-color: var(--mx-amber-3); background: var(--mx-amber-2); }
.mx-panel__head:hover { background: var(--mx-bg); }
.mx-panel--warning .mx-panel__head:hover { background: #fef3d5; }
.mx-panel__head-left { display: flex; align-items: center; gap: .55rem; font-size: .875rem; font-weight: 600; color: var(--mx-text); }
.mx-panel--warning .mx-panel__head-left { color: var(--mx-amber); }
.mx-panel__chevron { color: var(--mx-text-2); transition: transform .25s ease; flex-shrink: 0; }
.mx-panel.collapsed .mx-panel__chevron { transform: rotate(-90deg); }
.mx-panel.collapsed .mx-panel__body { display: none; }
.mx-panel__body { padding: 1.25rem; }
.mx-panel__active-badge {
    font-size: .62rem; font-weight: 700;
    background: var(--mx-blue); color: #fff;
    padding: .12rem .45rem; border-radius: 99px;
    letter-spacing: .06em;
}

/* ── FILTER GRID ─────────────── */
.mx-filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: .75rem 1rem;
    margin-bottom: 1rem;
}
.mx-filter-actions { display: flex; gap: .6rem; }

/* ── GENERATE ROW ────────────── */
.mx-generate-hint { font-size: .84rem; color: var(--mx-text-2); margin: 0 0 .9rem; }
.mx-generate-row { display: flex; align-items: flex-end; gap: .75rem; flex-wrap: wrap; }
.mx-field--grow { flex: 1; min-width: 240px; }
.mx-gen-result {
    margin-top: .9rem;
    padding: .75rem 1rem;
    border-radius: var(--mx-radius-sm);
    font-size: .84rem; font-weight: 500;
    animation: slideDown .25s ease;
}
.mx-gen-result.success { background: var(--mx-green-2); color: var(--mx-green); border: 1px solid #abefc6; }
.mx-gen-result.error   { background: var(--mx-red-2);   color: var(--mx-red);   border: 1px solid var(--mx-red-3); }

/* ── FORM FIELDS ─────────────── */
.mx-field { display: flex; flex-direction: column; gap: .3rem; }
.mx-label {
    font-size: .7rem; font-weight: 700; color: var(--mx-text-2);
    letter-spacing: .06em; text-transform: uppercase;
    display: flex; align-items: center; gap: .3rem;
}
.mx-select, .mx-input {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: .855rem; color: var(--mx-text);
    background: var(--mx-bg);
    border: 1.5px solid var(--mx-border-2);
    border-radius: var(--mx-radius-sm);
    padding: .5rem .75rem;
    outline: none;
    transition: border-color .15s ease, background .15s ease;
    width: 100%;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2398a2b3' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right .65rem center;
    padding-right: 2rem;
}
.mx-select:focus, .mx-input:focus {
    border-color: var(--mx-blue);
    background-color: #fff;
    box-shadow: 0 0 0 3px rgba(21,112,239,.12);
}

/* ── BUTTONS ─────────────────── */
.mx-btn {
    display: inline-flex; align-items: center; gap: .45rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: .855rem; font-weight: 600;
    padding: .55rem 1.1rem;
    border-radius: var(--mx-radius-sm);
    border: 1.5px solid transparent;
    cursor: pointer;
    text-decoration: none;
    transition: all .16s ease;
    white-space: nowrap;
}
.mx-btn--primary { background: var(--mx-blue); color: #fff; border-color: var(--mx-blue); }
.mx-btn--primary:hover { background: #0e62d4; border-color: #0e62d4; }
.mx-btn--warning { background: var(--mx-amber); color: #fff; border-color: var(--mx-amber); }
.mx-btn--warning:hover { background: #b45309; }
.mx-btn--danger { background: var(--mx-red); color: #fff; border-color: var(--mx-red); }
.mx-btn--danger:hover { background: #b91c1c; }
.mx-btn--ghost { background: transparent; color: var(--mx-text-2); border-color: var(--mx-border-2); }
.mx-btn--ghost:hover { background: var(--mx-bg); color: var(--mx-text); }
.mx-btn:disabled { opacity: .6; cursor: not-allowed; }

/* ── TABLE CARD ──────────────── */
.mx-card {
    background: var(--mx-surface);
    border: 1.5px solid var(--mx-border);
    border-radius: var(--mx-radius);
    box-shadow: var(--mx-shadow);
    overflow: hidden;
}
.mx-card__head {
    display: flex; align-items: center; justify-content: space-between;
    padding: .9rem 1.25rem;
    border-bottom: 1.5px solid var(--mx-border);
    background: var(--mx-bg);
    flex-wrap: wrap; gap: .75rem;
}
.mx-card__head-left { display: flex; align-items: center; gap: .5rem; font-size: .9rem; font-weight: 700; color: var(--mx-text); }
.mx-card__head-right { display: flex; align-items: center; gap: 1rem; }
.mx-total-badge {
    font-size: .72rem; font-weight: 700;
    background: var(--mx-blue-2); color: var(--mx-blue);
    border: 1px solid var(--mx-blue-3);
    padding: .25rem .65rem; border-radius: 99px;
}
.mx-perpage { display: flex; align-items: center; gap: .4rem; font-size: .78rem; color: var(--mx-text-2); }
.mx-perpage select {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: .78rem; padding: .2rem .5rem;
    border: 1.5px solid var(--mx-border-2);
    border-radius: var(--mx-radius-xs);
    background: #fff; cursor: pointer;
}

/* ── TABLE ───────────────────── */
.mx-table-wrap { overflow-x: auto; }
.mx-table { width: 100%; border-collapse: collapse; }
.mx-table thead tr {
    background: var(--mx-bg);
    border-bottom: 1.5px solid var(--mx-border);
}
.mx-table th {
    padding: .65rem 1rem;
    font-size: .67rem; font-weight: 700; letter-spacing: .07em;
    text-transform: uppercase; color: var(--mx-text-2);
    text-align: left; white-space: nowrap;
    display: table-cell; align-items: center; gap: .3rem;
}
.mx-th--num    { width: 3.5rem; }
.mx-th--center { text-align: center; }

.mx-tr {
    border-bottom: 1px solid var(--mx-border);
    transition: background .12s ease;
}
.mx-tr:last-child { border-bottom: none; }
.mx-tr:hover { background: #f9fafb; }
.mx-tr[data-status="ongoing"]   { border-left: 3px solid var(--mx-amber); }
.mx-tr[data-status="completed"] { border-left: 3px solid var(--mx-green); }
.mx-tr[data-status="cancelled"] { border-left: 3px solid var(--mx-red); opacity: .75; }

.mx-table td { padding: .75rem 1rem; font-size: .855rem; color: var(--mx-text); vertical-align: middle; }
.mx-td--num    { color: var(--mx-text-3); font-size: .75rem; }
.mx-td--center { text-align: center; }

/* ── TABLE CELLS ─────────────── */
.mx-cell-session { display: flex; flex-direction: column; gap: .05rem; }
.mx-cell-session__date { font-weight: 600; font-size: .84rem; }
.mx-cell-session__time { font-size: .75rem; color: var(--mx-blue); font-weight: 600; }

.mx-cell-cat { display: flex; flex-direction: column; gap: .05rem; }
.mx-cell-cat__disc { font-weight: 600; font-size: .84rem; }
.mx-cell-cat__age  { font-size: .72rem; color: var(--mx-text-2); }

.mx-cell-athlete { display: flex; align-items: center; gap: .6rem; }
.mx-avatar {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, var(--mx-blue-2), var(--mx-blue-3));
    color: var(--mx-blue);
    font-size: .65rem; font-weight: 800;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    border: 1.5px solid var(--mx-border);
}

.mx-chip {
    display: inline-block; font-size: .72rem; font-weight: 500;
    background: var(--mx-violet-2); color: var(--mx-violet);
    padding: .2rem .55rem; border-radius: 99px;
    max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.mx-order-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px;
    background: var(--mx-text); color: #fff;
    font-size: .78rem; font-weight: 700;
    border-radius: 50%;
}
.mx-time-chip {
    display: inline-block; font-size: .78rem; font-weight: 700;
    background: var(--mx-blue-2); color: var(--mx-blue);
    padding: .2rem .55rem; border-radius: var(--mx-radius-xs);
    letter-spacing: .04em;
}
.mx-arena-chip {
    display: inline-block; font-size: .72rem; font-weight: 600;
    background: #f0f9ff; color: #026aa2;
    border: 1px solid #b9e6fe;
    padding: .2rem .55rem; border-radius: var(--mx-radius-xs);
}
.mx-na { color: var(--mx-text-3); font-size: .8rem; }

/* Status */
.mx-status {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .72rem; font-weight: 700;
    padding: .28rem .65rem; border-radius: 99px;
    white-space: nowrap;
}
.mx-status__dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.mx-status--scheduled { background: var(--mx-blue-2);  color: var(--mx-blue);  }
.mx-status--scheduled .mx-status__dot  { background: var(--mx-blue); }
.mx-status--ongoing   { background: var(--mx-amber-2); color: var(--mx-amber); }
.mx-status--ongoing .mx-status__dot   { background: var(--mx-amber); animation: pulse 1.5s infinite; }
.mx-status--completed { background: var(--mx-green-2); color: var(--mx-green); }
.mx-status--completed .mx-status__dot { background: var(--mx-green); }
.mx-status--cancelled { background: #f2f4f7; color: var(--mx-text-2); }
.mx-status--cancelled .mx-status__dot { background: var(--mx-text-3); }
.mx-status--unknown   { background: #f2f4f7; color: var(--mx-text-2); }

/* ── ACTION BUTTONS ──────────── */
.mx-actions { display: flex; align-items: center; justify-content: center; gap: .35rem; }
.mx-action-btn {
    width: 30px; height: 30px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: var(--mx-radius-xs);
    border: 1.5px solid transparent;
    cursor: pointer; background: none;
    transition: all .15s ease;
}
.mx-action-btn--edit   { color: var(--mx-amber); border-color: var(--mx-amber-3); background: var(--mx-amber-2); }
.mx-action-btn--edit:hover { background: var(--mx-amber); color: #fff; border-color: var(--mx-amber); }
.mx-action-btn--delete { color: var(--mx-red); border-color: var(--mx-red-3); background: var(--mx-red-2); }
.mx-action-btn--delete:hover { background: var(--mx-red); color: #fff; border-color: var(--mx-red); }

/* ── EMPTY STATE ─────────────── */
.mx-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: .6rem; padding: 4rem 1rem;
    color: var(--mx-text-3);
}
.mx-empty p { font-size: .95rem; font-weight: 600; color: var(--mx-text-2); margin: 0; }
.mx-empty span { font-size: .82rem; }

/* ── PAGINATION ──────────────── */
.mx-pagination {
    padding: .85rem 1.25rem;
    border-top: 1.5px solid var(--mx-border);
    background: var(--mx-bg);
    display: flex; justify-content: flex-end;
}

/* ── MODAL ───────────────────── */
.mx-modal-overlay {
    display: none;
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(16,24,40,.5);
    backdrop-filter: blur(3px);
    align-items: center; justify-content: center;
    padding: 1.5rem;
}
.mx-modal-overlay.open { display: flex; animation: fadeIn .2s ease; }
.mx-modal {
    background: #fff;
    border-radius: var(--mx-radius);
    box-shadow: var(--mx-shadow-lg);
    width: 100%; max-width: 440px;
    animation: slideUp .25s ease;
    overflow: hidden;
}
.mx-modal--sm { max-width: 380px; }
.mx-modal__head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1.5px solid var(--mx-border);
    font-size: .9rem; font-weight: 700; color: var(--mx-text);
    gap: .5rem;
}
.mx-modal__head--danger { background: var(--mx-red-2); color: var(--mx-red); border-color: var(--mx-red-3); }
.mx-modal__head-left { display: flex; align-items: center; gap: .55rem; }
.mx-modal__close {
    width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    border: none; background: none; cursor: pointer;
    color: var(--mx-text-2); border-radius: 6px;
    transition: background .15s;
}
.mx-modal__close:hover { background: var(--mx-bg); }
.mx-modal__body { padding: 1.25rem; }
.mx-modal__foot {
    display: flex; gap: .6rem; justify-content: flex-end;
    padding: 1rem 1.25rem;
    border-top: 1.5px solid var(--mx-border);
    background: var(--mx-bg);
}

/* Status preview in modal */
.mx-status-preview {
    margin-top: .85rem; padding: .65rem .85rem;
    border-radius: var(--mx-radius-sm);
    font-size: .82rem; font-weight: 500;
    display: none;
}

/* Delete confirm */
.mx-delete-confirm {
    display: flex; flex-direction: column; align-items: center;
    text-align: center; gap: .5rem; padding: .5rem 0;
}
.mx-delete-confirm__icon {
    width: 52px; height: 52px;
    background: var(--mx-red-2); color: var(--mx-red);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: .25rem;
}
.mx-delete-confirm p { font-size: .9rem; font-weight: 600; color: var(--mx-text); margin: 0; }
.mx-delete-confirm span { font-size: .8rem; color: var(--mx-text-2); }

/* ── KEYFRAMES ───────────────── */
@keyframes fadeIn  { from { opacity: 0; }           to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes slideDown { from { transform: translateY(-8px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }

/* ── RESPONSIVE ──────────────── */
@media(max-width: 768px){
    .mx-page { padding: 1rem; }
    .mx-title { font-size: 1.8rem; }
    .mx-stats { flex-wrap: wrap; }
    .mx-stat { min-width: calc(50% - 1.5px); }
    .mx-filter-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ══ PANEL TOGGLE ════════════════════════════════════ */
function togglePanel(id) {
    document.getElementById(id).classList.toggle('collapsed');
}
// Filter panel starts open, generate starts collapsed
document.getElementById('generatePanel').classList.add('collapsed');

/* ══ MODAL HELPERS ═══════════════════════════════════ */
function openModal(id) {
    const el = document.getElementById(id);
    el.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}
// Close on overlay click
document.querySelectorAll('.mx-modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) closeModal(el.id); });
});
// Close on Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.mx-modal-overlay.open').forEach(m => closeModal(m.id));
});

/* ══ VALIDATE / GENERATE BRACKET ════════════════════ */
function generateBracket() {
    const sessionId = document.getElementById('genSessionId').value;
    if (!sessionId) { showGenResult('Pilih sesi terlebih dahulu.', 'error'); return; }

    const btn      = document.getElementById('genBtn');
    const btnText  = document.getElementById('genBtnText');
    btn.disabled   = true;
    btnText.textContent = 'Memproses…';

    fetch('/admin/matches/generate', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ competition_session_id: sessionId })
    })
    .then(r => r.json())
    .then(data => {
        showGenResult(data.message, data.status === 'success' ? 'success' : 'error');
        if (data.status === 'success') setTimeout(() => location.reload(), 1200);
    })
    .catch(() => showGenResult('Terjadi kesalahan pada server.', 'error'))
    .finally(() => { btn.disabled = false; btnText.textContent = 'Validasi / Jadwalkan'; });
}

function showGenResult(msg, type) {
    const el = document.getElementById('genResult');
    el.textContent = msg;
    el.className   = 'mx-gen-result ' + type;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 4000);
}

/* ══ EDIT STATUS ════════════════════════════════════ */
const STATUS_META = {
    scheduled:  { label: 'Dijadwalkan',  cls: 'mx-status--scheduled', bg: '#eff4ff', color: '#1570ef' },
    ongoing:    { label: 'Berlangsung',  cls: 'mx-status--ongoing',   bg: '#fffaeb', color: '#dc6803' },
    completed:  { label: 'Selesai',      cls: 'mx-status--completed', bg: '#ecfdf3', color: '#079455' },
    cancelled:  { label: 'Dibatalkan',   cls: 'mx-status--cancelled', bg: '#f2f4f7', color: '#667085' },
};

document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('editMatchId').value = this.dataset.id;
        document.getElementById('editStatus').value  = this.dataset.status;
        updateStatusPreview(this.dataset.status);
        openModal('editModal');
    });
});

document.getElementById('editStatus').addEventListener('change', function () {
    updateStatusPreview(this.value);
});

function updateStatusPreview(val) {
    const m   = STATUS_META[val] || {};
    const el  = document.getElementById('statusPreview');
    if (!m.label) { el.style.display = 'none'; return; }
    el.style.display    = 'block';
    el.style.background = m.bg;
    el.style.color      = m.color;
    el.style.border     = `1px solid ${m.color}30`;
    el.textContent      = '● ' + m.label;
}

function submitEditMatch() {
    const id     = document.getElementById('editMatchId').value;
    const status = document.getElementById('editStatus').value;
    const btn    = document.getElementById('submitEditBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Menyimpan…';

    fetch(`/admin/matches/${id}`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ status })
    })
    .then(r => r.json())
    .then(data => {
        closeModal('editModal');
        showToast(data.message, data.status === 'success' ? 'success' : 'error');
        if (data.status === 'success') setTimeout(() => location.reload(), 900);
    })
    .catch(() => showToast('Terjadi kesalahan.', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Update Status';
    });
}

/* ══ DELETE ═════════════════════════════════════════ */
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('deleteMatchId').value = this.dataset.id;
        document.getElementById('deleteMatchName').textContent = this.dataset.name;
        openModal('deleteModal');
    });
});

function submitDelete() {
    const id  = document.getElementById('deleteMatchId').value;
    const btn = document.getElementById('submitDeleteBtn');
    btn.disabled = true;
    btn.textContent = 'Menghapus…';

    fetch(`/admin/matches/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        closeModal('deleteModal');
        showToast(data.message, data.status === 'success' ? 'success' : 'error');
        if (data.status === 'success') setTimeout(() => location.reload(), 900);
    })
    .catch(() => showToast('Terjadi kesalahan.', 'error'))
    .finally(() => { btn.disabled = false; btn.textContent = 'Ya, Hapus'; });
}

/* ══ TOAST ══════════════════════════════════════════ */
function showToast(msg, type) {
    let toast = document.createElement('div');
    toast.style.cssText = `
        position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999;
        padding:.75rem 1.1rem; border-radius:10px; font-size:.855rem;
        font-weight:600; font-family:'Plus Jakarta Sans',sans-serif;
        box-shadow:0 8px 24px rgba(16,24,40,.18);
        display:flex; align-items:center; gap:.55rem;
        animation: slideUp .25s ease;
        background:${type === 'success' ? '#ecfdf3' : '#fef3f2'};
        color:${type === 'success' ? '#079455' : '#d92d20'};
        border:1px solid ${type === 'success' ? '#abefc6' : '#fecdca'};
    `;
    toast.textContent = (type === 'success' ? '✓ ' : '✕ ') + msg;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}
</script>
@endpush