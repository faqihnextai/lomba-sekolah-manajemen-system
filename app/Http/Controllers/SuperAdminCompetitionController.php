<?php

namespace App\Http\Controllers;

use App\Support\CompetitionCatalog;
use Illuminate\View\View;

class SuperAdminCompetitionController extends Controller
{
    public function show(string $program, CompetitionCatalog $catalog): View
    {
        abort_unless(array_key_exists($program, $catalog->all()), 404);

        $competition = $catalog->all()[$program];

        return view('super-admin.competition', compact('program', 'competition'));
    }
}
