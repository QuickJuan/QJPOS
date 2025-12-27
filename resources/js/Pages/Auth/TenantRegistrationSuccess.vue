<template>
    <div
        class="min-h-screen bg-gradient-to-br from-neutral-50 via-neutral-50 to-neutral-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
    >
        <div class="w-full max-w-2xl">
            <!-- Header with Logo/App Name -->
            <div class="text-center mb-12">
                <h1
                    class="text-3xl sm:text-4xl font-bold text-neutral-900 mb-2"
                >
                    {{ appName }}
                </h1>
                <div
                    class="h-1 w-24 mx-auto bg-gradient-to-r from-primary-500 to-primary-600 rounded-full"
                ></div>
            </div>

            <!-- Main Success Card -->
            <div
                class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-neutral-200"
            >
                <!-- Gradient Header Section -->
                <div
                    class="h-32 bg-gradient-to-r from-success-50 to-success-100 relative flex items-center justify-center"
                >
                    <div class="absolute inset-0 opacity-10">
                        <div
                            class="absolute top-4 right-8 w-32 h-32 bg-success-400 rounded-full mix-blend-multiply filter blur-3xl"
                        ></div>
                        <div
                            class="absolute -bottom-8 left-8 w-32 h-32 bg-primary-400 rounded-full mix-blend-multiply filter blur-3xl"
                        ></div>
                    </div>

                    <!-- Animated Success Icon -->
                    <div class="relative z-10">
                        <div
                            class="w-24 h-24 bg-success-500 rounded-full flex items-center justify-center shadow-lg transform transition-transform duration-500 hover:scale-110"
                        >
                            <svg
                                class="w-12 h-12 text-white"
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
                </div>

                <!-- Content Section -->
                <div class="px-8 sm:px-12 py-12 sm:py-16">
                    <!-- Main Heading -->
                    <h2
                        class="text-3xl sm:text-4xl font-bold text-neutral-900 text-center mb-4"
                    >
                        Welcome to {{ appName }}!
                    </h2>

                    <!-- Subheading -->
                    <p
                        class="text-center text-lg text-neutral-600 mb-10 leading-relaxed max-w-xl mx-auto"
                    >
                        Your business registration has been successfully
                        submitted. We're excited to have you on board!
                    </p>

                    <!-- Progress Steps -->
                    <div class="mb-12">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Step 1 -->
                            <div class="relative">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-12 h-12 bg-success-500 text-white rounded-full flex items-center justify-center font-bold shadow-lg mb-3"
                                    >
                                        ✓
                                    </div>
                                    <h3
                                        class="font-semibold text-neutral-900 text-center"
                                    >
                                        Application Sent
                                    </h3>
                                    <p
                                        class="text-xs text-neutral-600 text-center mt-2"
                                    >
                                        Your details are secure with us
                                    </p>
                                </div>
                                <div
                                    v-if="true"
                                    class="hidden sm:block absolute top-6 -right-2 w-4 h-1 bg-neutral-300"
                                ></div>
                            </div>

                            <!-- Step 2 -->
                            <div class="relative">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-12 h-12 bg-primary-500 text-white rounded-full flex items-center justify-center font-bold shadow-lg mb-3"
                                    >
                                        2
                                    </div>
                                    <h3
                                        class="font-semibold text-neutral-900 text-center"
                                    >
                                        Review Process
                                    </h3>
                                    <p
                                        class="text-xs text-neutral-600 text-center mt-2"
                                    >
                                        2-3 business days
                                    </p>
                                </div>
                                <div
                                    v-if="true"
                                    class="hidden sm:block absolute top-6 -right-2 w-4 h-1 bg-neutral-300"
                                ></div>
                            </div>

                            <!-- Step 3 -->
                            <div class="relative">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-12 h-12 bg-secondary-500 text-white rounded-full flex items-center justify-center font-bold shadow-lg mb-3"
                                    >
                                        3
                                    </div>
                                    <h3
                                        class="font-semibold text-neutral-900 text-center"
                                    >
                                        Account Ready
                                    </h3>
                                    <p
                                        class="text-xs text-neutral-600 text-center mt-2"
                                    >
                                        Start managing your POS
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Important Actions -->
                    <div class="space-y-4 mb-10">
                        <!-- Email Verification -->
                        <div
                            class="bg-gradient-to-r from-primary-50 to-primary-100 border-2 border-primary-200 rounded-2xl p-6 flex gap-4"
                        >
                            <div class="flex-shrink-0">
                                <svg
                                    class="w-8 h-8 text-primary-600"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"
                                    />
                                    <path
                                        d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"
                                    />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-primary-900 mb-1">
                                    Check Your Email
                                </h4>
                                <p class="text-primary-700 text-sm">
                                    We've sent a confirmation and login
                                    credentials to your email address. Check
                                    your spam folder if needed.
                                </p>
                            </div>
                        </div>

                        <!-- Support -->
                        <div
                            class="bg-gradient-to-r from-secondary-50 to-secondary-100 border-2 border-secondary-200 rounded-2xl p-6 flex gap-4"
                        >
                            <div class="flex-shrink-0">
                                <svg
                                    class="w-8 h-8 text-secondary-600"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"
                                    />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4
                                    class="font-semibold text-secondary-900 mb-1"
                                >
                                    Questions or Issues?
                                </h4>
                                <p class="text-secondary-700 text-sm">
                                    Our support team is here to help. Contact us
                                    at
                                    <a
                                        href="mailto:support@storepos.online"
                                        class="font-semibold hover:underline"
                                        >support@storepos.online</a
                                    >
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Primary Action Button -->
                    <div class="flex flex-col gap-3 mb-8">
                        <a
                            href="/"
                            class="block text-center px-8 py-4 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg text-lg"
                        >
                            Return to Login
                        </a>

                        <a
                            href="/terms-of-service"
                            class="block text-center px-8 py-3 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-semibold rounded-xl transition-all duration-200 text-base"
                        >
                            Review Terms & Policies
                        </a>
                    </div>

                    <!-- Footer Message -->
                    <div class="text-center">
                        <p class="text-neutral-600 text-sm">
                            Thank you for choosing {{ appName }} to power your
                            business! 🎉
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom Support Link -->
            <div class="mt-10 text-center">
                <p class="text-neutral-600 text-sm">
                    Need help? Check our
                    <a
                        href="#"
                        class="text-primary-600 font-semibold hover:underline"
                        >help center</a
                    >
                    or
                    <a
                        href="mailto:support@storepos.online"
                        class="text-primary-600 font-semibold hover:underline"
                        >contact support</a
                    >
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
const appName = import.meta.env.VITE_APP_NAME || "QuickJuan";
</script>
