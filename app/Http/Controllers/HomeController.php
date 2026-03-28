<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $activeBranch = $this->resolveActiveBranch();

        $todayAttendance = null;

        if ($activeBranch) {
            $todayAttendance = Attendance::query()
                ->where('user_id', $user->id)
                ->where('branch_id', $activeBranch['id'])
                ->whereDate('attendance_date', Carbon::today())
                ->latest('actual_timein')
                ->first();
        }

        $attendanceStatus = [
            'is_clocked_in' => (bool) ($todayAttendance && ! $todayAttendance->actual_timeout),
            'last_clock_in' => $todayAttendance?->actual_timein,
            'last_clock_out' => $todayAttendance?->actual_timeout,
        ];

        return Inertia::render('Home', [
            'user' => $user,
            'attendanceStatus' => $attendanceStatus,
        ]);
    }

    private function resolveActiveBranch(): ?array
    {
        $sessionBranch = session('active_branch');

        if (is_array($sessionBranch) && ! empty($sessionBranch['id'])) {
            return $sessionBranch;
        }

        $user = Auth::user();

        if (! $user?->branch_id) {
            return null;
        }

        $branch = Branch::find($user->branch_id);

        if (! $branch) {
            return null;
        }

        session(['active_branch' => $branch->toArray()]);

        return $branch->toArray();
    }
}
