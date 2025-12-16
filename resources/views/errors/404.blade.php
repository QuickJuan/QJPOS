<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | QuickJuan POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-orange-50 via-white to-orange-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full text-center fade-in">
        <!-- Animated 404 Illustration -->
        <div class="mb-8 float-animation">
            <svg class="w-64 h-64 mx-auto" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Shopping Cart -->
                <circle cx="100" cy="100" r="80" fill="#FFF7ED" stroke="#FB923C" stroke-width="2"/>
                <path d="M60 70L70 70L80 120L140 120L150 80L75 80" stroke="#FB923C" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                <circle cx="90" cy="135" r="6" fill="#FB923C"/>
                <circle cx="130" cy="135" r="6" fill="#FB923C"/>

                <!-- Question Mark -->
                <text x="100" y="110" font-size="48" font-weight="bold" fill="#EA580C" text-anchor="middle">?</text>

                <!-- Search Icon -->
                <circle cx="160" cy="60" r="15" stroke="#FB923C" stroke-width="3" fill="none"/>
                <line x1="171" y1="71" x2="182" y2="82" stroke="#FB923C" stroke-width="3" stroke-linecap="round"/>
            </svg>
        </div>

        <!-- 404 Text -->
        <div class="mb-6">
            <h1 class="text-8xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-orange-600 mb-2">
                404
            </h1>
            <h2 class="text-3xl font-bold text-gray-800 mb-3">
                Page Not Found
            </h2>
            <p class="text-lg text-gray-600 mb-2">
                Oops! The page you're looking for seems to have wandered off.
            </p>
            <p class="text-gray-500">
                It might have been moved, deleted, or never existed in the first place.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <a href="/" class="inline-flex items-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Go to Homepage
            </a>
            <button onclick="history.back()" class="inline-flex items-center px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 font-semibold rounded-lg border-2 border-gray-300 shadow hover:shadow-md transform hover:scale-105 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Go Back
            </button>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-lg shadow-md p-6 border border-orange-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Links</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="/dashboard" class="text-orange-600 hover:text-orange-700 hover:underline font-medium">
                    Dashboard
                </a>
                <a href="/resto/tables" class="text-orange-600 hover:text-orange-700 hover:underline font-medium">
                    Tables
                </a>
                <a href="/transactions" class="text-orange-600 hover:text-orange-700 hover:underline font-medium">
                    Transactions
                </a>
                <a href="/settings" class="text-orange-600 hover:text-orange-700 hover:underline font-medium">
                    Settings
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-gray-500 text-sm">
            <p>Need help? Contact support at <a href="mailto:support@quickjuan.com" class="text-orange-600 hover:text-orange-700 underline">support@quickjuan.com</a></p>
        </div>
    </div>

    <script>
        // Add stagger animation to elements
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach((el, index) => {
                el.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>
