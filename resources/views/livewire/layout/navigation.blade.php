<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();$this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2.5">
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-md shadow-blue-500/20">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <span class="font-bold text-xl text-gray-900 dark:text-white tracking-tight hidden sm:block">LSMS</span>
                    </a>
                </div>

                <!-- Navigation Links Desktop -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-6 lg:ms-8 sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if (auth()->user()->hasRole('superadmin'))
                        <x-nav-link :href="route('admin.setting-dokumen')" :active="request()->routeIs('admin.setting-dokumen')" wire:navigate>
                            {{ __('Setting Dokumen') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.manajemen-user')" :active="request()->routeIs('admin.manajemen-user')" wire:navigate>
                            {{ __('Manajemen User') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.lomba-adiwiyata')" :active="request()->routeIs('admin.lomba-adiwiyata')" wire:navigate>
                            {{ __('Adiwiyata') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.lomba-kantin-sehat')" :active="request()->routeIs('admin.lomba-kantin-sehat')" wire:navigate>
                            {{ __('Kantin Sehat') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.lomba-sekolah-sehat')" :active="request()->routeIs('admin.lomba-sekolah-sehat')" wire:navigate>
                            {{ __('Sekolah Sehat') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.lomba-literasi')" :active="request()->routeIs('admin.lomba-literasi')" wire:navigate>
                            {{ __('Literasi') }}
                        </x-nav-link>
                    @elseif (auth()->user()->program)
                        <x-nav-link :href="route('user.pengerjaan-dokumen', ['program' => auth()->user()->program])" :active="request()->routeIs('user.pengerjaan-dokumen')" wire:navigate>
                            {{ __('Pengerjaan Dokumen') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown Desktop -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="relative" x-data="{ dropdownOpen: false }">
                    <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false" class="flex items-center gap-2.5 px-3 py-1.5 text-sm font-medium rounded-full text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 ring-1 ring-gray-200 dark:ring-gray-700 transition">
                        <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs uppercase">
                            <span x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name.charAt(0)" x-on:profile-updated.window="name = $event.detail.name"></span>
                        </div>
                        <span class="font-semibold max-w-[120px] truncate" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span>
                        <svg :class="{'rotate-180': dropdownOpen}" class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="dropdownOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                         class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-1 z-50"
                         style="display: none;">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Masuk sebagai</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            {{ __('Profile Saya') }}
                        </a>
                        <button wire:click="logout" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 text-left border-t border-gray-100 dark:border-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            {{ __('Log Out') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hamburger Mobile -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if (auth()->user()->hasRole('superadmin'))
                <x-responsive-nav-link :href="route('admin.setting-dokumen')" :active="request()->routeIs('admin.setting-dokumen')" wire:navigate>
                    {{ __('Setting Dokumen') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.manajemen-user')" :active="request()->routeIs('admin.manajemen-user')" wire:navigate>
                    {{ __('Manajemen User') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.lomba-adiwiyata')" :active="request()->routeIs('admin.lomba-adiwiyata')" wire:navigate>
                    {{ __('Adiwiyata') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.lomba-kantin-sehat')" :active="request()->routeIs('admin.lomba-kantin-sehat')" wire:navigate>
                    {{ __('Kantin Sehat') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.lomba-sekolah-sehat')" :active="request()->routeIs('admin.lomba-sekolah-sehat')" wire:navigate>
                    {{ __('Sekolah Sehat') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.lomba-literasi')" :active="request()->routeIs('admin.lomba-literasi')" wire:navigate>
                    {{ __('Literasi') }}
                </x-responsive-nav-link>
            @elseif (auth()->user()->program)
                <x-responsive-nav-link :href="route('user.pengerjaan-dokumen', ['program' => auth()->user()->program])" :active="request()->routeIs('user.pengerjaan-dokumen')" wire:navigate>
                    {{ __('Pengerjaan Dokumen') }}
                </x-responsive-nav-link>
            @endif
            
        </div>

        <div class="pt-4 pb-3 border-t border-gray-200 dark:border-gray-800">
            <div class="px-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold uppercase">
                    <span x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name.charAt(0)" x-on:profile-updated.window="name = $event.detail.name"></span>
                </div>
                <div>
                    <div class="font-semibold text-base text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                    <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile Saya') }}
                </x-responsive-nav-link>
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link class="text-red-600 dark:text-red-400">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>