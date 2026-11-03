<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Support\CompetitionCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserPortalController extends Controller
{
    public function dashboard(Request $request, CompetitionCatalog $catalog): View
    {
        $user = $request->user();
        $allPrograms = $catalog->all();

        if ($user->hasRole('superadmin')) {
            return view('super-admin.dashboard', [
                'programs' => $allPrograms,
                'schools' => School::query()
                    ->with([
                        'competitionSettings',
                        'users' => fn ($query) => $query
                            ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'admin'))
                            ->with('roles'),
                    ])
                    ->orderBy('name')
                    ->get(),
            ]);
        }

        if ($user->hasRole('admin')) {
            abort_unless($user->school, 403, 'Akun admin belum memiliki sekolah.');

            return view('admin.dashboard', [
                'programs' => $catalog->all(),
                'activePrograms' => array_keys($catalog->activeForSchool($user->school)),
            ]);
        }

        $programs = $catalog->forUser($user);

        return view('user.dashboard', compact('programs'));
    }

    public function pengerjaanDokumen(
        Request $request,
        string $program,
        CompetitionCatalog $catalog,
    ): View {
        $user = $request->user();
        $competition = $catalog->all()[$program] ?? abort(404);

        if (! $user->hasRole('superadmin')
            && (! $user->school || ! array_key_exists($program, $catalog->activeForSchool($user->school)))) {
            abort(403, 'Lomba ini sedang tidak diaktifkan.');
        }

        if ($user->hasRole('admin')) {
            $competition['babList'] = array_values(array_filter(
                $competition['babList'],
                fn (array $module): bool => in_array(
                    $module['key'],
                    $catalog->moduleKeys($program),
                    true,
                ),
            ));
        }

        if ($user->hasRole('guru')) {
            $moduleKeys = $user->competitionAccesses()
                ->where('program', $program)
                ->pluck('module_key')
                ->all();

            if ($moduleKeys === [] && $user->competitionAccesses()->doesntExist() && $user->program === $program) {
                $moduleKeys = $catalog->moduleKeys($program);
            }

            $competition['babList'] = array_values(array_filter(
                $competition['babList'],
                fn (array $module): bool => in_array($module['key'], $moduleKeys, true),
            ));
        }

        return view('user.pengerjaan-dokumen', [
            'program' => $program,
            'competition' => $competition,
        ]);
    }
}
