<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{

    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'location_type_id',
        'gps_coordinates',
        'status_id',
        'work_hours',
        'notes'
    ];

    protected $casts = [
        'clock_in_time' => 'datetime:H:i',
        'clock_out_time' => 'datetime:H:i',
        'date' => 'date',
    ];


    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function locationType()
    {
        return $this->belongsTo(WorkArrangement::class, 'location_type_id');
    }

    public function status()
    {
        return $this->belongsTo(AttendanceStatus::class, 'status_id');
    }
}
