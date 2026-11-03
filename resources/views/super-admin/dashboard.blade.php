<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Dashboard Super Admin</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Pantau sekolah, admin, dan aktivasi lomba per sekolah.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sekolah terdaftar</p>
                    <p class="mt-2 text-3xl font-black text-gray-900 dark:text-white">{{ $schools->count() }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Admin sekolah</p>
                    <p class="mt-2 text-3xl font-black text-gray-900 dark:text-white">{{ $schools->sum(fn ($school) => $school->users->count()) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Lomba tersedia</p>
                    <p class="mt-2 text-3xl font-black text-gray-900 dark:text-white">{{ count($programs) }}</p>
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Sekolah & Aktivasi Lomba</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status tiap sekolah dapat diatur oleh admin sekolah atau superadmin.</p>
            </div>

            @forelse ($schools as $school)
                @php($activePrograms = $school->competitionSettings->where('enabled', true)->pluck('program')->all())
                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col gap-4 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $school->name }}</h4>
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ $school->code }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Admin sekolah</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse ($school->users as $admin)
                                    <span class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                        {{ $admin->name }} · {{ $admin->email }}
                                    </span>
                                @empty
                                    <span class="text-xs text-amber-700 dark:text-amber-300">Belum ada admin sekolah.</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                            Progres dokumen belum tersedia
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($programs as $programKey => $competition)
                            @php($isActive = in_array($programKey, $activePrograms, true))
                            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $competition['label'] }}</p>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $isActive ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                                <form method="POST" action="{{ route('super-admin.school.competition.status', ['school' => $school, 'program' => $programKey]) }}" class="mt-3">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="enabled" value="{{ $isActive ? '0' : '1' }}">
                                    <button type="submit" class="w-full rounded-lg px-3 py-2 text-xs font-bold transition {{ $isActive ? 'bg-amber-50 text-amber-800 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-200' : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                                        {{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-gray-700 dark:bg-gray-800">
                    <h4 class="font-semibold text-gray-900 dark:text-white">Belum ada sekolah terdaftar</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sekolah muncul di sini setelah superadmin membuat akun admin atau guru.</p>
                </div>
            @endforelse

            <section class="rounded-2xl border border-blue-100 bg-blue-50 p-5 dark:border-blue-900 dark:bg-blue-950/30">
                <h4 class="font-bold text-blue-950 dark:text-blue-100">Pemantauan progres dokumen</h4>
                <p class="mt-1 text-sm text-blue-800 dark:text-blue-200">
                    Progres dan aktivitas guru belum dihitung karena fitur input/unggah dokumen belum diaktifkan. Data progres akan ditampilkan setelah fitur tersebut dibuat.
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
