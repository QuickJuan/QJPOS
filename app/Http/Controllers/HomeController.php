<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();


        // Get attendance status for the current user (you may need to implement this based on your attendance model)
        $attendanceStatus = [
            'is_clocked_in' => false, // This should be fetched from your attendance system
            'last_clock_in' => null,
            'last_clock_out' => null,
        ];

        return Inertia::render('Home', [
            'user' => $user,
            'attendanceStatus' => $attendanceStatus,
        ]);
    }
}
