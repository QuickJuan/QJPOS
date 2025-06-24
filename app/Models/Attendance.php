<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'attendance_date',
        'schedule_timein',
        'schedule_timeout',
        'actual_timein',
        'actual_timeout',
    ];

    protected $casts = [
        'attendance_date'  => 'date',
        'schedule_timein'  => 'datetime',
        'schedule_timeout' => 'datetime',
        'actual_timein'    => 'datetime',
        'actual_timeout'   => 'datetime', 
    ];
}
