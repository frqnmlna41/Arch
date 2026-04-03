@extends('layouts.guest')

@section('title', 'Perguruan Registration')

@section('content')
    <div class="min-vh-100 d-flex align-items-center" style="">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-primary text-center py-4">
                            <h2 class="mb-0 fw-bold">
                                <i class="fas fa-school me-2"></i>
                                Daftar Perguruan
                            </h2>
                            <p class="mb-0 opacity-75 mt-2">Buat akun perguruan untuk mengelola atlet</p>
                        </div>

                        <div class="card-body p-5">
                            @include('components._alerts')

                            <form action="{{ route('auth.register-perguruan') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control border rounded-lg border-secondary @error('name') is-invalid @enderror"
                                            name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control border rounded-lg border-secondary @error('email') is-invalid @enderror"
                                            name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control border rounded-lg border-secondary @error('password') is-invalid @enderror"
                                            name="password" required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Konfirmasi Password <span
                                                class="text-danger">*</span></label>
                                        <input type="password"
                                            class="form-control border rounded-lg border-secondary @error('password_confirmation') is-invalid @enderror"
                                            name="password_confirmation" required>
                                        @error('password_confirmation')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Nama Perguruan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control border rounded-lg border-secondary @error('perguruan_name') is-invalid @enderror"
                                        name="perguruan_name" value="{{ old('perguruan_name') }}" required>
                                    @error('perguruan_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Alamat Perguruan</label>
                                    <textarea class="form-control border rounded-lg border-secondary @error('address') is-invalid @enderror" name="address" rows="3">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">No. Telepon</label>
                                        <input type="tel" class="form-control border rounded-lg border-secondary @error('phone') is-invalid @enderror"
                                            name="phone" value="{{ old('phone') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Logo Perguruan</label>
                                        <input type="file" class="form-control border rounded-lg border-secondary @error('logo') is-invalid @enderror"
                                            name="logo" accept="image/*">
                                        @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-user-plus me-2"></i>
                                        Daftar Perguruan
                                    </button>
                                </div>

                                <div class="text-center">
                                    <p class="mb-0 text-muted">
                                        Sudah punya akun?
                                        <a href="/login" class="text-decoration-none fw-semibold">
                                            Login sekarang
                                        </a>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
