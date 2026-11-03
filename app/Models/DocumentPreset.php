<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentPreset extends Model
{
    protected $fillable = [
        'school_id',
        'created_by',
        'program',
        'preset_type',
        'name',
        'components',
        'canvas_data',
        'template_key',
        'template_data',
    ];

    protected function casts(): array
    {
        return [
            'components' => 'array',
            'canvas_data' => 'array',
            'template_data' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files(): HasMany
    {
        return $this->hasMany(DocumentPresetFile::class);
    }
}
