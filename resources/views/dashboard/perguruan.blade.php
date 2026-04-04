@extends('layouts.app')

@section('title', 'Perguruan Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-school mr-3 text-emerald-600"></i>
                    Dashboard Perguruan
                </h1>
                <p class="text-gray-600 mt-1">{{ $user->perguruan?->name ?? $user->name }}</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-emerald-100 rounded-lg">
                        <i class="fas fa-dumbbell text-2xl text-emerald-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Atlet Saya</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ number_format($user->athletes()->active()->count()) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-calendar-check text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pendaftaran Aktif</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ number_format(App\Models\EventParticipant::where('registered_by', $user->id)->where('status', 'verified')->count()) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-medal text-2xl text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Juara</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ number_format(App\Models\Winner::whereHas('eventParticipant.registeredBy', fn($q) => $q->where('id', $user->id))->count()) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- My Athletes -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-users mr-2 text-emerald-500"></i>
                    Atlet Saya ({{ $user->athletes()->count() }})
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2 text-left text-sm font-medium text-gray-600">Nama</th>
                                <th class="py-2 text-left text-sm font-medium text-gray-600">Umur</th>
                                <th class="py-2 text-left text-sm font-medium text-gray-600">Berat</th>
                                <th class="py-2 text-right text-sm font-medium text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($user->athletes()->active()->latest()->take(5) as $athlete)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3">
                                        <div class="font-medium text-gray-900">{{ $athlete->name }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $athlete->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}</div>
                                    </td>
                                    <td class="py-3 text-sm font-medium text-gray-900">{{ $athlete->age }} th</td>
                                    <td class="py-3 text-sm font-medium text-gray-900">{{ $athlete->weight ?? '-' }} kg</td>
                                    <td class="py-3 text-right">
                                        <span
                                            class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full font-medium">
                                            Aktif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-500">
                                        Belum ada atlet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('athletes.create') }}"
                    class="mt-4 inline-block bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium">
                    <i class="fas fa-plus mr-2"></i>Tambah Atlet
                </a>
            </div>

            <!-- Recent Events -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-calendar mr-2 text-blue-500"></i>
                    Event Aktif
                </h3>
                @php
                    $activeEvents = App\Models\Event::active()->get();
                @endphp
                <div class="space-y-3">
                    @forelse($activeEvents as $event)
                        <div class="p-4 border rounded-lg hover:bg-blue-50 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $event->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $event->start_date->format('d M Y') }} -
                                        {{ $event->end_date->format('d M Y') }}</p>
                                </div>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-medium">
                                    {{ $event->participants->where('registered_by', $user->id)->where('status', 'verified')->count() }}
                                    atlet
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-500">
                            <i class="fas fa-calendar-times text-4xl mb-4 opacity-50"></i>
                            <p>Belum ada event aktif</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
