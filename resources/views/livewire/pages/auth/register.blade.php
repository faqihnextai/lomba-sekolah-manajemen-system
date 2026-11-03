<?php

use App\Models\User;
use App\Support\CompetitionCatalog;
use App\Support\SchoolRegistry;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $school_name = '';

    public string $school_code = '';

    public string $password = '';

    public string $password_confirmation = '';

    #[Url]
    public string $program = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(SchoolRegistry $schools, CompetitionCatalog $catalog): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'school_name' => ['required', 'string', 'max:255'],
            'school_code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'program' => ['required', 'string', Rule::in(array_keys(config('competitions.programs', [])))],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = DB::transaction(function () use ($validated, $schools, $catalog): User {
            $school = $schools->findOrCreate($validated['school_name'], $validated['school_code']);
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'program' => $validated['program'],
                'school_id' => $school->id,
            ]);

            $user->assignRole(Role::findOrCreate('guru', 'web'));

            foreach ($catalog->moduleKeys($validated['program']) as $moduleKey) {
                $user->competitionAccesses()->create([
                    'program' => $validated['program'],
                    'module_key' => $moduleKey,
                ]);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="school_name" :value="__('Nama Sekolah')" />
            <x-text-input wire:model="school_name" id="school_name" class="block mt-1 w-full" type="text" name="school_name" required maxlength="255" autocomplete="organization" />
            <x-input-error :messages="$errors->get('school_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="school_code" :value="__('Kode Sekolah')" />
            <x-text-input wire:model="school_code" id="school_code" class="block mt-1 w-full" type="text" name="school_code" required maxlength="20" placeholder="Contoh: DAHU" />
            <x-input-error :messages="$errors->get('school_code')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="program" :value="__('Kategori Lomba')" />
            <select wire:model="program" id="program" name="program" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Pilih kategori lomba</option>
                @foreach (config('competitions.programs', []) as $programKey => $competition)
                    <option value="{{ $programKey }}">{{ $competition['label'] }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('program')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
