@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Tenants</h1>
        <a href="{{ route('tenants.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Create Tenant</a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 rounded p-4 mb-6">
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold">Name</th>
                    <th class="px-6 py-3 text-left font-semibold">Email</th>
                    <th class="px-6 py-3 text-left font-semibold">Database</th>
                    <th class="px-6 py-3 text-left font-semibold">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tenants as $tenant)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3">{{ $tenant->name }}</td>
                        <td class="px-6 py-3">{{ $tenant->email }}</td>
                        <td class="px-6 py-3">{{ $tenant->tenancy_db_name }}</td>
                        <td class="px-6 py-3">{{ $tenant->subscription_status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-center text-gray-500">No tenants found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
