@extends('layouts.app')

@section('title', 'Q Square Martial Arts Competition')

@section('content')
<!-- Navbar -->
@include('components._navbar')

<!-- Main -->
<div class="container mx-auto px-4 py-30">
    <div class="flex flex-col md:flex-row items-center justify-center gap-25">
        <img src="{{ Vite::asset('resources/assets/Maskot_1.png') }}" alt="Maskot 1" class="hidden md:block w-32 md:w-40 h-auto object-contain" />
        <div>
            <h1 class="text-3xl font-bold text-center">Selamat Datang di Q Square Martial Arts Competition</h1>
            <p class="text-center text-base-content mt-4">Bergabunglah dengan turnamen bela diri yang menantang dan menarik!</p>
            <div class="flex justify-center mt-6">
                <a href="https://drive.google.com/file/d/10Rfv201iddVEF4PcIix78ob-pTivJjP1/view?usp=drivesdk" class="btn btn-accent btn-lg text-white shadow-sm hover:shadow-lg">Download Juklak Pertandingan</a>
            </div>
        </div>
        <img src="{{ Vite::asset('resources/assets/Maskot_3.png') }}" alt="Maskot 3" class="w-32 md:w-40 h-auto object-contain" />
    </div>

    <div class="divider mt-25"></div>

    <!-- Stats Card -->
    <div class="bg-gradient-to-r from-blue-400 to-blue-700 rounded-xl shadow-xl p-6 mt-16">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    Statistik Turnamen Saat Ini
                </h1>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            <div class="bg-base-100 p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-emerald-100 rounded-lg">
                        <i class="fas fa-school text-2xl text-emerald-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Perguruan Yang Terdaftar</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ '15' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-base-100 p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-calendar-check text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Peserta</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ '112' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-base-100 p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-medal text-2xl text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Juara</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ '5' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-base-100 p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-calendar text-2xl text-orange-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Kategori Pertandingan</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ '12' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-base-100 p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-map-marker-alt text-2xl text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Arena</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ '2' }}
                        </p>
                    </div>
                </div>
            </div>

        </div>
        
        <div class="flex flex-1 justify-end mt-6">
            <a href="#" class="link text-sm/6 font-semibold text-white">Lihat Semua <span aria-hidden="true">&rarr;</span></a>
        </div>
    </div>

    <div class="divider mt-16"></div>

    <!-- Frequently Asked Questions-->
    <div class="p-6 mt-8">
        <h1 class="text-2xl font-bold text-base-content mb-8">Pertanyaan yang Sering Diajukan</h2>
        <div class="collapse bg-base-100 border border-base-300">
            <input type="checkbox" name="my-accordion-1" />
            <div class="collapse-title font-semibold">How do I create an account?</div>
            <div class="collapse-content text-sm">Click the "Sign Up" button in the top right corner and follow the registration process.</div>
        </div>
        <div class="collapse bg-base-100 border border-base-300">
            <input type="checkbox" name="my-accordion-1" />
            <div class="collapse-title font-semibold">I forgot my password. What should I do?</div>
            <div class="collapse-content text-sm">Click on "Forgot Password" on the login page and follow the instructions sent to your email.</div>
        </div>
        <div class="collapse bg-base-100 border border-base-300">
            <input type="checkbox" name="my-accordion-1" />
            <div class="collapse-title font-semibold">How do I update my profile information?</div>
            <div class="collapse-content text-sm">Go to "My Account" settings and select "Edit Profile" to make changes.</div>
        </div>
    </div>
</div>

@endsection