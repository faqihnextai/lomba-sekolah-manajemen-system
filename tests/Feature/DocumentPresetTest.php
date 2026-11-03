<?php

namespace Tests\Feature;

use App\Models\DocumentPreset;
use App\Models\DocumentPresetFile;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentPresetTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_a_preset_for_their_school_even_if_another_school_id_is_submitted(): void
    {
        $school = $this->activeSchool('DAHU');
        $otherSchool = $this->activeSchool('MAAN');
        $admin = $this->userWithRole('admin', $school);

        $this->actingAs($admin)
            ->post(route('document-presets.store'), [
                'school_id' => $otherSchool->id,
                'program' => 'adiwiyata',
                'name' => 'Preset Sekolah',
            ])
            ->assertRedirect();

        $preset = DocumentPreset::query()->where('name', 'Preset Sekolah')->firstOrFail();
        $this->assertSame($school->id, $preset->school_id);
        $this->assertSame('adiwiyata', $preset->program);
        $this->assertSame($admin->id, $preset->created_by);
        $this->assertSame(['cover', 'header', 'body', 'footer', 'signature', 'other'], $preset->components);
    }

    public function test_admin_can_delete_a_saved_preset_and_all_of_its_files_without_deleting_other_presets(): void
    {
        Storage::fake('local');
        $school = $this->activeSchool('DAHU');
        $admin = $this->userWithRole('admin', $school);
        $preset = $this->createPreset($school, 'Preset yang dihapus');
        $otherPreset = $this->createPreset($school, 'Preset yang dipertahankan');
        $asset = $this->createAsset($preset);
        $exportPath = UploadedFile::fake()->image('hasil.png')->store('document-presets/exports', 'local');
        $export = $preset->files()->create([
            'kind' => 'export_png',
            'disk' => 'local',
            'storage_path' => $exportPath,
            'original_name' => 'hasil.png',
            'mime_type' => 'image/png',
            'size' => 100,
        ]);

        $this->actingAs($admin)
            ->delete(route('document-presets.destroy', $preset))
            ->assertRedirect(route('admin.setting-dokumen', [
                'school_id' => $school->id,
                'program' => 'adiwiyata',
            ]))
            ->assertSessionHas('success', 'Preset dokumen dan file terkait berhasil dihapus.');

        $this->assertDatabaseMissing('document_presets', ['id' => $preset->id]);
        $this->assertDatabaseMissing('document_preset_files', ['id' => $asset->id]);
        $this->assertDatabaseMissing('document_preset_files', ['id' => $export->id]);
        $this->assertDatabaseHas('document_presets', ['id' => $otherPreset->id]);
        Storage::disk('local')->assertMissing([$asset->storage_path, $exportPath]);
    }

    public function test_admin_cannot_delete_a_preset_owned_by_another_school(): void
    {
        $school = $this->activeSchool('DAHU');
        $otherSchool = $this->activeSchool('MAAN');
        $admin = $this->userWithRole('admin', $school);
        $otherPreset = $this->createPreset($otherSchool, 'Preset sekolah lain');

        $this->actingAs($admin)
            ->delete(route('document-presets.destroy', $otherPreset))
            ->assertNotFound();

        $this->assertDatabaseHas('document_presets', ['id' => $otherPreset->id]);
    }

    public function test_teacher_cannot_delete_a_saved_document_preset(): void
    {
        $school = $this->activeSchool('DAHU');
        $teacher = $this->userWithRole('guru', $school);
        $preset = $this->createPreset($school, 'Preset tersimpan');

        $this->actingAs($teacher)
            ->delete(route('document-presets.destroy', $preset))
            ->assertForbidden();

        $this->assertDatabaseHas('document_presets', ['id' => $preset->id]);
    }

    public function test_superadmin_can_view_only_presets_for_the_selected_school_and_active_competition(): void
    {
        $school = $this->activeSchool('DAHU');
        $otherSchool = $this->activeSchool('MAAN');
        $superAdmin = $this->userWithRole('superadmin');
        $preset = $this->createPreset($school, 'Preset DAHU');
        $this->createPreset($otherSchool, 'Preset MAAN');

        $this->actingAs($superAdmin)
            ->get(route('super-admin.setting-dokumen', [
                'school_id' => $school->id,
                'program' => 'adiwiyata',
                'preset' => $preset->id,
            ]))
            ->assertOk()
            ->assertSee('Preset DAHU')
            ->assertDontSee('Preset MAAN')
            ->assertSee('Sekolah')
            ->assertSee('Perlombaan aktif')
            ->assertSee('data-document-editor', false)
            ->assertSee('Canvas dokumen A4')
            ->assertSee('Buka canvas')
            ->assertSee('Buat template sekolah')
            ->assertSee('file HTML sekolah')
            ->assertSee('Hapus preset ini')
            ->assertDontSee('data-template-builder', false)
            ->assertDontSee('data-template-apply', false);
    }

    public function test_presets_are_not_opened_until_the_user_selects_one_at_the_end_of_the_page(): void
    {
        $school = $this->activeSchool('DAHU');
        $admin = $this->userWithRole('admin', $school);
        $this->createPreset($school, 'Preset Tersimpan');

        $this->actingAs($admin)
            ->get(route('admin.setting-dokumen', ['program' => 'adiwiyata']))
            ->assertOk()
            ->assertSee('Lihat preset yang sudah dibuat')
            ->assertSee('Pilih preset untuk dilihat')
            ->assertDontSee('data-document-editor', false);
    }

    public function test_superadmin_can_create_a_preset_for_the_selected_school(): void
    {
        $school = $this->activeSchool('DAHU');
        $superAdmin = $this->userWithRole('superadmin');

        $this->actingAs($superAdmin)
            ->post(route('document-presets.store'), [
                'school_id' => $school->id,
                'program' => 'adiwiyata',
                'name' => 'Preset oleh Superadmin',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('document_presets', [
            'school_id' => $school->id,
            'program' => 'adiwiyata',
            'name' => 'Preset oleh Superadmin',
            'created_by' => $superAdmin->id,
        ]);
    }

    public function test_admin_can_create_a_school_template_as_a_saved_html_file_separate_from_the_canvas(): void
    {
        Storage::fake('local');
        $school = $this->activeSchool('DAHU');
        $admin = $this->userWithRole('admin', $school);

        $this->actingAs($admin)
            ->post(route('document-presets.templates.store'), [
                'program' => 'adiwiyata',
                'name' => 'Cover Adiwiyata DAHU',
                'template_key' => 'cover-journal',
            ])
            ->assertRedirect();

        $preset = DocumentPreset::query()->where('name', 'Cover Adiwiyata DAHU')->firstOrFail();
        $this->assertSame('school_template', $preset->preset_type);
        $this->assertSame('cover-journal', $preset->template_key);
        $this->assertSame('Daarul Huda', $preset->template_data['school_name']);
        $this->assertSame([], $preset->canvas_data['pages']);

        $templateFile = $preset->files()->where('kind', 'school_template')->firstOrFail();
        $this->assertSame('text/html', $templateFile->mime_type);
        Storage::disk('local')->assertExists($templateFile->storage_path);
        $this->assertStringContainsString('Daarul Huda', Storage::disk('local')->get($templateFile->storage_path));

        $this->actingAs($admin)
            ->get(route('document-presets.canvas', $preset))
            ->assertNotFound();
    }

    public function test_admin_can_customize_a_school_template_and_store_escaped_html_and_uploaded_images(): void
    {
        Storage::fake('local');
        $school = $this->activeSchool('DAHU');
        $admin = $this->userWithRole('admin', $school);
        $preset = $school->documentPresets()->create([
            'created_by' => $admin->id,
            'program' => 'adiwiyata',
            'preset_type' => 'school_template',
            'template_key' => 'cover-journal',
            'name' => 'Cover Awal',
            'components' => ['cover'],
            'canvas_data' => ['version' => 1, 'pages' => []],
            'template_data' => [
                'logo' => null,
                'school_name' => 'Daarul Huda',
                'title' => 'Laporan Awal',
                'date' => '',
                'subtitle' => '',
                'author' => '',
            ],
        ]);
        $presetFile = $preset->files()->create([
            'uploaded_by' => $admin->id,
            'kind' => 'school_template',
            'disk' => 'local',
            'storage_path' => 'document-presets/placeholder/template.html',
            'original_name' => 'cover-awal.html',
            'mime_type' => 'text/html',
            'size' => 0,
        ]);

        $this->actingAs($admin)
            ->patch(route('document-presets.templates.update', $preset), [
                'name' => 'Cover Final',
                'template_data' => [
                    'logo' => '',
                    'school_name' => '<script>alert("x")</script>',
                    'title' => 'Laporan Final',
                    'date' => '3 November 2026',
                    'subtitle' => 'Adiwiyata',
                    'author' => 'Tim Sekolah',
                ],
                'template_uploads' => [
                    'logo' => UploadedFile::fake()->image('logo.png'),
                ],
            ])
            ->assertRedirect();

        $preset->refresh();
        $this->assertSame('Cover Final', $preset->name);
        $this->assertSame('<script>alert("x")</script>', $preset->template_data['school_name']);
        $this->assertDatabaseHas('document_preset_files', [
            'document_preset_id' => $preset->id,
            'kind' => 'asset',
            'original_name' => 'logo.png',
        ]);
        $templateFile = $preset->files()->where('kind', 'school_template')->firstOrFail();
        Storage::disk('local')->assertExists($templateFile->storage_path);
        $html = Storage::disk('local')->get($templateFile->storage_path);
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert("x")</script>', $html);
        $this->assertNotSame($presetFile->storage_path, $templateFile->storage_path);

        $this->actingAs($admin)
            ->get(route('document-presets.templates.show', $preset))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertHeader('Content-Security-Policy', "sandbox; default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; frame-ancestors 'self'")
            ->assertSee('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', false);
    }

    public function test_admin_cannot_customize_another_schools_template(): void
    {
        $school = $this->activeSchool('DAHU');
        $otherSchool = $this->activeSchool('MAAN');
        $admin = $this->userWithRole('admin', $school);
        $preset = $otherSchool->documentPresets()->create([
            'program' => 'adiwiyata',
            'preset_type' => 'school_template',
            'template_key' => 'footer-classic',
            'name' => 'Footer sekolah lain',
            'components' => ['footer'],
            'canvas_data' => ['version' => 1, 'pages' => []],
            'template_data' => ['footer_text' => 'Teks lama'],
        ]);

        $this->actingAs($admin)
            ->patch(route('document-presets.templates.update', $preset), [
                'name' => 'Diubah admin luar',
                'template_data' => ['footer_text' => 'Teks baru'],
            ])
            ->assertNotFound();

        $this->assertSame('Footer sekolah lain', $preset->fresh()->name);
        $this->assertSame('Teks lama', $preset->fresh()->template_data['footer_text']);
    }

    public function test_admin_can_open_the_canvas_for_an_active_school_preset(): void
    {
        $school = $this->activeSchool('DAHU');
        $admin = $this->userWithRole('admin', $school);
        $preset = $this->createPreset($school, 'Preset Sekolah');

        $this->actingAs($admin)
            ->get(route('document-presets.canvas', $preset))
            ->assertOk()
            ->assertSee('Canvas Dokumen')
            ->assertSee('Simpan')
            ->assertSee('Ekspor PNG')
            ->assertSee('Susunan elemen');
    }

    public function test_admin_cannot_create_a_preset_for_an_inactive_competition(): void
    {
        $school = School::query()->create(['name' => 'Daarul Huda', 'code' => 'DAHU']);
        $admin = $this->userWithRole('admin', $school);

        $this->actingAs($admin)
            ->post(route('document-presets.store'), [
                'program' => 'adiwiyata',
                'name' => 'Preset Nonaktif',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('document_presets', ['name' => 'Preset Nonaktif']);
    }

    public function test_teacher_cannot_create_a_document_preset(): void
    {
        $school = $this->activeSchool('DAHU');
        $teacher = $this->userWithRole('guru', $school);

        $this->actingAs($teacher)
            ->post(route('document-presets.store'), [
                'program' => 'adiwiyata',
                'name' => 'Preset Terlarang',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('document_presets', ['name' => 'Preset Terlarang']);
    }

    public function test_admin_cannot_open_another_schools_canvas(): void
    {
        $school = $this->activeSchool('DAHU');
        $otherSchool = $this->activeSchool('MAAN');
        $admin = $this->userWithRole('admin', $school);
        $otherPreset = $this->createPreset($otherSchool, 'Preset Sekolah Lain');

        $this->actingAs($admin)
            ->get(route('document-presets.canvas', $otherPreset))
            ->assertNotFound();
    }

    public function test_admin_can_upload_and_retrieve_an_image_asset_from_private_storage(): void
    {
        Storage::fake('local');
        $school = $this->activeSchool('DAHU');
        $admin = $this->userWithRole('admin', $school);
        $preset = $this->createPreset($school, 'Preset Sekolah');

        $response = $this->actingAs($admin)
            ->postJson(route('document-presets.assets.store', $preset), [
                'file' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertCreated()
            ->assertJsonStructure(['id', 'name', 'url']);

        $asset = DocumentPresetFile::query()->findOrFail($response->json('id'));
        $this->assertSame('asset', $asset->kind);
        $this->assertSame($preset->id, $asset->document_preset_id);
        Storage::disk('local')->assertExists($asset->storage_path);

        $this->actingAs($admin)
            ->get(route('document-presets.files.show', [$preset, $asset]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_admin_can_delete_an_unused_image_asset_from_their_preset(): void
    {
        Storage::fake('local');
        $school = $this->activeSchool('DAHU');
        $admin = $this->userWithRole('admin', $school);
        $preset = $this->createPreset($school, 'Preset Sekolah');
        $asset = $this->createAsset($preset);

        $this->actingAs($admin)
            ->deleteJson(route('document-presets.assets.destroy', [$preset, $asset]))
            ->assertOk()
            ->assertJsonPath('message', 'Gambar berhasil dihapus.');

        $this->assertDatabaseMissing('document_preset_files', ['id' => $asset->id]);
        Storage::disk('local')->assertMissing($asset->storage_path);
    }

    public function test_admin_cannot_delete_an_image_asset_still_used_by_a_saved_canvas(): void
    {
        Storage::fake('local');
        $school = $this->activeSchool('DAHU');
        $admin = $this->userWithRole('admin', $school);
        $preset = $this->createPreset($school, 'Preset Sekolah');
        $asset = $this->createAsset($preset);
        $preset->update([
            'canvas_data' => [
                'pages' => [[
                    'canvas' => [
                        'objects' => [[
                            'type' => 'image',
                            'presetAssetId' => $asset->id,
                        ]],
                    ],
                ]],
            ],
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('document-presets.assets.destroy', [$preset, $asset]))
            ->assertConflict()
            ->assertJsonPath('message', 'Gambar masih dipakai di canvas. Hapus gambar dari semua halaman dan simpan preset terlebih dahulu.');

        $this->assertDatabaseHas('document_preset_files', ['id' => $asset->id]);
        Storage::disk('local')->assertExists($asset->storage_path);
    }

    public function test_admin_cannot_delete_another_schools_image_asset(): void
    {
        Storage::fake('local');
        $school = $this->activeSchool('DAHU');
        $otherSchool = $this->activeSchool('MAAN');
        $admin = $this->userWithRole('admin', $school);
        $otherPreset = $this->createPreset($otherSchool, 'Preset Sekolah Lain');
        $otherAsset = $this->createAsset($otherPreset);

        $this->actingAs($admin)
            ->deleteJson(route('document-presets.assets.destroy', [$otherPreset, $otherAsset]))
            ->assertNotFound();

        $this->assertDatabaseHas('document_preset_files', ['id' => $otherAsset->id]);
    }

    public function test_saved_canvas_uses_only_assets_belonging_to_its_preset(): void
    {
        Storage::fake('local');
        $school = $this->activeSchool('DAHU');
        $admin = $this->userWithRole('admin', $school);
        $preset = $this->createPreset($school, 'Preset Sekolah');
        $otherPreset = $this->createPreset($school, 'Preset Lain');
        $asset = $this->createAsset($preset);
        $otherAsset = $this->createAsset($otherPreset);
        $canvasData = [
            'version' => 1,
            'pages' => [[
                'id' => 'page-1',
                'section' => 'cover',
                'template' => 'cover-journal',
                'listedInOther' => true,
                'canvas' => [
                    'objects' => [[
                        'type' => 'image',
                        'presetAssetId' => $asset->id,
                        'src' => 'https://example.invalid/image.png',
                    ]],
                ],
            ]],
        ];

        $this->actingAs($admin)
            ->patchJson(route('document-presets.update', $preset), [
                'name' => $preset->name,
                'components' => ['cover', 'body'],
                'canvas_data' => json_encode($canvasData, JSON_THROW_ON_ERROR),
            ])
            ->assertOk();

        $preset->refresh();
        $savedImage = $preset->canvas_data['pages'][0]['canvas']['objects'][0];
        $this->assertSame('cover', $preset->canvas_data['pages'][0]['section']);
        $this->assertSame('cover-journal', $preset->canvas_data['pages'][0]['template']);
        $this->assertTrue($preset->canvas_data['pages'][0]['listedInOther']);
        $this->assertSame($asset->id, $savedImage['presetAssetId']);
        $this->assertSame(route('document-presets.files.show', [$preset, $asset]), $savedImage['src']);
        $this->assertNotSame($otherAsset->id, $savedImage['presetAssetId']);
    }

    public function test_admin_cannot_save_an_image_from_a_different_preset(): void
    {
        Storage::fake('local');
        $school = $this->activeSchool('DAHU');
        $admin = $this->userWithRole('admin', $school);
        $preset = $this->createPreset($school, 'Preset Sekolah');
        $otherPreset = $this->createPreset($school, 'Preset Lain');
        $otherAsset = $this->createAsset($otherPreset);
        $canvasData = [
            'pages' => [[
                'canvas' => [
                    'objects' => [[
                        'type' => 'image',
                        'presetAssetId' => $otherAsset->id,
                        'src' => 'https://example.invalid/image.png',
                    ]],
                ],
            ]],
        ];

        $this->actingAs($admin)
            ->patchJson(route('document-presets.update', $preset), [
                'name' => $preset->name,
                'components' => ['cover'],
                'canvas_data' => json_encode($canvasData, JSON_THROW_ON_ERROR),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('canvas_data');

        $this->assertSame([], $preset->fresh()->canvas_data['pages'][0]['canvas']['objects']);
    }

    public function test_admin_cannot_upload_svg_as_a_canvas_asset(): void
    {
        Storage::fake('local');
        $school = $this->activeSchool('DAHU');
        $admin = $this->userWithRole('admin', $school);
        $preset = $this->createPreset($school, 'Preset Sekolah');

        $this->actingAs($admin)
            ->postJson(route('document-presets.assets.store', $preset), [
                'file' => UploadedFile::fake()->create('logo.svg', 1, 'image/svg+xml'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertDatabaseMissing('document_preset_files', [
            'document_preset_id' => $preset->id,
        ]);
    }

    public function test_admin_can_export_a_canvas_page_to_png_and_preview_it_later(): void
    {
        Storage::fake('local');
        $school = $this->activeSchool('DAHU');
        $admin = $this->userWithRole('admin', $school);
        $preset = $this->createPreset($school, 'Preset Sekolah');

        $response = $this->actingAs($admin)
            ->postJson(route('document-presets.exports.store', $preset), [
                'page' => 1,
                'file' => UploadedFile::fake()->image('page.png'),
            ])
            ->assertCreated()
            ->assertJsonStructure(['id', 'url', 'name']);

        $export = DocumentPresetFile::query()->findOrFail($response->json('id'));
        $this->assertSame('export_png', $export->kind);
        $this->assertSame(['page' => 1], $export->metadata);
        Storage::disk('local')->assertExists($export->storage_path);

        $this->actingAs($admin)
            ->get(route('admin.setting-dokumen', [
                'program' => 'adiwiyata',
                'preset' => $preset->id,
            ]))
            ->assertOk()
            ->assertSee(route('document-presets.files.show', [$preset, $export]), false);
    }

    private function activeSchool(string $code): School
    {
        $school = School::query()->create([
            'name' => $code === 'DAHU' ? 'Daarul Huda' : 'Mathlaul Anwar',
            'code' => $code,
        ]);
        $school->competitionSettings()->create([
            'program' => 'adiwiyata',
            'enabled' => true,
        ]);

        return $school;
    }

    private function userWithRole(string $role, ?School $school = null): User
    {
        Role::query()->firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user = User::factory()->create(['school_id' => $school?->id]);
        $user->assignRole($role);

        return $user;
    }

    private function createPreset(School $school, string $name): DocumentPreset
    {
        return $school->documentPresets()->create([
            'program' => 'adiwiyata',
            'name' => $name,
            'components' => ['cover', 'header', 'body', 'footer', 'signature', 'other'],
            'canvas_data' => [
                'pages' => [[
                    'id' => 'page-1',
                    'canvas' => ['objects' => []],
                ]],
            ],
        ]);
    }

    private function createAsset(DocumentPreset $preset): DocumentPresetFile
    {
        $path = UploadedFile::fake()->image('asset.png')->store('document-assets', 'local');

        return $preset->files()->create([
            'kind' => 'asset',
            'disk' => 'local',
            'storage_path' => $path,
            'original_name' => 'asset.png',
            'mime_type' => 'image/png',
            'size' => 100,
        ]);
    }
}
