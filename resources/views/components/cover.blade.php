<section class="space-y-5">
    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
        <h3 class="font-bold text-gray-900 dark:text-white">Pengaturan Halaman Cover</h3>
        <p class="text-xs text-gray-500 mt-1">Atur judul dan identitas pada halaman sampul.</p>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">Judul Laporan</label>
        <input type="text" x-model="coverJudul" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">Subjudul / Tahun Ajaran</label>
        <input type="text" x-model="coverTahun" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
    </div>
    <p class="text-xs text-gray-500">Pratinjau cover akan mengikuti perubahan isian secara langsung.</p>
</section>
