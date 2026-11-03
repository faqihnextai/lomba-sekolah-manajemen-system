<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Dashboard Admin</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Pilih lomba yang ingin diaktifkan untuk sekolah Anda.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-2xl border border-blue-100 bg-blue-50 p-5 dark:border-blue-900 dark:bg-blue-950/30">
                <p class="text-sm text-blue-800 dark:text-blue-200">Sekolah Anda</p>
                <h3 class="mt-1 text-lg font-bold text-blue-950 dark:text-white">
                    {{ auth()->user()->school->name }}
                </h3>
                <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                    Kode sekolah: {{ auth()->user()->school->code }}
                </p>
            </section>

            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($programs as $programKey => $competition)
                    @php($isActive = in_array($programKey, $activePrograms, true))
                    <section class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kompetisi</span>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $isActive ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <h3 class="mt-3 text-xl font-bold text-gray-900 dark:text-white">{{ $competition['label'] }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $competition['deskripsi'] }}</p>

                        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                            <form method="POST" action="{{ route('admin.competition.status', ['program' => $programKey]) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="enabled" value="{{ $isActive ? '0' : '1' }}">
                                <button type="submit" class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ $isActive ? 'bg-amber-100 text-amber-900 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-200' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                                    {{ $isActive ? 'Nonaktifkan' : 'Aktifkan lomba' }}
                                </button>
                            </form>
                            @if ($isActive)
                                <a href="{{ route('admin.competition', ['program' => $programKey]) }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900 dark:text-blue-300">
                                    Buka lomba <span aria-hidden="true">&rarr;</span>
                                </a>
                            @endif
                        </div>
                    </section>
                @endforeach
            </div>

            <section class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="font-bold text-gray-900 dark:text-white">Aktivitas Guru</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Riwayat aktivitas dan progres dokumen akan ditampilkan setelah fitur input dokumen guru diaktifkan.
                </p>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Belum ada data progres yang tercatat.</p>
            </section>
        </div>
    </div>
</x-app-layout>
