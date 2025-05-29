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
    public function index(Request $request)
    {
        try {
            // Basic pagination, e.g., 15 records per page
            // You can also add request validation for query parameters like 'employee_id', 'start_date', 'end_date'
            $query = Attendance::with(['employee:id,name', 'locationType:id,arrangement_type', 'status:id,status']); // Eager load relationships

            // Example: Filter by employee_id if provided in the request
            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->input('employee_id'));
            }

            // Example: Filter by date if provided
            if ($request->has('date')) {
                $query->where('date', $request->input('date'));
            }

            // Example: Filter by a date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
            }

            // Order by date and then by clock_in_time by default
            $attendances = $query->orderBy('date', 'desc')->orderBy('clock_in_time', 'asc')->paginate(15);

            // Transform the data to better match the frontend's AttendanceLogItem
            $transformedAttendances = $attendances->through(function ($attendance) {
                return [
                    'id' => (string) $attendance->id, // Ensure ID is a string
                    'date' => $attendance->date->format('Y-m-d'), // Format date as string
                    'checkInTime' => $attendance->clock_in_time ? $attendance->clock_in_time->format('H:i:s') : null,
                    'checkOutTime' => $attendance->clock_out_time ? $attendance->clock_out_time->format('H:i:s') : null,
                    'status' => $attendance->status ? $attendance->status->status : null, // Assuming 'name' attribute exists on AttendanceStatus
                    'workArrangement' => $attendance->locationType ? $attendance->locationType->arrangement_type : null, // Assuming 'name' attribute exists on WorkArrangement
                    'workDuration' => $attendance->work_hours ? $this->formatWorkHours($attendance->work_hours) : $this->calculateWorkDuration($attendance->clock_in_time, $attendance->clock_out_time),
                    'employeeName' => $attendance->employee ? $attendance->employee->name : null, // Example of adding related data
                    'gpsCoordinates' => $attendance->gps_coordinates,
                    'notes' => $attendance->notes,
                ];
            });

            return response()->json([
                'status' => 'success',
                'code' => Response::HTTP_OK,
                'message' => 'Attendance records retrieved successfully.',
                'data' => $transformedAttendances,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve attendance records', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve attendance records: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Helper function to format work hours (numeric) into "Xh Ym" format.
     */
    private function formatWorkHours($workHoursDecimal)
    {
        if (is_null($workHoursDecimal) || !is_numeric($workHoursDecimal)) {
            return null;
        }
        $hours = floor($workHoursDecimal);
        $minutes = round(($workHoursDecimal - $hours) * 60);
        return "{$hours}h {$minutes}m";
    }

    /**
     * Helper function to calculate work duration from clock_in_time and clock_out_time.
     */
    private function calculateWorkDuration($clockIn, $clockOut)
    {
        if (!$clockIn || !$clockOut) {
            return null;
        }
        // Assuming $clockIn and $clockOut are Carbon instances (due to casts in the model)
        // If they are strings, you might need to parse them first:
        // $clockIn = \Carbon\Carbon::parse($clockIn);
        // $clockOut = \Carbon\Carbon::parse($clockOut);

        $duration = $clockOut->diff($clockIn);
        return "{$duration->h}h {$duration->i}m";
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
    public function show(Attendance $attendance)
    {
        try {
            $attendance->load(['employee:id,name', 'locationType:id,name', 'status:id,name']);

            return response()->json([
                'status' => 'success',
                'code' => Response::HTTP_OK,
                'message' => 'Attendance record retrieved successfully.',
                'data' => [ // Transform the single record
                    'id' => (string) $attendance->id,
                    'date' => $attendance->date->format('Y-m-d'),
                    'checkInTime' => $attendance->clock_in_time ? $attendance->clock_in_time->format('H:i:s') : null,
                    'checkOutTime' => $attendance->clock_out_time ? $attendance->clock_out_time->format('H:i:s') : null,
                    'status' => $attendance->status ? $attendance->status->status : null,
                    'workArrangement' => $attendance->locationType ? $attendance->locationType->arrangement_type : null,
                    'workDuration' => $attendance->work_hours ? $this->formatWorkHours($attendance->work_hours) : $this->calculateWorkDuration($attendance->clock_in_time, $attendance->clock_out_time),
                    'employeeName' => $attendance->employee ? $attendance->employee->name : null,
                    'gpsCoordinates' => $attendance->gps_coordinates,
                    'notes' => $attendance->notes,
                ],
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Attendance record not found."
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve attendance record with ID {$attendance->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve attendance record: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
    public function update(Request $request, Attendance $attendance)
    {
        Log::info("Clock Out Update request for ID {$attendance->id}");

        try {
            $validated = $request->validate([
                'clock_out_time' => 'required |date_format:H:i:s',
                // 'status_id' => 'nullable|exists:attendance_statuses,id',
                // 'work_hours' => 'nullable|numeric', // Recalculate or allow manual update
            ]);

            Log::info('Validation passed', $validated);


            $attendance->fill($validated);

            $attendance->save();

            $attendance->load(['employee:id,name', 'locationType:id,arrangement_type', 'status:id,status']);


            return response()->json([
                'status' => 'success',
                'code' => Response::HTTP_OK,
                'message' => "Attendance record ID {$attendance->id} updated successfully.",
                'data' => [ // Transform the single record
                    'id' => (string) $attendance->id,
                    'date' => $attendance->date->format('Y-m-d'),
                    'checkInTime' => $attendance->clock_in_time ? $attendance->clock_in_time->format('H:i:s') : null,
                    'checkOutTime' => $attendance->clock_out_time ? $attendance->clock_out_time->format('H:i:s') : null,
                    'status' => $attendance->status ? $attendance->status->status : null,
                    'workArrangement' => $attendance->locationType ? $attendance->locationType->arrangement_type : null,
                    'workDuration' => $attendance->work_hours ? $this->formatWorkHours($attendance->work_hours) : $this->calculateWorkDuration($attendance->clock_in_time, $attendance->clock_out_time),
                    'employeeName' => $attendance->employee ? $attendance->employee->name : null,
                    'gpsCoordinates' => $attendance->gps_coordinates,
                    'notes' => $attendance->notes,
                ],
            ], Response::HTTP_OK);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("Attendance update validation failed for ID {$attendance->id}", [
                'errors' => $e->errors(),
                'body' => $request->all()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Attendance record with ID {$attendance->id} not found."
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error("Attendance update failed for ID {$attendance->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Attendance update failed: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
