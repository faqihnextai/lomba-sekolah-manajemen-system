<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTeacherAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_lists_only_gurus_from_their_school(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $otherSchool = School::create(['name' => 'Mathlaul Anwar', 'code' => 'MAAN']);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');
        $schoolTeacher = User::factory()->create([
            'name' => 'Guru Sekolah Sendiri',
            'school_id' => $school->id,
        ]);
        $schoolTeacher->assignRole('guru');
        $otherTeacher = User::factory()->create([
            'name' => 'Guru Sekolah Lain',
            'school_id' => $otherSchool->id,
        ]);
        $otherTeacher->assignRole('guru');

        $this->actingAs($admin)
            ->get(route('admin.akses-guru'))
            ->assertOk()
            ->assertSee('Guru Sekolah Sendiri')
            ->assertSee('DAHU')
            ->assertSee('Tambahkan Guru')
            ->assertSee('Role akun: Guru')
            ->assertDontSee('Guru Sekolah Lain');
    }

    public function test_admin_can_create_only_a_guru_for_their_own_school(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $otherSchool = School::create(['name' => 'Mathlaul Anwar', 'code' => 'MAAN']);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.akses-guru.store'), [
                'name' => 'Guru Baru',
                'email' => 'guru.baru@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'superadmin',
                'school_id' => $otherSchool->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Akun guru berhasil ditambahkan ke sekolah Anda.');

        $teacher = User::where('email', 'guru.baru@example.com')->firstOrFail();

        $this->assertSame($school->id, $teacher->school_id);
        $this->assertTrue($teacher->hasRole('guru'));
        $this->assertFalse($teacher->hasRole('admin'));
        $this->assertFalse($teacher->hasRole('superadmin'));
        $this->assertTrue(Hash::check('password123', $teacher->password));
    }

    public function test_only_an_admin_can_create_a_teacher_from_access_management(): void
    {
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $teacher = User::factory()->create();
        $teacher->assignRole('guru');

        $this->actingAs($teacher)
            ->post(route('admin.akses-guru.store'), [
                'name' => 'Tidak Diizinkan',
                'email' => 'ditolak@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'ditolak@example.com']);
    }

    public function test_teacher_creation_rejects_duplicate_email_and_mismatched_passwords(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');
        $existingUser = User::factory()->create();

        $this->from(route('admin.akses-guru'))
            ->actingAs($admin)
            ->post(route('admin.akses-guru.store'), [
                'name' => 'Guru Baru',
                'email' => $existingUser->email,
                'password' => 'password123',
                'password_confirmation' => 'different123',
            ])
            ->assertRedirect(route('admin.akses-guru'))
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertDatabaseMissing('users', ['name' => 'Guru Baru']);
    }

    public function test_admin_cannot_change_a_teacher_access_in_another_school(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $otherSchool = School::create(['name' => 'Mathlaul Anwar', 'code' => 'MAAN']);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');
        $otherTeacher = User::factory()->create(['school_id' => $otherSchool->id]);
        $otherTeacher->assignRole('guru');

        $this->actingAs($admin)
            ->patch(route('admin.akses-guru.update', $otherTeacher), [
                'modules' => [
                    'literasi' => ['kebijakan-keorganisasian-literasi'],
                ],
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('user_competition_access', [
            'user_id' => $otherTeacher->id,
            'program' => 'literasi',
        ]);
    }

    public function test_admin_can_assign_a_module_to_a_guru_in_their_school(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $school->competitionSettings()->create([
            'program' => 'literasi',
            'enabled' => true,
        ]);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');
        $teacher = User::factory()->create(['school_id' => $school->id]);
        $teacher->assignRole('guru');

        $this->actingAs($admin)
            ->patch(route('admin.akses-guru.update', $teacher), [
                'modules' => [
                    'literasi' => ['kebijakan-keorganisasian-literasi'],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('user_competition_access', [
            'user_id' => $teacher->id,
            'program' => 'literasi',
            'module_key' => 'kebijakan-keorganisasian-literasi',
        ]);
    }
}
