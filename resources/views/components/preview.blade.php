<div class="sticky top-24 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-950 p-4">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">Live Preview A4</h3>
        <span class="text-[10px] rounded-full bg-blue-100 text-blue-700 px-2 py-1">Pratinjau</span>
    </div>
    <div class="relative aspect-[210/297] bg-white text-gray-900 shadow-lg rounded-sm p-5 sm:p-6 flex flex-col overflow-hidden">
        <div x-show="enableIsi" class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.06] -rotate-45">
            <span class="text-xl font-black text-center" x-text="teksWatermark"></span>
        </div>
        <div x-show="activeMenu === 'cover' && enableCover" class="relative flex-1 flex flex-col items-center justify-center text-center gap-6">
            <div class="w-16 h-16 rounded-full border-2 border-gray-300 flex items-center justify-center text-[8px] text-gray-400 font-bold">LOGO</div>
            <div><h1 class="text-lg font-black leading-tight" x-text="coverJudul"></h1><p class="text-xs font-bold mt-2 text-gray-600" x-text="coverTahun"></p></div>
            <div class="mt-8"><p class="text-[9px] font-bold" x-text="namaSekolah"></p><p class="text-[8px] text-gray-500 mt-1" x-text="alamat"></p></div>
        </div>
        <div x-show="activeMenu !== 'cover' || !enableCover" class="relative flex-1 flex flex-col">
            <div x-show="enableHeader" class="flex items-center gap-2 pb-2 mb-4"
                 :class="{'border-b-4 border-double border-gray-900': garisPembatas === 'double', 'border-b-2 border-gray-900': garisPembatas === 'solid', 'border-b-2 border-dashed border-gray-900': garisPembatas === 'dashed'}">
                <div x-show="posisiLogo !== 'kanan'" class="w-8 h-8 border rounded flex items-center justify-center text-[7px] text-gray-400">LOGO</div>
                <div class="text-center flex-1"><p class="text-[9px] font-extrabold" x-text="namaSekolah"></p><p class="text-[7px] text-gray-500" x-text="alamat"></p><p class="text-[7px] text-gray-500" x-text="'NPSN: ' + npsn"></p></div>
                <div x-show="posisiLogo !== 'kiri'" class="w-8 h-8 border rounded flex items-center justify-center text-[7px] text-gray-400">LOGO</div>
            </div>
            <div x-show="enableIsi" class="flex-1">
                <div x-show="layoutIsi !== 'foto_only'" class="space-y-2 mb-4"><div class="h-1.5 bg-gray-200 rounded w-3/4"></div><div class="h-1.5 bg-gray-200 rounded"></div><div class="h-1.5 bg-gray-200 rounded w-5/6"></div></div>
                <div x-show="layoutIsi !== 'teks_only'" class="grid gap-2" :class="{'grid-cols-2': kolomLayout === '2', 'grid-cols-3': kolomLayout === '3', 'grid-cols-4': kolomLayout === 'grid'}">
                    <template x-for="i in (kolomLayout === '2' ? 2 : (kolomLayout === '3' ? 3 : 4))" :key="i">
                        <div class="border rounded p-1.5"><div class="h-12 bg-gray-200 rounded mb-1"></div><p x-show="metaNama" class="text-[6px] font-bold">Nama Kegiatan</p><p x-show="metaWaktu" class="text-[6px] text-gray-500">Tanggal</p><p x-show="metaUraian" class="text-[6px]">Uraian kegiatan</p><p x-show="metaPj" class="text-[6px] italic">Penanggung jawab</p></div>
                    </template>
                </div>
            </div>
            <div x-show="enableFooter" class="mt-auto pt-3 border-t border-gray-200">
                <div class="text-right mb-4"><p class="text-[7px] mb-5">Kepala Sekolah</p><p class="text-[8px] font-bold underline" x-text="kepsekNama"></p><p class="text-[7px] text-gray-500" x-text="'NIP. ' + kepsekNip"></p></div>
                <div class="border-t pt-1 flex justify-between text-[6px] text-gray-500"><span x-text="footerCopyright"></span><span><span x-show="tanggalCetak">02/11/2026 </span><span x-show="nomorHalaman">Hal. 1</span></span></div>
            </div>
        </div>
    </div>
    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-3">Pratinjau ilustratif ukuran A4. PDF yang dipilih ditampilkan pada canvas di panel Isi Dokumen.</p>
</div>
