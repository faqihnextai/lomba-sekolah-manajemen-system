<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ManajemenUserController;
use App\Http\Controllers\SettingsDocumentController;
use App\Http\Controllers\UserPortalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

require __DIR__.'/auth.php';

Route::view('/select-lomba', 'SelectLomba')
    ->name('select-lomba');

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Setting Dokumen
        Route::get('/setting-dokumen', [SettingsDocumentController::class, 'index'])
            ->middleware('user.role:superadmin')
            ->name('setting-dokumen');

        // Hak Akses
        Route::get('/manajemen-user', [ManajemenUserController::class, 'index'])
            ->middleware('user.role:superadmin')
            ->name('manajemen-user');

        Route::post('/manajemen-user', [ManajemenUserController::class, 'store'])
            ->middleware('user.role:superadmin')
            ->name('manajemen-user.store');

        Route::patch('/manajemen-user/{user}/role', [ManajemenUserController::class, 'updateRole'])
            ->middleware('user.role:superadmin')
            ->name('manajemen-user.update-role');

        // Menu Pengerjaan Lomba
        Route::get('/lomba-adiwiyata', [AdminController::class, 'lombaAdiwiyata'])
            ->middleware('user.program:adiwiyata')
            ->name('lomba-adiwiyata');

        Route::get('/lomba-kantin-sehat', [AdminController::class, 'lombaKantinSehat'])
            ->middleware('user.program:kantin-sehat')
            ->name('lomba-kantin-sehat');

        Route::get('/lomba-sekolah-sehat', [AdminController::class, 'lombaSekolahSehat'])
            ->middleware('user.program:sekolah-sehat')
            ->name('lomba-sekolah-sehat');

        Route::view('/lomba-literasi', 'admin.lomba-literasi-sekolah')
            ->middleware('user.program:literasi')
            ->name('lomba-literasi');
    });

// Dashboard
Route::get('/dashboard', [UserPortalController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/pengerjaan-dokumen/{program}', [UserPortalController::class, 'pengerjaanDokumen'])
            ->middleware('user.program')
            ->name('pengerjaan-dokumen');
    });

// Profile
Route::view('/profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');
