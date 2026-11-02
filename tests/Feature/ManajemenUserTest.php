<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManajemenUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_literasi_page_is_available_from_its_named_route(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $this->actingAs($superadmin)
            ->get(route('admin.lomba-literasi'))
            ->assertOk()
            ->assertSee('Pengerjaan Dokumen: Lomba Literasi (GLS)');
    }

    public function test_superadmin_can_view_the_user_management_page(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'guru', 'guard_name' => 'web']);

        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');
        $listedUser = User::factory()->create([
            'name' => 'Guru Terdaftar',
            'email' => 'guru.terdaftar@example.com',
        ]);
        $listedUser->assignRole('guru');

        $this->actingAs($superadmin)
            ->get(route('admin.manajemen-user'))
            ->assertOk()
            ->assertSee('Form Pendaftaran Pengguna')
            ->assertSee('Guru Terdaftar')
            ->assertSee('guru.terdaftar@example.com')
            ->assertSee('<table', false)
            ->assertDontSee('action="'.route('admin.manajemen-user.store').'"', false);
    }

    public function test_user_can_be_created_with_a_selected_role(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'guru', 'guard_name' => 'web']);

        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $this->actingAs($superadmin)
            ->post(route('admin.manajemen-user.store'), [
                'name' => 'Guru Baru',
                'email' => 'guru.baru@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'guru',
                'program' => 'adiwiyata',
            ])
            ->assertRedirect();

        $user = User::where('email', 'guru.baru@example.com')->firstOrFail();

        $this->assertSame('Guru Baru', $user->name);
        $this->assertSame('adiwiyata', $user->program);
        $this->assertTrue($user->hasRole('guru'));
    }

    public function test_non_superadmin_cannot_create_users(): void
    {
        Role::create(['name' => 'guru', 'guard_name' => 'web']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.manajemen-user.store'), [
                'name' => 'Akun Tidak Sah',
                'email' => 'tidak.sah@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'guru',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'tidak.sah@example.com']);
    }

    public function test_superadmin_can_assign_a_program_to_an_existing_user(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'guru', 'guard_name' => 'web']);

        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');
        $user = User::factory()->create();

        $this->actingAs($superadmin)
            ->patch(route('admin.manajemen-user.update-role', $user), [
                'role' => 'guru',
                'program' => 'kantin-sehat',
            ])
            ->assertRedirect();

        $this->assertSame('kantin-sehat', $user->refresh()->program);
        $this->assertTrue($user->hasRole('guru'));
    }
}
