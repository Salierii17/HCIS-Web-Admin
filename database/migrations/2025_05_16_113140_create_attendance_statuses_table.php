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
        Schema::create('attendance_statuses', function (Blueprint $table) {
            $table->id();
<<<<<<< HEAD
            $table->enum('status', ['Present', 'Absent', 'Half Day', 'Late']);
=======
            $table->enum('status', ['Present', 'Late']);
            // $table->string('status');
>>>>>>> origin/training
            $table->timestamps();
        });

        DB::table('attendance_statuses')->truncate();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_statuses');
    }
};
