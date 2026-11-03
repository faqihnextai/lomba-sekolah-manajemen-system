<?php

use App\Http\Controllers\AdminTeacherAccessController;
use App\Http\Controllers\DocumentPresetController;
use App\Http\Controllers\ManajemenUserController;
use App\Http\Controllers\SchoolCompetitionController;
use App\Http\Controllers\SettingsDocumentController;
use App\Http\Controllers\SuperAdminCompetitionController;
use App\Http\Controllers\UserPortalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

require __DIR__.'/auth.php';

Route::view('/select-lomba', 'SelectLomba')
    ->name('select-lomba');

Route::middleware(['auth', 'verified'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {
        Route::middleware('user.role:superadmin')->group(function () {
            Route::get('/setting-dokumen', [SettingsDocumentController::class, 'index'])
                ->name('setting-dokumen');
            Route::get('/manajemen-user', [ManajemenUserController::class, 'index'])
                ->name('manajemen-user');
            Route::post('/manajemen-user', [ManajemenUserController::class, 'store'])
                ->name('manajemen-user.store');
            Route::patch('/manajemen-user/{user}/role', [ManajemenUserController::class, 'updateRole'])
                ->name('manajemen-user.update-role');

            Route::get('/competitions/{program}', [SuperAdminCompetitionController::class, 'show'])
                ->name('competition');
            Route::patch('/schools/{school}/competitions/{program}/status', [SchoolCompetitionController::class, 'updateForSuperAdmin'])
                ->name('school.competition.status');
        });
    });

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/setting-dokumen', [SettingsDocumentController::class, 'index'])
            ->middleware('user.role:admin,superadmin')
            ->name('setting-dokumen');

        Route::get('/akses-guru', [AdminTeacherAccessController::class, 'index'])
            ->middleware('user.role:admin')
            ->name('akses-guru');

        Route::post('/akses-guru', [AdminTeacherAccessController::class, 'store'])
            ->middleware('user.role:admin')
            ->name('akses-guru.store');

        Route::patch('/competitions/{program}/status', [SchoolCompetitionController::class, 'updateForAdmin'])
            ->middleware('user.role:admin')
            ->name('competition.status');

        Route::patch('/akses-guru/{teacher}', [AdminTeacherAccessController::class, 'update'])
            ->middleware('user.role:admin')
            ->name('akses-guru.update');

        Route::get('/competitions/{program}', [UserPortalController::class, 'pengerjaanDokumen'])
            ->middleware('user.role:admin')
            ->middleware('user.program')
            ->name('competition');
    });

Route::middleware(['auth', 'verified', 'user.role:admin,superadmin'])
    ->prefix('document-presets')
    ->name('document-presets.')
    ->group(function () {
        Route::post('/', [DocumentPresetController::class, 'store'])
            ->name('store');
        Route::post('/templates', [DocumentPresetController::class, 'storeTemplate'])
            ->name('templates.store');
        Route::delete('/{preset}', [DocumentPresetController::class, 'destroy'])
            ->name('destroy');
        Route::patch('/{preset}/template', [DocumentPresetController::class, 'updateTemplate'])
            ->name('templates.update');
        Route::get('/{preset}/template', [DocumentPresetController::class, 'showTemplate'])
            ->name('templates.show');
        Route::get('/{preset}/canvas', [DocumentPresetController::class, 'canvas'])
            ->name('canvas');
        Route::patch('/{preset}', [DocumentPresetController::class, 'update'])
            ->name('update');
        Route::post('/{preset}/assets', [DocumentPresetController::class, 'uploadAsset'])
            ->name('assets.store');
        Route::delete('/{preset}/assets/{file}', [DocumentPresetController::class, 'destroyAsset'])
            ->name('assets.destroy');
        Route::post('/{preset}/exports', [DocumentPresetController::class, 'storeExport'])
            ->name('exports.store');
        Route::get('/{preset}/files/{file}', [DocumentPresetController::class, 'showFile'])
            ->name('files.show');
    });

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

Route::view('/profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');
