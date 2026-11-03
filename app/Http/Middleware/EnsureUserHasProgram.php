<?php

namespace App\Http\Middleware;

use App\Models\SchoolCompetitionSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasProgram
{
    /**
     * Ensure users only access the competition assigned to their account.
     */
    public function handle(Request $request, Closure $next, ?string $program = null): Response
    {
        $user = $request->user();
        $requestedProgram = $program ?? $request->route('program');

        abort_unless(
            is_string($requestedProgram) && array_key_exists($requestedProgram, config('competitions.programs', [])),
            404,
        );

        if ($user?->hasRole('superadmin')) {
            return $next($request);
        }

        $isActive = $user?->school_id
            ? SchoolCompetitionSetting::query()
                ->where('school_id', $user->school_id)
                ->where('program', $requestedProgram)
                ->value('enabled')
            : null;

        abort_unless(
            (bool) $isActive,
            403,
            'Lomba ini sedang tidak diaktifkan.',
        );

        $hasAccess = $user?->hasRole('admin')
            || $user?->competitionAccesses()->where('program', $requestedProgram)->exists()
            || (
                $user?->competitionAccesses()->doesntExist()
                && $user?->program === $requestedProgram
            );

        abort_unless($hasAccess, 403, 'Anda tidak memiliki akses ke kategori lomba ini.');

        return $next($request);
    }
}
