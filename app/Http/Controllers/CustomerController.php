<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('customer_name')->get();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'birth_date' => 'nullable|date|before:today',
            'type' => 'required|in:regular,vip',
            'email_subscribe' => 'boolean',
            'sms_subscribe' => 'boolean',
        ]);

        $customer = Customer::create($validated);

        return back()->with([
            'success' => 'Customer added successfully',
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'birth_date' => 'nullable|date|before:today',
            'type' => 'required|in:regular,vip',
            'email_subscribe' => 'boolean',
            'sms_subscribe' => 'boolean',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return back()->with('success', 'Customer deleted successfully');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $customers = Customer::query()
            ->when($query, function ($q) use ($query) {
                $q->where('customer_name', 'like', "%{$query}%")
                    ->orWhere('contact_no', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('customer_name')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }
}
