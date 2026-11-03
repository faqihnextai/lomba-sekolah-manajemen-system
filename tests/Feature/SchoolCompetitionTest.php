<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SchoolCompetitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_school_competitions_and_allows_activation(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Daarul Huda')
            ->assertSee('Aktifkan lomba')
            ->assertSee('Aktivitas Guru')
            ->assertSee('Belum ada data progres yang tercatat.');

        $this->patch(route('admin.competition.status', ['program' => 'adiwiyata']), [
            'enabled' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('school_competition_settings', [
            'school_id' => $school->id,
            'program' => 'adiwiyata',
            'enabled' => true,
            'updated_by' => $admin->id,
        ]);
    }

    public function test_admin_can_only_activate_competitions_for_their_own_school(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $otherSchool = School::create(['name' => 'Mathlaul Anwar', 'code' => 'MAAN']);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->patch(route('admin.competition.status', ['program' => 'adiwiyata']), [
                'enabled' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('school_competition_settings', [
            'school_id' => $school->id,
            'program' => 'adiwiyata',
            'enabled' => true,
        ]);
        $this->assertDatabaseMissing('school_competition_settings', [
            'school_id' => $otherSchool->id,
            'program' => 'adiwiyata',
        ]);
    }

    public function test_teacher_cannot_activate_a_school_competition(): void
    {
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $teacher = User::factory()->create(['school_id' => $school->id]);
        $teacher->assignRole('guru');

        $this->actingAs($teacher)
            ->patch(route('admin.competition.status', ['program' => 'adiwiyata']), [
                'enabled' => true,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('school_competition_settings', [
            'school_id' => $school->id,
            'program' => 'adiwiyata',
        ]);
    }

    public function test_admin_cannot_activate_an_unknown_competition(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->patch('/admin/competitions/unknown/status', ['enabled' => true])
            ->assertNotFound();
    }

    public function test_superadmin_can_toggle_competition_for_selected_school_without_affecting_other_schools(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $otherSchool = School::create(['name' => 'Mathlaul Anwar', 'code' => 'MAAN']);
        $otherSchool->competitionSettings()->create([
            'program' => 'adiwiyata',
            'enabled' => true,
        ]);

        $this->actingAs($superadmin)
            ->patch(route('super-admin.school.competition.status', [
                'school' => $school,
                'program' => 'adiwiyata',
            ]), [
                'enabled' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('school_competition_settings', [
            'school_id' => $school->id,
            'program' => 'adiwiyata',
            'enabled' => true,
            'updated_by' => $superadmin->id,
        ]);
        $this->assertDatabaseHas('school_competition_settings', [
            'school_id' => $otherSchool->id,
            'program' => 'adiwiyata',
            'enabled' => true,
        ]);
    }
}
