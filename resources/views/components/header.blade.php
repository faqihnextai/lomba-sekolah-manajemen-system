<section class="space-y-5">
    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
        <h3 class="font-bold text-gray-900 dark:text-white">Pengaturan Kop / Header</h3>
        <p class="text-xs text-gray-500 mt-1">Identitas sekolah yang ditampilkan di bagian atas dokumen.</p>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">Nama Sekolah</label>
        <input type="text" x-model="namaSekolah" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">NPSN</label>
            <input type="text" x-model="npsn" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">Posisi Logo</label>
            <select x-model="posisiLogo" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="kiri">Kiri saja</option><option value="kanan">Kanan saja</option><option value="kiri-kanan">Kiri dan kanan</option>
            </select>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">Alamat Sekolah</label>
        <textarea rows="3" x-model="alamat" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">Model Garis Pembatas</label>
        <select x-model="garisPembatas" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="double">Ganda</option><option value="solid">Tunggal tebal</option><option value="dashed">Putus-putus</option>
        </select>
    </div>
</section>
