<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class ManajemenUserController extends Controller
{
    /**
     * Hanya role yang memang disediakan sistem yang boleh dipilih
     * melalui halaman ini. Sesuaikan daftar ini dengan kebutuhan.
     */
    private const ALLOWED_ROLES = ['superadmin', 'admin', 'guru'];

    public function index(): View
    {
        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->whereIn('name', self::ALLOWED_ROLES)
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();
        $programs = config('competitions.programs', []);

        return view('admin.manajemen-user', compact('users', 'roles', 'programs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(self::ALLOWED_ROLES)],
            'program' => [
                'required_unless:role,superadmin',
                'nullable',
                'string',
                Rule::in(array_keys(config('competitions.programs', []))),
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'program' => $validated['program'] ?? null,
        ]);

        $user->syncRoles([$validated['role']]);

        return back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Update a user's role and assigned competition.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(self::ALLOWED_ROLES)],
            'program' => [
                'required_unless:role,superadmin',
                'nullable',
                'string',
                Rule::in(array_keys(config('competitions.programs', []))),
            ],
        ]);

        // Mencegah admin menghapus akses superadmin miliknya sendiri.
        if ($request->user()->is($user)
            && $user->hasRole('superadmin')
            && $validated['role'] !== 'superadmin') {
            return back()->withErrors([
                'role' => 'Anda tidak dapat menurunkan role superadmin milik akun sendiri.',
            ]);
        }

        $user->syncRoles([$validated['role']]);
        $user->update(['program' => $validated['program'] ?? null]);

        return back()->with('success', 'Role pengguna berhasil diperbarui.');
    }
}
