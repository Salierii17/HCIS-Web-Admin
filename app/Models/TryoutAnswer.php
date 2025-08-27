<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TryoutAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tryout_id',
        'question_id',
        'option_id',
        'score',
    ];
}
