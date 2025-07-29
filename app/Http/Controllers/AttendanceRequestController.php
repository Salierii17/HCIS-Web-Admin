<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceApproval;
use Illuminate\Http\Request;

class AttendanceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // --- FIX: Updated validation rules ---
        $validated = $request->validate([
            'attendance_id' => 'required|integer|exists:attendances,id',
            'employee_reason' => 'required|string|max:1000',

            // This rule means the field is optional, but if it's present, it must be in H:i format.
            // It's also required IF AND ONLY IF the other two correction fields are not present.
            'requested_clock_in_time' => [
                'nullable',
                'date_format:H:i',
                'required_without_all:requested_clock_out_time,requested_location_type_id',
            ],

            'requested_clock_out_time' => [
                'nullable',
                'date_format:H:i',
                'required_without_all:requested_clock_in_time,requested_location_type_id',
            ],

            'requested_location_type_id' => [
                'nullable',
                'integer',
                'exists:work_arrangements,id',
                'required_without_all:requested_clock_in_time,requested_clock_out_time',
            ],
        ]);

        $attendance = Attendance::findOrFail($validated['attendance_id']);
        $requestingUser = auth()->user();

        // Security Check
        if ($attendance->employee_id !== $requestingUser->id && $requestingUser->name !== 'Super Admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Business Logic: Prevent duplicate pending requests
        if ($attendance->approval_status === 'Pending Approval') {
            return response()->json(['message' => 'An approval request for this record already exists.'], 409);
        }

        // Create the approval request record using only the validated data
        AttendanceApproval::create($validated + [
            'requested_by_id' => $requestingUser->id,
            'status' => 'pending',
        ]);

        // Update the original attendance record
        $attendance->update(['approval_status' => 'Pending Approval']);

        return response()->json(['message' => 'Correction request submitted successfully.'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AttendanceApproval $attendanceApproval)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AttendanceApproval $attendanceApproval)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttendanceApproval $attendanceApproval)
    {
        //
    }
}
