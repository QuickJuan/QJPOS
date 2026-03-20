<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code'                  => 'SL',
                'name'                  => 'Sick Leave',
                'is_paid'               => true,
                'default_days_per_year' => 15,
                'requires_document'     => true,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'is_active'             => true,
                'sort_order'            => 10,
                'notes'                 => 'Company policy / Labor Code Art. 97. Medical certificate required for absences of 2+ consecutive days.',
            ],
            [
                'code'                  => 'VL',
                'name'                  => 'Vacation Leave',
                'is_paid'               => true,
                'default_days_per_year' => 15,
                'requires_document'     => false,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'is_active'             => true,
                'sort_order'            => 20,
                'notes'                 => 'Company policy.',
            ],
            [
                'code'                  => 'SIL',
                'name'                  => 'Service Incentive Leave',
                'is_paid'               => true,
                'default_days_per_year' => 5,
                'requires_document'     => false,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'is_active'             => true,
                'sort_order'            => 30,
                'notes'                 => 'Labor Code Art. 95. Applies to employees who have rendered at least one year of service.',
            ],
            [
                'code'                  => 'ML',
                'name'                  => 'Maternity Leave',
                'is_paid'               => true,
                'default_days_per_year' => 105,
                'requires_document'     => true,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => true,
                'is_active'             => true,
                'sort_order'            => 40,
                'notes'                 => 'R.A. 11210 — 105-Day Expanded Maternity Leave Act. 120 days for solo parents.',
            ],
            [
                'code'                  => 'PL',
                'name'                  => 'Paternity Leave',
                'is_paid'               => true,
                'default_days_per_year' => 7,
                'requires_document'     => true,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'is_active'             => true,
                'sort_order'            => 50,
                'notes'                 => 'R.A. 8187 — Paternity Leave Act of 1996. For legally married male employees.',
            ],
            [
                'code'                  => 'SPL',
                'name'                  => 'Solo Parent Leave',
                'is_paid'               => true,
                'default_days_per_year' => 7,
                'requires_document'     => true,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'is_active'             => true,
                'sort_order'            => 60,
                'notes'                 => 'R.A. 8972 — Solo Parents\' Welfare Act of 2000. Requires Solo Parent ID.',
            ],
            [
                'code'                  => 'VAWC',
                'name'                  => 'VAWC Leave',
                'is_paid'               => true,
                'default_days_per_year' => 10,
                'requires_document'     => true,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => true,
                'is_active'             => true,
                'sort_order'            => 70,
                'notes'                 => 'R.A. 9262 — Anti-Violence Against Women and Their Children Act.',
            ],
            [
                'code'                  => 'BWL',
                'name'                  => 'Bereavement Leave',
                'is_paid'               => true,
                'default_days_per_year' => 3,
                'requires_document'     => true,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'is_active'             => true,
                'sort_order'            => 80,
                'notes'                 => 'Company policy. For immediate family members.',
            ],
            [
                'code'                  => 'LWOP',
                'name'                  => 'Leave Without Pay',
                'is_paid'               => false,
                'default_days_per_year' => 0,
                'requires_document'     => false,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => true,
                'is_active'             => true,
                'sort_order'            => 90,
                'notes'                 => 'Unpaid leave. Results in salary deduction for days absent.',
            ],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
