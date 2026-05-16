@extends('layouts.admin')

@section('title', 'Perguruan Management')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="mb-0">Perguruan Management</h1>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('admin.perguruans.index') }}" class="d-flex mb-3">
            <input type="text" name="search" class="form-control me-2" placeholder="Cari nama/email..."
                value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-secondary">
                <i class="fas fa-search"></i>
            </button>
        </form>

        @include('components._alerts')

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    {{ $perguruans->total() }} Perguruan
                </h5>
            </div>
            <div class="card-body">
                @if ($perguruans->isEmpty())
                    <p class="text-muted text-center py-4">Tidak ada data perguruan.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Perguruan</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>RegistedBy</th>
                                    <th>Status</th>
                                    <th>Terdaftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($perguruans as $index => $user)
                                    <tr>
                                        <td>{{ $perguruans->firstItem() + $index }}</td>
                                        <td>{{ $user->perguruan?->name ?? '-' }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone ?? '-' }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>
                                            @if ($user->status === 'active')
                                                <span class="badge bg-success">Aktif</span>
                                            @elseif($user->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $user->status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if ($user->status === 'pending')
                                                    <button class="btn btn-sm btn-success verify-perguruan"
                                                        data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                                        title="Verify">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger reject-perguruan"
                                                        data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                                        title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                                <a href="{{ route('admin.perguruans.show', $user) }}"
                                                    class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $perguruans->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Verify --}}
    @include('components._modal', [
        'id' => 'verifyModal',
        'title' => 'Verify Perguruan',
        'body' => '
                        <form id="verifyForm">
                            <input type="hidden" name="id" id="verifyId">
                            <p>Approve <strong id="verifyName"></strong>?</p>
                            <div class="mb-3">
                                <label class="form-label">Nama Perguruan</label>
                                <input type="text" class="form-control" name="name" id="perguruanName" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" name="address" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone">
                            </div>
                        </form>
                    ',
        'footer' => '
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-success" onclick="verifyPerguruan()">Verify</button>
                    ',
    ])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.verify-perguruan').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('verifyId').value = this.dataset.id;
                    document.getElementById('verifyName').textContent = this.dataset.name;
                    document.getElementById('perguruanName').value = this.dataset.name;
                    new bootstrap.Modal(document.getElementById('verifyModal')).show();
                });
            });

            document.querySelectorAll('.reject-perguruan').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('Tolak pendaftaran ' + this.dataset.name + '?')) {
                        rejectPerguruan(this.dataset.id);
                    }
                });
            });
        });

        function verifyPerguruan() {
            const form = document.getElementById('verifyForm');
            const formData = new FormData(form);

            fetch(`/admin/perguruans/${formData.get('id')}/verify`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') location.reload();
                    else alert(data.message);
                });
        }

        function rejectPerguruan(id) {
            const reason = prompt('Alasan penolakan:');
            if (reason) {
                fetch(`/admin/perguruans/${id}/reject`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            reason
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success') location.reload();
                        else alert(data.message);
                    });
            }
        }
    </script>
@endsection
