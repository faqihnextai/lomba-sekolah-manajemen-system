<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class UserPortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $programs = config('competitions.programs', []);

        if (! $user->hasRole('superadmin')) {
            $programs = $user->program && isset($programs[$user->program])
                ? [$user->program => $programs[$user->program]]
                : [];
        }

        return view('user.dashboard', compact('programs'));
    }

    public function pengerjaanDokumen(string $program): View
    {
        return view('user.pengerjaan-dokumen', [
            'program' => $program,
            'competition' => config("competitions.programs.{$program}"),
        ]);
    }
}
