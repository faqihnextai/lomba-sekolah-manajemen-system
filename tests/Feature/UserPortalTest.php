<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
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

    public function test_user_dashboard_only_shows_the_assigned_competition(): void
    {
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $user = User::factory()->create(['program' => 'adiwiyata']);
        $user->assignRole('guru');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Program Adiwiyata')
            ->assertDontSee('Kantin Sehat');
    }

    public function test_superadmin_navigation_shows_all_admin_sections(): void
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $this->actingAs($superadmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('href="'.route('admin.setting-dokumen').'"', false)
            ->assertSee('href="'.route('admin.manajemen-user').'"', false)
            ->assertSee('href="'.route('admin.lomba-adiwiyata').'"', false)
            ->assertSee('href="'.route('admin.lomba-literasi').'"', false);
    }

    public function test_regular_user_navigation_only_shows_assigned_work(): void
    {
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $user = User::factory()->create(['program' => 'adiwiyata']);
        $user->assignRole('guru');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('href="'.route('user.pengerjaan-dokumen', ['program' => 'adiwiyata']).'"', false)
            ->assertDontSee('href="'.route('admin.manajemen-user').'"', false)
            ->assertDontSee('href="'.route('admin.setting-dokumen').'"', false);
    }

    public function test_user_can_open_only_the_assigned_competition(): void
    {
        Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $user = User::factory()->create(['program' => 'adiwiyata']);
        $user->assignRole('guru');

        $this->actingAs($user)
            ->get(route('user.pengerjaan-dokumen', ['program' => 'adiwiyata']))
            ->assertOk()
            ->assertSee('Program Sekolah Adiwiyata (GPBLHS)');

        $this->get(route('user.pengerjaan-dokumen', ['program' => 'literasi']))
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

    public function test_registration_stores_the_selected_competition_and_assigns_guru_role(): void
    {
        Volt::test('pages.auth.register')
            ->set('name', 'Guru Literasi')
            ->set('email', 'guru.literasi@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('program', 'literasi')
            ->call('register')
            ->assertHasNoErrors();

        $user = User::where('email', 'guru.literasi@example.com')->firstOrFail();

        $this->assertSame('literasi', $user->program);
        $this->assertTrue($user->hasRole('guru'));
    }
}
