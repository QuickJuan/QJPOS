<template>
    <div class="relative min-h-screen overflow-hidden bg-slate-100">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.14),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(16,185,129,0.12),_transparent_28%)]"
        />

        <header class="relative border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-950 text-white shadow-lg"
                        >
                            <svg
                                class="h-8 w-8"
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
                            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-sky-700">
                                QJPOS
                            </p>
                            <h1 class="text-2xl font-semibold tracking-tight text-slate-950">
                                Employee Attendance
                            </h1>
                            <p class="text-sm text-slate-500">
                                Camera-based clock in and clock out monitoring
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs uppercase tracking-[0.22em] text-slate-500">
                                Branch
                            </p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">
                                {{ activeBranch?.name || "No Branch Selected" }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Code: {{ activeBranch?.branch_code || "N/A" }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs uppercase tracking-[0.22em] text-slate-500">
                                Current Time
                            </p>
                            <p class="mt-1 text-sm font-semibold tabular-nums text-slate-900">
                                {{ currentTime }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Live local clock
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 sm:col-span-2 lg:col-span-1">
                            <p class="text-xs uppercase tracking-[0.22em] text-slate-500">
                                Today
                            </p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">
                                {{ currentDate }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Attendance records update automatically
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="relative mx-auto flex max-w-7xl flex-col px-4 py-6 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";

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
