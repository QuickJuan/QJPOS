<template>
    <Head :title="props.title" />
    <main
        class="min-h-screen bg-gradient-to-br from-neutral-900 via-primary-900/20 to-neutral-800"
        role="main"
    >
        <div class="grid md:grid-cols-2 lg:grid-cols-5 min-h-screen">
            <!-- Left Panel - Welcome Section -->
            <div
                class="hidden md:block md:col-span-1 lg:col-span-3 relative overflow-hidden bg-gradient-to-br from-primary-600 via-primary-700 to-secondary-900"
                role="banner"
                aria-label="QJPOS welcome section"
            >
                <!-- Subtle Background Pattern -->
                <div class="absolute inset-0 opacity-5" aria-hidden="true">
                    <div
                        class="absolute top-20 left-20 w-32 h-32 border border-white rounded-full"
                    ></div>
                    <div
                        class="absolute bottom-32 right-32 w-40 h-40 border border-white rounded-full"
                    ></div>
                </div>

                <!-- Main Content -->
                <div
                    class="relative z-10 flex flex-col justify-center h-full px-8 md:px-16 lg:px-24"
                >
                    <!-- Brand Section -->
                    <div class="text-center">
                        <!-- Logo -->
                        <div class="flex justify-center mb-8">
                            <div
                                class="bg-white bg-opacity-20 p-6 rounded-3xl backdrop-blur-sm"
                                aria-hidden="true"
                            >
                                <svg
                                    class="w-16 h-16 text-white"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M12 2L2 7V10C2 16 6 20.5 12 22C18 20.5 22 16 22 10V7L12 2ZM12 4.19L19 7.3V10C19 15.85 15.74 19.94 12 20.96C8.26 19.94 5 15.85 5 10V7.3L12 4.19ZM12 8C10.34 8 9 9.34 9 11S10.34 14 12 14S15 12.66 15 11S13.66 8 12 8ZM12 10C12.55 10 13 10.45 13 11S12.55 12 12 12S11 11.55 11 11S11.45 10 12 10Z"
                                    />
                                </svg>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <h1
                                    class="text-5xl lg:text-6xl font-bold text-white mb-4"
                                    id="main-heading"
                                >
                                    Welcome Back to
                                </h1>
                                <h2
                                    class="text-6xl lg:text-7xl font-bold text-white mb-4"
                                    id="brand-heading"
                                >
                                    QJ<span class="text-tertiary-100">POS</span>
                                </h2>
                                <div
                                    class="h-1 w-32 bg-gradient-to-r from-tertiary-100 to-white rounded-full mx-auto"
                                    aria-hidden="true"
                                ></div>
                            </div>

                            <div class="space-y-4">
                                <p class="text-2xl text-white font-medium">
                                    Ready to manage your business?
                                </p>
                                <p
                                    class="text-lg text-neutral-100 max-w-md mx-auto"
                                >
                                    Sign in to access your dashboard and
                                    continue where you left off.
                                </p>
                            </div>
                        </div>

                        <!-- Quick Status Indicators -->
                        <div
                            class="mt-16 grid grid-cols-2 gap-8 max-w-sm mx-auto"
                        >
                            <div
                                class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-4 text-center"
                                role="status"
                                aria-label="System status"
                            >
                                <div
                                    class="flex items-center justify-center mb-2"
                                >
                                    <div
                                        class="w-3 h-3 bg-success-400 rounded-full mr-2"
                                    ></div>
                                    <span class="text-white font-semibold"
                                        >System Online</span
                                    >
                                </div>
                                <p class="text-neutral-100 text-sm">
                                    All services running
                                </p>
                            </div>

                            <div
                                class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-4 text-center"
                                role="status"
                                aria-label="Support availability"
                            >
                                <div
                                    class="flex items-center justify-center mb-2"
                                >
                                    <div
                                        class="w-3 h-3 bg-primary-400 rounded-full mr-2"
                                    ></div>
                                    <span class="text-white font-semibold"
                                        >Support Ready</span
                                    >
                                </div>
                                <p class="text-neutral-100 text-sm">
                                    Help when you need it
                                </p>
                            </div>
                        </div>

                        <!-- Simple Footer -->
                        <div class="mt-20">
                            <p class="text-neutral-200 text-sm">
                                Secure • Reliable • Always Available
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="md:col-span-1 lg:col-span-2 flex items-center justify-center p-8 bg-gradient-to-br from-white/95 to-neutral-50/95 backdrop-blur-sm"
                role="form"
                aria-label="Login form section"
            >
                <div class="w-full max-w-md">
                    <slot></slot>
                </div>
            </div>
        </div>
    </main>

    <Toast />
</template>

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { Toast } from "primevue";

const props = defineProps<{
    title?: string;
}>();
</script>
