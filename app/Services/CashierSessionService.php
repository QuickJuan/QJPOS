<?php
namespace App\Services;

use App\Models\CashierSession;

class CashierSessionService
{
    public function __construct(public CashierSession $model)
    {
        $this->model = $model;
    }
}
