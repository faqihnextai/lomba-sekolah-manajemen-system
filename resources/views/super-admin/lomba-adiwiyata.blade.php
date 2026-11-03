<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-white leading-tight">
            {{ __('Pengerjaan Dokumen: Lomba Adiwiyata') }}
        </h2>
    </x-slot>

    <x-modul-lomba
        judul="Program Sekolah Adiwiyata (GPBLHS)"
        deskripsi="Struktur dokumen mengacu pada ketentuan Program Adiwiyata & SIDIA"
        warna="emerald"
        :babList="[
            [
                'judul' => 'Persyaratan & Administrasi Pengusulan',
                'items' => [
                    'Surat Permohonan Pengusulan & SK Tim Adiwiyata Sekolah',
                    'Kuesioner Evaluasi Mandiri dan Berita Acara penilaian',
                    'Fotokopi SK Penghargaan Adiwiyata jenjang sebelumnya (jika ada) serta Laporan Pembinaan',
                ]
            ],
            [
                'judul' => 'Perencanaan Gerakan PBLHS (Peduli & Berbudaya Lingkungan Hidup)',
                'items' => [
                    'Dokumen Evaluasi Diri Sekolah (EDS) / IPMLH & Rencana Gerakan PBLHS',
                    'Dokumen Kurikulum (KTSP/KOSP) yang mengintegrasikan penerapan perilaku ramah lingkungan',
                    'Rencana Pelaksanaan Pembelajaran (RPP) / Modul Ajar berwawasan lingkungan',
                ]
            ],
            [
                'judul' => 'Pelaksanaan Kegiatan Aksi Lingkungan',
                'items' => [
                    'Dokumen Aksi Kebersihan & Sanitasi Sekolah',
                    'Pengelolaan Sampah (pengurangan, pemilahan, dan daur ulang)',
                    'Pemeliharaan Keanekaragaman Hayati (penanaman dan pelestarian flora/fauna)',
                    'Efisiensi & Konservasi Energi (penghematan listrik) serta Air (penghematan dan pemanfaatan air hujan)',
                ]
            ],
            [
                'judul' => 'Aspek Pendukung & Kemitraan',
                'items' => [
                    'Dokumen Pembentukan & Kegiatan Kader Adiwiyata',
                    'Publikasi Kegiatan (media sosial, website, majalah dinding)',
                    'Surat Perjanjian Kerja Sama (MoU) & Jejaring Kemitraan Lingkungan',
                ]
            ],
            [
                'judul' => 'Pemantauan & Evaluasi (Monev)',
                'items' => [
                    'Laporan Monev berkala perubahan perilaku warga sekolah dan kualitas lingkungan',
                ]
            ],
        ]"
    />
</x-app-layout>