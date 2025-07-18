<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceApproval extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    // Relationship to the employee who made the request
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }
}