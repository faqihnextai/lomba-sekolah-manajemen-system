<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-bold text-xl text-gray-900 dark:text-white">Setting Dokumen</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Atur komponen dokumen dan tinjau hasilnya sebelum digunakan.</p>
        </div>
    </x-slot>

    <div class="py-6" x-data="documentSettings()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                <aside class="xl:col-span-3 space-y-3">
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-3">Komponen Dokumen</h3>
                        @foreach ([
                            ['key' => 'cover', 'label' => 'Halaman Cover', 'enabled' => 'enableCover'],
                            ['key' => 'header', 'label' => 'Kop / Header', 'enabled' => 'enableHeader'],
                            ['key' => 'isi', 'label' => 'Isi Dokumen', 'enabled' => 'enableIsi'],
                            ['key' => 'footer', 'label' => 'Footer & Pengesahan', 'enabled' => 'enableFooter'],
                        ] as $item)
                            <div class="flex items-center gap-3 rounded-xl border border-gray-100 dark:border-gray-700 p-3 mb-2"
                                 :class="activeMenu === '{{ $item['key'] }}' ? 'ring-2 ring-blue-500 border-blue-500' : ''">
                                    <input type="checkbox" x-model="{{ $item['enabled'] }}"
                                        @change="toggleComponent('{{ $item['key'] }}', '{{ $item['enabled'] }}', $event.target.checked)"
                                        class="rounded text-blue-600 focus:ring-blue-500">
                                <button type="button" @click="if ({{ $item['enabled'] }}) activeMenu = '{{ $item['key'] }}'"
                                        class="flex-1 text-left text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ $item['label'] }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div class="rounded-2xl border border-blue-100 bg-blue-50 dark:bg-blue-950/30 dark:border-blue-900 p-4">
                        <div class="flex gap-2 text-blue-800 dark:text-blue-200">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 110-20 10 10 0 010 20z"/></svg>
                            <p class="text-xs leading-relaxed">Mode tampilan UI/UX. Perubahan di halaman ini belum disimpan ke database.</p>
                        </div>
                    </div>
                </aside>

                <main class="xl:col-span-5">
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 sm:p-6 min-h-[560px]">
                        <div x-show="activeMenu === 'cover' && enableCover" x-cloak>
                            <x-cover />
                        </div>
                        <div x-show="activeMenu === 'header' && enableHeader" x-cloak>
                            <x-header />
                        </div>
                        <div x-show="activeMenu === 'isi' && enableIsi" x-cloak>
                            <x-isi :documents="$documents ?? collect()" />
                        </div>
                        <div x-show="activeMenu === 'footer' && enableFooter" x-cloak>
                            <x-footer />
                        </div>
                        <div x-show="!isActiveMenuEnabled()" x-cloak class="py-16 text-center">
                            <p class="font-semibold text-gray-700 dark:text-gray-200">Komponen sedang dinonaktifkan</p>
                            <p class="text-sm text-gray-500 mt-1">Aktifkan komponen melalui daftar di sebelah kiri.</p>
                        </div>
                    </div>
                </main>

                <section class="xl:col-span-4">
                    <x-preview />
                </section>
            </div>
        </div>
    </div>

    <script>
        function documentSettings() {
            return {
                activeMenu: 'isi',
                enableCover: false,
                enableHeader: true,
                enableIsi: true,
                enableFooter: true,
                coverJudul: 'LAPORAN EVALUASI MANDIRI',
                coverTahun: 'TAHUN AJARAN 2026/2027',
                namaSekolah: 'SD ISLAM DAARUL HUDA',
                npsn: '20601234',
                alamat: 'Jl. Pendidikan No. 45, Kota Tangerang, Banten',
                posisiLogo: 'kiri-kanan',
                garisPembatas: 'double',
                layoutIsi: 'kombinasi',
                kolomLayout: '2',
                metaWaktu: true,
                metaNama: true,
                metaUraian: true,
                metaPj: true,
                kepsekNama: 'Hj. Hariyanie, SE, MM',
                kepsekNip: '19750412 200501 2 008',
                footerCopyright: '© 2026 Tim Administrasi Lomba Sekolah',
                nomorHalaman: true,
                tanggalCetak: true,
                teksWatermark: 'DOKUMEN RESMI SEKOLAH',
                toggleComponent(menu, enabledProperty, isEnabled) {
                    this[enabledProperty] = isEnabled;

                    if (isEnabled) {
                        this.activeMenu = menu;
                        return;
                    }

                    if (this.activeMenu === menu) {
                        const enabledMenus = {
                            cover: this.enableCover,
                            header: this.enableHeader,
                            isi: this.enableIsi,
                            footer: this.enableFooter,
                        };

                        this.activeMenu = Object.keys(enabledMenus).find(key => enabledMenus[key]) ?? null;
                    }
                },
                isActiveMenuEnabled() {
                    return (this.activeMenu === 'cover' && this.enableCover)
                        || (this.activeMenu === 'header' && this.enableHeader)
                        || (this.activeMenu === 'isi' && this.enableIsi)
                        || (this.activeMenu === 'footer' && this.enableFooter);
                }
            }
        }
    </script>
</x-app-layout>
