<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'notes',
        'approval_status',
    ];

    protected $casts = [
        'clock_in_time' => 'datetime:H:i',
        'clock_out_time' => 'datetime:H:i',
        'date' => 'date',
        'work_hours' => 'float'
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

    protected function formattedWorkDuration(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                if (!is_null($attributes['work_hours'])) {
                    $workHoursDecimal = $attributes['work_hours'];
                    $hours = floor($workHoursDecimal);
                    $minutes = round(($workHoursDecimal - $hours) * 60);
                    return "{$hours}h {$minutes}m";
                }

                if (!empty($attributes['clock_in_time']) && !empty($attributes['clock_out_time'])) {
                    $clockIn = Carbon::parse($attributes['clock_in_time']);
                    $clockOut = Carbon::parse($attributes['clock_out_time']);

                    if ($clockOut->gt($clockIn)) {
                        $duration = $clockOut->diff($clockIn);
                        return "{$duration->h}h {$duration->i}m";
                    }
                }
                return null;
            }
        );
    }
    protected function GpsCoordinatesArray(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $coordinates = $attributes['gps_coordinates'] ?? null;
                if ($coordinates) {
                    $parts = explode(',', $coordinates);
                    if (count($parts) === 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]))) {
                        return [
                            'latitude' => (float) trim($parts[0]),
                            'longitude' => (float) trim($parts[1]),
                        ];
                    }
                }
                return null;
            }
        );
    }
}
