<?php

namespace App\Http\Controllers;

use App\Models\Central\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'tenancy_db_name' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'domain' => 'nullable|string',
            'billing_type' => 'required|string',
            'subscription' => 'required|string',
            'subscription_status' => 'required|string',
        ]);

        $domain = $validated['domain'] ?? null;
        unset($validated['domain']);

        try {
            $tenant = Tenant::create($validated);

            if ($domain && $tenant) {
                $tenant->domains()->create([
                    'domain' => $domain,
                ]);
            }

            return redirect()->route('tenants.index')->with('success', 'Tenant created successfully');
        } catch (\Exception $e) {
            \Log::error('Tenant creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create tenant']);
        }
    }

    public function index()
    {
        $tenants = Tenant::all();
        return view('tenants.index', compact('tenants'));
    }
}
