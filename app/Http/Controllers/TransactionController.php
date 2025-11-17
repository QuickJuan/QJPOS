<?php
namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        
        return Inertia::render('Transactions/Index', [
            'cashiers' => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }
}
