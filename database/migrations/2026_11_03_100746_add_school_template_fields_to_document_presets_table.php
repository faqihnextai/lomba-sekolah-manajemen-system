<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('document_presets', function (Blueprint $table) {
            Schema::table('document_presets', function (Blueprint $table) {
                $table->string('preset_type', 30)->default('canvas')->after('program');
                $table->string('template_key', 100)->nullable()->after('preset_type');
                $table->json('template_data')->nullable()->after('canvas_data');

                $table->index(['school_id', 'program', 'preset_type']);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_presets', function (Blueprint $table) {
            Schema::table('document_presets', function (Blueprint $table) {
                $table->dropIndex(['school_id', 'program', 'preset_type']);
                $table->dropColumn(['preset_type', 'template_key', 'template_data']);
            });
        });
    }
};
