<?php

namespace App\Http\Controllers;

use App\Models\DocumentPreset;
use App\Models\School;
use App\Support\CompetitionCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsDocumentController extends Controller
{
    public function index(Request $request, CompetitionCatalog $catalog): View
    {
        $user = $request->user();
        $isSuperAdmin = $user->hasRole('superadmin');

        if ($isSuperAdmin) {
            $schools = School::query()->orderBy('name')->get();
            $selectedSchool = $schools->firstWhere('id', $request->integer('school_id'))
                ?? $schools->first();
        } else {
            abort_unless($user->school_id, 403, 'Akun admin belum memiliki sekolah.');

            $selectedSchool = $user->school;
            $schools = collect([$selectedSchool]);
        }

        $programs = $selectedSchool
            ? $catalog->activeForSchool($selectedSchool)
            : [];
        $requestedProgram = $request->query('program');
        $selectedProgram = is_string($requestedProgram) && array_key_exists($requestedProgram, $programs)
            ? $requestedProgram
            : array_key_first($programs);

        $presets = $selectedSchool && $selectedProgram
            ? DocumentPreset::query()
                ->where('school_id', $selectedSchool->id)
                ->where('program', $selectedProgram)
                ->orderBy('name')
                ->get(['id', 'school_id', 'program', 'preset_type', 'template_key', 'name', 'components', 'created_by', 'created_at', 'updated_at'])
            : collect();

        $selectedPresetId = $request->integer('preset');
        $selectedPreset = $selectedPresetId > 0
            ? $presets->firstWhere('id', $selectedPresetId)
            : null;
        $selectedPreset = $selectedPreset
            ? DocumentPreset::query()->findOrFail($selectedPreset->id)
            : null;
        $selectedPreset?->load('school');
        $latestExport = $selectedPreset?->files()
            ->where('kind', 'export_png')
            ->latest()
            ->first();
        $assets = $selectedPreset?->files()
            ->where('kind', 'asset')
            ->latest()
            ->get() ?? collect();
        $templateFile = $selectedPreset?->files()
            ->where('kind', 'school_template')
            ->first();
        $documentTemplates = config('document_templates', []);

        $view = $isSuperAdmin
            ? 'super-admin.setting-dokumen'
            : 'admin.setting-dokumen';

        return view($view, compact(
            'assets',
            'isSuperAdmin',
            'latestExport',
            'presets',
            'programs',
            'schools',
            'selectedPreset',
            'selectedProgram',
            'selectedSchool',
            'templateFile',
            'documentTemplates',
        ));
    }
}
