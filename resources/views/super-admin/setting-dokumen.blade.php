<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Setting Dokumen</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Atur perlombaan, preset dokumen sekolah, dan desain PNG.
            </p>
        </div>
    </x-slot>

    @php
        $settingsRoute = $isSuperAdmin ? 'super-admin.setting-dokumen' : 'admin.setting-dokumen';
        $documentComponents = [
            'cover' => 'Cover',
            'header' => 'Header / Kop',
            'body' => 'Isi dokumen',
            'footer' => 'Footer',
            'signature' => 'Tanda tangan',
            'other' => 'Lainnya',
        ];
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div role="status" class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950/30 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-200">
                    <p class="font-semibold">Periksa kembali pengaturan dokumen:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-sm font-black text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">1</span>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white">Setting dokumen perlombaan</h3>
                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">Pilih sekolah dan perlombaan yang sudah diaktifkan.</p>
                        </div>
                    </div>

                    @if ($selectedSchool)
                        <form method="GET" action="{{ route($settingsRoute) }}" class="mt-5 space-y-4">
                            @if ($isSuperAdmin)
                                <div>
                                    <label for="document-school" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sekolah</label>
                                    <select id="document-school" name="school_id" onchange="this.form.submit()" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                        @foreach ($schools as $school)
                                            <option value="{{ $school->id }}" @selected($school->id === $selectedSchool->id)>{{ $school->name }} ({{ $school->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="rounded-xl bg-blue-50 px-4 py-3 dark:bg-blue-950/30">
                                    <p class="text-xs text-blue-700 dark:text-blue-300">Sekolah Anda</p>
                                    <p class="mt-1 font-semibold text-blue-950 dark:text-white">{{ $selectedSchool->name }}</p>
                                    <p class="text-xs text-blue-700 dark:text-blue-300">Kode {{ $selectedSchool->code }}</p>
                                </div>
                            @endif

                            <div>
                                <label for="document-program" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Perlombaan aktif</label>
                                <select id="document-program" name="program" onchange="this.form.submit()" @disabled(count($programs) === 0) class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:disabled:bg-gray-700">
                                    @forelse ($programs as $programKey => $competition)
                                        <option value="{{ $programKey }}" @selected($programKey === $selectedProgram)>{{ $competition['label'] }}</option>
                                    @empty
                                        <option value="">Belum ada perlombaan aktif</option>
                                    @endforelse
                                </select>
                            </div>
                            <p class="rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-200">
                                Preset hanya dapat dibuat untuk perlombaan yang aktif di sekolah terpilih.
                            </p>
                        </form>
                    @else
                        <div class="mt-5 rounded-xl border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                            Belum ada sekolah terdaftar untuk diatur.
                        </div>
                    @endif
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-sm font-black text-violet-700 dark:bg-violet-900/40 dark:text-violet-200">2</span>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white">Preset sekolah & canvas bebas</h3>
                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">Template resmi dibuat dari formulir HTML. Canvas hanya untuk desain bebas.</p>
                        </div>
                    </div>

                    @if ($selectedSchool && $selectedProgram)
                        <form method="POST" action="{{ route('document-presets.store') }}" class="mt-4 space-y-3 rounded-xl border border-blue-100 bg-blue-50/60 p-4 dark:border-blue-900 dark:bg-blue-950/20">
                            @csrf
                            <input type="hidden" name="school_id" value="{{ $isSuperAdmin ? $selectedSchool->id : '' }}">
                            <input type="hidden" name="program" value="{{ $selectedProgram }}">
                            <div>
                                <h4 class="text-sm font-bold text-blue-950 dark:text-blue-100">Preset bebas (canvas)</h4>
                                <p class="mt-1 text-xs text-blue-800 dark:text-blue-200">Mulai dari halaman kosong dan susun elemen sendiri di canvas.</p>
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <label for="new-preset-name" class="sr-only">Nama preset bebas</label>
                                <input id="new-preset-name" name="name" type="text" required maxlength="120" placeholder="Contoh: Desain laporan khusus" class="min-w-0 flex-1 rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700 disabled:opacity-50" @disabled(! $selectedProgram)>
                                    Buat canvas bebas
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('document-presets.templates.store') }}" class="mt-3 space-y-3 rounded-xl border border-violet-200 bg-violet-50/70 p-4 dark:border-violet-900 dark:bg-violet-950/20">
                            @csrf
                            <input type="hidden" name="school_id" value="{{ $isSuperAdmin ? $selectedSchool->id : '' }}">
                            <input type="hidden" name="program" value="{{ $selectedProgram }}">
                            <div>
                                <h4 class="text-sm font-bold text-violet-950 dark:text-violet-100">Buat template sekolah</h4>
                                <p class="mt-1 text-xs leading-5 text-violet-800 dark:text-violet-200">Pilih format bawaan. Sistem membuat file HTML sekolah yang dapat disesuaikan lewat formulir, bukan canvas.</p>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label for="school-template-key" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Jenis dan gaya template</label>
                                    <select id="school-template-key" name="template_key" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                        <option value="">Pilih template</option>
                                        @foreach ($documentTemplates as $templateKey => $templateDefinition)
                                            <option value="{{ $templateKey }}" @selected(old('template_key') === $templateKey)>{{ $templateDefinition['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="school-template-name" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Nama template sekolah</label>
                                    <input id="school-template-name" name="name" value="{{ old('name') }}" type="text" required maxlength="120" placeholder="Contoh: Cover Adiwiyata Daarul Huda" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                </div>
                            </div>
                            <button type="submit" class="w-full rounded-lg bg-violet-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-800 disabled:opacity-50" @disabled(! $selectedProgram)>
                                Buat dan simpan ke preset sekolah
                            </button>
                        </form>

                        @if ($selectedPreset)
                            <div class="mt-4 rounded-xl border border-red-100 bg-red-50 p-3 dark:border-red-900 dark:bg-red-950/20">
                                <form method="POST" action="{{ route('document-presets.destroy', $selectedPreset) }}" onsubmit="return confirm('Hapus preset sekolah ini beserta file dan gambar yang terkait? Template bawaan sistem tetap tersedia.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950/50">
                                        Hapus preset ini
                                    </button>
                                    <span class="ml-2 text-xs text-red-700 dark:text-red-300">Template bawaan sistem tidak dapat dihapus.</span>
                                </form>
                            </div>
                            @if ($selectedPreset->preset_type === 'school_template')
                                @include('super-admin.partials.school-template-editor')
                            @else
                                <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-700">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Bagian dalam preset bebas</p>
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        @foreach ($documentComponents as $componentKey => $componentLabel)
                                            <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-700 dark:border-gray-700 dark:text-gray-300">
                                                <input type="checkbox" data-component-toggle value="{{ $componentKey }}" @checked(in_array($componentKey, $selectedPreset->components ?? [], true)) class="rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                                                {{ $componentLabel }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="mt-4 rounded-xl bg-gray-50 px-3 py-2 text-xs text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                Pilih apakah ingin membuat template sekolah dari formulir atau preset canvas bebas.
                            </p>
                        @endif
                    @else
                        <p class="mt-5 rounded-xl bg-gray-50 px-3 py-3 text-sm text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                            Pilih sekolah dengan perlombaan aktif terlebih dahulu.
                        </p>
                    @endif
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">3</span>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white">Preview preset sekolah</h3>
                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">Template HTML dan hasil PNG canvas ditampilkan di sini.</p>
                        </div>
                    </div>

                    <div data-export-preview-container class="mt-4 flex min-h-52 items-center justify-center overflow-hidden rounded-xl border border-dashed border-gray-300 bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-900">
                        @if ($selectedPreset?->preset_type === 'school_template' && $templateFile)
                            <iframe
                                title="Pratinjau template sekolah {{ $selectedPreset->name }}"
                                src="{{ route('document-presets.templates.show', $selectedPreset) }}"
                                sandbox
                                class="h-80 w-full rounded-lg bg-white"
                            ></iframe>
                        @elseif ($latestExport)
                            <img data-export-preview src="{{ route('document-presets.files.show', [$selectedPreset, $latestExport]) }}" alt="Preview PNG {{ $selectedPreset->name }}" class="max-h-64 w-auto rounded border border-gray-200 bg-white object-contain shadow-sm dark:border-gray-700">
                        @else
                            <div class="max-w-xs text-center">
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Preview belum tersedia</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Simpan formulir template atau ekspor canvas sebagai PNG untuk menampilkan pratinjau.</p>
                            </div>
                        @endif
                    </div>
                    @if ($selectedPreset)
                        <p class="mt-3 truncate text-xs text-gray-500 dark:text-gray-400">{{ $selectedPreset->name }}</p>
                    @endif
                </section>
            </div>

            <section class="rounded-2xl border border-violet-200 bg-violet-50 p-5 dark:border-violet-900 dark:bg-violet-950/30">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="font-bold text-violet-950 dark:text-violet-100">Lihat preset yang sudah dibuat</h3>
                        <p class="mt-1 text-sm text-violet-800 dark:text-violet-200">Pilih preset sekolah atau preset canvas yang ingin dibuka.</p>
                    </div>
                    @if ($presets->isNotEmpty())
                        <form method="GET" action="{{ route($settingsRoute) }}" class="grid w-full grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_auto] lg:max-w-2xl">
                            @if ($isSuperAdmin)
                                <input type="hidden" name="school_id" value="{{ $selectedSchool->id }}">
                            @endif
                            <input type="hidden" name="program" value="{{ $selectedProgram }}">
                            <label for="document-preset" class="sr-only">Pilih preset tersimpan</label>
                            <select id="document-preset" name="preset" required class="block w-full rounded-xl border-violet-200 bg-white shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-violet-800 dark:bg-gray-900 dark:text-white">
                                <option value="">Pilih preset untuk dilihat</option>
                                @foreach ($presets as $presetOption)
                                    <option value="{{ $presetOption->id }}" @selected($selectedPreset?->id === $presetOption->id)>
                                        {{ $presetOption->preset_type === 'school_template' ? 'Template sekolah' : 'Canvas bebas' }} · {{ $presetOption->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="rounded-xl bg-violet-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-violet-800">
                                Buka preset
                            </button>
                        </form>
                    @else
                        <p class="rounded-xl bg-white/70 px-4 py-3 text-sm text-violet-800 dark:bg-gray-900/50 dark:text-violet-200">
                            Belum ada preset untuk sekolah dan lomba ini.
                        </p>
                    @endif
                </div>
            </section>

            @if ($selectedPreset?->preset_type === 'canvas')
                <div class="flex flex-col gap-3 rounded-2xl border border-blue-100 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/30 sm:flex-row sm:items-center sm:justify-between lg:hidden">
                    <p class="text-sm text-blue-900 dark:text-blue-100">Canvas bebas lebih nyaman digunakan pada halaman khusus di ponsel.</p>
                    <a href="{{ route('document-presets.canvas', $selectedPreset) }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">
                        Buka canvas
                    </a>
                </div>

                <div class="hidden lg:block">
                    @include('super-admin.partials.document-editor', ['selectedPreset' => $selectedPreset, 'assets' => $assets, 'documentComponents' => $documentComponents])
                </div>
            @elseif ($selectedPreset?->preset_type === 'school_template')
                <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5 dark:border-violet-900 dark:bg-violet-950/30">
                    <h3 class="font-semibold text-violet-950 dark:text-violet-100">Template sekolah tersimpan</h3>
                    <p class="mt-1 text-sm text-violet-800 dark:text-violet-200">Template ini dikelola melalui formulir di card preset dan disimpan sebagai HTML. Canvas bebas tidak mengubahnya.</p>
                </div>
            @elseif ($selectedSchool && $selectedProgram)
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Belum ada preset yang dipilih</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat preset di card kedua atau pilih preset tersimpan dari daftar di atas.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
