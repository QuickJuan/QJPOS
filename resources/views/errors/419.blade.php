<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Expired - QuickJuan POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <!-- Icon -->
        <div class="mb-6">
            <svg class="w-24 h-24 mx-auto text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <!-- Title -->
        <h1 class="text-3xl font-bold text-gray-900 mb-4">
            Page Expired
        </h1>

        <!-- Message -->
        <p class="text-gray-600 mb-6">
            Your session has expired due to inactivity. This page will automatically reload in <span id="countdown" class="font-bold text-primary-600">5</span> seconds.
        </p>

        <!-- Manual Reload Button -->
        <button
            onclick="window.location.reload()"
            class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200"
        >
            Reload Now
        </button>

        <!-- Additional Info -->
        <p class="text-sm text-gray-500 mt-6">
            If the problem persists, please clear your browser cache or contact support.
        </p>
    </div>

    <script>
        // Countdown and auto-reload
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');

        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(timer);
                window.location.reload();
            }
        }, 1000);

        // Allow manual reload to cancel timer
        document.querySelector('button').addEventListener('click', () => {
            clearInterval(timer);
        });
    </script>
</body>
</html>
