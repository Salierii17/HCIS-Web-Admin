<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => 1, // Default to user 1
            'date' => $this->faker->date(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '17:00:00',
            'location_type_id' => 1,
            'status_id' => 1, // Default to 'Present' before observer runs
            'approval_status' => 'In Progress',
            'notes' => 'Seeded record',
        ];
    }
}
