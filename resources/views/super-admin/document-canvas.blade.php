<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <a href="{{ route(auth()->user()->hasRole('superadmin') ? 'super-admin.setting-dokumen' : 'admin.setting-dokumen', ['school_id' => $preset->school_id, 'program' => $preset->program, 'preset' => $preset->id]) }}" class="w-fit text-sm font-semibold text-blue-700 hover:text-blue-900 dark:text-blue-300">
                &larr; Kembali ke Setting Dokumen
            </a>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Canvas Dokumen</h2>
        </div>
    </x-slot>

    @php
        $documentComponents = [
            'cover' => 'Cover',
            'header' => 'Header / Kop',
            'body' => 'Isi dokumen',
            'footer' => 'Footer',
            'signature' => 'Tanda tangan',
            'other' => 'Lainnya',
        ];
    @endphp

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            @include('super-admin.partials.document-editor', [
                'selectedPreset' => $preset,
                'assets' => $assets,
                'documentComponents' => $documentComponents,
                'showComponentSettings' => true,
            ])
        </div>
    </div>
</x-app-layout>
