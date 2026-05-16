@extends('layouts.coach')

@section('title', 'Perguruan | Pelatih Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-school mr-3 text-blue-600"></i>
                    {{ auth()->user()->perguruan->name ?? 'Perguruan' }} Dashboard
                </h1>
                <p class="text-gray-600 mt-1">Kelola atlet, event, dan prestasi perguruan</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-users text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Atlet</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['totalAthletes']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-calendar-check text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Event Aktif</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['activeEvents']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <i class="fas fa-trophy text-2xl text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Kemenangan</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['totalWins']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-star text-2xl text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Tagihan</p>
                        {{-- <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['totalScores']) }}</p> --}}
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Athletes -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list mr-2 text-indigo-500"></i>
                        Atlet Terbaru ({{ $athletes->count() }})
                    </h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($athletes as $athlete)
                        <div class="p-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                        {{ substr($athlete->name, 0, 2) }}
                                    </div>
                                    <div class="ml-4">
                                        <p class="font-medium text-gray-900">{{ $athlete->name }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $athlete->gender }} | {{ $athlete->weight }}kg | {{ $athlete->age }}thn
                                        </p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                    Active
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-gray-500">
                            Belum ada atlet terdaftar
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Participants -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-calendar mr-2 text-green-500"></i>
                        Pendaftaran Event ({{ $participants->count() }})
                    </h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($participants as $participant)
                        <div class="p-6 hover:bg-gray-50">
                            <p class="font-medium text-gray-900">{{ $participant->athlete->name }}</p>
                            {{-- <p class="text-sm text-gray-500">{{ $participant->event->name }}</p> --}}
                        </div>
                    @empty
                        <div class="p-12 text-center text-gray-500">
                            Belum ada pendaftaran event
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
