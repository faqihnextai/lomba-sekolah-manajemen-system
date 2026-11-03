@php
    $templateDefinition = $documentTemplates[$selectedPreset->template_key];
    $templateData = $selectedPreset->template_data ?? [];
@endphp

<form
    method="POST"
    action="{{ route('document-presets.templates.update', $selectedPreset) }}"
    enctype="multipart/form-data"
    class="mt-4 space-y-4 rounded-xl border border-violet-200 bg-violet-50/70 p-4 dark:border-violet-900 dark:bg-violet-950/20"
>
    @csrf
    @method('PATCH')

    <div>
        <h4 class="text-sm font-bold text-violet-950 dark:text-violet-100">Form template sekolah</h4>
        <p class="mt-1 text-xs leading-5 text-violet-800 dark:text-violet-200">
            {{ $templateDefinition['label'] }}. Perubahan disimpan sebagai file HTML sekolah; template ini tidak dibuka di canvas.
        </p>
        @if ($templateFile)
            <a href="{{ route('document-presets.templates.show', $selectedPreset) }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-xs font-semibold text-violet-700 underline dark:text-violet-300">
                Buka file template HTML
            </a>
        @endif
    </div>

    <div>
        <label for="school-template-edit-name-{{ $selectedPreset->id }}" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Nama preset sekolah</label>
        <input
            id="school-template-edit-name-{{ $selectedPreset->id }}"
            name="name"
            value="{{ old('name', $selectedPreset->name) }}"
            type="text"
            required
            maxlength="120"
            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
        >
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        @foreach ($templateDefinition['fields'] as $field)
            @php
                $fieldKey = $field['key'];
                $fieldId = "school-template-{$selectedPreset->id}-{$fieldKey}";
                $fieldValue = old("template_data.{$fieldKey}", $templateData[$fieldKey] ?? '');
            @endphp

            <div @class(['sm:col-span-2' => $field['type'] === 'textarea'])>
                @if ($field['type'] === 'image')
                    <label for="{{ $fieldId }}" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $field['label'] }}</label>
                    <select
                        id="{{ $fieldId }}"
                        name="template_data[{{ $fieldKey }}]"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                    >
                        <option value="">Tidak menggunakan gambar</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}" @selected((string) $fieldValue === (string) $asset->id)>
                                {{ $asset->original_name }}
                            </option>
                        @endforeach
                    </select>
                    <label for="{{ $fieldId }}-upload" class="mt-2 block text-[11px] font-medium text-gray-600 dark:text-gray-400">Atau unggah gambar baru</label>
                    <input
                        id="{{ $fieldId }}-upload"
                        type="file"
                        name="template_uploads[{{ $fieldKey }}]"
                        accept="image/png,image/jpeg,image/webp"
                        class="mt-1 block w-full text-xs text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-violet-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-violet-800 hover:file:bg-violet-200 dark:text-gray-300 dark:file:bg-violet-900/50 dark:file:text-violet-200"
                    >
                @else
                    <label for="{{ $fieldId }}" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $field['label'] }}</label>
                    @if ($field['type'] === 'textarea')
                        <textarea
                            id="{{ $fieldId }}"
                            name="template_data[{{ $fieldKey }}]"
                            rows="5"
                            maxlength="5000"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                        >{{ $fieldValue }}</textarea>
                    @else
                        <input
                            id="{{ $fieldId }}"
                            name="template_data[{{ $fieldKey }}]"
                            value="{{ $fieldValue }}"
                            type="text"
                            maxlength="5000"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                        >
                    @endif
                @endif
            </div>
        @endforeach
    </div>

    <button type="submit" class="w-full rounded-lg bg-violet-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-800">
        Simpan formulir dan file HTML
    </button>
</form>
