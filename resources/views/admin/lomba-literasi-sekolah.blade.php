<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-white leading-tight">
            {{ __('Pengerjaan Dokumen: Lomba Literasi (GLS)') }}
        </h2>
    </x-slot>

    <x-modul-lomba
        judul="Gerakan Literasi Sekolah (GLS)"
        deskripsi="Struktur dokumen mengacu pada Panduan Gerakan Literasi Sekolah"
        warna="indigo"
        :babList="[
            [
                'judul' => 'Kebijakan & Keorganisasian Literasi',
                'items' => [
                    'SK Tim Literasi Sekolah (TLS) & Rencana Program Literasi (Tahunan/Semesteran)',
                    'Dasar Hukum & Peta Strategi GLS Sekolah',
                ]
            ],
            [
                'judul' => 'Kegiatan Pembiasaan Literasi',
                'items' => [
                    'Jurnal Keterlaksanaan Pembiasaan 15 Menit Membaca sebelum KBM',
                    'Jurnal Membaca Siswa & Catatan Ringkasan/Peta Konsep Bacaan',
                ]
            ],
            [
                'judul' => 'Kegiatan Pengembangan & Ekstrakurikuler',
                'items' => [
                    'Jurnal Wajib Kunjung Perpustakaan (Jam Literasi)',
                    'Laporan Kegiatan Klub Literasi (Klub Buku, Klub Penulis Cilik, Klub Film/Fotografi)',
                    'Dokumentasi Projek Lintas Kurikulum & Projek Buku Bersama Keluarga',
                ]
            ],
            [
                'judul' => 'Sarana & Prasarana Literasi',
                'items' => [
                    'Data Inventaris Perpustakaan, Sudut Baca Kelas, dan Area Baca Sekolah',
                    'Inventarisasi Bahan Kaya Teks di Lingkungan Sekolah',
                ]
            ],
            [
                'judul' => 'Apresiasi, Perayaan & Evaluasi',
                'items' => [
                    'Dokumentasi Festival/Pameran Literasi Sekolah dan Peringatan Hari Besar berbasis Literasi',
                    'Data Pemberian Penghargaan/Apresiasi Literasi kepada Siswa',
                    'Instrumen Monitoring dan Evaluasi GLS',
                ]
            ],
        ]"
    />
</x-app-layout>