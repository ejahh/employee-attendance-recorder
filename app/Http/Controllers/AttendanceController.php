<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    /**
     * Get all attendance records for a specific employee, or for a specific date if provided.
     */
    public function byEmployee(Request $request, $employee_id)
    {
        $query = Attendance::where('employee_id', $employee_id);
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->query('date'));
        }
        return $query->get();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Attendance::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'time_in_AM' => ['required', 'regex:/^(1[0-2]|[1-9]):[0-5][0-9] (AM|PM)$/'],
            'time_out_AM' => ['required', 'regex:/^(1[0-2]|[1-9]):[0-5][0-9] (AM|PM)$/'],
            'time_in_PM' => ['required', 'regex:/^(1[0-2]|[1-9]):[0-5][0-9] (AM|PM)$/'],
            'time_out_PM' => ['required', 'regex:/^(1[0-2]|[1-9]):[0-5][0-9] (AM|PM)$/'],
            'status' => 'required|string',
        ]);
        // Convert to H:i:s for DB
        $validated['time_in_AM'] = date('H:i:s', strtotime($validated['time_in_AM']));
        $validated['time_out_AM'] = date('H:i:s', strtotime($validated['time_out_AM']));
        $validated['time_in_PM'] = date('H:i:s', strtotime($validated['time_in_PM']));
        $validated['time_out_PM'] = date('H:i:s', strtotime($validated['time_out_PM']));
        $attendance = Attendance::create($validated);
        $employee = $attendance->employee;
        return response()->json([
            'attendance' => $attendance,
            'employee_name' => $employee ? trim($employee->first_name . ' ' . ($employee->middle_name ? $employee->middle_name . ' ' : '') . $employee->last_name) : null,
            'date' => $attendance->created_at ? $attendance->created_at->toDateString() : null,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Attendance::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $attendance = Attendance::findOrFail($id);
        $validated = $request->validate([
            'employee_id' => 'sometimes|required|exists:employees,id',
            'time_in_AM' => ['sometimes','required','regex:/^(1[0-2]|[1-9]):[0-5][0-9] (AM|PM)$/'],
            'time_out_AM' => ['sometimes','required','regex:/^(1[0-2]|[1-9]):[0-5][0-9] (AM|PM)$/'],
            'time_in_PM' => ['sometimes','required','regex:/^(1[0-2]|[1-9]):[0-5][0-9] (AM|PM)$/'],
            'time_out_PM' => ['sometimes','required','regex:/^(1[0-2]|[1-9]):[0-5][0-9] (AM|PM)$/'],
            'status' => 'sometimes|required|string',
        ]);
        // Convert to H:i:s for DB if present
        foreach (['time_in_AM', 'time_out_AM', 'time_in_PM', 'time_out_PM'] as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = date('H:i:s', strtotime($validated[$field]));
            }
        }
        $attendance->update($validated);
        return $attendance;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();
        return response()->json(['message' => 'Attendance record deleted successfully']);
    }
}
