@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <div class="min-vh-100 d-flex align-items-center py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card border-0 shadow">
                        <div class="card-body p-5">
                            <div class="text-center mb-5">
                                <h2 class="fw-bold text-primary mb-2">Tournament CMS</h2>
                                <p class="text-muted">Sistem Manajemen Kejuaraan Olahraga</p>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Info:</strong> Pendaftaran perguruan melalui <a
                                    href="{{ route('auth.register-perguruan') }}" class="alert-link">form perguruan</a>.
                                Atlet didaftarkan oleh perguruan masing-masing.
                            </div>

                            <div class="text-center">
                                <a href="/login" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login
                                </a>
                                <a href="{{ route('auth.register-perguruan') }}" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-school me-2"></i> Daftar Perguruan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
