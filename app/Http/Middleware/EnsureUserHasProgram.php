<?php

namespace App\Http\Middleware;

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

        abort_unless(
            $user && ($user->hasRole('superadmin') || $user->program === $requestedProgram),
            403,
            'Anda tidak memiliki akses ke kategori lomba ini.',
        );

        return $next($request);
    }
}
