<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_competition_selection_cards_link_to_login(): void
    {
        $this->get(route('select-lomba'))
            ->assertOk()
            ->assertSee('href="'.route('login').'"', false)
            ->assertDontSee(route('register'), false);
    }

    public function test_user_dashboard_only_shows_assigned_active_competitions(): void
    {
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $school = $this->createSchoolWithEnabledPrograms(['adiwiyata']);
        $user = User::factory()->create(['program' => 'adiwiyata', 'school_id' => $school->id]);
        $user->assignRole('guru');
        $user->competitionAccesses()->create([
            'program' => 'adiwiyata',
            'module_key' => 'persyaratan-administrasi-pengusulan',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Program Adiwiyata')
            ->assertDontSee('Kantin Sehat');
    }

    public function test_superadmin_navigation_shows_superadmin_sections_and_active_competitions(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $this->actingAs($superadmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('href="'.route('super-admin.setting-dokumen').'"', false)
            ->assertSee('href="'.route('super-admin.manajemen-user').'"', false)
            ->assertSee('href="'.route('super-admin.competition', ['program' => 'adiwiyata']).'"', false)
            ->assertSee('href="'.route('super-admin.competition', ['program' => 'literasi']).'"', false);
    }

    public function test_guru_navigation_only_shows_assigned_work(): void
    {
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $school = $this->createSchoolWithEnabledPrograms(['adiwiyata']);
        $user = User::factory()->create(['program' => 'adiwiyata', 'school_id' => $school->id]);
        $user->assignRole('guru');
        $user->competitionAccesses()->create([
            'program' => 'adiwiyata',
            'module_key' => 'persyaratan-administrasi-pengusulan',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('href="'.route('user.pengerjaan-dokumen', ['program' => 'adiwiyata']).'"', false)
            ->assertDontSee('href="'.route('admin.akses-guru').'"', false)
            ->assertDontSee('href="'.route('admin.setting-dokumen').'"', false);
    }

    public function test_guru_can_open_an_assigned_program_and_sees_only_assigned_modules(): void
    {
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $school = $this->createSchoolWithEnabledPrograms(['adiwiyata']);
        $user = User::factory()->create(['program' => 'adiwiyata', 'school_id' => $school->id]);
        $user->assignRole('guru');
        $user->competitionAccesses()->create([
            'program' => 'adiwiyata',
            'module_key' => 'persyaratan-administrasi-pengusulan',
        ]);

        $this->actingAs($user)
            ->get(route('user.pengerjaan-dokumen', ['program' => 'adiwiyata']))
            ->assertOk()
            ->assertSee('Persyaratan')
            ->assertDontSee('Perencanaan Gerakan PBLHS');
    }

    public function test_guru_cannot_open_an_unassigned_program(): void
    {
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $school = $this->createSchoolWithEnabledPrograms(['adiwiyata', 'literasi']);
        $user = User::factory()->create(['program' => 'adiwiyata', 'school_id' => $school->id]);
        $user->assignRole('guru');
        $user->competitionAccesses()->create([
            'program' => 'adiwiyata',
            'module_key' => 'persyaratan-administrasi-pengusulan',
        ]);

        $this->actingAs($user)
            ->get(route('user.pengerjaan-dokumen', ['program' => 'literasi']))
            ->assertForbidden();
    }

    public function test_guru_cannot_open_a_disabled_competition(): void
    {
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $user = User::factory()->create(['program' => 'adiwiyata', 'school_id' => $school->id]);
        $user->assignRole('guru');
        $user->competitionAccesses()->create([
            'program' => 'adiwiyata',
            'module_key' => 'persyaratan-administrasi-pengusulan',
        ]);
        $school->competitionSettings()->create([
            'program' => 'adiwiyata',
            'enabled' => false,
        ]);

        $this->actingAs($user)
            ->get(route('user.pengerjaan-dokumen', ['program' => 'adiwiyata']))
            ->assertForbidden();
    }

    public function test_superadmin_can_open_any_competition(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $this->actingAs($superadmin)
            ->get(route('user.pengerjaan-dokumen', ['program' => 'literasi']))
            ->assertOk()
            ->assertSee('Gerakan Literasi Sekolah (GLS)');
    }

    /**
     * @param  list<string>  $programs
     */
    private function createSchoolWithEnabledPrograms(array $programs): School
    {
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);

        foreach ($programs as $program) {
            $school->competitionSettings()->create([
                'program' => $program,
                'enabled' => true,
            ]);
        }

        return $school;
    }
}
