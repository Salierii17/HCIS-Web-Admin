<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->enum('status', ['Present', 'Absent', 'Half Day', 'Late']);
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
