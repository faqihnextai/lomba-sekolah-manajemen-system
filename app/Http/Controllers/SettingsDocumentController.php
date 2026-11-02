<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SettingsDocumentController extends Controller
{
    /**
     * Menampilkan halaman pengaturan dan daftar PDF dari Spatie Media Library.
     * Halaman ini hanya untuk UI/UX; tidak ada proses penyimpanan konfigurasi.
     */
    public function index(): View
    {
        $documents = collect();

        if (class_exists(Media::class)) {
            $documents = Media::query()
                ->where('mime_type', 'application/pdf')
                ->latest()
                ->get();
        }

        return view('admin.setting-dokumen', compact('documents'));
    }
}
