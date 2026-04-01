@extends('layouts.admin')

@section('title', 'Perguruan Detail')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1>Perguruan Detail</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.perguruans.index') }}">Perguruans</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.perguruans.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> Back to List
            </a>
            @if ($user->status === 'pending')
                <button class="btn btn-success verify-perguruan" data-id="{{ $user->id }}">
                    <i class="fas fa-check"></i> Verify
                </button>
                <button class="btn btn-danger reject-perguruan" data-id="{{ $user->id }}">
                    <i class="fas fa-times"></i> Reject
                </button>
            @endif
        </div>
    </div>

    @include('components._alerts')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Pengguna</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%"><strong>Nama Lengkap</strong></td>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Phone</strong></td>
                            <td>{{ $user->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>
                                @switch($user->status)
                                    @case('pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @break

                                    @case('active')
                                        <span class="badge bg-success">Active</span>
                                    @break

                                    @case('rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @break

                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($user->status) }}</span>
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Registered</strong></td>
                            <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Role</strong></td>
                            <td>
                                @forelse($user->roles as $role)
                                    <span class="badge bg-primary">{{ $role->name }}</span>
                                @empty
                                    <span class="badge bg-secondary">No Role</span>
                                @endforelse
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if ($user->perguruan)
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Informasi Perguruan</h6>
                    </div>
                    <div class="card-body text-center">
                        @if ($user->perguruan->logo)
                            <img src="{{ Storage::url($user->perguruan->logo) }}" class="rounded-circle mb-3"
                                style="width: 100px; height: 100px; object-fit: cover;">
                        @endif
                        <h6 class="fw-bold">{{ $user->perguruan->name }}</h6>
                        <p class="text-muted small">{{ $user->perguruan->slug }}</p>
                        @if ($user->perguruan->address)
                            <p class="mb-1"><i
                                    class="fas fa-map-marker-alt me-1 text-danger"></i>{{ $user->perguruan->address }}</p>
                        @endif
                        @if ($user->perguruan->phone)
                            <p class="mb-1"><i class="fas fa-phone me-1 text-success"></i>{{ $user->perguruan->phone }}
                            </p>
                        @endif
                        @if ($user->perguruan->email)
                            <p><i class="fas fa-envelope me-1 text-primary"></i>{{ $user->perguruan->email }}</p>
                        @endif
                        <hr>
                        <small class="text-muted">
                            Active: {{ $user->perguruan->is_active ? 'Yes' : 'No' }}<br>
                            Created: {{ $user->perguruan->created_at->format('d M Y') }}
                        </small>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0">Athletes ({{ $athletes->count() }})</h6>
        </div>
        <div class="card-body">
            @if ($athletes->count())
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Weight</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($athletes as $athlete)
                                <tr>
                                    <td>
                                        <strong>{{ $athlete->name }}</strong>
                                        @if ($athlete->club)
                                            <br><small class="text-muted">{{ $athlete->club }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $athlete->gender == 'male' ? 'bg-blue' : 'bg-pink' }}">
                                            {{ ucfirst($athlete->gender) }}
                                        </span>
                                    </td>
                                    <td>{{ $athlete->age }}</td>
                                    <td>{{ $athlete->weight }} kg</td>
                                    <td>
                                        <span class="badge {{ $athlete->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $athlete->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No athletes registered yet</p>
                </div>
            @endif
        </div>
    </div>

    @include('partials._modal')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.verify-perguruan, .reject-perguruan').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id || this.closest('[data-id]').dataset.id;
                    if (this.classList.contains('verify-perguruan')) {
                        // AJAX verify
                        fetch(`/admin/perguruans/${id}/verify`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                            }
                        }).then(r => r.json()).then(data => {
                            if (data.status === 'success') location.reload();
                            else alert(data.message);
                        });
                    } else {
                        if (confirm('Reject this registration?')) {
                            // AJAX reject
                        }
                    }
                });
            });
        });
    </script>
@endsection
