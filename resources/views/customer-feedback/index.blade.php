<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
<div class="max-w-3xl mx-auto p-4 sm:p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Customer Feedback</h1>
            <p class="text-sm text-gray-600 mt-1">Only published feedback is shown here.</p>
        </div>
        <a href="/" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">Home</a>
    </div>

    <div class="space-y-4">
        @forelse ($feedbacks as $feedback)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Invoice #{{ $feedback->invoice_no }}</div>
                        <div class="font-semibold text-gray-900">
                            {{ $feedback->name ?: 'Anonymous' }}
                        </div>
                    </div>
                    @if ($feedback->rating)
                        <div class="text-sm font-semibold text-gray-900 whitespace-nowrap">
                            {{ str_repeat('★', (int) $feedback->rating) }}{{ str_repeat('☆', 5 - (int) $feedback->rating) }}
                        </div>
                    @endif
                </div>
                <div class="mt-3 text-gray-700 whitespace-pre-line">{{ $feedback->message }}</div>
                <div class="mt-3 text-xs text-gray-500">
                    {{ $feedback->created_at?->format('M d, Y') }}
                </div>
            </div>
        @empty
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center text-gray-600">
                No published feedback yet.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $feedbacks->links() }}
    </div>
</div>
</body>
</html>

