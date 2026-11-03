<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\CompetitionCatalog;
use App\Support\SchoolRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ManajemenUserController extends Controller
{
    private const ALLOWED_ROLES = ['superadmin', 'admin', 'guru'];

    public function index(CompetitionCatalog $catalog): View
    {
        $users = User::query()
            ->with(['roles', 'school', 'competitionAccesses'])
            ->orderBy('name')
            ->get();
        $roles = self::ALLOWED_ROLES;
        $programs = $catalog->all();

        return view('super-admin.manajemen-user', compact('users', 'roles', 'programs'));
    }

    public function store(Request $request, CompetitionCatalog $catalog, SchoolRegistry $schools): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(self::ALLOWED_ROLES)],
            'school_name' => ['required_unless:role,superadmin', 'nullable', 'string', 'max:255'],
            'school_code' => ['required_unless:role,superadmin', 'nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['array'],
            'modules.*.*' => ['string'],
        ]);
        $modules = $this->validatedModules($validated['modules'] ?? [], $validated['role'], $catalog);

        DB::transaction(function () use ($validated, $modules, $schools): void {
            $school = $validated['role'] === 'superadmin'
                ? null
                : $schools->findOrCreate($validated['school_name'], $validated['school_code']);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'program' => array_key_first($modules),
                'school_id' => $school?->id,
            ]);

            $user->syncRoles([$validated['role']]);
            $this->syncModules($user, $modules);
        });

        return back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function updateRole(
        Request $request,
        User $user,
        CompetitionCatalog $catalog,
        SchoolRegistry $schools,
    ): RedirectResponse {
        $validated = $request->validate([
            'role' => ['required', Rule::in(self::ALLOWED_ROLES)],
            'school_name' => ['required_unless:role,superadmin', 'nullable', 'string', 'max:255'],
            'school_code' => ['required_unless:role,superadmin', 'nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['array'],
            'modules.*.*' => ['string'],
        ]);
        $modules = $this->validatedModules($validated['modules'] ?? [], $validated['role'], $catalog);

        if ($request->user()->is($user)
            && $user->hasRole('superadmin')
            && $validated['role'] !== 'superadmin') {
            return back()->withErrors([
                'role' => 'Anda tidak dapat menurunkan role superadmin milik akun sendiri.',
            ]);
        }

        DB::transaction(function () use ($validated, $modules, $schools, $user): void {
            if ($validated['role'] === 'superadmin') {
                $schoolId = null;
            } elseif ($user->school) {
                $schoolCode = strtoupper(trim($validated['school_code']));
                $schoolId = $user->school->code === $schoolCode
                    ? $schools->update($user->school, $validated['school_name'], $schoolCode)->id
                    : $schools->findOrCreate($validated['school_name'], $schoolCode)->id;
            } else {
                $schoolId = $schools->findOrCreate($validated['school_name'], $validated['school_code'])->id;
            }

            $user->syncRoles([$validated['role']]);
            $user->update([
                'program' => array_key_first($modules),
                'school_id' => $schoolId,
            ]);
            $this->syncModules($user, $modules);
        });

        return back()->with('success', 'Akses pengguna berhasil diperbarui.');
    }

    /**
     * @param  array<string, list<string>>  $submittedModules
     * @return array<string, list<string>>
     */
    private function validatedModules(array $submittedModules, string $role, CompetitionCatalog $catalog): array
    {
        if ($role !== 'guru') {
            return [];
        }

        $modules = [];

        foreach ($submittedModules as $program => $moduleKeys) {
            if (! is_string($program) || ! array_key_exists($program, $catalog->all())) {
                throw ValidationException::withMessages([
                    'modules' => 'Kategori lomba yang dipilih tidak tersedia.',
                ]);
            }

            $allowedModuleKeys = $catalog->moduleKeys($program);

            foreach (array_unique($moduleKeys) as $moduleKey) {
                if (! in_array($moduleKey, $allowedModuleKeys, true)) {
                    throw ValidationException::withMessages([
                        'modules' => 'Modul yang dipilih tidak sesuai dengan kategori lombanya.',
                    ]);
                }

                $modules[$program][] = $moduleKey;
            }
        }

        if ($modules === []) {
            throw ValidationException::withMessages([
                'modules' => 'Pilih setidaknya satu modul untuk akun guru.',
            ]);
        }

        return $modules;
    }

    /**
     * @param  array<string, list<string>>  $modules
     */
    private function syncModules(User $user, array $modules): void
    {
        $user->competitionAccesses()->delete();

        foreach ($modules as $program => $moduleKeys) {
            foreach ($moduleKeys as $moduleKey) {
                $user->competitionAccesses()->create([
                    'program' => $program,
                    'module_key' => $moduleKey,
                ]);
            }
        }
    }
}
