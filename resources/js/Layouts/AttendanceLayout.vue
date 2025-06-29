<template>
    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col"
    >
        <!-- Header -->
        <header class="bg-white shadow-lg border-b-2 border-primary">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo and Branch Info -->
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center space-x-3">
                            <div class="bg-primary p-3 rounded-xl">
                                <svg
                                    class="w-10 h-10 text-white"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M12 2L2 7V10C2 16 6 20.5 12 22C18 20.5 22 16 22 10V7L12 2ZM12 4.19L19 7.3V10C19 15.85 15.74 19.94 12 20.96C8.26 19.94 5 15.85 5 10V7.3L12 4.19Z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">
                                    QJ<span class="text-primary">POS</span>
                                </h1>
                                <p class="text-sm text-gray-600 font-medium">
                                    Employee Attendance
                                </p>
                            </div>
                        </div>

                        <!-- Branch Info -->
                        <div class="hidden md:block">
                            <div class="flex items-center space-x-3">
                                <svg
                                    class="w-5 h-5 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    ></path>
                                </svg>
                                <div>
                                    <p
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        {{
                                            activeBranch?.name ||
                                            "No Branch Selected"
                                        }}
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        Code:
                                        {{ activeBranch?.branch_code || "N/A" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Real-time Date/Time -->
                    <div class="text-right">
                        <div class="flex items-center space-x-3">
                            <svg
                                class="w-5 h-5 text-gray-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                            <div>
                                <p
                                    class="text-lg font-bold text-gray-900 tabular-nums"
                                >
                                    {{ currentTime }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ currentDate }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Branch Info -->
                <div class="md:hidden pb-4">
                    <div class="flex items-center space-x-3">
                        <svg
                            class="w-5 h-5 text-primary"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                            ></path>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ activeBranch?.name || "No Branch Selected" }}
                            </p>
                            <p class="text-xs text-gray-600">
                                Code: {{ activeBranch?.branch_code || "N/A" }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex flex-grow flex-col">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    activeBranch: Object,
});

// Real-time date and time
const currentDateTime = ref(new Date());

// Computed properties for formatted date and time
const currentTime = computed(() => {
    return currentDateTime.value.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: true,
    });
});

const currentDate = computed(() => {
    return currentDateTime.value.toLocaleDateString("en-US", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
});

const currentYear = computed(() => {
    return currentDateTime.value.getFullYear();
});

// Update time every second
let timeInterval = null;

onMounted(() => {
    timeInterval = setInterval(() => {
        currentDateTime.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (timeInterval) {
        clearInterval(timeInterval);
    }
});
</script>

<style scoped>
.tabular-nums {
    font-variant-numeric: tabular-nums;
}
</style>
