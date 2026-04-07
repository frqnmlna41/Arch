@extends('layouts.admin')

@section('title', 'Dashboard Perguruan')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-user-plus mr-3 text-emerald-600"></i>
                    Tambah Atlet
                </h1>
            </div>
        </div>
        <!-- Form -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <form action="{{ route('athletes.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="name" id="name" required
                            class="input input-neutral mt-1 block w-full">
                    </div>
                    <div>
                        <label for="perguruan" class="block text-sm font-medium text-gray-700">Perguruan</label>
                        <p class="mt-1 text-base">{{ 'Perguruan Kung Fu' }}</p>
                    </div>
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                        <select name="gender" id="gender" required
                            class="input input-neutral mt-1 block w-full">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700">Berat</label>
                        <input type="text" name="weight" id="weight" required
                            class="input input-neutral mt-1 block w-full">
                    </div>
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" required
                            class="input input-neutral mt-1 block w-full">
                    </div>
                    <div>
                        <label for="photo" class="block text-sm font-medium text-gray-700">Foto Atlet</label>
                        <input type="file" name="photo" id="photo" accept="image/*"
                            class="file-input file-input-neutral mt-1 block w-full text-sm text-gray-500">
                    </div>
                    <div>
                        <a href="/admin/dashboard-perguruan" type="button"
                            class="btn btn-error mt-6">
                            Batalkan
                        </a>
                        <button type="submit"
                            class="btn btn-success mt-6 ml-2">
                            Simpan Atlet
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection