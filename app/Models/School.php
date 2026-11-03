<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = ['name', 'code'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function competitionSettings(): HasMany
    {
        return $this->hasMany(SchoolCompetitionSetting::class);
    }

    public function documentPresets(): HasMany
    {
        return $this->hasMany(DocumentPreset::class);
    }
}
