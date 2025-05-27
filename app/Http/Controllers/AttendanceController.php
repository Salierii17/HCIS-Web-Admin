<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Attendance request received', [
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ]);

        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'clock_in_time' => 'nullable|date_format:H:i:s',
                'clock_out_time' => 'nullable|date_format:H:i:s',
                'location_type_id' => 'nullable|exists:work_arrangements,id',
                'gps_coordinates' => 'nullable|string',
                'status_id' => 'required|exists:attendance_statuses,id',
                'work_hours' => 'nullable|numeric',
                'notes' => 'nullable|string',
            ]);

            Log::info('Validation passed', $validated);

            $attendance = Attendance::create($validated);

            Log::info('Attendance created', ['id' => $attendance->id]);

            return response()->json([
                'status' => 'success',
                'code' => Response::HTTP_CREATED,
                'message' => 'Attendance record created successfully.',
                'data' => $attendance,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('Attendance creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Log::info('Clock Out Updated', [
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ]);

        try {
            $validated = $request->validate([
                'clock_out_time' => 'required |date_format:H:i:s'
            ]);

            Log::info('Validation passed', $validated);

            $attendance = Attendance::findOrFail($id);

            $attendance->clock_out_time = $request->input("clock_out_time");

            $attendance->save();

            return response()->json([
                'status' => 'success',
                'code' => Response::HTTP_OK,
                'message' => "Clock out for attendance record ID $id is updated",
                "data" => $attendance,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Clock Out Update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
