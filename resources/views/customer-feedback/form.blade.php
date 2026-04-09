<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback - Invoice #{{ $order->invoice_no }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-start;
            gap: 6px;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            cursor: pointer;
            color: #d1d5db;
            transition: transform 120ms ease, color 120ms ease;
        }
        .star-rating label svg {
            width: 28px;
            height: 28px;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #fcd34d;
        }
        .star-rating input:checked ~ label {
            color: #fbbf24;
        }
        .star-rating label:active {
            transform: scale(0.96);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
<div class="max-w-xl mx-auto p-4 sm:p-8">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-7">
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold text-gray-900">Customer Feedback</h1>
            <p class="text-sm text-gray-600 mt-1">Invoice #{{ $order->invoice_no }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                <div class="font-semibold mb-1">Please fix the errors below:</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('customer-feedback.store', ['invoiceNo' => $order->invoice_no]) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name (optional)</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Your name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email (optional)</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="you@example.com">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                <div class="flex items-center justify-between">
                    <div class="star-rating" aria-label="Rating">
                        @for ($i = 5; $i >= 1; $i--)
                            <input
                                id="rating-{{ $i }}"
                                type="radio"
                                name="rating"
                                value="{{ $i }}"
                                required
                                {{ (string) old('rating') === (string) $i ? 'checked' : '' }}
                            >
                            <label for="rating-{{ $i }}" title="{{ $i }} {{ $i === 1 ? 'star' : 'stars' }}">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.97a1 1 0 00.95.69h4.174c.969 0 1.371 1.24.588 1.81l-3.377 2.455a1 1 0 00-.364 1.118l1.287 3.97c.3.921-.755 1.688-1.539 1.118l-3.377-2.455a1 1 0 00-1.175 0l-3.377 2.455c-.784.57-1.838-.197-1.539-1.118l1.287-3.97a1 1 0 00-.364-1.118L2.049 9.397c-.783-.57-.38-1.81.588-1.81h4.174a1 1 0 00.95-.69l1.286-3.97z"/>
                                </svg>
                            </label>
                        @endfor
                    </div>
                    <div class="text-xs text-gray-500">Tap a star</div>
                </div>
                <p class="text-xs text-gray-500 mt-2">1 = Poor, 5 = Excellent</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Feedback</label>
                <textarea name="message" rows="5" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tell us what we did well and what we can improve..." required>{{ old('message') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Photos (optional)</label>
                <input type="file" name="photos[]" accept="image/*" multiple class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-500 mt-1">Up to 5 photos, max 5MB each.</p>
            </div>

            <button type="submit" class="w-full rounded-xl bg-indigo-600 text-white font-semibold py-3 hover:bg-indigo-700 transition shadow-sm">
                Submit Feedback
            </button>
        </form>
    </div>

    <div class="text-center text-xs text-gray-500 mt-4">
        Thank you for helping us improve.
    </div>
</div>
</body>
</html>
