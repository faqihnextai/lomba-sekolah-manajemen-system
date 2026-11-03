<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use App\Support\CompetitionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminTeacherAccessController extends Controller
{
    public function index(Request $request, CompetitionCatalog $catalog): View
    {
        $school = $request->user()->school;
        abort_unless($school, 403, 'Akun admin belum memiliki sekolah.');

        $teachers = User::query()
            ->where('school_id', $school->id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'guru'))
            ->with('competitionAccesses')
            ->orderBy('name')
            ->get();
        $programs = $catalog->activeForSchool($school);

        return view('admin.akses-guru', compact('school', 'teachers', 'programs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $admin = $request->user();
        abort_unless($admin->hasRole('admin') && $admin->school, 403, 'Akun admin belum memiliki sekolah.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($admin, $validated): void {
            $teacher = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'school_id' => $admin->school_id,
            ]);

            $teacher->syncRoles(['guru']);
        });

        return back()->with('success', 'Akun guru berhasil ditambahkan ke sekolah Anda.');
    }

    public function update(
        Request $request,
        User $teacher,
        CompetitionCatalog $catalog,
    ): RedirectResponse {
        abort_unless(
            $request->user()->school_id
                && $teacher->school_id === $request->user()->school_id
                && $teacher->hasRole('guru'),
            404,
        );

        $validated = $request->validate([
            'modules' => ['nullable', 'array'],
            'modules.*' => ['array'],
            'modules.*.*' => ['string'],
        ]);
        $modules = $this->validatedModules($validated['modules'] ?? [], $catalog, $request->user()->school);

        DB::transaction(function () use ($teacher, $modules): void {
            $teacher->competitionAccesses()->delete();

            foreach ($modules as $program => $moduleKeys) {
                foreach ($moduleKeys as $moduleKey) {
                    $teacher->competitionAccesses()->create([
                        'program' => $program,
                        'module_key' => $moduleKey,
                    ]);
                }
            }

            $teacher->update(['program' => array_key_first($modules)]);
        });

        return back()->with('success', "Akses {$teacher->name} berhasil diperbarui.");
    }

    /**
     * @param  array<string, list<string>>  $submittedModules
     * @return array<string, list<string>>
     */
    private function validatedModules(
        array $submittedModules,
        CompetitionCatalog $catalog,
        School $school,
    ): array {
        $modules = [];
        $activePrograms = $catalog->activeForSchool($school);

        foreach ($submittedModules as $program => $moduleKeys) {
            if (! is_string($program) || ! array_key_exists($program, $activePrograms)) {
                throw ValidationException::withMessages([
                    'modules' => 'Kategori lomba belum aktif di sekolah ini.',
                ]);
            }

            foreach (array_unique($moduleKeys) as $moduleKey) {
                if (! in_array($moduleKey, $catalog->moduleKeys($program), true)) {
                    throw ValidationException::withMessages([
                        'modules' => 'Modul yang dipilih tidak sesuai dengan kategori lombanya.',
                    ]);
                }

                $modules[$program][] = $moduleKey;
            }
        }

        return $modules;
    }
}
