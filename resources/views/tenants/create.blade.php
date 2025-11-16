@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">Create Tenant</h1>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded p-4 mb-6">
            <ul class="list-disc list-inside text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('tenants.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block font-semibold mb-2">Business Name *</label>
                <input type="text" id="name" name="name" required class="w-full border rounded px-3 py-2" value="{{ old('name') }}">
            </div>

            <div>
                <label for="email" class="block font-semibold mb-2">Email *</label>
                <input type="email" id="email" name="email" required class="w-full border rounded px-3 py-2" value="{{ old('email') }}">
            </div>

            <div>
                <label for="tenancy_db_name" class="block font-semibold mb-2">Database Name *</label>
                <input type="text" id="tenancy_db_name" name="tenancy_db_name" required class="w-full border rounded px-3 py-2" value="{{ old('tenancy_db_name') }}">
            </div>

            <div>
                <label for="phone" class="block font-semibold mb-2">Phone</label>
                <input type="text" id="phone" name="phone" class="w-full border rounded px-3 py-2" value="{{ old('phone') }}">
            </div>

            <div>
                <label for="address" class="block font-semibold mb-2">Address</label>
                <input type="text" id="address" name="address" class="w-full border rounded px-3 py-2" value="{{ old('address') }}">
            </div>

            <div>
                <label for="domain" class="block font-semibold mb-2">Domain</label>
                <input type="text" id="domain" name="domain" class="w-full border rounded px-3 py-2" value="{{ old('domain') }}">
            </div>

            <div>
                <label for="billing_type" class="block font-semibold mb-2">Billing Type *</label>
                <input type="text" id="billing_type" name="billing_type" required class="w-full border rounded px-3 py-2" value="{{ old('billing_type') }}">
            </div>

            <div>
                <label for="subscription" class="block font-semibold mb-2">Subscription *</label>
                <input type="text" id="subscription" name="subscription" required class="w-full border rounded px-3 py-2" value="{{ old('subscription') }}">
            </div>

            <div>
                <label for="subscription_status" class="block font-semibold mb-2">Status *</label>
                <input type="text" id="subscription_status" name="subscription_status" required class="w-full border rounded px-3 py-2" value="{{ old('subscription_status') }}">
            </div>
        </div>

        <div class="flex gap-4 mt-6">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Create Tenant</button>
            <a href="{{ route('tenants.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">Cancel</a>
        </div>
    </form>
</div>
@endsection
