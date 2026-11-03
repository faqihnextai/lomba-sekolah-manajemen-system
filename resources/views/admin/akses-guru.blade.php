<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Akses Guru</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Atur modul dokumen yang menjadi tanggung jawab guru di sekolah Anda.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">Periksa kembali akses guru:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 text-sm text-blue-900 dark:border-blue-900 dark:bg-blue-950/30 dark:text-blue-200">
                <span class="font-semibold">{{ $school->name }}</span>
                <span class="mx-1">·</span>
                Kode sekolah {{ $school->code }}
                <p class="mt-1 text-blue-700 dark:text-blue-300">Daftar ini hanya menampilkan guru dari sekolah Anda.</p>
            </div>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                <div class="flex flex-col gap-1">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambahkan Guru</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Akun akan otomatis menggunakan sekolah dan kode sekolah admin Anda.</p>
                </div>

                <form method="POST" action="{{ route('admin.akses-guru.store') }}" class="mt-5 space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="teacher-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama guru</label>
                            <input id="teacher-name" name="name" type="text" value="{{ old('name') }}" required maxlength="255" autocomplete="name"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label for="teacher-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email guru</label>
                            <input id="teacher-email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label for="teacher-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kata sandi awal</label>
                            <input id="teacher-password" name="password" type="password" required minlength="8" autocomplete="new-password"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label for="teacher-password-confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ulangi kata sandi</label>
                            <input id="teacher-password-confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label for="teacher-school-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama sekolah</label>
                            <input id="teacher-school-name" type="text" value="{{ $school->name }}" readonly
                                   class="mt-1 block w-full rounded-xl border-gray-300 bg-gray-100 text-gray-600 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>
                        <div>
                            <label for="teacher-school-code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode sekolah</label>
                            <input id="teacher-school-code" type="text" value="{{ $school->code }}" readonly
                                   class="mt-1 block w-full rounded-xl border-gray-300 bg-gray-100 text-gray-600 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-100 pt-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <span class="w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Role akun: Guru</span>
                        <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Tambahkan Guru
                        </button>
                    </div>
                </form>
            </section>

            @forelse ($teachers as $teacher)
                @php
                    $assignedModules = $teacher->competitionAccesses
                        ->groupBy('program')
                        ->map(fn ($accesses) => $accesses->pluck('module_key')->all())
                        ->all();
                @endphp
                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white">{{ $teacher->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $teacher->email }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                            Guru · {{ $teacher->school?->code }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.akses-guru.update', $teacher) }}" class="space-y-5 p-5">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            @foreach ($programs as $programKey => $competition)
                                <fieldset class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                                    <legend class="px-2 text-sm font-bold text-gray-900 dark:text-white">{{ $competition['label'] }}</legend>
                                    <div class="space-y-3">
                                        @foreach ($competition['babList'] as $module)
                                            <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                                                <input type="checkbox"
                                                       name="modules[{{ $programKey }}][]"
                                                       value="{{ $module['key'] }}"
                                                       @checked(in_array($module['key'], $assignedModules[$programKey] ?? [], true))
                                                       class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span>{{ $module['judul'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Simpan Akses Guru
                            </button>
                        </div>
                    </form>
                </section>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Belum ada guru di sekolah ini</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gunakan form Tambahkan Guru di atas untuk membuat akun guru dengan kode sekolah Anda.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
