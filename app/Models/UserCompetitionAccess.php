<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCompetitionAccess extends Model
{
    protected $table = 'user_competition_access';

    protected $fillable = ['user_id', 'program', 'module_key'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
