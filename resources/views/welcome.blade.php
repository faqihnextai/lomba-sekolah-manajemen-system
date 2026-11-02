<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sistem Manajemen Lomba Sekolah</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-blue-600 selection:text-white">
            <div class="w-full max-w-3xl px-6">
                <div class="text-center mb-10">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Sistem Manajemen Lomba Sekolah</h1>
                    <p class="text-gray-600 dark:text-gray-400">Silakan pilih peran Anda untuk melanjutkan.</p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Card Admin -->
                    <a href="{{ route('login') }}" class="flex flex-col items-center p-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500 transition-all duration-300 transform hover:-translate-y-1">
                        <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Admin / Penilai</h2>
                        <p class="text-sm text-center text-gray-500 dark:text-gray-400">Login untuk mengakses dasbor penilaian, verifikasi dokumen, dan manajemen sistem.</p>
                    </a>

                    <!-- Card Guru/User -->
                    <a href="{{ route('select-lomba') }}" class="flex flex-col items-center p-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 hover:border-green-500 dark:hover:border-green-500 transition-all duration-300 transform hover:-translate-y-1">
                        <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Sekolah / Guru</h2>
                        <p class="text-sm text-center text-gray-500 dark:text-gray-400">Pilih kategori lomba, isi kuesioner evaluasi, dan unggah dokumen pendukung.</p>
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>