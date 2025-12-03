<?php
namespace App\Services;

use App\Models\Branch;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchService
{
    protected float $taxRate;

    public function __construct(public Branch $model)
    {
        $this->model = $model;

    }


    //increment brancgh order number
    public function incrementOrderNumber(int $branchId): int
    {
        $branch = $this->model->findOrFail($branchId);

        if (! $branch) {
            throw new Exception('Branch not found.');
        }

        $branch->increment('order_number');

        return $branch->order_number;
    }

}
