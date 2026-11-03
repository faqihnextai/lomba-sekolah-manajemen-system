<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManajemenUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_open_a_competition_dashboard(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $this->actingAs($superadmin)
            ->get(route('super-admin.competition', ['program' => 'literasi']))
            ->assertOk()
            ->assertSee('Gerakan Literasi Sekolah (GLS)');
    }

    public function test_superadmin_can_view_user_management_and_school_fields(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'guru', 'guard_name' => 'web']);

        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $this->actingAs($superadmin)
            ->get(route('super-admin.manajemen-user'))
            ->assertOk()
            ->assertSee('Form Pendaftaran Pengguna')
            ->assertSee('Nama sekolah')
            ->assertSee('Kode sekolah')
            ->assertSee('Penugasan modul guru')
            ->assertSee('action="'.route('super-admin.manajemen-user.store').'"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_superadmin_can_create_a_guru_with_school_and_module_access(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $this->actingAs($superadmin)
            ->post(route('super-admin.manajemen-user.store'), [
                'name' => 'Guru Baru',
                'email' => 'guru.baru@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'guru',
                'school_name' => 'Daarul Huda',
                'school_code' => 'dahu',
                'modules' => [
                    'adiwiyata' => ['persyaratan-administrasi-pengusulan'],
                ],
            ])
            ->assertRedirect();

        $user = User::where('email', 'guru.baru@example.com')->firstOrFail();

        $this->assertSame('DAHU', $user->school->code);
        $this->assertSame('Daarul Huda', $user->school->name);
        $this->assertTrue($user->hasRole('guru'));
        $this->assertDatabaseHas('user_competition_access', [
            'user_id' => $user->id,
            'program' => 'adiwiyata',
            'module_key' => 'persyaratan-administrasi-pengusulan',
        ]);
    }

    public function test_non_superadmin_cannot_create_users(): void
    {
        Role::create(['name' => 'guru', 'guard_name' => 'web']);

        $this->actingAs(User::factory()->create())
            ->post(route('super-admin.manajemen-user.store'), [])
            ->assertForbidden();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_superadmin_can_update_a_users_school_and_module_access(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');
        $user = User::factory()->create();

        $this->actingAs($superadmin)
            ->patch(route('super-admin.manajemen-user.update-role', $user), [
                'role' => 'guru',
                'school_name' => 'Mathlaul Anwar',
                'school_code' => 'MAAN',
                'modules' => [
                    'kantin-sehat' => ['kebijakan-keorganisasian-kantin'],
                ],
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('MAAN', $user->school->code);
        $this->assertSame('kantin-sehat', $user->program);
        $this->assertTrue($user->hasRole('guru'));
        $this->assertDatabaseHas('user_competition_access', [
            'user_id' => $user->id,
            'program' => 'kantin-sehat',
            'module_key' => 'kebijakan-keorganisasian-kantin',
        ]);
    }

    public function test_school_code_cannot_be_reused_for_another_school_name(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');
        $payload = [
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'guru',
            'school_code' => 'DAHU',
            'modules' => ['adiwiyata' => ['persyaratan-administrasi-pengusulan']],
        ];

        $this->actingAs($superadmin)
            ->post(route('super-admin.manajemen-user.store'), [
                ...$payload,
                'name' => 'Guru Pertama',
                'email' => 'guru.pertama@example.com',
                'school_name' => 'Daarul Huda',
            ])
            ->assertRedirect();

        $this->from(route('super-admin.manajemen-user'))
            ->actingAs($superadmin)
            ->post(route('super-admin.manajemen-user.store'), [
                ...$payload,
                'name' => 'Guru Kedua',
                'email' => 'guru.kedua@example.com',
                'school_name' => 'Mathlaul Anwar',
            ])
            ->assertSessionHasErrors('school_code');

        $this->assertDatabaseMissing('users', ['email' => 'guru.kedua@example.com']);
    }

    public function test_superadmin_can_activate_a_competition_for_one_school(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);

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
    }

    public function test_superadmin_dashboard_lists_school_admins_and_school_scoped_competitions(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');
        $school = School::create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $admin = User::factory()->create([
            'name' => 'Admin Daarul Huda',
            'school_id' => $school->id,
        ]);
        $admin->assignRole('admin');
        $school->competitionSettings()->create([
            'program' => 'adiwiyata',
            'enabled' => true,
            'updated_by' => $superadmin->id,
        ]);

        $this->actingAs($superadmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Daarul Huda')
            ->assertSee('DAHU')
            ->assertSee('Admin Daarul Huda')
            ->assertSee('Progres dokumen belum tersedia')
            ->assertSee('Aktif');
    }

    public function test_superadmin_cannot_change_a_competition_for_an_unknown_school(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $this->actingAs($superadmin)
            ->patch('/super-admin/schools/999/competitions/adiwiyata/status', [
                'enabled' => true,
            ])
            ->assertNotFound();
    }
}
