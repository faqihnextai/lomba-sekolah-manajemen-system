# Panduan Alur Developer

Dokumen ini adalah peta kerja aplikasi berdasarkan struktur source code yang ada di repository saat ini. Gunakan sebagai orientasi saat kembali mengerjakan fitur; perilaku aktual tetap mengikuti code, konfigurasi environment, dan status migration.

## 1. Gambaran aplikasi

Aplikasi ini adalah sistem manajemen lomba sekolah berbasis:

- PHP 8.3 dan Laravel 13.
- Blade untuk halaman; Livewire 3 dan Volt untuk beberapa halaman autentikasi.
- Spatie Permission untuk role.
- Tailwind CSS 3, Vite, dan JavaScript ES modules.
- Fabric.js 7 untuk editor canvas dokumen.
- MySQL/MariaDB pada konfigurasi kerja lokal yang digunakan dalam pengembangan; PHPUnit menggunakan SQLite in-memory.

### Diagram alur request

```text
Browser
  -> routes/web.php atau routes/auth.php
  -> middleware auth / verified / user.role / user.program
  -> controller atau Livewire Volt component
  -> model Eloquent / query database
  -> Blade view + data
  -> browser menjalankan Livewire dan resources/js
  -> operasi berikutnya mengirim HTTP request ke route Laravel
```

### Prinsip domain

- Role aplikasi: `superadmin`, `admin`, dan `guru`. Pemeriksaan akses menggunakan nama role, bukan asumsi ID role.
- Setiap admin/guru terhubung ke satu sekolah melalui `users.school_id`.
- Kode sekolah unik dan disimpan di `schools`.
- Aktivasi lomba saat ini berlaku per sekolah, melalui `school_competition_settings`.
- Penugasan guru dicatat per program dan modul di `user_competition_access`.
- Preset dokumen dimiliki sekolah dan program lomba; file disimpan privat dan metadata file dicatat di database.

## 2. Peta folder

| Lokasi | Tanggung jawab |
|---|---|
| `app/Http/Controllers/` | Controller HTTP: portal, manajemen user, lomba, akses guru, dan preset dokumen. |
| `app/Http/Middleware/` | Pemeriksaan role dan akses program aktif/modul. |
| `app/Livewire/` | Kelas Livewire bersama seperti form login dan aksi logout. |
| `app/Models/` | Model Eloquent dan relasi database. |
| `app/Providers/` | Registrasi service provider aplikasi dan mounting Volt. |
| `app/Support/` | Aturan domain bersama: katalog lomba dan identitas sekolah. |
| `bootstrap/app.php` | Routing Laravel, middleware alias, proxy, dan aturan respons exception. |
| `config/competitions.php` | Katalog program lomba, bab, dan daftar dokumen. |
| `config/filesystems.php` | Disk penyimpanan `local`, `public`, dan konfigurasi `s3`. |
| `database/migrations/` | Riwayat dan definisi skema database. |
| `database/seeders/` | Seeder role dan seeder database utama. |
| `resources/views/admin/` | Dashboard admin, setting dokumen, dan akses guru. |
| `resources/views/super-admin/` | Dashboard superadmin, manajemen user, halaman lomba, setting dokumen, dan canvas. |
| `resources/views/user/` | Dashboard guru dan halaman pengerjaan dokumen. |
| `resources/views/livewire/` | Volt/Livewire untuk login, registrasi, navigasi, dan profil. |
| `resources/views/components/` | Komponen Blade bersama, termasuk cover/header/isi/footer/preview. |
| `resources/js/` | Entry JavaScript dan handler khusus editor canvas. |
| `resources/css/` | Entry Tailwind dan aturan tambahan canvas. |
| `tests/Feature/` | Tes alur HTTP, autentikasi, role, sekolah, lomba, dan preset dokumen. |
| `tests/Unit/` | Tes unit tanpa alur HTTP. |
| `public/` | Front controller, file publik, dan hasil build Vite. |

Folder `vendor/` dan `node_modules/` berisi dependency; jangan mengeditnya untuk mengubah fitur aplikasi. File `.env` adalah konfigurasi lokal rahasia; jangan masukkan isinya ke dokumentasi atau Git.

## 3. Routing dan middleware

### Definisi rute

- [routes/web.php](routes/web.php): dashboard, admin, superadmin, guru, aktivasi lomba, akses guru, serta preset dokumen.
- [routes/auth.php](routes/auth.php): route login, registrasi, reset password, konfirmasi password, dan verifikasi email melalui Volt/controller.
- [routes/console.php](routes/console.php): command Artisan khusus aplikasi.
- [bootstrap/app.php](bootstrap/app.php): mendaftarkan alias middleware:
  - `user.role` → [EnsureUserHasRole](app/Http/Middleware/EnsureUserHasRole.php)
  - `user.program` → [EnsureUserHasProgram](app/Http/Middleware/EnsureUserHasProgram.php)

### Hal yang dicek middleware

1. `auth`: pengguna harus login.
2. `verified`: email harus terverifikasi untuk sebagian besar halaman operasional.
3. `user.role:<role>`: endpoint hanya untuk role yang diperbolehkan.
4. `user.program`: program harus dikenal dan aktif untuk sekolah; akses modul guru juga diperiksa.

Daftar nama rute aktual dapat dilihat dengan:

```bash
php artisan route:list
php artisan route:list --name=document-presets
```

## 4. Alur autentikasi dan role

- Route autentikasi didaftarkan di [routes/auth.php](routes/auth.php).
- Login dan registrasi Volt berada di `resources/views/livewire/pages/auth/`.
- Registrasi saat ini mengumpulkan nama sekolah, kode sekolah, lomba awal, dan kredensial. Akun publik diberi role `guru`, bukan `admin` atau `superadmin`.
- [SchoolRegistry](app/Support/SchoolRegistry.php) menormalisasi kode sekolah dan memastikan kode yang sama tidak dipakai untuk nama sekolah berbeda.
- Role disediakan oleh [RoleSeeder](database/seeders/RoleSeeder.php), yang membuat `superadmin`, `admin`, dan `guru`.
- Role disimpan oleh Spatie Permission; `User` memakai trait `HasRoles`.
- [ManajemenUserController](app/Http/Controllers/ManajemenUserController.php) adalah permukaan superadmin untuk membuat akun, menetapkan role, sekolah, dan akses modul.
- [AdminTeacherAccessController](app/Http/Controllers/AdminTeacherAccessController.php) memungkinkan admin membuat akun guru dengan role tetap `guru` di sekolah admin itu, lalu mengatur modulnya.

## 5. Alur dashboard dan lomba

- [UserPortalController](app/Http/Controllers/UserPortalController.php) memilih dashboard berdasarkan role.
- Superadmin melihat daftar sekolah, admin sekolah, dan aktivasi lomba per sekolah.
- Admin melihat lomba sekolahnya sendiri dan dapat mengubah status aktivasi sekolahnya.
- Guru hanya mendapat lomba dan modul yang ditugaskan kepadanya.
- [CompetitionCatalog](app/Support/CompetitionCatalog.php) membaca definisi lomba dari `config/competitions.php`, membuat key modul, dan menyaring daftar berdasarkan status sekolah.
- [SchoolCompetitionController](app/Http/Controllers/SchoolCompetitionController.php) menyimpan aktivasi lomba admin atau superadmin.
- [EnsureUserHasProgram](app/Http/Middleware/EnsureUserHasProgram.php) memblokir akses ke program yang tidak dikenal, nonaktif di sekolah, atau tidak ditugaskan.

Konfigurasi konten lomba ada di [config/competitions.php](config/competitions.php). Tambahkan/ubah program di sana dengan tetap menjaga key program stabil karena key tersebut digunakan oleh database dan route.

## 6. Alur Setting Dokumen dan Canvas

### Tampilan

- Route halaman admin: `admin.setting-dokumen`.
- Route halaman superadmin: `super-admin.setting-dokumen`.
- Keduanya menggunakan [SettingsDocumentController](app/Http/Controllers/SettingsDocumentController.php).
- View admin saat ini memakai ulang view superadmin lewat `@include` di `resources/views/admin/setting-dokumen.blade.php`.
- [resources/views/super-admin/setting-dokumen.blade.php](resources/views/super-admin/setting-dokumen.blade.php) menampilkan tiga card:
  1. sekolah dan lomba aktif;
  2. membuat **template sekolah** berbasis form HTML atau **preset canvas bebas**;
  3. preview template HTML atau ekspor PNG canvas.
- Pemilihan preset tersimpan berada setelah ketiga card; preset tidak dibuka otomatis.
- Pemilih di bagian akhir menampilkan template sekolah dan canvas bebas. Hanya preset canvas yang membuka editor desktop/halaman canvas mobile.
- Partial UI editor: [resources/views/super-admin/partials/document-editor.blade.php](resources/views/super-admin/partials/document-editor.blade.php).
- Form untuk menyesuaikan nama, teks, dan gambar template sekolah: [resources/views/super-admin/partials/school-template-editor.blade.php](resources/views/super-admin/partials/school-template-editor.blade.php).
- Layout HTML yang dirender lalu disimpan sebagai file template privat: [resources/views/document-templates/school-template.blade.php](resources/views/document-templates/school-template.blade.php).
- Daftar gaya template dan definisi field: [config/document_templates.php](config/document_templates.php).
- Halaman khusus canvas: [resources/views/super-admin/document-canvas.blade.php](resources/views/super-admin/document-canvas.blade.php).
- Template bawaan dipilih dari form card preset. Membuatnya menghasilkan record preset bertipe `school_template` dan file HTML privat pada disk `local`; isian teks dan gambar dapat diubah dari form lalu HTML di-render dan disimpan ulang.
- Template sekolah tidak memakai Fabric.js, tidak bisa dibuka di canvas, dan dipratinjau sebagai HTML. Preset bertipe `canvas` tetap kosong saat dibuat dan dipakai hanya untuk desain bebas.
- Tersedia tiga gaya cover, tiga kop (2-4 logo), tiga pendahuluan, tiga temuan masalah, tiga layout lampiran, tiga footer, dan tiga tanda tangan.
- Template bawaan adalah definisi di konfigurasi, bukan record/file sekolah; pilihan bawaan tersebut tidak dapat dihapus. Record template yang sudah dibuat sekolah dapat dihapus bersama file terkait.

### Data yang disiapkan untuk Blade

`SettingsDocumentController@index`:

1. Menentukan role pengguna.
2. Superadmin mendapat daftar sekolah dan dapat memilih sekolah lewat query `school_id`.
3. Admin dikunci pada sekolahnya sendiri.
4. Mengambil hanya program aktif sekolah melalui `CompetitionCatalog`.
5. Mengambil preset berdasarkan sekolah + program terpilih.
6. Mengambil file aset, ekspor canvas terakhir, dan file HTML template bila preset terpilih bertipe sekolah.
7. Mengirim data tersebut ke view admin atau superadmin.

### Route operasi preset

Route berada pada group `document-presets.*` di `routes/web.php`, dengan middleware login, verifikasi, dan role admin/superadmin.

| Nama route | Method | Tujuan |
|---|---|---|
| `document-presets.store` | POST | Membuat preset kosong bertipe `canvas` untuk desain bebas. |
| `document-presets.templates.store` | POST | Membuat preset sekolah dari salah satu definisi template bawaan dan menghasilkan file HTML awal. |
| `document-presets.templates.update` | PATCH | Menyimpan nama, teks, dan gambar isian template; menghasilkan ulang file HTML. |
| `document-presets.templates.show` | GET | Menayangkan file HTML privat untuk preview setelah memeriksa akses. |
| `document-presets.destroy` | DELETE | Menghapus record preset sekolah/canvas yang berwenang dan semua file terkait; definisi bawaan di config tetap. |
| `document-presets.canvas` | GET | Membuka editor hanya untuk preset bertipe `canvas`. |
| `document-presets.update` | PATCH | Menyimpan nama, bagian aktif, dan JSON canvas hanya untuk preset canvas. |
| `document-presets.assets.store` | POST | Mengunggah gambar aset khusus preset canvas. |
| `document-presets.assets.destroy` | DELETE | Menghapus aset yang tidak lagi dipakai canvas atau form template. |
| `document-presets.exports.store` | POST | Menyimpan hasil ekspor preset canvas sebagai PNG. |
| `document-presets.files.show` | GET | Mengirim aset/PNG privat setelah memeriksa hak akses. |

Controller untuk operasi tersebut adalah [DocumentPresetController](app/Http/Controllers/DocumentPresetController.php). Ia memeriksa sekolah dan status program, memvalidasi isi JSON dan tipe/ukuran file, menyimpan file ke disk `local`, lalu mencatat metadata di tabel file.

### Handler JavaScript

- Entry Vite [resources/js/app.js](resources/js/app.js) mengimpor `document-editor.js`.
- [resources/js/document-editor.js](resources/js/document-editor.js) menemukan elemen `[data-document-editor]`, membuat Fabric `Canvas`, lalu menghubungkan setiap tombol/form berdasarkan `data-action`, `data-property`, dan atribut `data-*`.
- Canvas mempunyai ukuran A4 portrait 794 × 1123 px.
- Perubahan objek memicu snapshot riwayat, lalu autosave setelah jeda singkat. Tombol Simpan dan shortcut `Ctrl/Cmd+S` menyimpan langsung.
- Form template sekolah tidak menghasilkan objek Fabric. Form mengirim data ke `document-presets.templates.update`; controller me-render Blade menjadi file HTML yang disimpan pada folder preset privat.
- Handler canvas hanya beroperasi pada record `preset_type=canvas`; template sekolah ditolak oleh endpoint canvas dan tidak mempunyai kontrol masuk ke editor.
- Aset yang diunggah dimasukkan ke canvas dengan ID aset. Saat JSON disimpan, backend mengizinkan hanya aset milik preset yang sama dan membangun ulang URL file sendiri.
- Tombol hapus aset membuang objek terkait dari halaman canvas, menyimpan perubahan, lalu menghapus file privat dan metadatanya. Backend menolak penghapusan jika masih ada referensi.
- Zoom memakai transformasi viewport Fabric pada isi canvas (bukan mengubah zoom halaman browser), tombol tambah/kurang/reset, dan gesture pinch dua jari; rentangnya 50%-200% dan ekspor tetap merender pada ukuran A4 normal.
- Susunan elemen menyediakan dropdown pilih elemen serta daftar layer yang dapat diklik; keduanya memilih objek aktif pada canvas.
- Ekspor dilakukan per halaman aktif ke PNG, dikirim ke Laravel, dicatat sebagai file `export_png`, lalu tersedia untuk preview dan unduh.
- `resources/css/app.css` mengimpor Tailwind dan mengatur ukuran kontainer Fabric agar sesuai layar.
- Build entry Vite dikonfigurasi di [vite.config.js](vite.config.js).

### Batas editor saat ini

- Fitur inti: teks, gambar, bentuk, format teks dasar, layer, zoom, beberapa halaman, undo/redo, autosave, dan ekspor PNG satu halaman per permintaan.
- Ini bukan seluruh fitur Canva atau Microsoft Word: belum ada layout ulang teks otomatis lintas halaman, DOCX/PDF export, kolaborasi waktu nyata, dan banyak fitur profesional lainnya.
- Controller membatasi data JSON canvas hingga 200.000 karakter; dokumen kompleks tetap dibatasi ukuran tersebut.
- Maksimum 25 halaman dan 500 objek per preset.
- Format upload aset: JPG/JPEG, PNG, atau WebP, maksimum 10 MB dan ukuran sisi maksimum 5.000 px. SVG tidak diterima.
- Menonaktifkan sebuah lomba akan membuat operasi preset untuk lomba tersebut ditolak sampai lomba aktif lagi.

## 7. Database dan hubungan model

### Tabel utama aplikasi

| Tabel | Isi dan relasi penting |
|---|---|
| `users` | Akun, email, password hash, `school_id`, program pilihan awal. |
| `schools` | Nama dan kode sekolah unik. Satu sekolah memiliki banyak user, pengaturan lomba, dan preset. |
| `roles`, `permissions`, pivot Spatie | Role dan permission. Role aplikasi yang digunakan adalah `superadmin`, `admin`, dan `guru`. |
| `school_competition_settings` | Status `enabled` per pasangan sekolah + program, serta pengguna terakhir yang mengubahnya. |
| `user_competition_access` | Program dan key modul yang ditugaskan untuk seorang guru. |
| `document_presets` | Preset milik sekolah + lomba; `preset_type` membedakan `canvas` dan `school_template`; menyimpan JSON canvas atau `template_key` + `template_data`. |
| `document_preset_files` | Referensi gambar, file HTML template sekolah, dan PNG ekspor; termasuk path, disk, MIME type, ukuran, pengunggah, dan metadata. |
| `media` | Tabel Spatie Media Library yang digunakan bagian lain aplikasi; editor preset saat ini menggunakan tabel `document_preset_files`, bukan tabel ini. |

### Relasi konseptual

```text
schools 1 ─── * users
schools 1 ─── * school_competition_settings
schools 1 ─── * document_presets
users   1 ─── * user_competition_access
document_presets 1 ─── * document_preset_files
```

Role Spatie disimpan terpisah dalam pivot role, tidak disimpan sebagai angka role di `users`.

### Migration yang perlu dikenal

- `0001_01_01_*`: user/session/password reset, cache, dan jobs Laravel.
- `2026_11_02_044248_*`: tabel Spatie Permission.
- `2026_11_02_081133_*`: tabel media Spatie.
- `2026_11_02_094941_*`: program pilihan awal user.
- `2026_11_03_065458_*` hingga `065501_*`: sekolah, status lomba historis, `school_id`, dan akses modul user.
- `2026_11_03_071940_*`: status lomba **per sekolah** yang saat ini digunakan.
- `2026_11_03_080122_*` dan `080123_*`: preset dokumen dan metadata aset/file preset.
- `2026_11_03_100746_*`: tipe preset dan data template sekolah.

**Catatan migrasi:** `competition_settings` adalah migration historis untuk status global lama. Alur aktif sekarang membaca `school_competition_settings`; jangan mengira tabel global itu sebagai sumber status sekolah terkini. Migration yang sudah diterapkan/shared sebaiknya tidak diedit; buat migration baru untuk perubahan schema berikutnya.

### Model domain

- [User](app/Models/User.php): autentikasi, role, sekolah, dan akses lomba.
- [School](app/Models/School.php): user, pengaturan kompetisi, dan preset.
- [SchoolCompetitionSetting](app/Models/SchoolCompetitionSetting.php): status lomba sekolah.
- [UserCompetitionAccess](app/Models/UserCompetitionAccess.php): akses guru per modul.
- [DocumentPreset](app/Models/DocumentPreset.php): tipe preset, JSON canvas atau data template, dan relasi sekolah/pembuat/file.
- [DocumentPresetFile](app/Models/DocumentPresetFile.php): metadata file dan relasi preset/pengunggah.

JSON `components`, `canvas_data`, `template_data`, dan `metadata` di-cast oleh Eloquent menjadi array.

## 8. Penyimpanan file

Konfigurasi disk berada di [config/filesystems.php](config/filesystems.php):

- `local`: file privat di bawah `storage/app/private`.
- `public`: file publik di bawah `storage/app/public`, biasanya diakses melalui `storage:link`.
- `s3`: disk object storage, jika kredensial dan environment sudah diatur.

Editor secara eksplisit menggunakan disk `local`, bukan disk default yang mungkin berubah berdasarkan `.env`. URL file melewati route Laravel dan otorisasi. Metadata di database tidak berisi byte gambar; file fisik tetap berada di filesystem.

## 9. Tes dan pemeriksaan

Tes feature utama:

- `tests/Feature/Auth/`: autentikasi, registrasi, verifikasi, dan reset password.
- [AdminTeacherAccessTest](tests/Feature/AdminTeacherAccessTest.php): sekolah dan akses modul guru.
- [SchoolCompetitionTest](tests/Feature/SchoolCompetitionTest.php): aktivasi lomba per sekolah.
- [ManajemenUserTest](tests/Feature/ManajemenUserTest.php): pembuatan/perubahan role dan sekolah melalui superadmin.
- [DocumentPresetTest](tests/Feature/DocumentPresetTest.php): role, isolasi sekolah, preset, upload, canvas, dan ekspor.
- `tests/Feature/UserPortalTest.php`: dashboard dan akses program guru.

Konfigurasi PHPUnit memakai SQLite in-memory agar test tidak menyentuh database lokal. Perintah yang biasa dipakai:

<!-- ```bash
php artisan test --compact
php artisan test --compact tests\Feature\DocumentPresetTest.php
vendor/bin/pint --dirty --format agent
npm run build
php artisan route:list
php artisan migrate:status
```

## 10. Menjalankan dan merilis

Prasyarat versi dan dependency ada di `composer.json` dan `package.json`. Jangan memasang/menaikkan paket kecuali dibutuhkan dan sudah disetujui untuk perubahan yang relevan.

Contoh langkah setup yang umum:

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

Pada hosting, siapkan `.env` hosting yang benar, pastikan storage writable, build aset produksi, lalu jalankan migration dengan:

```bash
php artisan migrate --force
```

Jangan memakai `php artisan migrate:fresh` pada database yang berisi data karena perintah itu menghapus lalu membuat ulang tabel. Periksa migration yang tertunda terlebih dahulu dengan `php artisan migrate:status`.

## 11. Cara mulai mengubah fitur

Untuk permintaan perubahan baru, ikuti urutan ini:

1. Temukan route dan middleware di `routes/`.
2. Ikuti controller/Volt component yang menerima request.
3. Periksa model dan migration yang menjadi sumber data.
4. Telusuri view Blade yang dirender, termasuk partial/component.
5. Untuk interaksi browser, ikuti `data-*` attribute dari Blade ke handler JavaScript.
6. Periksa otorisasi tenant/sekolah, validasi input, serta lokasi penyimpanan file.
7. Tambah/ubah feature test untuk perilaku dan batas akses yang terpengaruh.
8. Jalankan test terfokus, Pint jika PHP berubah, lalu build Vite jika frontend berubah.
9. Migration baru dibuat dengan Artisan dan dijalankan pada environment yang dituju.

## 12. Rencana alur upload dokumen guru (belum dibuat)

Bagian ini adalah rancangan untuk pekerjaan berikutnya, bukan klaim bahwa fitur upload dokumen guru sudah tersimpan ke database. Upload gambar untuk **aset preset canvas** pada `document-presets.*` adalah fitur berbeda dari upload bukti/dokumen lomba oleh guru.

### Tujuan dan lingkup versi pertama

Guru membuka lomba dan modul yang ditugaskan, lalu mengirim satu atau beberapa foto dokumen. Setiap file diberi bagian dokumen dan penjelasan manual. -->

Bagian yang disediakan:

- `cover`
- `header`
- `body` — isi/bukti dokumen
- `footer`
- `signature`
- `other`

Setiap kiriman terikat ke sekolah akun guru, program aktif, modul yang ditugaskan, dan bagian dokumen. Field keterangan ditulis manual pada tahap pertama. AI adalah kemungkinan pengembangan berikutnya; integrasi AI dan API key tidak termasuk versi awal.

### Alur request yang direncanakan

```text
Guru login
  -> dashboard hanya menampilkan lomba aktif dan modul yang ditugaskan
  -> buka modul lomba
  -> pilih bagian (cover/header/body/footer/signature/other)
  -> pilih/ambil foto + isi penjelasan manual
  -> POST route upload
  -> controller/form request memvalidasi role, sekolah, lomba, modul, bagian, file
  -> file disimpan ke disk privat Laravel
  -> metadata dan penjelasan disimpan ke database
  -> halaman modul menampilkan kiriman milik sekolah tersebut
```

Untuk desain URL, lanjutkan pola route yang sudah ada:

```text
GET    /user/competitions/{program}/modules/{module}/uploads
POST   /user/competitions/{program}/modules/{module}/uploads
PATCH  /user/document-uploads/{upload}
DELETE /user/document-uploads/{upload}
GET    /user/document-uploads/{upload}/file
```

Nama route yang disarankan:

| Nama route | Method | Tujuan |
|---|---|---|
| `user.document-uploads.index` | GET | Daftar upload untuk sekolah + lomba + modul yang sedang dibuka. |
| `user.document-uploads.store` | POST | Membuat satu record upload foto dan penjelasan. |
| `user.document-uploads.update` | PATCH | Mengubah bagian atau keterangan upload milik sekolahnya. |
| `user.document-uploads.destroy` | DELETE | Menghapus upload milik sekolahnya beserta file privat. |
| `user.document-uploads.file` | GET | Mengirim gambar setelah pemeriksaan akses sekolah. |

Rute upload harus memakai `auth`, `verified`, `user.role:guru`, dan `user.program`. Selain middleware, controller/Form Request wajib memeriksa bahwa guru benar-benar ditugaskan ke `{module}` untuk `{program}`. Route binding `{upload}` sendiri tidak memberikan otorisasi.

### Aturan sekolah dan kode sekolah

- `school_id` diambil dari `$request->user()->school_id`; **jangan** menerima `school_id` atau `school_code` sebagai sumber kepemilikan dari browser.
- Semua query daftar, edit, hapus, dan unduh dibatasi oleh `school_id` akun yang login. Kiriman milik sekolah lain sebaiknya menghasilkan `404`.
- Identitas sekolah utama adalah foreign key `school_id` ke `schools.id`. Kode sekolah sudah unik di `schools.code`, sehingga kode tidak perlu disalin ke setiap upload.
- Saat perlu menampilkan kode, ambil melalui relasi `upload->school->code`. Dengan begitu, rename/koreksi data sekolah tidak membuat salinan kode sekolah menjadi berbeda.
- Program harus aktif di sekolah tersebut, dan modul harus ada pada akses guru. Jangan hanya memeriksa nilai dropdown atau parameter URL.

### Database yang disarankan

Gunakan satu baris `school_document_uploads` per foto yang diunggah. Bila sebuah dokumen memerlukan beberapa foto, guru membuat beberapa upload; masing-masing dapat mempunyai bagian dan keterangan sendiri. Ini lebih sederhana untuk versi awal daripada menyimpan banyak file dalam satu JSON.

Migration baru yang disarankan:

```text
database/migrations/*_create_school_document_uploads_table.php
```

Kolom:

| Kolom | Tipe/tujuan |
|---|---|
| `id` | Primary key. |
| `school_id` | Foreign key ke `schools.id`, cascade saat sekolah benar-benar dihapus. Ini adalah pembatas tenant. |
| `uploaded_by` | Foreign key ke `users.id`, null saat user dihapus agar audit record dapat dipertahankan (atau cascade bila kebijakan penghapusan akun mengharuskan file ikut dihapus). |
| `program` | Key program dari `config/competitions.php`, misalnya `adiwiyata`. |
| `module_key` | Key modul yang ditugaskan ke guru. |
| `document_key` | Key stabil untuk item/jenis dokumen dalam modul; bukan label yang berubah-ubah. |
| `document_label` | Snapshot label dokumen yang terlihat saat upload dibuat. |
| `section` | String terbatas: `cover`, `header`, `body`, `footer`, `signature`, `other`. |
| `explanation` | Keterangan manual guru, tipe `text`. Bisa `nullable` bila UX mengizinkan menyimpan file sebelum keterangan selesai. |
| `explanation_source` | String terbatas; versi pertama selalu `manual`, kelak bisa membedakan hasil `ai` yang telah disetujui. Default `manual`. |
| `disk` | Disk Laravel yang menyimpan file; versi awal `local`. |
| `storage_path` | Path relatif privat yang dibuat server, bukan nama path dari request. |
| `original_name` | Nama file upload yang sudah dibersihkan untuk tampilan/download. |
| `mime_type` | MIME type hasil validasi Laravel. |
| `size` | Ukuran file dalam byte. |
| timestamps | Waktu pembuatan/perubahan. |

Index yang disarankan untuk query halaman modul:

```text
(school_id, program, module_key, created_at)
```

Pembuatan migration nantinya harus memakai Artisan. Jika kebutuhan audit/review sudah disepakati, status seperti `draft`, `submitted`, atau `reviewed` bisa dirancang terpisah; jangan menambah alur persetujuan yang belum diminta ke tahap upload dasar.

`document_key` harus stabil. `config/competitions.php` saat ini menyimpan `babList[].items` sebagai teks; sebelum mengaitkan upload dengan item tertentu, ubah/ perluas struktur katalog agar tiap item mempunyai key eksplisit dan label. Jangan menggunakan teks judul sebagai identitas permanen karena judul dapat diedit atau diterjemahkan.

### Model dan relasi yang disarankan

```text
app/Models/SchoolDocumentUpload.php
```

Relasi model:

```text
SchoolDocumentUpload belongsTo School
SchoolDocumentUpload belongsTo User (uploadedBy)
School hasMany SchoolDocumentUpload
User hasMany SchoolDocumentUpload
```

Cast yang diperlukan: timestamps bawaan Eloquent; `explanation_source` dan `section` dapat tetap berupa string yang divalidasi dengan allow-list. Jika menjadi domain yang lebih besar, pertimbangkan Enum PHP, tetapi jangan menduplikasi daftar nilai tanpa satu sumber kebenaran.

### Controller dan Form Request yang disarankan

```text
app/Http/Controllers/UserDocumentUploadController.php
app/Http/Requests/StoreSchoolDocumentUploadRequest.php
app/Http/Requests/UpdateSchoolDocumentUploadRequest.php
```

Controller mengatur alur HTTP: daftar, simpan, edit keterangan/bagian, hapus, dan file response. Form Request atau validasi controller memeriksa:

- hanya role `guru` untuk upload awal;
- sekolah diambil dari user yang login;
- program tersedia dan aktif untuk sekolah;
- key modul ditugaskan ke guru;
- `section` termasuk allow-list;
- `document_key` termasuk dokumen katalog pada program/modul yang dipilih;
- file benar-benar gambar sesuai kebijakan aplikasi, misalnya JPEG/PNG/WebP, ukuran maksimum yang ditentukan, dan dimensi maksimum;
- penjelasan memiliki panjang maksimum yang wajar;
- update/delete/file hanya berlaku untuk upload di sekolah dan lingkup akses pengguna.

Gunakan transaksi untuk menyelaraskan record database dengan operasi file sejauh memungkinkan. Saat file dihapus/ganti, hapus path privat yang tercatat setelah operasi record sukses; rancang pemulihan bila penyimpanan atau penghapusan file gagal agar tidak meninggalkan orphan.

### Penyimpanan gambar privat

Gunakan disk `local` yang berakar di `storage/app/private`, serupa file preset. Contoh struktur path yang dibuat server:

```text
school-document-uploads/{school_id}/{program}/{module_key}/{uuid}.{extension}
```

Jangan memakai `public` URL atau mengandalkan nama file yang diberikan client. Tabel hanya mencatat metadata/path; byte gambar tetap berada di Storage. Route unduh/preview memeriksa sekolah dan akses sebelum mengirim file.

Jangan mencatat gambar atau penjelasan sensitif ke log. API key AI, jika nanti dipakai, harus berada di `.env`/secret manager dan hanya dibaca server; jangan pernah dikirim ke JavaScript atau Blade.

### File yang akan dibuat/diubah

| File | Perubahan yang direncanakan |
|---|---|
| `database/migrations/*_create_school_document_uploads_table.php` | Skema record upload per sekolah, lomba, modul, bagian, dan gambar. |
| `app/Models/SchoolDocumentUpload.php` | Model upload dan relasi. |
| `app/Models/School.php` | Tambah relasi `documentUploads()`. |
| `app/Models/User.php` | Tambah relasi `documentUploads()` untuk audit uploader, bila sesuai pola model. |
| `app/Http/Controllers/UserDocumentUploadController.php` | Daftar, upload, update keterangan, delete, dan response privat untuk file. |
| `app/Http/Requests/StoreSchoolDocumentUploadRequest.php` | Validasi/otorisasi payload create. |
| `app/Http/Requests/UpdateSchoolDocumentUploadRequest.php` | Validasi/otorisasi edit penjelasan dan bagian. |
| `routes/web.php` | Tambahkan route `user.document-uploads.*` dengan middleware role/program. |
| `config/competitions.php` | Beri key stabil untuk dokumen persyaratan pada tiap modul, bila upload dikaitkan ke item checklist. |
| `app/Support/CompetitionCatalog.php` | Helper terpusat untuk validasi key modul dan dokumen program. |
| `resources/views/components/modul-lomba.blade.php` | Ganti modal/alert simulasi upload dengan form nyata: bagian, foto, penjelasan, dan daftar hasil simpan. |
| `resources/views/components/user/document-upload-form.blade.php` | Komponen form upload reusable jika bentuknya bermanfaat di banyak modul. |
| `resources/views/components/user/document-upload-list.blade.php` | Daftar preview, bagian, keterangan, dan waktu upload. |
| `resources/views/user/pengerjaan-dokumen.blade.php` | Teruskan data sekolah/module/upload ke komponen bila controller menyiapkannya. |
| `resources/js/user-document-uploads.js` | Hanya jika diperlukan untuk preview gambar, progress upload, atau interaksi tanpa reload; validasi/otorisasi tetap server-side. |
| `tests/Feature/UserDocumentUploadTest.php` | Tes role, sekolah, program aktif, modul, upload, file privat, validasi, update, dan delete. |

### Hubungan dengan UI yang sekarang

`resources/views/components/modul-lomba.blade.php` saat ini berisi placeholder status dokumen dan form upload simulasi yang `preventDefault()` lalu hanya menampilkan alert. Progres yang ditampilkan di bagian itu juga diberi label simulasi. Implementasi upload backend harus mengganti simulasi tersebut; jangan menganggap upload yang terlihat sekarang sudah masuk database.

Dashboard admin/superadmin saat ini juga menyatakan progres dokumen belum tersedia. Setelah submission nyata tersedia, progres harus dihitung dari record upload berdasarkan sekolah + program + modul/dokumen, bukan angka statis.

## 13. Batas TO DO

===========================
==========TO DO============
===========================

- [ ] Menentukan apakah satu kiriman mewakili satu foto atau perlu beberapa foto dalam satu dokumen; rancangan awal saat ini memakai satu record per foto.
- [ ] Menetapkan ukuran file, batas dimensi, format gambar yang diperbolehkan, serta teks penjelasan maksimum.
- [ ] Memberi key stabil untuk setiap persyaratan dokumen di katalog lomba sebelum menyimpan upload per item.
- [ ] Membuat migration `school_document_uploads` dengan foreign key `school_id`, program, modul, bagian, penjelasan, dan metadata file.
- [ ] Membuat model `SchoolDocumentUpload` beserta relasi sekolah, uploader, dan user.
- [ ] Membuat `UserDocumentUploadController` dan validasi/Form Request untuk operasi upload.
- [ ] Menambahkan route guru untuk melihat daftar, upload foto, mengedit penjelasan/bagian, menghapus kiriman, dan melihat file privat.
- [ ] Memastikan semua query dibatasi berdasarkan `school_id` dari user login; jangan menerima kode/sekolah pemilik dari browser.
- [ ] Memastikan program sedang aktif dan modul sudah ditugaskan kepada guru pada setiap operasi, bukan hanya saat halaman dibuka.
- [ ] Mengganti form/alert simulasi di `resources/views/components/modul-lomba.blade.php` dengan upload sungguhan dan preview gambar.
- [ ] Menyimpan foto ke disk privat serta mencatat metadata file; menguji pembersihan file saat upload dibatalkan atau record dihapus.
- [ ] Menambahkan feature test untuk akses lintas sekolah, role selain guru, lomba nonaktif, modul tak ditugaskan, validasi file, dan file privat.
- [ ] Menghitung progres admin/superadmin dari upload tersimpan dan menghapus indikator persentase simulasi.
- [ ] **Tahap berikutnya, belum sekarang:** menentukan provider AI, kebijakan privasi/persetujuan pengiriman foto, biaya/limit, dan penyimpanan API key di server.
- [ ] **Tahap berikutnya, belum sekarang:** menghasilkan saran penjelasan dengan AI sebagai draft yang ditinjau guru; jangan otomatis menimpa penjelasan manual atau menyimpan AI sebagai final tanpa persetujuan.
