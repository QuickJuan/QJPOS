<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLeaveCredit;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserLeaveController extends Controller
{
    public function index(Request $request)
    {
        $employee = $request->user()?->employee;

        $leaveRequests = $employee
            ? LeaveRequest::with('leaveType')
                ->where('employee_id', $employee->id)
                ->orderByDesc('start_date')
                ->paginate(15)
                ->through(fn ($r) => [
                    'id'               => $r->id,
                    'leave_type'       => $r->leaveType?->name,
                    'leave_type_code'  => $r->leaveType?->code,
                    'start_date'       => $r->start_date?->format('M d, Y'),
                    'end_date'         => $r->end_date?->format('M d, Y'),
                    'days_requested'   => (float) $r->days_requested,
                    'days_with_pay'    => (float) ($r->days_with_pay ?? 0),
                    'days_without_pay' => (float) ($r->days_without_pay ?? 0),
                    'is_half_day'      => (bool) $r->is_half_day,
                    'status'           => $r->status,
                    'reason'           => $r->reason,
                    'admin_notes'      => $r->admin_notes,
                    'created_at'       => $r->created_at?->format('M d, Y'),
                ])
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

        $leaveCredits = $employee
            ? EmployeeLeaveCredit::with('leaveType')
                ->where('employee_id', $employee->id)
                ->where('year', now()->year)
                ->orderBy('id')
                ->get()
                ->map(fn ($c) => [
                    'id'              => $c->id,
                    'leave_type'      => $c->leaveType?->name,
                    'leave_type_code' => $c->leaveType?->code,
                    'total_days'      => (float) $c->total_days,
                    'used_days'       => (float) $c->used_days,
                    'remaining_days'  => max(0, (float) $c->total_days - (float) $c->used_days),
                ])
            : [];

        $leaveTypes = LeaveType::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code', 'default_days_per_year', 'requires_document']);

        return Inertia::render('User/Leaves', [
            'leaveRequests' => $leaveRequests,
            'leaveCredits'  => $leaveCredits,
            'leaveTypes'    => $leaveTypes,
        ]);
    }

    public function store(Request $request)
    {
        $employee = $request->user()?->employee;

        if (! $employee) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'employee_id' => 'Your account does not have an employee profile linked. Please contact HR.',
            ]);
        }

        $validated = $request->validate([
            'leave_type_id'  => ['required', 'exists:leave_types,id'],
            'start_date'     => ['required', 'date'],
            'end_date'       => ['required', 'date', 'gte:start_date'],
            'days_requested' => ['required', 'numeric', 'min:0.5'],
            'is_half_day'    => ['boolean'],
            'reason'         => ['nullable', 'string', 'max:1000'],
        ]);

        LeaveRequest::create([
            'employee_id'    => $employee->id,
            'leave_type_id'  => $validated['leave_type_id'],
            'start_date'     => $validated['start_date'],
            'end_date'       => $validated['end_date'],
            'days_requested' => $validated['days_requested'],
            'is_half_day'    => $validated['is_half_day'] ?? false,
            'reason'         => $validated['reason'] ?? null,
            'status'         => 'pending',
        ]);

        return back()->with('success', 'Leave request submitted successfully. Please wait for admin approval.');
    }
}
