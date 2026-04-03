@extends('layouts.admin')

@section('title', 'Pending Perguruan Registrations')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Pending Perguruan Registrations</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    @include('components._alerts')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-users me-2"></i>
                {{ $perguruans->total() }} Pending Registrations
            </h5>
        </div>

        <div class="card-body">
            @include('components._table', [
                'headers' => ['No', 'Name', 'Email', 'Phone', 'Perguruan', 'Registered', 'Actions'],
                'rows' => $perguruans->map(function ($user, $index) {
                    return [
                        $perguruans->firstItem() + $index,
                        $user->name,
                        $user->email,
                        $user->phone ?? '-',
                        $user->perguruan?->name ?? 'Not set',
                        $user->created_at->format('d M Y H:i'),
                        '
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-success verify-perguruan"
                                                data-id="' .
                        $user->id .
                        '"
                                                data-name="' .
                        htmlspecialchars($user->name) .
                        '"
                                                title="Verify">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger reject-perguruan"
                                                data-id="' .
                        $user->id .
                        '"
                                                data-name="' .
                        htmlspecialchars($user->name) .
                        '"
                                                title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <a href="' .
                        route('admin.perguruans.show', $user) .
                        '" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                ',
                    ];
                }),
            ])

            <div class="mt-3">
                {{ $perguruans->links() }}
            </div>
        </div>
    </div>

    @include('components._modal', [
        'id' => 'verifyModal',
        'title' => 'Verify Perguruan',
        'body' => '
            <form id="verifyForm">
                <input type="hidden" name="id" id="verifyId">
                <p>Approve <strong id="verifyName"></strong> registration?</p>
                <div class="mb-3">
                    <label class="form-label">Perguruan Name</label>
                    <input type="text" class="form-control" name="perguruan_name" id="perguruanName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="address" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="tel" class="form-control" name="phone">
                </div>
            </form>
        ',
        'footer' => '
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success" onclick="verifyPerguruan()">Verify</button>
        ',
    ])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verify button
            document.querySelectorAll('.verify-perguruan').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('verifyId').value = this.dataset.id;
                    document.getElementById('verifyName').textContent = this.dataset.name;
                    document.getElementById('perguruanName').value = this.dataset.name;
                    new bootstrap.Modal(document.getElementById('verifyModal')).show();
                });
            });

            // Reject button
            document.querySelectorAll('.reject-perguruan').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('Reject ' + this.dataset.name + ' registration?')) {
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        }

        function rejectPerguruan(id) {
            const reason = prompt('Reason for rejection:');
            if (reason) {
                fetch(`/admin/perguruans/${id}/reject`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            reason
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
            }
        }
    </script>

@endsection
