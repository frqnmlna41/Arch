{{-- resources/views/admin/arenas/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Tambah Arena')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.arenas.index') }}"
        class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500 transition">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div>
        <h2 class="text-xl font-bold text-gray-800">Tambah Arena</h2>
        <p class="text-sm text-gray-500">Tambahkan arena / lapangan pertandingan baru</p>
    </div>
</div>

<div class="max-w-2xl">
    <form action="{{ route('admin.arenas.store') }}" method="POST" class="space-y-5">
        @csrf
        @include('admin.arenas._form')
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.arenas.index') }}" class="btn btn-sm btn-ghost text-gray-500">Batal</a>
            <button type="submit" class="btn btn-sm bg-orange-500 hover:bg-orange-600 text-white border-none gap-2">
                <i class="fas fa-save"></i> Simpan Arena
            </button>
        </div>
    </form>
</div>

@endsection