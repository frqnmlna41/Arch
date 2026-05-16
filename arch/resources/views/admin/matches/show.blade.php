@extends('layouts.admin')

@section('title', 'Detail Match #' . $match->match_number)

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Detail Match #{{ $match->match_number }}</h1>
            <a href="{{ route('admin.matches.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="row g-4">

            {{-- Info Utama --}}
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Pertandingan</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="180">Event</th>
                                <td>{{ $match->event?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Lokasi Event</th>
                                <td>{{ $match->event?->location ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Disiplin</th>
                                <td>{{ $match->discipline?->name ?? '-' }}
                                    <span class="badge bg-secondary ms-1">{{ $match->discipline?->type }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Kategori Usia</th>
                                <td>{{ $match->ageCategory?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Ronde</th>
                                <td>{{ str_replace('_', ' ', ucfirst($match->round ?? '-')) }}</td>
                            </tr>
                            <tr>
                                <th>Arena</th>
                                <td>{{ $match->arena?->name ?? '-' }}
                                    @if ($match->arena?->location)
                                        <small class="text-muted">— {{ $match->arena->location }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal</th>
                                <td>
                                    @if ($match->match_date)
                                        {{ \Carbon\Carbon::parse($match->match_date)->format('d F Y') }}
                                        pukul {{ $match->match_time ?? '-' }}
                                    @else
                                        <span class="text-muted">Belum dijadwalkan</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @php
                                        $badgeClass = match ($match->status) {
                                            'scheduled' => 'bg-primary',
                                            'ongoing' => 'bg-warning text-dark',
                                            'completed' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} fs-6">
                                        {{ ucfirst($match->status ?? '-') }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Hasil --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Hasil</h5>
                    </div>
                    <div class="card-body text-center">
                        @if ($match->result)
                            <div class="display-6 fw-bold text-success mb-2">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <p class="lead mb-1">Pemenang</p>
                            <h4 class="fw-bold">{{ $match->result->winner?->name ?? '-' }}</h4>
                        @else
                            <p class="text-muted mt-3">Belum ada hasil</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- VS Card --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Peserta</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center text-center">
                            {{-- Atlet 1 --}}
                            <div class="col-md-5">
                                <div
                                    class="p-3 border rounded
                                @if ($match->result?->winner_id === $match->athlete1?->id) border-success bg-success bg-opacity-10 @endif">
                                    <div class="display-6 mb-2">🥋</div>
                                    <h4>{{ $match->athlete1?->name ?? '-' }}</h4>
                                    <p class="text-muted mb-1">{{ $match->athlete1?->club ?? '-' }}</p>
                                    <span class="badge bg-secondary">{{ $match->athlete1?->gender ?? '-' }}</span>
                                    @if ($match->result?->winner_id === $match->athlete1?->id)
                                        <div class="mt-2"><span class="badge bg-success fs-6">🏆 Pemenang</span></div>
                                    @endif
                                </div>
                            </div>

                            {{-- VS --}}
                            <div class="col-md-2">
                                <span class="display-4 fw-bold text-muted">VS</span>
                            </div>

                            {{-- Atlet 2 --}}
                            <div class="col-md-5">
                                @if ($match->athlete2)
                                    <div
                                        class="p-3 border rounded
                                @if ($match->result?->winner_id === $match->athlete2?->id) border-success bg-success bg-opacity-10 @endif">
                                        <div class="display-6 mb-2">🥋</div>
                                        <h4>{{ $match->athlete2->name }}</h4>
                                        <p class="text-muted mb-1">{{ $match->athlete2->club ?? '-' }}</p>
                                        <span class="badge bg-secondary">{{ $match->athlete2->gender ?? '-' }}</span>
                                        @if ($match->result?->winner_id === $match->athlete2?->id)
                                            <div class="mt-2"><span class="badge bg-success fs-6">🏆 Pemenang</span></div>
                                        @endif
                                    </div>
                                @else
                                    <div class="p-3 border rounded border-dashed text-muted">
                                        <div class="display-6 mb-2">🚫</div>
                                        <h4>BYE</h4>
                                        <p>Tidak ada lawan</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Skor per Juri --}}
            @if ($match->scores && $match->scores->count())
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Skor Juri</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Juri</th>
                                            <th>Skor Atlet 1</th>
                                            <th>Skor Atlet 2</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($match->scores as $score)
                                            <tr>
                                                <td>{{ $score->judge?->name ?? '-' }}</td>
                                                <td>{{ $score->athlete1_score ?? '-' }}</td>
                                                <td>{{ $score->athlete2_score ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection
