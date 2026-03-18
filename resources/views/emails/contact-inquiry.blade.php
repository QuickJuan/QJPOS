@component('mail::message')
# New Contact Inquiry

**Name:** {{ $inquiry->name ?? 'N/A' }}

**Email:** {{ $inquiry->email ?? 'N/A' }}

**Phone:** {{ $inquiry->phone ?? 'N/A' }}

**Message:**

{{ $inquiry->message ?? 'N/A' }}

@isset($inquiry->fields)
@component('mail::panel')
Additional Fields:

@foreach(($inquiry->fields ?? []) as $key => $value)
- **{{ $key }}:** {{ is_array($value) ? json_encode($value) : $value }}
@endforeach
@endcomponent
@endisset

Thanks,
{{ config('app.name') }}
@endcomponent
