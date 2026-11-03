<?php

namespace App\Support;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Str;

class CompetitionCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        $programs = config('competitions.programs', []);

        foreach ($programs as &$program) {
            foreach ($program['babList'] as &$module) {
                $module['key'] = Str::slug($module['judul']);
            }
            unset($module);
        }
        unset($program);

        return $programs;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function active(): array
    {
        return $this->all();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function activeForSchool(School $school): array
    {
        $programs = $this->all();
        $statuses = $school->competitionSettings->pluck('enabled', 'program');

        return array_filter(
            $programs,
            fn (array $competition, string $program): bool => (bool) ($statuses[$program] ?? false),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function forUser(User $user): array
    {
        if (! $user->hasRole('superadmin') && ! $user->school) {
            return [];
        }

        $programs = $user->hasRole('superadmin')
            ? $this->all()
            : $this->activeForSchool($user->school);

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return $programs;
        }

        $assignments = $user->competitionAccesses
            ->groupBy('program')
            ->map(fn ($accesses) => $accesses->pluck('module_key')->all())
            ->all();

        if ($assignments === [] && $user->program) {
            $assignments[$user->program] = $this->moduleKeys($user->program);
        }

        $programs = array_intersect_key($programs, $assignments);

        foreach ($programs as $programKey => &$competition) {
            $moduleKeys = $assignments[$programKey];
            $competition['babList'] = array_values(array_filter(
                $competition['babList'],
                fn (array $module): bool => in_array($module['key'], $moduleKeys, true),
            ));
        }
        unset($competition);

        return $programs;
    }

    /**
     * @return list<string>
     */
    public function moduleKeys(string $program): array
    {
        return array_column($this->all()[$program]['babList'] ?? [], 'key');
    }
}
