<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Rules\UserBelongsToBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AttendanceController extends Controller
{
    /**
     * Display the attendance page
     */
    public function index()
    {
        $activeBranch = session('active_branch');

        return Inertia::render('Attendance/Index', [
            'activeBranch' => $activeBranch,
        ]);
    }

    /**
     * Handle clock in/out process
     */
    public function toggle(Request $request)
    {
        $activeBranch = session('active_branch');
        
        // Check if user has a branch selected
        if (!$activeBranch) {
            return back()->withErrors([
                'message' => 'Please select a branch before clocking in/out.'
            ]);
        }

        $request->validate([
            'employee_code' => [
                'required', 
                'string',
                new UserBelongsToBranch($activeBranch['id'])
            ],
            'photo' => 'required|string', // base64 encoded image
        ]);
        
        // Find user by employee code (validation already confirmed user exists and belongs to branch)
        $user = User::where('employee_code', $request->employee_code)->first();

        // Check if user is currently clocked in at this branch
        $currentAttendance = Attendance::where('user_id', $user->id)
            ->where('branch_id', $activeBranch['id'])
            ->whereNull('actual_timeout')
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        try {
            if ($currentAttendance) {
                // Clock out - check if 5 minutes have passed since clock in
                $clockInTime = Carbon::parse($currentAttendance->actual_timein);
                $now = Carbon::now();
                $minutesPassed = $clockInTime->diffInMinutes($now);
                
                if ($minutesPassed < 5) {
                    $remainingMinutes = 5 - $minutesPassed;
                    
                    return back()->withErrors([
                        'message' => "You must wait at least 5 minutes before clocking out. Please wait {$remainingMinutes} more minute(s)."
                    ]);
                }
                
                // Clock out - update actual_timeout
                $currentAttendance->update([
                    'actual_timeout' => Carbon::now(),
                ]);

                // Save clock out photo
                try {
                    $this->saveAttendancePhoto($request->photo, $currentAttendance, 'clock_out');
                } catch (\Exception $photoError) {
                    \Log::error('Error saving clock out photo: ' . $photoError->getMessage());
                    // Continue without photo if photo saving fails
                }

                return back()->with([
                    'success' => true,
                    'action' => 'clock_out',
                    'message' => "Successfully clocked out {$user->name}.",
                    'attendance' => $currentAttendance->fresh(),
                    'employee' => [
                        'name' => $user->name,
                        'employee_code' => $user->employee_code,
                    ],
                ]);
            } else {
                // Clock in - create new attendance record
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'branch_id' => $activeBranch['id'],
                    'attendance_date' => Carbon::today(),
                    'actual_timein' => Carbon::now(),
                ]);

                // Save clock in photo
                try {
                    $this->saveAttendancePhoto($request->photo, $attendance, 'clock_in');
                } catch (\Exception $photoError) {
                    \Log::error('Error saving clock in photo: ' . $photoError->getMessage());
                    // Continue without photo if photo saving fails
                }

                return back()->with([
                    'success' => true,
                    'action' => 'clock_in',
                    'message' => "Successfully clocked in {$user->name}.",
                    'attendance' => $attendance,
                    'employee' => [
                        'name' => $user->name,
                        'employee_code' => $user->employee_code,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Attendance toggle error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return back()->withErrors([
                'message' => 'An error occurred while processing attendance: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save attendance photo using Spatie Media Library
     */
    private function saveAttendancePhoto($base64Photo, $attendance, $type)
    {
        try {
            // Remove data URL prefix if present
            $photoData = preg_replace('/^data:image\/\w+;base64,/', '', $base64Photo);
            $photoData = base64_decode($photoData);

            if (!$photoData) {
                throw new \Exception('Invalid photo data provided');
            }

            // Create a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'attendance_photo');
            if (!$tempFile) {
                throw new \Exception('Failed to create temporary file');
            }

            $bytesWritten = file_put_contents($tempFile, $photoData);
            if ($bytesWritten === false) {
                throw new \Exception('Failed to write photo data to temporary file');
            }

            // Generate filename
            $filename = "{$type}_" . Carbon::now()->format('Y_m_d_H_i_s') . '.jpg';

            // Add photo to media collection with proper collection name
            $media = $attendance->addMedia($tempFile)
                ->usingName($filename)
                ->usingFileName($filename)
                ->toMediaCollection($type === 'clock_in' ? 'clock_in_photos' : 'clock_out_photos');

            // Clean up temporary file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }

            return $media;
        } catch (\Exception $e) {
            // Clean up temporary file if it exists
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            \Log::error('Photo saving error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get attendance history
     */
    public function history()
    {
        $user = Auth::user();
        
        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('attendance_date', 'desc')
            ->orderBy('actual_timein', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'attendances' => $attendances
        ]);
    }

    /**
     * Check employee status by employee code
     */
    public function checkStatus(Request $request)
    {
        $activeBranch = session('active_branch');
        
        // Check if user has a branch selected
        if (!$activeBranch) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a branch before checking status.'
            ], 400);
        }

        $request->validate([
            'employee_code' => [
                'required',
                'string',
                new UserBelongsToBranch($activeBranch['id'])
            ],
        ]);

        // Find user by employee code (validation already confirmed user exists and belongs to branch)
        $user = User::where('employee_code', $request->employee_code)->first();

        // Check if user is currently clocked in at this branch
        $currentAttendance = Attendance::where('user_id', $user->id)
            ->where('branch_id', $activeBranch['id'])
            ->whereNull('actual_timeout')
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        $canClockOut = true;
        $timeUntilClockOut = null;
        
        if ($currentAttendance) {
            $clockInTime = Carbon::parse($currentAttendance->actual_timein);
            $now = Carbon::now();
            $minutesPassed = $clockInTime->diffInMinutes($now);
            
            if ($minutesPassed < 5) {
                $canClockOut = false;
                $remainingMinutes = 5 - $minutesPassed;
                $totalSecondsRemaining = (5 * 60) - $clockInTime->diffInSeconds($now);
                
                $timeUntilClockOut = [
                    'minutes' => $remainingMinutes,
                    'seconds' => $totalSecondsRemaining % 60,
                    'total_seconds' => $totalSecondsRemaining
                ];
            }
        }

        return response()->json([
            'success' => true,
            'employee' => [
                'name' => $user->name,
                'employee_code' => $user->employee_code,
            ],
            'isClockedIn' => $currentAttendance ? true : false,
            'currentAttendance' => $currentAttendance,
            'canClockOut' => $canClockOut,
            'timeUntilClockOut' => $timeUntilClockOut,
        ]);
    }

    /**
     * Get today's attendance records for the current branch
     */
    public function today()
    {
        $activeBranch = session('active_branch');
        
        if (!$activeBranch) {
            return response()->json([
                'success' => false,
                'message' => 'No active branch selected'
            ], 400);
        }

        $today = Carbon::today();
        
        $attendance = Attendance::with('user')
            ->whereDate('attendance_date', $today)
            ->whereHas('user', function ($query) use ($activeBranch) {
                $query->where('branch_id', $activeBranch['id']);
            })
            ->orderBy('actual_timein', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'attendance' => $attendance
        ]);
    }
}
