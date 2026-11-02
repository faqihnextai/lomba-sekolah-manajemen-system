# Refactor Setting Dokumen

## File
Salin isi folder `resources/views` dan `app/Http/Controllers` ke folder proyek Laravel dengan struktur yang sama.

## Route
Tambahkan isi `routes/web.php.snippet` ke `routes/web.php`. Jika route admin sudah berada di dalam group middleware/prefix, masukkan definisi GET tersebut ke group yang sesuai dan sesuaikan URL agar tidak terjadi prefix ganda.

## Media Library dan PDF Canvas
Controller mengambil media dengan `mime_type = application/pdf` dari Spatie Media Library. Pastikan paket `spatie/laravel-medialibrary` sudah terpasang dan PDF sudah masuk ke tabel `media`.

Komponen Isi Dokumen memakai PDF.js melalui CDN untuk merender halaman PDF ke elemen `<canvas>`. URL media harus dapat diakses browser. Jika file disimpan pada domain/storage berbeda, pastikan CORS mengizinkan akses PDF.js.

## Catatan UI/UX
- Tidak ada tombol simpan dan tidak ada endpoint POST/PUT untuk menyimpan pengaturan.
- Canvas saat ini untuk melihat/render halaman PDF, bukan editor anotasi atau pengubah isi PDF.
- Perubahan input hanya hidup di browser selama halaman terbuka.
- Komponen menggunakan alias Blade `<x-document.* />`, sesuai lokasi `resources/views/components/document/`.
