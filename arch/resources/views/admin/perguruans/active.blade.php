@extends('layouts.admin')

@section('title', 'Active Perguruan List')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Active Perguruans</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.perguruans.index') }}">Pending</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Active</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.perguruans.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-clock me-1"></i> Pending
            </a>
            <a href="{{ route('admin.perguruans.active') }}" class="btn btn-primary">
                <i class="fas fa-check-double me-1"></i> Active
            </a>
        </div>
    </div>

    @include('components._alerts')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-school me-2 text-success"></i>
                {{ $perguruans->total() }} Verified Active Perguruans
            </h5>
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm me-2"
                    placeholder="Cari nama/email..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="card-body">
            @include('components._table', [
                'headers' => ['No', 'Name', 'Email', 'Phone', 'Perguruan', 'Verified', 'Athletes', 'Actions'],
                'rows' => $perguruans->map(function ($user, $index) {
                    return [
                        $perguruans->firstItem() + $index,
                        $user->name,
                        $user->email,
                        $user->phone ?? '-',
                        $user->perguruan?->name ?? 'Not set',
                        $user->updated_at->format('d M Y H:i'),
                        $user->perguruan?->athletes_count ?? 0,
                        '
                                        <div class="btn-group">
                                            <a href="' .
                        route('admin.perguruans.show', $user) .
                        '"
                                               class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="' .
                        route('admin.athletes.index') .
                        '?perguruan_id=' .
                        $user->perguruan_id .
                        '"
                                               class="btn btn-sm btn-success" title="Atlet">
                                                <i class="fas fa-users"></i>
                                            </a>
                                        </div>
                                    ',
                    ];
                }),
            ])

            <div class="mt-3">
                {{ $perguruans->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
