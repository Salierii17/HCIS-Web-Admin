<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignTraining extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'deadline',
    ];
    protected $casts = [
    'deadline' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
