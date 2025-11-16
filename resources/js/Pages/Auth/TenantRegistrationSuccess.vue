<template>
    <div
        class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-xl w-full">
            <!-- Success Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8 sm:p-10 text-center">
                <!-- Success Icon -->
                <div class="mb-6">
                    <div
                        class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto animate-pulse"
                    >
                        <svg
                            class="w-10 h-10 text-green-600"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Heading -->
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    Application Received!
                </h1>

                <!-- Message -->
                <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                    Thank you for your interest in StorePos. We've received your
                    application and will review it carefully.
                </p>

                <!-- Key Points -->
                <div
                    class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6 mb-8 text-left space-y-4"
                >
                    <div class="flex gap-3">
                        <span class="text-2xl">📧</span>
                        <div>
                            <p class="font-semibold text-gray-900">
                                Check Your Email
                            </p>
                            <p class="text-gray-600 text-sm">
                                We've sent a confirmation to the email address
                                you provided.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <span class="text-2xl">⏱️</span>
                        <div>
                            <p class="font-semibold text-gray-900">
                                We'll Be in Touch
                            </p>
                            <p class="text-gray-600 text-sm">
                                Our team will review your application and
                                contact you within 2-3 business days.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <span class="text-2xl">💬</span>
                        <div>
                            <p class="font-semibold text-gray-900">
                                Questions?
                            </p>
                            <p class="text-gray-600 text-sm">
                                Feel free to reply to the confirmation email or
                                contact us anytime.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-8">
                    <p class="text-gray-700 text-sm">
                        Need immediate assistance? Contact us at
                        <br />
                        <a
                            href="mailto:support@storepos.online"
                            class="text-indigo-600 font-semibold hover:underline"
                        >
                            support@storepos.online
                        </a>
                    </p>
                </div>

                <!-- Action Button -->
                <a
                    href="/"
                    class="inline-block px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition duration-200 text-lg"
                >
                    Return to Home
                </a>

                <!-- Secondary Links -->
                <p class="mt-8 text-gray-600 text-sm">
                    <a
                        href="/terms-of-service"
                        class="text-indigo-600 hover:underline"
                        >Terms of Service</a
                    >
                    •
                    <a
                        href="/privacy-policy"
                        class="text-indigo-600 hover:underline"
                        >Privacy Policy</a
                    >
                </p>
            </div>

            <!-- Bottom Message -->
            <div class="mt-8 text-center text-gray-600 text-sm">
                <p>
                    Thank you for choosing StorePos to power your business! 🎉
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
// No setup needed for this success page
</script>
