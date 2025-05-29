<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users');
            $table->date('date');
            $table->Time('clock_in_time')->nullable();
            $table->Time('clock_out_time')->nullable();
            $table->foreignId('location_type_id')->nullable();
            $table->string('gps_coordinates')->nullable();
            $table->foreignId('status_id')->constrained('attendance_status');
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
