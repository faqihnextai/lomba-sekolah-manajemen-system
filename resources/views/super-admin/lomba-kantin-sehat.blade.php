<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-white leading-tight">
            {{ __('Pengerjaan Dokumen: Lomba Kantin Sehat') }}
        </h2>
    </x-slot>

    <x-modul-lomba
        judul="Pedoman Nasional Kantin Sehat Sekolah"
        deskripsi="Struktur dokumen kelayakan higiene sanitasi, keamanan pangan, dan gizi kantin"
        warna="orange"
        :babList="[
            [
                'judul' => 'Kebijakan & Keorganisasian Kantin',
                'items' => [
                    'Surat Edaran / Kebijakan Tertulis Penyelenggaraan Kantin Sehat',
                    'SK Tim Pengelola Kantin & Perencanaan Anggaran Kantin',
                    'Surat Kontrak Kerja Sama / Kesepakatan dengan Vendor/Penjual Makanan',
                ]
            ],
            [
                'judul' => 'Sarana & Prasarana Hygienic Kantin',
                'items' => [
                    'Dokumen ketersediaan sumber air bersih dan fasilitas sanitasi (bak cuci piring air mengalir, tempat cuci tangan)',
                    'Pengelolaan tempat pengolahan, tempat penyimpanan bahan makanan, dan saluran limbah tertutup',
                    'Bukti jarak lokasi kantin minimal 20 meter dari Tempat Pembuangan Sampah (TPS)',
                ]
            ],
            [
                'judul' => 'Keamanan & Kualitas Pangan',
                'items' => [
                    'Daftar Menu Makanan/Minuman dan Hasil Analisis Profil Gizi (pembatasan gula, garam, lemak/GGL)',
                    'Dokumen Sertifikasi Kantin: Sertifikat Laik Higiene Sanitasi Dinkes, Piagam Bintang Keamanan Pangan (PBKP) BPOM, dan Sertifikat Halal',
                    'Rekam Uji Keamanan Pangan (bebas formalin, boraks, dan pewarna kimia) serta Checklist Cek KLIK (Kemasan, Label, Izin Edar, Kedaluwarsa)',
                ]
            ],
            [
                'judul' => 'SDM & Penjamah Makanan',
                'items' => [
                    'Data Penjamah Makanan, Sertifikat Orientasi/Pelatihan Higiene Kantin, dan Rekam Kesehatan berkala',
                    'Standar Kebersihan Perorangan (penggunaan celemek, tutup kepala, sarung tangan, alas kaki)',
                ]
            ],
            [
                'judul' => 'Promosi & Monitoring Kantin',
                'items' => [
                    'Media Informasi & Edukasi Gizi Seimbang',
                    'Lembar Pemantauan / Checklist Evaluasi Rutin Kantin Sehat',
                ]
            ],
        ]"
    />
</x-app-layout>