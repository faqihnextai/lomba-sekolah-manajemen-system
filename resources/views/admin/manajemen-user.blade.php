<x-app-layout>
    <x-slot name="header">
        <div>
            <div>
                <h2 class="font-bold text-xl text-gray-900 dark:text-white leading-tight">
                    {{ __('Manajemen Pengguna & Pendaftaran') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Daftarkan pengguna baru, kelola akun, dan atur pendelegasian matriks hak akses.
                </p>
            </div>
        </div>
    </x-slot>

    <!-- Alpine.js State untuk Tab Navigasi & Form -->
    <div class="py-8" x-data="{ activeTab: 'form', roleSelected: @js(old('role', 'guru')), programSelected: @js(old('program', '')), filterRole: 'koordinator' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-xl bg-green-50 text-green-800 border border-green-200 px-4 py-3 text-sm">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl bg-red-50 text-red-800 border border-red-200 px-4 py-3 text-sm">
                    <p class="font-semibold mb-1">Periksa kembali data:</p>
                    <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <!-- TAB NAVIGASI -->
            <div class="flex space-x-2 border-b border-gray-200 dark:border-gray-700 mb-6 pb-2 overflow-x-auto">
                <button @click="activeTab = 'form'" :class="activeTab === 'form' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-bold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'" class="px-5 py-2.5 rounded-t-xl text-sm transition whitespace-nowrap">
                    Pendaftaran & Daftar User
                </button>
                <button @click="activeTab = 'matriks'" :class="activeTab === 'matriks' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-bold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'" class="px-5 py-2.5 rounded-t-xl text-sm transition whitespace-nowrap">
                    Manajemen Akses & Workflow
                </button>
            </div>

            <!-- TAB 1: FORM PENDAFTARAN & DAFTAR USER -->
            <div x-show="activeTab === 'form'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                
                <!-- Form Pendaftaran -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 sm:p-8 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Form Pendaftaran Pengguna</h3>
                            <p class="text-sm text-gray-500">Tambahkan akses untuk kepanitiaan atau guru pelaksana.</p>
                        </div>
                    </div>

                    <form class="p-6 sm:p-8 space-y-6" onsubmit="event.preventDefault()">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Input Nama Lengkap -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="Masukkan nama lengkap..." class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>

                            <!-- Input Email -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Alamat Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" placeholder="email@sekolah.sch.id" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>

                            <!-- Input Password -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kata Sandi Sementara</label>
                                <input type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter..." class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Konfirmasi Kata Sandi</label>
                                <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi..." class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>

                            <!-- Select Role -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pilih Hak Akses (Role)</label>
                                <div class="relative">
                                    <select name="role" required x-model="roleSelected" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 appearance-none pl-4 pr-10 py-2.5 transition font-medium">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}">{{ match($role->name) { 'superadmin' => 'Super Admin (Akses Penuh)', 'admin' => 'Ketua Pelaksana / Koordinator', 'guru' => 'Guru / Anggota Pelaksana', default => ucfirst($role->name) } }}</option>
                                        @endforeach
                                    </select>
                                    
                                    <!-- Indikator Visual Role Terpilih -->
                                    <div class="absolute right-10 top-1/2 -translate-y-1/2 flex items-center pointer-events-none">
                                        <span x-show="roleSelected === 'superadmin'" class="w-3 h-3 rounded-full bg-purple-500 shadow-[0_0_8px_rgba(168,85,247,0.6)]"></span>
                                        <span x-show="roleSelected === 'admin'" class="w-3 h-3 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.6)]"></span>
                                        <span x-show="roleSelected === 'guru'" class="w-3 h-3 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                                    </div>
                                </div>
                            </div>

                            <div x-show="roleSelected !== 'superadmin'">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kategori Lomba</label>
                                <select name="program" x-model="programSelected" :required="roleSelected !== 'superadmin'" :disabled="roleSelected === 'superadmin'" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">Pilih kategori lomba</option>
                                    @foreach ($programs as $programKey => $competition)
                                        <option value="{{ $programKey }}">{{ $competition['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="button" disabled class="inline-flex cursor-not-allowed items-center gap-2 rounded-xl bg-gray-400 px-6 py-3 text-sm font-bold text-white shadow-md dark:bg-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Pendaftaran Belum Aktif
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Daftar User -->
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Daftar Pengguna Sistem</h3>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600 dark:bg-gray-700/50 dark:text-gray-300">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold">Pengguna</th>
                                    <th class="px-5 py-3 text-left font-semibold">Role</th>
                                    <th class="px-5 py-3 text-left font-semibold">Kategori</th>
                                    <th class="px-5 py-3 text-left font-semibold">Terdaftar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($users as $user)
                                    @php
                                        $role = $user->roles->first();
                                    @endphp
                                    <tr>
                                        <td class="px-5 py-4">
                                            <div class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                        </td>
                                        <td class="px-5 py-4 text-gray-700 dark:text-gray-300">
                                            {{ $role ? match($role->name) { 'superadmin' => 'Super Admin', 'admin' => 'Admin / Koordinator', 'guru' => 'Guru / Anggota', default => ucfirst($role->name) } : 'Belum memiliki role' }}
                                        </td>
                                        <td class="px-5 py-4 text-gray-700 dark:text-gray-300">
                                            {{ $user->program && isset($programs[$user->program]) ? $programs[$user->program]['label'] : '-' }}
                                        </td>
                                        <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ $user->created_at?->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">Belum ada pengguna terdaftar.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: MANAJEMEN AKSES -->
            <div x-show="activeTab === 'matriks'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8" style="display: none;">

                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 p-6 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-white">Assignment Role dan Kategori User</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600 dark:bg-gray-700/50 dark:text-gray-300">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold">Pengguna</th>
                                    <th class="px-5 py-3 text-left font-semibold">Role</th>
                                    <th class="px-5 py-3 text-left font-semibold">Kategori Lomba</th>
                                    <th class="px-5 py-3 text-left font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($users as $user)
                                    @php
                                        $currentRole = $user->roles->first()?->name ?? 'guru';
                                    @endphp
                                    <tr>
                                        <td class="px-5 py-4">
                                            <div class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                        </td>
                                        <td colspan="3" class="px-5 py-4">
                                            <form method="POST" action="{{ route('admin.manajemen-user.update-role', $user) }}" x-data="{ selectedRole: @js($currentRole), selectedProgram: @js($user->program ?? '') }" class="grid grid-cols-1 items-center gap-2 sm:grid-cols-[1fr_1.4fr_auto]">
                                                @csrf
                                                @method('PATCH')
                                                <select name="role" x-model="selectedRole" required class="w-full rounded-lg border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                                    @foreach ($roles as $availableRole)
                                                        <option value="{{ $availableRole->name }}">{{ match($availableRole->name) { 'superadmin' => 'Super Admin', 'admin' => 'Admin / Koordinator', 'guru' => 'Guru / Anggota', default => ucfirst($availableRole->name) } }}</option>
                                                    @endforeach
                                                </select>
                                                <select name="program" x-model="selectedProgram" :required="selectedRole !== 'superadmin'" :disabled="selectedRole === 'superadmin'" class="w-full rounded-lg border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                                    <option value="">Pilih kategori lomba</option>
                                                    @foreach ($programs as $programKey => $competition)
                                                        <option value="{{ $programKey }}">{{ $competition['label'] }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-700">Simpan Akses</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">Belum ada pengguna terdaftar.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
                
                <!-- 1. PENGATURAN PERAN USER (ROLE MANAGEMENT) -->
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">1. Definisi Peran Sistem (Role Management)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <!-- Super Admin -->
                        <div @click="filterRole = 'superadmin'" :class="filterRole === 'superadmin' ? 'ring-2 ring-blue-600 bg-blue-50/20 dark:bg-blue-900/10' : ''" class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 cursor-pointer transition">
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-3 py-1 text-xs font-bold bg-purple-100 text-purple-700 rounded-full">Level 1 • Full Access</span>
                                <span class="text-xs text-gray-400">2 User</span>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white">Super Admin</h4>
                            <p class="text-xs font-semibold text-gray-500 mb-3">Kepala Sekolah / Operator Utama</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                Akses penuh ke seluruh menu, kustomisasi template dokumen, manajemen matriks role, dan penguncian akhir dokumen (<em>final lock</em>).
                            </p>
                        </div>

                        <!-- Koordinator Lomba -->
                        <div @click="filterRole = 'koordinator'" :class="filterRole === 'koordinator' ? 'ring-2 ring-blue-600 bg-blue-50/20 dark:bg-blue-900/10' : ''" class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 cursor-pointer transition">
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-3 py-1 text-xs font-bold bg-blue-100 text-blue-700 rounded-full">Level 2 • Verifikator</span>
                                <span class="text-xs text-gray-400">4 User</span>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white">Ketua Pelaksana</h4>
                            <p class="text-xs font-semibold text-gray-500 mb-3">Koordinator Lomba Kategori</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                Hak akses mengedit dan memverifikasi dokumen pada kategori lomba spesifik (misal: PJ Adiwiyata hanya mengakses modul Adiwiyata).
                            </p>
                        </div>

                        <!-- Anggota Tim / Guru Pelaksana -->
                        <div @click="filterRole = 'anggota'" :class="filterRole === 'anggota' ? 'ring-2 ring-blue-600 bg-blue-50/20 dark:bg-blue-900/10' : ''" class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 cursor-pointer transition">
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-3 py-1 text-xs font-bold bg-emerald-100 text-emerald-700 rounded-full">Level 3 • Kontributor</span>
                                <span class="text-xs text-gray-400">18 User</span>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white">Anggota Tim / Guru</h4>
                            <p class="text-xs font-semibold text-gray-500 mb-3">Guru Pelaksana / Staf Dokumentasi</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                Hanya dapat mengunggah bukti fisik (PDF), menginput laporan tertulis, dan mengunggah foto kegiatan tanpa akses hapus/approve.
                            </p>
                        </div>

                    </div>
                </div>

                <!-- 2. MATRIKS HAK AKSES SUB-MENU -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">2. Matriks Hak Akses Sub-Menu (Permission Checklist)</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Atur izin spesifik (Create, Read, Update, Delete, Approve) untuk setiap modul lomba</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">Filter Role:</span>
                            <select x-model="filterRole" class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm font-semibold">
                                <option value="superadmin">Super Admin (Kepsek/Operator)</option>
                                <option value="koordinator">Ketua Pelaksana (PJ Kategori)</option>
                                <option value="anggota">Anggota Tim / Guru Pelaksana</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-3.5">Sub-Menu Modul Lomba</th>
                                    <th class="px-6 py-3.5">Penanggung Jawab (Koordinator)</th>
                                    <th class="px-4 py-3.5 text-center">Create</th>
                                    <th class="px-4 py-3.5 text-center">Read</th>
                                    <th class="px-4 py-3.5 text-center">Update</th>
                                    <th class="px-4 py-3.5 text-center">Delete</th>
                                    <th class="px-4 py-3.5 text-center">Approve</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @php
                                    $modules = [
                                        ['nama' => 'Lomba Adiwiyata (GPBLHS)', 'pj' => 'Koordinator Adiwiyata'],
                                        ['nama' => 'Lomba Kantin Sehat', 'pj' => 'Koordinator Kantin & Gizi'],
                                        ['nama' => 'Lomba Sekolah Sehat (UKS/PHBS)', 'pj' => 'Koordinator UKS'],
                                        ['nama' => 'Lomba Literasi (GLS)', 'pj' => 'Tim Literasi Sekolah (TLS)'],
                                    ];
                                @endphp
                                @foreach ($modules as $mod)
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $mod['nama'] }}</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $mod['pj'] }}</td>
                                    <td class="px-4 py-4 text-center"><input type="checkbox" checked class="rounded text-blue-600"></td>
                                    <td class="px-4 py-4 text-center"><input type="checkbox" checked class="rounded text-blue-600"></td>
                                    <td class="px-4 py-4 text-center"><input type="checkbox" :checked="filterRole !== 'anggota'" class="rounded text-blue-600"></td>
                                    <td class="px-4 py-4 text-center"><input type="checkbox" :checked="filterRole === 'superadmin'" class="rounded text-blue-600"></td>
                                    <td class="px-4 py-4 text-center"><input type="checkbox" :checked="filterRole !== 'anggota'" class="rounded text-emerald-600"></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. STATUS DOKUMEN & VERIFIKASI (ALUR WORKFLOW) -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-2">3. Status Dokumen & Alur Verifikasi</h3>
                    <p class="text-xs text-gray-500 mb-6">Indikator tahapan validasi berkas sebelum dikunci untuk penilaian akhir</p>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="p-4 rounded-xl border-l-4 border-gray-400 bg-gray-50 dark:bg-gray-700/40">
                            <div class="text-xs font-bold uppercase text-gray-500">Tahap 1</div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5">Drafting</div>
                            <p class="text-xs text-gray-500 mt-1">Guru/Anggota mengunggah bukti fisik & foto kegiatan.</p>
                        </div>
                        <div class="p-4 rounded-xl border-l-4 border-amber-500 bg-amber-50/50 dark:bg-amber-900/20">
                            <div class="text-xs font-bold uppercase text-amber-600">Tahap 2</div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5">Review Koordinator</div>
                            <p class="text-xs text-gray-500 mt-1">Koordinator memeriksa kelengkapan dan kesesuaian berkas.</p>
                        </div>
                        <div class="p-4 rounded-xl border-l-4 border-emerald-500 bg-emerald-50/50 dark:bg-emerald-900/20">
                            <div class="text-xs font-bold uppercase text-emerald-600">Tahap 3</div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5">Approved</div>
                            <p class="text-xs text-gray-500 mt-1">Dokumen dinyatakan valid dan siap masuk kompilasi cetak.</p>
                        </div>
                        <div class="p-4 rounded-xl border-l-4 border-purple-600 bg-purple-50/50 dark:bg-purple-900/20">
                            <div class="text-xs font-bold uppercase text-purple-600">Tahap 4</div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5">Locked (Final)</div>
                            <p class="text-xs text-gray-500 mt-1">Dikunci oleh Super Admin/Kepsek untuk pengusulan resmi.</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>