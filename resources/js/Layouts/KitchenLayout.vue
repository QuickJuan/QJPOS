<template>
    <div class="flex flex-col lg:flex-row h-screen bg-white">
        <!-- Sidebar -->
        <div
            class="w-full lg:w-64 bg-gray-100 border-b lg:border-b-0 lg:border-r border-gray-300 p-4 flex flex-col lg:flex-col flex-row lg:h-screen overflow-x-auto lg:overflow-x-visible"
        >
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-xl font-bold text-gray-900 mb-2">
                    Kitchen Display
                </h1>
                <div class="h-px bg-gray-300"></div>
            </div>

            <!-- Stats -->
            <div class="space-y-3 mb-6">
                <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                    <p class="text-blue-700 text-xs font-bold">
                        Pending Orders
                    </p>
                    <p class="text-2xl font-bold text-blue-900">
                        {{ pendingCount }}
                    </p>
                </div>
                <div
                    class="bg-orange-50 p-3 rounded-lg border border-orange-200"
                >
                    <p class="text-orange-700 text-xs font-bold">
                        Longest Wait
                    </p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ longestWait }} min
                    </p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="space-y-1 mb-5">
                <Link
                    :href="route('resto.pending-orders.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-200 transition"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>
                    Kitchen Orders
                </Link>
                <Link
                    :href="route('resto.pending-orders.order-status')"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-200 transition"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                        />
                    </svg>
                    Order Status Screen
                </Link>
            </nav>

            <!-- Connection Status -->
            <div class="mt-auto pt-6 border-t border-gray-300">
                <div class="flex items-center gap-2">
                    <div
                        :class="[
                            'w-3 h-3 rounded-full',
                            isConnected ? 'bg-green-500' : 'bg-red-500',
                        ]"
                    ></div>
                    <span class="text-xs text-gray-600">
                        {{ isConnected ? "Connected" : "Disconnected" }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden w-full">
            <!-- Top Bar -->
            <div
                class="bg-white border-b border-gray-300 px-4 lg:px-6 py-3 lg:py-4 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-2 lg:gap-0"
            >
                <h2 class="text-lg lg:text-2xl font-bold text-gray-900">
                    Pending Kitchen Orders
                </h2>
                <div class="text-xs lg:text-sm text-gray-600">
                    Last updated: {{ lastUpdateTime }}
                </div>
            </div>

            <!-- Orders Grid -->
            <div class="flex-1 overflow-auto p-3 lg:p-6">
                <slot />
            </div>
        </div>

        <!-- PWA Install Banner -->
        <PWAInstallBanner />
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import PWAInstallBanner from "@/Components/PWAInstallBanner.vue";

const props = defineProps<{
    isConnected?: boolean;
    pendingCount?: number;
    longestWait?: number;
}>();

const lastUpdateTime = computed(() => {
    return new Date().toLocaleTimeString();
});
</script>
