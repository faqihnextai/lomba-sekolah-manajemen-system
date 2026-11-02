<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilih Kategori Lomba</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased font-sans bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
    <div class="relative min-h-screen py-12 px-4 sm:px-6 lg:px-8 flex flex-col items-center">

        <div class="w-full max-w-5xl mb-8 flex justify-start items-center">
            <a href="{{ url('/') }}"
                class="text-sm font-medium text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 flex items-center transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
        </div>

        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">Pilih Kategori Evaluasi</h1>
            <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Silakan pilih kategori program yang ingin Anda
                ikuti untuk memulai pengisian kuesioner dan pengunggahan berkas.</p>
        </div>

        <div class="grid gap-6 w-full max-w-5xl md:grid-cols-2 lg:grid-cols-4">

            <!-- Card Adiwiyata -->
            <a href="{{ route('login') }}"
                class="group flex flex-col bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-500 transition-all duration-300 overflow-hidden">
                <div class="h-3 bg-emerald-500"></div>
                <div class="p-6 flex flex-col flex-grow items-center text-center">
                    <div
                        class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center mb-4 transform group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Program Adiwiyata</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Evaluasi sekolah berbudaya lingkungan hidup,
                        kebersihan, dan pengelolaan sampah.</p>
                </div>
            </a>

            <!-- Card Kantin Sehat -->
            <a href="{{ route('login') }}"
                class="group flex flex-col bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 dark:border-gray-700 hover:border-orange-500 dark:hover:border-orange-500 transition-all duration-300 overflow-hidden">
                <div class="h-3 bg-orange-500"></div>
                <div class="p-6 flex flex-col flex-grow items-center text-center">
                    <div
                        class="w-16 h-16 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-2xl flex items-center justify-center mb-4 transform group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Kantin Sehat</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Evaluasi penyediaan makanan bergizi, keamanan
                        pangan, dan sanitasi kantin.</p>
                </div>
            </a>

            <!-- Card Sekolah Sehat -->
            <a href="{{ route('login') }}"
                class="group flex flex-col bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 dark:border-gray-700 hover:border-red-500 dark:hover:border-red-500 transition-all duration-300 overflow-hidden">
                <div class="h-3 bg-red-500"></div>
                <div class="p-6 flex flex-col flex-grow items-center text-center">
                    <div
                        class="w-16 h-16 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-2xl flex items-center justify-center mb-4 transform group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Sekolah Sehat</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Evaluasi Perilaku Hidup Bersih dan Sehat (PHBS)
                        serta fasilitas sanitasi dasar.</p>
                </div>
            </a>

            <!-- Card Literasi -->
            <a href="{{ route('login') }}"
                class="group flex flex-col bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all duration-300 overflow-hidden">
                <div class="h-3 bg-indigo-500"></div>
                <div class="p-6 flex flex-col flex-grow items-center text-center">
                    <div
                        class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center mb-4 transform group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Gerakan Literasi</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Evaluasi program 15 menit membaca, sudut baca,
                        dan pemanfaatan perpustakaan.</p>
                </div>
            </a>

        </div>
    </div>
</body>

</html>