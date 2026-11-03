<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $competition['label'] }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Panel superadmin · seluruh modul lomba</p>
            </div>
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900 dark:text-blue-300">Kembali ke Dashboard</a>
        </div>
    </x-slot>

    <x-modul-lomba
        :judul="$competition['judul']"
        :deskripsi="$competition['deskripsi']"
        :warna="$competition['warna']"
        :babList="$competition['babList']"
    />
</x-app-layout>
