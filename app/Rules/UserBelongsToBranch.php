<?php

namespace App\Rules;

use App\Models\User;
use App\Models\Branch;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserBelongsToBranch implements ValidationRule
{
    protected $branchId;

    /**
     * Create a new rule instance.
     */
    public function __construct($branchId)
    {
        $this->branchId = $branchId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Find user by employee code
        $user = User::where('employee_code', $value)->first();
        
        if (!$user) {
            $fail('Invalid employee code. Employee not found.');
            return;
        }

        // Check if branch exists
        $branch = Branch::find($this->branchId);
        
        if (!$branch) {
            $fail('Branch not found.');
            return;
        }

        // Check if user is associated with the branch
        if (!$user->canLoginTo($branch)) {
            $fail('You are not authorized to clock in/out at this branch.');
        }
    }
}
