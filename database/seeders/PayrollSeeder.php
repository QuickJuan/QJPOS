<?php

namespace Database\Seeders;

use App\Models\CompensationGroup;
use App\Models\CompensationType;
use Illuminate\Database\Seeder;

/**
 * Seeds Philippine payroll compensation groups and types.
 *
 * Groups:
 *   1. Government Mandated  – statutory deductions (SSS, PhilHealth, Pag-IBIG, BIR withholding)
 *   2. De Minimis Benefits  – non-taxable benefits with BIR-prescribed ceilings
 *   3. Taxable Allowances   – additional income subject to withholding tax
 *   4. Company Deductions   – internal deductions (cash advance, loans, absences)
 *
 * Run via: php artisan tenants:artisan "db:seed --class=PayrollSeeder"
 */
class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        // ──────────────────────────────────────────────────────────────────────
        // 1.  Government Mandated Deductions
        // ──────────────────────────────────────────────────────────────────────
        $govtGroup = CompensationGroup::updateOrCreate(
            ['name' => 'Government Mandated'],
            [
                'applies_to'  => 'deduction',
                'color'       => '#ef4444',
                'sort_order'  => 10,
                'description' => 'Statutory deductions required by Philippine law (SSS, PhilHealth, Pag-IBIG, BIR withholding tax).',
            ]
        );

        $this->seedTypes($govtGroup->id, [
            [
                'code'                  => 'SSS',
                'name'                  => 'SSS Contribution',
                'type'                  => 'deduction',
                'is_taxable'            => false,
                'is_mandatory'          => true,
                'is_employer_shared'    => true,
                'computation_type'      => 'table',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 10,
                'notes'                 => 'R.A. 11199 (Social Security Act of 2018). Employee share = 4.5% of MSC; Employer share = 9.5% of MSC. Amount from SSS contribution table.',
            ],
            [
                'code'                  => 'PHIC',
                'name'                  => 'PhilHealth / PHIC Contribution',
                'type'                  => 'deduction',
                'is_taxable'            => false,
                'is_mandatory'          => true,
                'is_employer_shared'    => true,
                'computation_type'      => 'percentage',
                'default_amount'        => null,
                'default_rate'          => 2.50,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 20,
                'notes'                 => 'R.A. 11223 (Universal Health Care Act). Total premium = 5% of basic salary (shared equally). Employee share = 2.5% of basic salary.',
            ],
            [
                'code'                  => 'PAGIBIG',
                'name'                  => 'Pag-IBIG / HDMF Contribution',
                'type'                  => 'deduction',
                'is_taxable'            => false,
                'is_mandatory'          => true,
                'is_employer_shared'    => true,
                'computation_type'      => 'fixed',
                'default_amount'        => 100.00,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 30,
                'notes'                 => 'R.A. 9679. Mandatory ₱100/month employee share for salaries ≤ ₱1,500; ₱200 for > ₱1,500. Employer also contributes ₱100. Maximum voluntary set by member.',
            ],
            [
                'code'                  => 'WHT',
                'name'                  => 'Withholding Tax (BIR)',
                'type'                  => 'deduction',
                'is_taxable'            => false,
                'is_mandatory'          => true,
                'is_employer_shared'    => false,
                'computation_type'      => 'table',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 40,
                'notes'                 => 'NIRC / R.A. 10963 (TRAIN Law). Amount varies by taxable income and BIR civil status (S/M/ME codes). Use BIR Tax Table for computation.',
            ],
        ]);

        // ──────────────────────────────────────────────────────────────────────
        // 2.  De Minimis Benefits (Non-Taxable within BIR limits)
        // ──────────────────────────────────────────────────────────────────────
        $deMinimisGroup = CompensationGroup::updateOrCreate(
            ['name' => 'De Minimis Benefits'],
            [
                'applies_to'  => 'income',
                'color'       => '#16a34a',
                'sort_order'  => 20,
                'description' => 'Non-taxable benefits within BIR-prescribed ceilings (RR 11-2018, as amended). Amounts exceeding the limits become part of "Other Benefits" subject to tax.',
            ]
        );

        $this->seedTypes($deMinimisGroup->id, [
            [
                'code'                  => 'RICE_ALLOW',
                'name'                  => 'Rice Allowance',
                'type'                  => 'income',
                'is_taxable'            => false,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => 2000.00,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 10,
                'notes'                 => 'Non-taxable up to ₱2,000/month (₱24,000/year). Excess is taxable.',
            ],
            [
                'code'                  => 'CLOTHING_ALLOW',
                'name'                  => 'Clothing / Uniform Allowance',
                'type'                  => 'income',
                'is_taxable'            => false,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => 417.00,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 20,
                'notes'                 => 'Non-taxable up to ₱6,000/year (≈₱417/month). Excess is taxable.',
            ],
            [
                'code'                  => 'MEDICAL_ALLOW',
                'name'                  => 'Medical Cash Allowance',
                'type'                  => 'income',
                'is_taxable'            => false,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => 750.00,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 30,
                'notes'                 => 'Non-taxable up to ₱1,500/quarter (₱750/semi-monthly). Excess is taxable.',
            ],
            [
                'code'                  => 'LAUNDRY_ALLOW',
                'name'                  => 'Laundry Allowance',
                'type'                  => 'income',
                'is_taxable'            => false,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => 300.00,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 40,
                'notes'                 => 'Non-taxable up to ₱300/month (employees whose job requires laundry). Excess is taxable.',
            ],
            [
                'code'                  => 'ACHIEVE_AWARD',
                'name'                  => 'Achievement / Service Award',
                'type'                  => 'income',
                'is_taxable'            => false,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 50,
                'notes'                 => 'Non-taxable up to ₱10,000/year for length-of-service or safety achievement awards. Excess is taxable.',
            ],
        ]);

        // ──────────────────────────────────────────────────────────────────────
        // 3.  Taxable Allowances & Additional Income
        // ──────────────────────────────────────────────────────────────────────
        $taxableGroup = CompensationGroup::updateOrCreate(
            ['name' => 'Taxable Allowances'],
            [
                'applies_to'  => 'income',
                'color'       => '#3b82f6',
                'sort_order'  => 30,
                'description' => 'Additional income or allowances subject to BIR withholding tax.',
            ]
        );

        $this->seedTypes($taxableGroup->id, [
            [
                'code'                  => 'MEAL_ALLOW',
                'name'                  => 'Meal Allowance',
                'type'                  => 'income',
                'is_taxable'            => true,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => true,
                'sort_order'            => 10,
                'notes'                 => 'Taxable unless categorized as overtime meal allowance for specific circumstances.',
            ],
            [
                'code'                  => 'TRANSPORT_ALLOW',
                'name'                  => 'Transportation Allowance',
                'type'                  => 'income',
                'is_taxable'            => true,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => true,
                'sort_order'            => 20,
                'notes'                 => null,
            ],
            [
                'code'                  => 'COMM_ALLOW',
                'name'                  => 'Communication Allowance',
                'type'                  => 'income',
                'is_taxable'            => true,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 30,
                'notes'                 => null,
            ],
            [
                'code'                  => 'OVERTIME_PAY',
                'name'                  => 'Overtime Pay',
                'type'                  => 'income',
                'is_taxable'            => true,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'percentage',
                'default_amount'        => null,
                'default_rate'          => 25.00,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => true,
                'sort_order'            => 40,
                'notes'                 => 'Regular OT = 25% premium. Holiday OT = 30% additional. Computed on hourly rate.',
            ],
            [
                'code'                  => 'HOLIDAY_PAY',
                'name'                  => 'Holiday Pay',
                'type'                  => 'income',
                'is_taxable'            => true,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'percentage',
                'default_amount'        => null,
                'default_rate'          => 100.00,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => true,
                'sort_order'            => 50,
                'notes'                 => 'Regular holiday = 200% (100% premium). Special non-working = 130% if worked.',
            ],
            [
                'code'                  => 'NIGHT_DIFF',
                'name'                  => 'Night Differential Pay',
                'type'                  => 'income',
                'is_taxable'            => true,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'percentage',
                'default_amount'        => null,
                'default_rate'          => 10.00,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => true,
                'sort_order'            => 60,
                'notes'                 => 'Minimum 10% night shift differential for work from 10PM–6AM (Labor Code Art. 86).',
            ],
            [
                'code'                  => 'OTHER_ALLOW',
                'name'                  => 'Other Allowances',
                'type'                  => 'income',
                'is_taxable'            => true,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 99,
                'notes'                 => null,
            ],
        ]);

        // ──────────────────────────────────────────────────────────────────────
        // 4.  Company / Internal Deductions
        // ──────────────────────────────────────────────────────────────────────
        $companyDedGroup = CompensationGroup::updateOrCreate(
            ['name' => 'Company Deductions'],
            [
                'applies_to'  => 'deduction',
                'color'       => '#f59e0b',
                'sort_order'  => 40,
                'description' => 'Internal deductions such as cash advances, employee loans, and attendance-related deductions.',
            ]
        );

        $this->seedTypes($companyDedGroup->id, [
            [
                'code'                  => 'CASH_ADVANCE',
                'name'                  => 'Cash Advance',
                'type'                  => 'deduction',
                'is_taxable'            => false,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => true,
                'sort_order'            => 10,
                'notes'                 => null,
            ],
            [
                'code'                  => 'EMP_LOAN',
                'name'                  => 'Employee Loan',
                'type'                  => 'deduction',
                'is_taxable'            => false,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 20,
                'notes'                 => null,
            ],
            [
                'code'                  => 'ABSENCE_DED',
                'name'                  => 'Absence / Leave Without Pay',
                'type'                  => 'deduction',
                'is_taxable'            => false,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => true,
                'sort_order'            => 30,
                'notes'                 => 'Deducted based on daily rate × days absent.',
            ],
            [
                'code'                  => 'LATE_UNDER',
                'name'                  => 'Late / Undertime Deduction',
                'type'                  => 'deduction',
                'is_taxable'            => false,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => true,
                'sort_order'            => 40,
                'notes'                 => 'Deducted based on hourly rate × late/undertime hours.',
            ],
            [
                'code'                  => 'UNIFORM_DED',
                'name'                  => 'Uniform Deduction',
                'type'                  => 'deduction',
                'is_taxable'            => false,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => false,
                'sort_order'            => 50,
                'notes'                 => null,
            ],
            [
                'code'                  => 'OTHER_DED',
                'name'                  => 'Other Deductions',
                'type'                  => 'deduction',
                'is_taxable'            => false,
                'is_mandatory'          => false,
                'is_employer_shared'    => false,
                'computation_type'      => 'fixed',
                'default_amount'        => null,
                'default_rate'          => null,
                'applies_to_regular'    => true,
                'applies_to_part_time'  => true,
                'sort_order'            => 99,
                'notes'                 => null,
            ],
        ]);

        $this->command->info('✅ Payroll seeder complete: 4 groups, ' . CompensationType::count() . ' types seeded.');
    }

    private function seedTypes(int $groupId, array $types): void
    {
        foreach ($types as $type) {
            CompensationType::updateOrCreate(
                ['code' => $type['code']],
                array_merge($type, ['compensation_group_id' => $groupId])
            );
        }
    }
}
