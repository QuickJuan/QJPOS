<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Already Submitted - Invoice #{{ $order->invoice_no }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
<div class="max-w-lg w-full bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center">
    <h1 class="text-2xl font-bold text-gray-900">Feedback already submitted</h1>
    <p class="text-gray-600 mt-2">We already have feedback for Invoice #{{ $order->invoice_no }}. Thank you!</p>
    <div class="mt-6 flex justify-center">
        <a href="/" class="inline-flex items-center justify-center rounded-lg border border-gray-300 text-gray-700 font-semibold px-4 py-2.5 hover:bg-gray-50">
            Back to home
        </a>
    </div>
</div>
</body>
</html>

