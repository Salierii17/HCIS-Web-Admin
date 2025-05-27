<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tryout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'duration',
        'started_at',
        'finished_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function tryOutAnswers()
    {
        return $this->hasMany(TryoutAnswer::class);
    }
}
