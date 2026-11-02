<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-white leading-tight">
            {{ __('Pengerjaan Dokumen: Lomba Sekolah Sehat') }}
        </h2>
    </x-slot>

    <x-modul-lomba
        judul="Program Sekolah Dasar Bersih dan Sehat (UKS & PHBS)"
        deskripsi="Struktur dokumen Trias UKS: Pendidikan Kesehatan, Pelayanan Kesehatan, dan Pembinaan Lingkungan"
        warna="red"
        :babList="[
            [
                'judul' => 'Kebijakan & Manajemen Sekolah Sehat',
                'items' => [
                    'SK Tim Pelaksana UKS / Sekolah Sehat',
                    'Program Kerja, Visi-Misi, dan Rencana Anggaran Sekolah Sehat (RKAS)',
                ]
            ],
            [
                'judul' => 'Pendidikan Kesehatan',
                'items' => [
                    'Dokumen Kurikulum Pendidikan Kesehatan & Pembiasaan Perilaku Hidup Bersih dan Sehat (PHBS)',
                    'Laporan Kegiatan Edukasi / Aksi Bersama (CTPS, penanaman pohon, kerja bakti, kampanye kesehatan)',
                ]
            ],
            [
                'judul' => 'Pelayanan Kesehatan',
                'items' => [
                    'Rekam Penjaringan Kesehatan Peserta Didik (pemeriksaan gigi, mata, telinga, BB/TB) bekerja sama dengan Puskesmas',
                    'Catatan Pertolongan Pertama (P3K/P3P) & Buku Kesehatan Siswa',
                    'Dokumen Pelaksanaan Imunisasi & Pelayanan Konseling',
                ]
            ],
            [
                'judul' => 'Pembinaan Lingkungan Sekolah Sehat',
                'items' => [
                    'Pemeliharaan kebersihan bangunan, halaman, dan drainase air hujan',
                    'Fasilitas Kamar Mandi/WC & Urinoir terpisah (putra/putri) yang memenuhi standar',
                    'Penyediaan Tempat Sampah Terpisah (organik, anorganik, B3) bertutup di setiap ruangan',
                ]
            ],
            [
                'judul' => 'Monitoring, Evaluasi & Pelaporan',
                'items' => [
                    'Instrumen Monitoring Evaluasi (Monev) Kesehatan Lingkungan & Laporan Hasil Evaluasi PHBS',
                ]
            ],
        ]"
    />
</x-app-layout>