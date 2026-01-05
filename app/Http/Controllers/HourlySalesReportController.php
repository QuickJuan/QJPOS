<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class HourlySalesReportController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->user()->branch_id;
        $settings = app(GeneralSettings::class);
        $timezone = $settings->timezone ?? 'Asia/Manila';
        $timezoneOffset = $this->getTimezoneOffset($timezone);

        // Default to current month if not provided
        $month = $request->input('month', now()->format('Y-m'));

        // Query with timezone conversion
        $salesData = DB::select("
            SELECT
                DAYOFWEEK(CONVERT_TZ(o.created_at, '+00:00', ?)) as day_of_week,
                HOUR(CONVERT_TZ(o.created_at, '+00:00', ?)) as hour,
                SUM(oi.sub_total) as total_sales
            FROM orders o
            INNER JOIN order_items oi ON o.id = oi.order_id
            WHERE o.status != 'refund'
                AND oi.is_void = 0
                AND oi.parent_id IS NULL
                AND o.branch_id = ?
                AND DATE_FORMAT(CONVERT_TZ(o.created_at, '+00:00', ?), '%Y-%m') = ?
            GROUP BY
                DAYOFWEEK(CONVERT_TZ(o.created_at, '+00:00', ?)),
                HOUR(CONVERT_TZ(o.created_at, '+00:00', ?))
            ORDER BY HOUR(CONVERT_TZ(o.created_at, '+00:00', ?))
        ", [
            $timezoneOffset, $timezoneOffset, $branchId,
            $timezoneOffset, $month,
            $timezoneOffset, $timezoneOffset, $timezoneOffset
        ]);

        // Transform data into the desired format
        $reportData = $this->transformSalesData(collect($salesData));

        return Inertia::render('Reports/HourlySales', [
            'reportData' => $reportData,
            'selectedMonth' => $month,
            'monthLabel' => Carbon::parse($month . '-01')->format('F Y'),
        ]);
    }

    private function transformSalesData($salesData)
    {
        // Get unique hours from the data, or use default range if no data
        $hoursInData = $salesData->pluck('hour')->unique()->sort()->values();

        if ($hoursInData->isEmpty()) {
            // Default to common business hours if no data
            $hoursInData = collect(range(7, 22));
        }

        // Build hours array dynamically
        $hours = $hoursInData->map(function ($hour) {
            return [
                'time' => Carbon::createFromTime($hour, 0)->format('g:00 A'),
                'hour' => $hour,
            ];
        })->toArray();

        // Days of week (1=Sunday, 2=Monday, ..., 7=Saturday in MySQL DAYOFWEEK)
        $daysMap = [
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday',
            1 => 'Sunday',
        ];

        $result = [];

        foreach ($hours as $index => $hourData) {
            $row = [
                'time' => $hourData['time'],
                'Monday' => 0,
                'Tuesday' => 0,
                'Wednesday' => 0,
                'Thursday' => 0,
                'Friday' => 0,
                'Saturday' => 0,
                'Sunday' => 0,
                'Total' => 0,
            ];

            // Fill in the sales data
            foreach ($salesData as $data) {
                if ($data->hour == $hourData['hour']) {
                    $dayName = $daysMap[$data->day_of_week] ?? null;
                    if ($dayName && isset($row[$dayName])) {
                        $row[$dayName] = round($data->total_sales, 0);
                        $row['Total'] += round($data->total_sales, 0);
                    }
                }
            }

            $result[] = $row;
        }

        return $result;
    }

    private function getTimezoneOffset($timezone)
    {
        try {
            $dt = new \DateTime('now', new \DateTimeZone($timezone));
            $offset = $dt->getOffset();
            $hours = abs($offset) / 3600;
            $minutes = abs($offset) % 3600 / 60;
            $sign = $offset >= 0 ? '+' : '-';
            return sprintf('%s%02d:%02d', $sign, $hours, $minutes);
        } catch (\Exception $e) {
            // Default to Asia/Manila if timezone is invalid
            return '+08:00';
        }
    }
}
