<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Dashboard Pengguna</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih kategori lomba yang ditugaskan ke akun Anda.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="border-b border-gray-200 pb-5 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">Masuk sebagai</p>
                <h3 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h3>
            </section>

            @if ($programs === [])
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200">
                    Kategori lomba belum ditugaskan ke akun ini. Hubungi superadmin untuk mendapatkan akses.
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($programs as $programKey => $competition)
                        <a href="{{ route('user.pengerjaan-dokumen', ['program' => $programKey]) }}"
                           class="group rounded-xl border border-gray-200 bg-white p-5 transition hover:border-blue-400 hover:shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-500">
                            <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Pengerjaan Dokumen</p>
                            <h3 class="mt-2 text-lg font-bold text-gray-900 group-hover:text-blue-700 dark:text-white dark:group-hover:text-blue-300">{{ $competition['label'] }}</h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $competition['deskripsi'] }}</p>
                            <span class="mt-5 inline-flex text-sm font-semibold text-blue-700 dark:text-blue-300">Buka dokumen <span class="ml-1" aria-hidden="true">&rarr;</span></span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>