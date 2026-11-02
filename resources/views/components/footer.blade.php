<section class="space-y-5">
    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
        <h3 class="font-bold text-gray-900 dark:text-white">Footer & Pengesahan</h3>
        <p class="text-xs text-gray-500 mt-1">Atur informasi penandatangan dan bagian bawah dokumen.</p>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">Nama Kepala Sekolah</label>
        <input type="text" x-model="kepsekNama" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">NIP Kepala Sekolah</label>
        <input type="text" x-model="kepsekNip" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">Teks Copyright</label>
        <input type="text" x-model="footerCopyright" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
    </div>
    <div class="space-y-3 rounded-xl bg-gray-50 dark:bg-gray-900 p-4 border border-gray-200 dark:border-gray-700">
        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200"><input type="checkbox" x-model="nomorHalaman" class="rounded text-blue-600"> Tampilkan nomor halaman</label>
        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200"><input type="checkbox" x-model="tanggalCetak" class="rounded text-blue-600"> Tampilkan tanggal cetak</label>
    </div>
</section>
