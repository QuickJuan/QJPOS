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


    //increment branch order number
    public function getNextOrderNumber(int $branchId): int
    {
        $branch = $this->model->findOrFail($branchId);
        $branch->increment('order_number');

        return $branch->order_number;
    }

    public function getNextInvoiceNumber(int $branchId): int
    {
        $branch = $this->model->findOrFail($branchId);
        $branch->increment('invoice_number');

        return $branch->invoice_number;
    }

    public function getNextBillNumber(int $branchId): int
    {
        $branch = $this->model->findOrFail($branchId);
        $branch->increment('bill_number');

        return $branch->bill_number;
    }

}
