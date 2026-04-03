@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-tachometer-alt mr-3 text-blue-600"></i>
                    Admin Dashboard
                </h1>
                <p class="text-gray-600 mt-1">Kelola seluruh sistem kejuaraan</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.perguruans.index') }}" class="btn-primary py-2 px-6">
                    <i class="fas fa-school mr-2"></i>
                    Kelola Perguruan
                </a>

            </div>
        </div>

        <!-- Stats Cards - Expanded for all features -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-school text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Perguruan</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalPerguruan']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-user text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Atlet</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalAthletes']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-calendar text-2xl text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Event</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalEvents']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-lg">
                        <i class="fas fa-clock text-2xl text-orange-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['pendingPerguruan']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-100 rounded-lg">
                        <i class="fas fa-trophy text-2xl text-indigo-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Olahraga</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalSports']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow lg:hidden xl:flex">
                <div class="flex items-center">
                    <div class="p-3 bg-teal-100 rounded-lg">
                        <i class="fas fa-gavel text-2xl text-teal-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Displin</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalDisciplines']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow lg:hidden xl:flex">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <i class="fas fa-map-marker-alt text-2xl text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Arena</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalArenas']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow lg:hidden xl:flex">
                <div class="flex items-center">
                    <div class="p-3 bg-pink-100 rounded-lg">
                        <i class="fas fa-certificate text-2xl text-pink-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Sertifikat</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['totalCertificates']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pending Perguruan -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-clock mr-2 text-yellow-500"></i>
                    Pending Perguruan ({{ $stats['pendingPerguruan'] }})
                </h3>
                @if ($pendingPerguruans->count())
                    <div class="space-y-3">
                        @foreach ($pendingPerguruans as $user)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $user->email }}</p>
                                </div>
                                <a href="{{ route('admin.perguruans.verify', $user) }}"
                                    class="btn-primary text-sm py-1 px-3">
                                    Verifikasi
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('admin.perguruan.index') }}"
                        class="mt-4 block text-blue-600 hover:text-blue-700 font-medium">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                @else
                    <p class="text-gray-500 text-center py-8">Tidak ada pending perguruan</p>
                @endif
            </div>

            <!-- Recent Events -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-calendar mr-2 text-green-500"></i>
                    Event Terbaru
                </h3>
                @if ($recentEvents->count())
                    <div class="space-y-3">
                        @foreach ($recentEvents as $event)
                            <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition-colors">
                                <div
                                    class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                                    {{ $event->start_date->format('d') }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $event->name }}</p>
                                    <p class="text-sm text-gray-500">Various Sports</p>
                                    <p class="text-xs text-gray-400">{{ $event->start_date->format('d M Y') }} -
                                        {{ $event->end_date->format('d M Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Belum ada event</p>
                @endif
            </div>
        </div>

        <!-- Latest Activities -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list mr-2 text-indigo-500"></i>
                    Aktivitas Terbaru
                </h3>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach ($recentUsers as $user)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div
                                    class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                    </p>
                                </div>
                                <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full font-medium">
                                    {{ $user->roles->pluck('name')->implode(', ') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
