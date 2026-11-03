<section
    data-document-editor
    data-preset-id="{{ $selectedPreset->id }}"
    data-preset-name="{{ $selectedPreset->name }}"
    data-canvas-data="{{ base64_encode(json_encode($selectedPreset->canvas_data, JSON_THROW_ON_ERROR)) }}"
    data-save-url="{{ route('document-presets.update', $selectedPreset) }}"
    data-upload-url="{{ route('document-presets.assets.store', $selectedPreset) }}"
    data-asset-delete-url="{{ route('document-presets.assets.destroy', [$selectedPreset, '__FILE_ID__']) }}"
    data-export-url="{{ route('document-presets.exports.store', $selectedPreset) }}"
    class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
>
    <div class="flex flex-col gap-4 border-b border-gray-200 p-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {{ $selectedPreset->school->name }} · {{ config("competitions.programs.{$selectedPreset->program}.label") }}
            </p>
            <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-center">
                <label for="preset-name-{{ $selectedPreset->id }}" class="sr-only">Nama preset dokumen</label>
                <input id="preset-name-{{ $selectedPreset->id }}" data-preset-name-input value="{{ $selectedPreset->name }}" maxlength="120" class="w-full rounded-lg border-gray-300 text-base font-bold text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white sm:max-w-md">
                <span data-editor-status role="status" aria-live="polite" class="text-xs text-gray-500 dark:text-gray-400">Semua perubahan tersimpan.</span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" data-action="undo" aria-label="Urungkan" title="Urungkan (Ctrl+Z)" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-40 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">↶</button>
            <button type="button" data-action="redo" aria-label="Ulangi" title="Ulangi (Ctrl+Y)" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-40 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">↷</button>
            <button type="button" data-action="save" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">Simpan</button>
            <button type="button" data-action="export" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700">Ekspor PNG</button>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12">
        <aside class="space-y-5 border-b border-gray-200 p-4 dark:border-gray-700 lg:col-span-2 lg:border-b-0 lg:border-r">
            @if ($showComponentSettings ?? false)
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Bagian dokumen</h3>
                    <div class="mt-3 space-y-2">
                        @foreach ($documentComponents as $componentKey => $componentLabel)
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" data-component-toggle value="{{ $componentKey }}" @checked(in_array($componentKey, $selectedPreset->components ?? [], true)) class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                {{ $componentLabel }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Elemen & gambar</h3>
                    <label class="cursor-pointer rounded-lg border border-blue-200 px-2.5 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-50 dark:border-blue-900 dark:text-blue-200 dark:hover:bg-blue-950/30">
                        Unggah
                        <input type="file" data-upload-input accept="image/png,image/jpeg,image/webp" class="sr-only">
                    </label>
                </div>
                <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">Pilih gambar untuk menambahkannya ke halaman.</p>
                <div data-asset-list class="mt-3 grid grid-cols-2 gap-2">
                    @foreach ($assets as $asset)
                        <div data-asset-card="{{ $asset->id }}" class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                            <button type="button" data-asset-id="{{ $asset->id }}" data-asset-url="{{ route('document-presets.files.show', [$selectedPreset, $asset]) }}" data-asset-name="{{ $asset->original_name }}" class="group block w-full text-left hover:border-blue-500">
                                <img src="{{ route('document-presets.files.show', [$selectedPreset, $asset]) }}" alt="" class="aspect-square w-full bg-gray-50 object-contain dark:bg-gray-900">
                                <span class="block truncate px-2 py-1.5 text-[10px] text-gray-600 dark:text-gray-300">{{ $asset->original_name }}</span>
                            </button>
                            <button type="button" data-delete-asset="{{ $asset->id }}" class="w-full border-t border-gray-200 px-2 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50 dark:border-gray-700 dark:text-red-300 dark:hover:bg-red-950/30">
                                Hapus gambar
                            </button>
                        </div>
                    @endforeach
                </div>
                <p data-assets-empty @class(['mt-3 text-xs text-gray-400', 'hidden' => $assets->isNotEmpty()])>Belum ada gambar yang diunggah.</p>
            </div>

        </aside>

        <main class="space-y-4 bg-gray-100 p-4 dark:bg-gray-950 lg:col-span-7">
            <div class="flex flex-wrap items-center justify-center gap-2">
                <button type="button" data-action="add-text" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">+ Teks</button>
                <button type="button" data-action="add-rect" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">+ Kotak</button>
                <button type="button" data-action="add-circle" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">+ Lingkaran</button>
                <span class="mx-1 h-6 border-l border-gray-300 dark:border-gray-600"></span>
                <button type="button" data-action="previous-page" aria-label="Halaman sebelumnya" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">‹</button>
                <span data-page-indicator class="min-w-20 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">Halaman 1 dari 1</span>
                <button type="button" data-action="next-page" aria-label="Halaman berikutnya" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">›</button>
                <button type="button" data-action="add-page" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-40 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">+ Halaman</button>
                <button type="button" data-action="remove-page" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50 disabled:opacity-40 dark:border-gray-600 dark:bg-gray-800 dark:text-red-300">Hapus halaman</button>
            </div>

            <div class="document-canvas-scroll overflow-auto rounded-xl border border-gray-300 bg-gray-200 p-3 dark:border-gray-700 dark:bg-gray-900 sm:p-5">
                <div class="document-canvas-container mx-auto w-fit bg-white shadow-lg">
                    <canvas data-fabric-canvas aria-label="Canvas dokumen A4"></canvas>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                <span>Zoom</span>
                <button type="button" data-action="zoom-out" aria-label="Perkecil canvas" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">−</button>
                <span data-zoom-label>100%</span>
                <button type="button" data-action="zoom-in" aria-label="Perbesar canvas" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">+</button>
                <button type="button" data-action="zoom-reset" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">Reset</button>
                <span class="hidden sm:inline">· A4 portrait</span>
                <span class="hidden sm:inline">· Pinch dua jari untuk zoom</span>
            </div>
        </main>

        <aside class="space-y-6 p-4 lg:col-span-3">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Format elemen</h3>
                <div class="mt-3 space-y-3">
                    <div>
                        <label for="element-text-{{ $selectedPreset->id }}" class="block text-xs font-medium text-gray-600 dark:text-gray-300">Teks terpilih</label>
                        <textarea id="element-text-{{ $selectedPreset->id }}" rows="3" data-property="text" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:disabled:bg-gray-800" disabled></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="element-font-{{ $selectedPreset->id }}" class="block text-xs font-medium text-gray-600 dark:text-gray-300">Jenis huruf</label>
                            <select id="element-font-{{ $selectedPreset->id }}" data-property="fontFamily" class="mt-1 block w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:disabled:bg-gray-800" disabled>
                                <option value="Arial">Arial</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Times New Roman">Times New Roman</option>
                                <option value="Verdana">Verdana</option>
                                <option value="Tahoma">Tahoma</option>
                            </select>
                        </div>
                        <div>
                            <label for="element-size-{{ $selectedPreset->id }}" class="block text-xs font-medium text-gray-600 dark:text-gray-300">Ukuran</label>
                            <input id="element-size-{{ $selectedPreset->id }}" type="number" min="8" max="180" data-property="fontSize" class="mt-1 block w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:disabled:bg-gray-800" disabled>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" data-format="bold" aria-label="Tebalkan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-black text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200">B</button>
                        <button type="button" data-format="italic" aria-label="Miringkan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm italic text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200">I</button>
                        <button type="button" data-format="underline" aria-label="Garis bawah" class="rounded-lg border border-gray-300 px-3 py-2 text-sm underline text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200">U</button>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-300 px-2 py-1.5 text-xs text-gray-600 dark:border-gray-600 dark:text-gray-300">
                            Warna
                            <input type="color" data-property="fill" value="#111827" class="h-6 w-7 cursor-pointer border-0 bg-transparent p-0 disabled:opacity-40" disabled>
                        </label>
                        <select data-property="textAlign" aria-label="Perataan teks" class="rounded-lg border-gray-300 text-xs shadow-sm disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-white" disabled>
                            <option value="left">Kiri</option>
                            <option value="center">Tengah</option>
                            <option value="right">Kanan</option>
                            <option value="justify">Rata kiri-kanan</option>
                        </select>
                    </div>
                    <button type="button" data-action="delete-object" class="w-full rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50 disabled:opacity-40 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950/30" disabled>Hapus elemen terpilih</button>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Susunan elemen</h3>
                    <div class="flex gap-1">
                        <button type="button" data-action="layer-up" aria-label="Naikkan layer" class="rounded border border-gray-300 px-2 py-1 text-xs dark:border-gray-600 dark:text-gray-200">↑</button>
                        <button type="button" data-action="layer-down" aria-label="Turunkan layer" class="rounded border border-gray-300 px-2 py-1 text-xs dark:border-gray-600 dark:text-gray-200">↓</button>
                    </div>
                </div>
                <label for="layer-select-{{ $selectedPreset->id }}" class="mt-3 block text-xs font-medium text-gray-600 dark:text-gray-300">Pilih elemen untuk diedit</label>
                <select id="layer-select-{{ $selectedPreset->id }}" data-layer-select class="mt-1 block w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">Pilih elemen</option>
                </select>
                <ol data-layer-list class="mt-3 max-h-64 space-y-1 overflow-y-auto"></ol>
                <p data-layers-empty class="mt-2 text-xs text-gray-400">Belum ada elemen di halaman ini.</p>
            </div>
        </aside>
    </div>
</section>
