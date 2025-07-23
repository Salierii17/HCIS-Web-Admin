<?php

namespace App\Http\Controllers;

use App\Models\AttendanceApproval;
use Illuminate\Http\Request;

class AttendanceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $validated = $request->validate([
            'attendance_id' => 'required|integer|exists:attendances,id',
            'requested_clock_out_time' => 'required|date_format:H:i',
            'employee_reason' => 'required|string|max:1000',
        ]);

        $attendance = Attendance::findOrFail($validated['attendance_id']);

          // Authorization: Ensure the user is requesting a change for their own attendance.
        if ($attendance->user_id !== auth()->id()) {
            return response()->json(['message' => 'You do not have permission to modify this record.'], 403);
        }

        // Logic: Prevent creating a new request if one is already pending.
        if ($attendance->approval_status === 'Pending Approval') {
             return response()->json(['message' => 'An approval request for this record already exists.'], 409);
        }

        // Create the approval request using the exact columns from your migration
        $approvalRequest = AttendanceApproval::create([
            'attendance_id' => $validated['attendance_id'],
            'requested_by_id' => auth()->id(), // The logged-in employee
            'requested_clock_out_time' => $validated['requested_clock_out_time'],
            'employee_reason' => $validated['employee_reason'],
            'status' => 'pending', // Default status
        ]);

        // Update the original attendance record to show it's under review
        $attendance->update(['approval_status' => 'Pending Approval']);

        return response()->json([
            'message' => 'Correction request submitted successfully.',
            'data' => $approvalRequest
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
