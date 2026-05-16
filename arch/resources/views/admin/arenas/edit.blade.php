@extends('layouts.admin')
@section('title', 'Edit Arena - ' . $arena->name)

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.arenas.show', $arena) }}"
        class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500 transition">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div>
        <h2 class="text-xl font-bold text-gray-800">Edit Arena</h2>
        <p class="text-sm text-gray-500">{{ $arena->name }}</p>
    </div>
</div>

<div class="max-w-2xl">
    <form action="{{ route('admin.arenas.update', $arena) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')
        @include('admin.arenas._form')
        <div class="flex justify-between items-center">
            {{-- Delete --}}
            @if($arena->matches_count === 0)
            <form action="{{ route('admin.arenas.destroy', $arena) }}" method="POST"
                onsubmit="return confirm('Hapus arena {{ addslashes($arena->name) }}? Tindakan ini tidak bisa dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-ghost text-red-400 hover:bg-red-50 gap-1">
                    <i class="fas fa-trash text-xs"></i> Hapus Arena
                </button>
            </form>
            @else
            <span class="text-xs text-gray-400 italic">
                <i class="fas fa-info-circle mr-1"></i>
                Arena tidak bisa dihapus karena memiliki {{ $arena->matches_count }} pertandingan
            </span>
            @endif

            <div class="flex gap-3">
                <a href="{{ route('admin.arenas.show', $arena) }}" class="btn btn-sm btn-ghost text-gray-500">Batal</a>
                <button type="submit" class="btn btn-sm bg-orange-500 hover:bg-orange-600 text-white border-none gap-2">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

@endsection