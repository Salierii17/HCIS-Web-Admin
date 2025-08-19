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

    // Always load these relationships
    protected $with = ['user', 'package'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // Helper method to get user email safely
    public function getUserEmailAttribute()
    {
        return $this->user?->email ?? 'N/A';
    }

    // Helper method to get package name safely
    public function getPackageNameAttribute()
    {
        return $this->package?->name ?? 'N/A';
    }
}
