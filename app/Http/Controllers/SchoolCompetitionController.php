<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolCompetitionSetting;
use App\Support\CompetitionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SchoolCompetitionController extends Controller
{
    public function updateForAdmin(
        Request $request,
        string $program,
        CompetitionCatalog $catalog,
    ): RedirectResponse {
        $school = $request->user()->school;
        abort_unless($school, 403, 'Akun admin belum memiliki sekolah.');
        $this->ensureProgramExists($program, $catalog);

        return $this->saveStatus($request, $school, $program);
    }

    public function updateForSuperAdmin(
        Request $request,
        School $school,
        string $program,
        CompetitionCatalog $catalog,
    ): RedirectResponse {
        $this->ensureProgramExists($program, $catalog);

        return $this->saveStatus($request, $school, $program);
    }

    private function saveStatus(Request $request, School $school, string $program): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        SchoolCompetitionSetting::query()->updateOrCreate(
            [
                'school_id' => $school->id,
                'program' => $program,
            ],
            [
                'enabled' => $request->boolean('enabled'),
                'updated_by' => $request->user()->id,
            ],
        );

        $message = $request->boolean('enabled')
            ? 'Lomba berhasil diaktifkan untuk sekolah.'
            : 'Lomba berhasil dinonaktifkan untuk sekolah.';

        return back()->with('success', $message);
    }

    private function ensureProgramExists(string $program, CompetitionCatalog $catalog): void
    {
        abort_unless(array_key_exists($program, $catalog->all()), 404);
    }
}
