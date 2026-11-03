<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen Pengguna</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Kelola akun, kode sekolah, role, dan penugasan modul guru.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">Periksa kembali data:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 p-6 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Form Pendaftaran Pengguna</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kode sekolah yang sama menghubungkan akun admin dan guru dalam satu sekolah.</p>
                </div>

                <form method="POST" action="{{ route('super-admin.manajemen-user.store') }}" class="space-y-6 p-6" x-data="{ roleSelected: @js(old('role', 'guru')) }">
                    @csrf
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Nama lengkap
                            <input name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" class="mt-2 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </label>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Email
                            <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="mt-2 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </label>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Kata sandi sementara
                            <input type="password" name="password" required minlength="8" autocomplete="new-password" class="mt-2 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </label>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Konfirmasi kata sandi
                            <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="mt-2 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </label>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Role pengguna
                            <select name="role" x-model="roleSelected" required class="mt-2 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ match($role) { 'superadmin' => 'Super Admin', 'admin' => 'Admin Sekolah', 'guru' => 'Guru', default => ucfirst($role) } }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div x-show="roleSelected !== 'superadmin'" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Nama sekolah
                                <input name="school_name" value="{{ old('school_name') }}" :required="roleSelected !== 'superadmin'" :disabled="roleSelected === 'superadmin'" maxlength="255" placeholder="Contoh: Daarul Huda" class="mt-2 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </label>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Kode sekolah
                                <input name="school_code" value="{{ old('school_code') }}" :required="roleSelected !== 'superadmin'" :disabled="roleSelected === 'superadmin'" maxlength="20" placeholder="Contoh: DAHU" class="mt-2 w-full rounded-xl border-gray-300 uppercase dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </label>
                        </div>
                    </div>

                    <div x-show="roleSelected === 'guru'" class="space-y-4">
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white">Penugasan modul guru</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Guru hanya dapat membuka bab yang dicentang di bawah.</p>
                        </div>
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            @foreach ($programs as $programKey => $competition)
                                <fieldset class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                                    <legend class="px-2 text-sm font-bold text-gray-900 dark:text-white">{{ $competition['label'] }}</legend>
                                    <div class="space-y-3">
                                        @foreach ($competition['babList'] as $module)
                                            <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                                                <input type="checkbox" name="modules[{{ $programKey }}][]" value="{{ $module['key'] }}" @checked(in_array($module['key'], old("modules.{$programKey}", []), true)) class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span>{{ $module['judul'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 pt-5 dark:border-gray-700">
                        <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Tambahkan Pengguna
                        </button>
                    </div>
                </form>
            </section>

            <section class="space-y-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Daftar Pengguna</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $users->count() }} akun terdaftar.</p>
                </div>

                @forelse ($users as $user)
                    @php
                        $currentRole = $user->roles->first()?->name ?? 'guru';
                        $assignedModules = $user->competitionAccesses
                            ->groupBy('program')
                            ->map(fn ($accesses) => $accesses->pluck('module_key')->all())
                            ->all();
                    @endphp
                    <details class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <summary class="flex cursor-pointer list-none flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="rounded-full bg-blue-50 px-3 py-1 font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ $user->school?->name ?? 'Sekolah belum diatur' }} · {{ $user->school?->code ?? '—' }}</span>
                                <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $currentRole }}</span>
                            </div>
                        </summary>

                        <form method="POST" action="{{ route('super-admin.manajemen-user.update-role', $user) }}" class="space-y-5 border-t border-gray-100 p-5 dark:border-gray-700" x-data="{ roleSelected: @js($currentRole) }">
                            @csrf
                            @method('PATCH')
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Role
                                    <select name="role" x-model="roleSelected" required class="mt-2 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role }}">{{ match($role) { 'superadmin' => 'Super Admin', 'admin' => 'Admin Sekolah', 'guru' => 'Guru', default => ucfirst($role) } }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label x-show="roleSelected !== 'superadmin'" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Nama sekolah
                                    <input name="school_name" value="{{ $user->school?->name }}" :required="roleSelected !== 'superadmin'" :disabled="roleSelected === 'superadmin'" maxlength="255" class="mt-2 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                </label>
                                <label x-show="roleSelected !== 'superadmin'" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Kode sekolah
                                    <input name="school_code" value="{{ $user->school?->code }}" :required="roleSelected !== 'superadmin'" :disabled="roleSelected === 'superadmin'" maxlength="20" class="mt-2 w-full rounded-xl border-gray-300 uppercase dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                </label>
                            </div>

                            <div x-show="roleSelected === 'guru'" class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                @foreach ($programs as $programKey => $competition)
                                    <fieldset class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                                        <legend class="px-2 text-sm font-bold text-gray-900 dark:text-white">{{ $competition['label'] }}</legend>
                                        <div class="space-y-3">
                                            @foreach ($competition['babList'] as $module)
                                                <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                                                    <input type="checkbox" name="modules[{{ $programKey }}][]" value="{{ $module['key'] }}" @checked(in_array($module['key'], $assignedModules[$programKey] ?? [], true)) class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <span>{{ $module['judul'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                @endforeach
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-gray-700 dark:bg-blue-600 dark:hover:bg-blue-700">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </details>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700">
                        Belum ada pengguna terdaftar.
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
