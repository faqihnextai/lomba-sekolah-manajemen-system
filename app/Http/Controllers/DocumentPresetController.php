<?php

namespace App\Http\Controllers;

use App\Models\DocumentPreset;
use App\Models\DocumentPresetFile;
use App\Models\School;
use App\Support\CompetitionCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use JsonException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentPresetController extends Controller
{
    private const COMPONENTS = ['cover', 'header', 'body', 'footer', 'signature', 'other'];

    private const STORAGE_DISK = 'local';

    public function canvas(
        Request $request,
        DocumentPreset $preset,
        CompetitionCatalog $catalog,
    ): View {
        $this->authorizePreset($request, $preset, $catalog);
        abort_unless($preset->preset_type === 'canvas', 404);
        $preset->load('school');

        $assets = $preset->files()
            ->where('kind', 'asset')
            ->latest()
            ->get();

        return view('super-admin.document-canvas', compact('assets', 'preset'));
    }

    public function store(Request $request, CompetitionCatalog $catalog): RedirectResponse
    {
        $isSuperAdmin = $request->user()->hasRole('superadmin');
        abort_unless($isSuperAdmin || $request->user()->hasRole('admin'), 403);

        $validated = $request->validate([
            'school_id' => [$isSuperAdmin ? 'required' : 'nullable', 'integer', 'exists:schools,id'],
            'program' => ['required', 'string', Rule::in(array_keys(config('competitions.programs', [])))],
            'name' => ['required', 'string', 'max:120'],
        ]);
        $school = $this->schoolForUser($request, $validated['school_id'] ?? null);
        $this->assertProgramIsActive($school, $validated['program'], $catalog);

        if (DocumentPreset::query()
            ->where('school_id', $school->id)
            ->where('program', $validated['program'])
            ->where('name', trim($validated['name']))
            ->exists()) {
            throw ValidationException::withMessages([
                'name' => 'Nama preset sudah digunakan untuk lomba ini.',
            ]);
        }

        $preset = $school->documentPresets()->create([
            'created_by' => $request->user()->id,
            'program' => $validated['program'],
            'preset_type' => 'canvas',
            'name' => trim($validated['name']),
            'components' => self::COMPONENTS,
            'canvas_data' => [
                'version' => 1,
                'pages' => [
                    [
                        'id' => 'page-1',
                        'title' => 'Halaman 1',
                        'canvas' => [
                            'version' => '7.4.0',
                            'objects' => [],
                        ],
                    ],
                ],
            ],
        ]);

        return redirect()->route($this->settingsRoute($request), [
            'school_id' => $school->id,
            'program' => $preset->program,
            'preset' => $preset->id,
        ])->with('success', 'Preset dokumen berhasil dibuat.');
    }

    public function storeTemplate(Request $request, CompetitionCatalog $catalog): RedirectResponse
    {
        $isSuperAdmin = $request->user()->hasRole('superadmin');
        $isAdmin = $request->user()->hasRole('admin');
        abort_unless($isSuperAdmin || $isAdmin, 403);

        $templates = config('document_templates', []);
        $validated = $request->validate([
            'school_id' => [$isSuperAdmin ? 'required' : 'nullable', 'integer', 'exists:schools,id'],
            'program' => ['required', 'string', Rule::in(array_keys(config('competitions.programs', [])))],
            'name' => ['required', 'string', 'max:120'],
            'template_key' => ['required', 'string', Rule::in(array_keys($templates))],
        ]);
        $school = $this->schoolForUser($request, $validated['school_id'] ?? null);
        $this->assertProgramIsActive($school, $validated['program'], $catalog);
        $name = trim($validated['name']);

        $this->assertPresetNameIsAvailable($school, $validated['program'], $name);

        $template = $templates[$validated['template_key']];
        $data = $this->defaultTemplateData($template, $school->name);
        $preset = $school->documentPresets()->create([
            'created_by' => $request->user()->id,
            'program' => $validated['program'],
            'preset_type' => 'school_template',
            'template_key' => $validated['template_key'],
            'name' => $name,
            'components' => [$template['section']],
            'canvas_data' => ['version' => 1, 'pages' => []],
            'template_data' => $data,
        ]);
        $this->storeSchoolTemplateFile($preset, $template, $data);

        return redirect()->route($this->settingsRoute($request), [
            'school_id' => $school->id,
            'program' => $preset->program,
            'preset' => $preset->id,
        ])->with('success', 'Template sekolah berhasil dibuat. Isi dan sesuaikan formulirnya.');
    }

    public function updateTemplate(
        Request $request,
        DocumentPreset $preset,
        CompetitionCatalog $catalog,
    ): RedirectResponse {
        $this->authorizePreset($request, $preset, $catalog);
        abort_unless($preset->preset_type === 'school_template', 404);

        $template = config("document_templates.{$preset->template_key}");
        abort_unless(is_array($template), 404);

        $fieldKeys = array_column($template['fields'], 'key');
        $imageKeys = array_values(array_map(
            static fn (array $field): string => $field['key'],
            array_filter($template['fields'], static fn (array $field): bool => $field['type'] === 'image'),
        ));
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'template_data' => ['sometimes', 'array:'.implode(',', $fieldKeys)],
            'template_uploads' => ['sometimes', 'array:'.implode(',', $imageKeys)],
        ];

        foreach ($template['fields'] as $field) {
            $key = $field['key'];
            $rules["template_data.{$key}"] = $field['type'] === 'image'
                ? ['nullable', 'integer']
                : ['nullable', 'string', 'max:5000'];
            if ($field['type'] === 'image') {
                $rules["template_uploads.{$key}"] = [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'dimensions:max_width=5000,max_height=5000',
                    'max:10240',
                ];
            }
        }

        $validated = $request->validate($rules);
        $name = trim($validated['name']);
        $this->assertPresetNameIsAvailable(
            $preset->school,
            $preset->program,
            $name,
            $preset->id,
        );

        $data = array_replace(
            $this->defaultTemplateData($template, $preset->school->name),
            $preset->template_data ?? [],
            $validated['template_data'] ?? [],
        );

        foreach ($imageKeys as $key) {
            $assetId = filter_var($data[$key] ?? null, FILTER_VALIDATE_INT);
            if ($assetId) {
                $assetExists = $preset->files()
                    ->where('kind', 'asset')
                    ->whereKey($assetId)
                    ->exists();
                if (! $assetExists) {
                    throw ValidationException::withMessages([
                        "template_data.{$key}" => 'Gambar harus berasal dari aset template sekolah ini.',
                    ]);
                }
            } else {
                $data[$key] = null;
            }

            $upload = $request->file("template_uploads.{$key}");
            if (! $upload) {
                continue;
            }

            $path = $upload->store("document-presets/{$preset->school_id}/{$preset->id}/assets", self::STORAGE_DISK);
            if (! is_string($path)) {
                throw new RuntimeException('Gagal menyimpan gambar template.');
            }

            $asset = $preset->files()->create([
                'uploaded_by' => $request->user()->id,
                'kind' => 'asset',
                'disk' => self::STORAGE_DISK,
                'storage_path' => $path,
                'original_name' => $this->safeOriginalName($upload->getClientOriginalName()),
                'mime_type' => $upload->getMimeType() ?? 'application/octet-stream',
                'size' => $upload->getSize(),
                'metadata' => ['template_field' => $key],
            ]);
            $data[$key] = $asset->id;
        }

        $preset->update([
            'name' => $name,
            'template_data' => $data,
        ]);
        $this->storeSchoolTemplateFile($preset->fresh(), $template, $data);

        return redirect()->route($this->settingsRoute($request), [
            'school_id' => $preset->school_id,
            'program' => $preset->program,
            'preset' => $preset->id,
        ])->with('success', 'Template sekolah berhasil disimpan.');
    }

    public function showTemplate(
        Request $request,
        DocumentPreset $preset,
        CompetitionCatalog $catalog,
    ): Response {
        $this->authorizePreset($request, $preset, $catalog);
        abort_unless($preset->preset_type === 'school_template', 404);

        $file = $preset->files()->where('kind', 'school_template')->firstOrFail();
        abort_unless(Storage::disk($file->disk)->exists($file->storage_path), 404);

        return response(Storage::disk($file->disk)->get($file->storage_path), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Security-Policy' => "sandbox; default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; frame-ancestors 'self'",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(
        Request $request,
        DocumentPreset $preset,
        CompetitionCatalog $catalog,
    ): RedirectResponse {
        $this->authorizePreset($request, $preset, $catalog, requireActiveProgram: false);

        $schoolId = $preset->school_id;
        $program = $preset->program;

        foreach ($preset->files as $file) {
            $disk = Storage::disk($file->disk);
            if ($disk->exists($file->storage_path) && ! $disk->delete($file->storage_path)) {
                throw new RuntimeException('File preset tidak dapat dihapus dari penyimpanan.');
            }
        }

        $preset->delete();

        return redirect()
            ->route($this->settingsRoute($request), [
                'school_id' => $schoolId,
                'program' => $program,
            ])
            ->with('success', 'Preset dokumen dan file terkait berhasil dihapus.');
    }

    public function update(
        Request $request,
        DocumentPreset $preset,
        CompetitionCatalog $catalog,
    ): JsonResponse {
        $this->authorizePreset($request, $preset, $catalog);
        abort_unless($preset->preset_type === 'canvas', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'components' => ['required', 'array', 'min:1'],
            'components.*' => ['required', 'string', Rule::in(self::COMPONENTS)],
            'canvas_data' => ['required', 'string', 'max:200000'],
        ]);

        $name = trim($validated['name']);
        if (DocumentPreset::query()
            ->where('school_id', $preset->school_id)
            ->where('program', $preset->program)
            ->where('name', $name)
            ->where('id', '!=', $preset->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'name' => 'Nama preset sudah digunakan untuk lomba ini.',
            ]);
        }

        $canvasData = $this->decodeCanvasData($validated['canvas_data'], $preset);
        $preset->update([
            'name' => $name,
            'components' => array_values(array_unique($validated['components'])),
            'canvas_data' => $canvasData,
        ]);

        return response()->json([
            'message' => 'Preset dan desain berhasil disimpan.',
            'updated_at' => $preset->fresh()->updated_at?->toIso8601String(),
        ]);
    }

    public function uploadAsset(Request $request, DocumentPreset $preset, CompetitionCatalog $catalog): JsonResponse
    {
        $this->authorizePreset($request, $preset, $catalog);
        abort_unless($preset->preset_type === 'canvas', 404);

        $validated = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'dimensions:max_width=5000,max_height=5000', 'max:10240'],
        ]);

        $file = $validated['file'];
        $path = $file->store("document-presets/{$preset->school_id}/{$preset->id}/assets", self::STORAGE_DISK);

        if (! is_string($path)) {
            throw new RuntimeException('Gagal menyimpan gambar yang diunggah.');
        }

        $storedFile = $preset->files()->create([
            'uploaded_by' => $request->user()->id,
            'kind' => 'asset',
            'disk' => self::STORAGE_DISK,
            'storage_path' => $path,
            'original_name' => $this->safeOriginalName($file->getClientOriginalName()),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'id' => $storedFile->id,
            'name' => $storedFile->original_name,
            'url' => route('document-presets.files.show', [$preset, $storedFile]),
        ], 201);
    }

    public function destroyAsset(
        Request $request,
        DocumentPreset $preset,
        DocumentPresetFile $file,
        CompetitionCatalog $catalog,
    ): JsonResponse {
        $this->authorizePreset($request, $preset, $catalog);
        abort_unless($file->document_preset_id === $preset->id && $file->kind === 'asset', 404);

        if ($this->canvasReferencesAsset($preset, $file->id)) {
            return response()->json([
                'message' => 'Gambar masih dipakai di canvas. Hapus gambar dari semua halaman dan simpan preset terlebih dahulu.',
            ], 409);
        }
        if ($this->templateReferencesAsset($preset, $file->id)) {
            return response()->json([
                'message' => 'Gambar masih dipakai oleh template sekolah. Lepaskan gambar dari formulir dan simpan terlebih dahulu.',
            ], 409);
        }

        $disk = Storage::disk($file->disk);
        if ($disk->exists($file->storage_path) && ! $disk->delete($file->storage_path)) {
            throw new RuntimeException('Gambar tidak dapat dihapus dari penyimpanan.');
        }

        $file->delete();

        return response()->json(['message' => 'Gambar berhasil dihapus.']);
    }

    public function storeExport(Request $request, DocumentPreset $preset, CompetitionCatalog $catalog): JsonResponse
    {
        $this->authorizePreset($request, $preset, $catalog);
        abort_unless($preset->preset_type === 'canvas', 404);

        $validated = $request->validate([
            'file' => ['required', 'image', 'mimes:png', 'dimensions:max_width=5000,max_height=5000', 'max:10240'],
            'page' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $file = $validated['file'];
        $pageNumber = $validated['page'];
        $path = $file->store("document-presets/{$preset->school_id}/{$preset->id}/exports", self::STORAGE_DISK);

        if (! is_string($path)) {
            throw new RuntimeException('Gagal menyimpan hasil ekspor PNG.');
        }

        $storedFile = $preset->files()->create([
            'uploaded_by' => $request->user()->id,
            'kind' => 'export_png',
            'disk' => self::STORAGE_DISK,
            'storage_path' => $path,
            'original_name' => (Str::slug($preset->name) ?: "preset-{$preset->id}")."-halaman-{$pageNumber}.png",
            'mime_type' => 'image/png',
            'size' => $file->getSize(),
            'metadata' => ['page' => $pageNumber],
        ]);

        return response()->json([
            'id' => $storedFile->id,
            'url' => route('document-presets.files.show', [$preset, $storedFile]),
            'name' => $storedFile->original_name,
        ], 201);
    }

    public function showFile(
        Request $request,
        DocumentPreset $preset,
        DocumentPresetFile $file,
        CompetitionCatalog $catalog,
    ): StreamedResponse {
        $this->authorizePreset($request, $preset, $catalog);
        abort_unless($file->document_preset_id === $preset->id, 404);
        abort_unless($file->kind !== 'school_template', 404);
        abort_unless(Storage::disk($file->disk)->exists($file->storage_path), 404);

        return Storage::disk($file->disk)->response(
            $file->storage_path,
            $file->original_name,
            [
                'Content-Type' => $file->mime_type,
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }

    private function authorizePreset(
        Request $request,
        DocumentPreset $preset,
        CompetitionCatalog $catalog,
        bool $requireActiveProgram = true,
    ): void {
        $user = $request->user();
        $isSuperAdmin = $user->hasRole('superadmin');
        $isAdmin = $user->hasRole('admin');
        abort_unless($isSuperAdmin || $isAdmin, 403);

        if (! $isSuperAdmin) {
            abort_unless($isAdmin && $user->school_id === $preset->school_id, 404);
        }

        if ($requireActiveProgram) {
            $school = School::query()
                ->with('competitionSettings')
                ->findOrFail($preset->school_id);
            $this->assertProgramIsActive($school, $preset->program, $catalog);
        }
    }

    private function schoolForUser(Request $request, ?int $requestedSchoolId): School
    {
        $user = $request->user();

        if ($user->hasRole('superadmin')) {
            abort_unless($requestedSchoolId, 422, 'Pilih sekolah terlebih dahulu.');

            return School::query()->findOrFail($requestedSchoolId);
        }

        abort_unless($user->hasRole('admin') && $user->school_id, 403, 'Akun admin belum memiliki sekolah.');

        return School::query()->findOrFail($user->school_id);
    }

    private function assertProgramIsActive(School $school, string $program, CompetitionCatalog $catalog): void
    {
        abort_unless(array_key_exists($program, $catalog->activeForSchool($school)), 403, 'Lomba ini belum aktif di sekolah yang dipilih.');
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array<string, mixed>
     */
    private function defaultTemplateData(array $template, string $schoolName): array
    {
        $data = [];
        foreach ($template['fields'] as $field) {
            $data[$field['key']] = ($field['school_name'] ?? false)
                ? $schoolName
                : ($field['default'] ?? '');
        }

        return $data;
    }

    private function assertPresetNameIsAvailable(
        School $school,
        string $program,
        string $name,
        ?int $exceptPresetId = null,
    ): void {
        $query = DocumentPreset::query()
            ->where('school_id', $school->id)
            ->where('program', $program)
            ->where('name', $name);

        if ($exceptPresetId !== null) {
            $query->where('id', '!=', $exceptPresetId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => 'Nama preset sudah digunakan untuk lomba ini.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $template
     * @param  array<string, mixed>  $data
     */
    private function storeSchoolTemplateFile(DocumentPreset $preset, array $template, array $data): void
    {
        $imageKeys = array_map(
            static fn (array $field): string => $field['key'],
            array_filter($template['fields'], static fn (array $field): bool => $field['type'] === 'image'),
        );
        $assets = $preset->files()
            ->where('kind', 'asset')
            ->whereIn('id', array_filter(array_map(
                static fn (string $key): int|false => filter_var($data[$key] ?? null, FILTER_VALIDATE_INT),
                $imageKeys,
            )))
            ->get()
            ->keyBy('id');
        $images = [];
        foreach ($imageKeys as $key) {
            $asset = $assets->get((int) ($data[$key] ?? 0));
            $images[$key] = $asset
                ? route('document-presets.files.show', [$preset, $asset])
                : null;
        }

        $html = view('document-templates.school-template', [
            'preset' => $preset,
            'template' => $template,
            'data' => $data,
            'images' => $images,
        ])->render();
        $path = "document-presets/{$preset->school_id}/{$preset->id}/template/template.html";
        $disk = Storage::disk(self::STORAGE_DISK);
        if (! $disk->put($path, $html)) {
            throw new RuntimeException('File template sekolah tidak dapat disimpan.');
        }

        $preset->files()->updateOrCreate(
            ['kind' => 'school_template'],
            [
                'uploaded_by' => $preset->created_by,
                'disk' => self::STORAGE_DISK,
                'storage_path' => $path,
                'original_name' => (Str::slug($preset->name) ?: "template-{$preset->id}").'.html',
                'mime_type' => 'text/html',
                'size' => strlen($html),
                'metadata' => ['template_key' => $preset->template_key],
            ],
        );
    }

    private function settingsRoute(Request $request): string
    {
        return $request->user()->hasRole('superadmin')
            ? 'super-admin.setting-dokumen'
            : 'admin.setting-dokumen';
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeCanvasData(string $serializedCanvas, DocumentPreset $preset): array
    {
        try {
            $canvasData = json_decode($serializedCanvas, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw ValidationException::withMessages([
                'canvas_data' => 'Data canvas tidak valid.',
            ]);
        }

        if (! is_array($canvasData)
            || ! is_array($canvasData['pages'] ?? null)
            || count($canvasData['pages']) < 1
            || count($canvasData['pages']) > 25
            || ! array_is_list($canvasData['pages'])) {
            throw ValidationException::withMessages([
                'canvas_data' => 'Canvas harus memiliki antara 1 sampai 25 halaman.',
            ]);
        }

        $assetsById = $preset->files()
            ->where('kind', 'asset')
            ->get(['id', 'document_preset_id'])
            ->keyBy('id')
            ->all();
        $objectCount = 0;
        foreach ($canvasData['pages'] as &$page) {
            if (! is_array($page)
                || ! is_array($page['canvas'] ?? null)
                || ! is_array($page['canvas']['objects'] ?? null)
                || ! array_is_list($page['canvas']['objects'])) {
                throw ValidationException::withMessages([
                    'canvas_data' => 'Struktur halaman canvas tidak valid.',
                ]);
            }

            if (isset($page['section']) && ! in_array($page['section'], self::COMPONENTS, true)) {
                throw ValidationException::withMessages([
                    'canvas_data' => 'Bagian halaman canvas tidak valid.',
                ]);
            }

            if (isset($page['listedInOther']) && ! is_bool($page['listedInOther'])) {
                throw ValidationException::withMessages([
                    'canvas_data' => 'Status bagian lainnya pada halaman canvas tidak valid.',
                ]);
            }

            if (isset($page['canvas']['backgroundImage']) || isset($page['canvas']['overlayImage'])) {
                throw ValidationException::withMessages([
                    'canvas_data' => 'Gambar latar harus ditambahkan sebagai aset canvas.',
                ]);
            }

            $this->restoreAssetUrls($page['canvas']['objects'], $preset, $assetsById, $objectCount);
        }
        unset($page);

        return $canvasData;
    }

    /**
     * @param  list<array<string, mixed>>  $objects
     * @param  array<int, DocumentPresetFile>  $assetsById
     */
    private function restoreAssetUrls(
        array &$objects,
        DocumentPreset $preset,
        array $assetsById,
        int &$objectCount,
    ): void {
        foreach ($objects as &$object) {
            if (! is_array($object)) {
                throw ValidationException::withMessages([
                    'canvas_data' => 'Objek canvas tidak valid.',
                ]);
            }
            $objectCount++;
            if ($objectCount > 500) {
                throw ValidationException::withMessages([
                    'canvas_data' => 'Canvas maksimal dapat menyimpan 500 elemen.',
                ]);
            }

            if (strtolower((string) ($object['type'] ?? '')) === 'image') {
                $assetId = filter_var($object['presetAssetId'] ?? null, FILTER_VALIDATE_INT);
                $asset = $assetId ? ($assetsById[$assetId] ?? null) : null;

                if (! $asset) {
                    throw ValidationException::withMessages([
                        'canvas_data' => 'Gambar canvas harus berasal dari aset preset ini.',
                    ]);
                }

                $object['src'] = route('document-presets.files.show', [$preset, $asset]);
                $object['crossOrigin'] = 'same-origin';
            } elseif (isset($object['src'])) {
                throw ValidationException::withMessages([
                    'canvas_data' => 'Sumber gambar objek tidak valid.',
                ]);
            }

            if (isset($object['objects'])) {
                if (! is_array($object['objects']) || ! array_is_list($object['objects'])) {
                    throw ValidationException::withMessages([
                        'canvas_data' => 'Grup objek canvas tidak valid.',
                    ]);
                }

                $this->restoreAssetUrls($object['objects'], $preset, $assetsById, $objectCount);
            }
        }
        unset($object);
    }

    private function safeOriginalName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?? '';
        $name = mb_substr($name, 0, 255);

        return $name !== '' ? $name : 'gambar';
    }

    private function canvasReferencesAsset(DocumentPreset $preset, int $assetId): bool
    {
        foreach ($preset->canvas_data['pages'] ?? [] as $page) {
            if (! is_array($page)) {
                continue;
            }
            $objects = $page['canvas']['objects'] ?? [];
            if ($this->objectsReferenceAsset($objects, $assetId)) {
                return true;
            }
        }

        return false;
    }

    private function templateReferencesAsset(DocumentPreset $preset, int $assetId): bool
    {
        $template = config("document_templates.{$preset->template_key}");
        if (! is_array($template)) {
            return false;
        }

        $imageKeys = array_map(
            static fn (array $field): string => $field['key'],
            array_filter($template['fields'], static fn (array $field): bool => $field['type'] === 'image'),
        );

        return in_array($assetId, array_map(
            static fn (mixed $value): int => (int) $value,
            array_filter(
                array_intersect_key($preset->template_data ?? [], array_flip($imageKeys)),
                static fn (mixed $value): bool => is_numeric($value),
            ),
        ), true);
    }

    private function objectsReferenceAsset(mixed $objects, int $assetId): bool
    {
        if (! is_array($objects)) {
            return false;
        }

        foreach ($objects as $object) {
            if (! is_array($object)) {
                continue;
            }

            if ((int) ($object['presetAssetId'] ?? 0) === $assetId) {
                return true;
            }

            if ($this->objectsReferenceAsset($object['objects'] ?? null, $assetId)) {
                return true;
            }
        }

        return false;
    }
}
