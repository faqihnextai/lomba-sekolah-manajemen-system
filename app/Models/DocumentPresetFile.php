<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentPresetFile extends Model
{
    protected $fillable = [
        'document_preset_id',
        'uploaded_by',
        'kind',
        'disk',
        'storage_path',
        'original_name',
        'mime_type',
        'size',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function preset(): BelongsTo
    {
        return $this->belongsTo(DocumentPreset::class, 'document_preset_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
