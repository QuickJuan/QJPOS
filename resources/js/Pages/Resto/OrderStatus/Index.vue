<template>
    <div class="h-screen flex flex-col bg-gray-950 overflow-hidden select-none">
        <!-- Top bar -->
        <div
            class="bg-gray-900 px-6 py-3 flex items-center justify-between border-b border-gray-800 shrink-0"
        >
            <div class="flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                <span class="text-white font-bold text-lg tracking-wide">
                    {{ appName }} · Order Status
                </span>
            </div>
            <span class="text-gray-400 font-mono text-base">{{
                currentTime
            }}</span>
        </div>

        <!-- Split display -->
        <div class="flex-1 flex overflow-hidden">
            <!-- In Preparing column -->
            <div
                class="w-1/2 border-r border-gray-800 flex flex-col overflow-hidden"
            >
                <div class="bg-gray-800 px-6 py-4 shrink-0">
                    <h2
                        class="text-gray-200 font-extrabold text-xl uppercase tracking-widest text-center"
                    >
                        In Preparing
                    </h2>
                </div>
                <div
                    class="flex-1 overflow-auto p-5 flex flex-wrap content-start gap-4"
                >
                    <div
                        v-for="batch in preparing"
                        :key="batch.batch_number"
                        class="bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4 text-center min-w-32 flex flex-col items-center gap-1"
                    >
                        <div
                            class="text-white font-black leading-none"
                            :class="
                                batch.display_name.length > 10
                                    ? 'text-2xl'
                                    : 'text-4xl'
                            "
                        >
                            {{ batch.display_name }}
                        </div>
                        <div class="text-gray-400 text-xs font-medium">
                            {{ batch.table_name }}
                        </div>
                        <div class="text-gray-500 text-xs mt-0.5">
                            {{ batch.ready_count }}/{{ batch.item_count }} done
                        </div>
                    </div>
                    <div
                        v-if="!preparing.length"
                        class="text-gray-700 text-lg w-full text-center py-16"
                    >
                        No orders in preparation
                    </div>
                </div>
            </div>

            <!-- Now Serving column -->
            <div class="w-1/2 flex flex-col overflow-hidden">
                <div class="bg-orange-600 px-6 py-4 shrink-0">
                    <h2
                        class="text-white font-extrabold text-xl uppercase tracking-widest text-center"
                    >
                        Now Serving
                    </h2>
                </div>
                <div
                    class="flex-1 overflow-auto p-5 flex flex-wrap content-start gap-4 bg-gray-900"
                >
                    <div
                        v-for="batch in serving"
                        :key="batch.batch_number"
                        class="bg-orange-500 rounded-2xl px-5 py-4 text-center min-w-32 flex flex-col items-center gap-1"
                    >
                        <div
                            class="text-white font-black leading-none"
                            :class="
                                batch.display_name.length > 10
                                    ? 'text-2xl'
                                    : 'text-4xl'
                            "
                        >
                            {{ batch.display_name }}
                        </div>
                        <div class="text-orange-200 text-xs font-medium">
                            {{ batch.table_name }}
                        </div>
                    </div>
                    <div
                        v-if="!serving.length"
                        class="text-orange-900/30 text-lg w-full text-center py-16"
                    >
                        No orders ready yet
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";

defineProps<{
    preparing: {
        batch_number: number;
        display_name: string;
        table_name: string;
        item_count: number;
        ready_count: number;
    }[];
    serving: {
        batch_number: number;
        display_name: string;
        table_name: string;
        item_count: number;
        ready_count: number;
    }[];
}>();

const page = usePage();
const appName = (page.props as any)?.appName ?? "QuickJuan";

const currentTime = ref(new Date().toLocaleTimeString());

let clockInterval: ReturnType<typeof setInterval>;
let pollInterval: ReturnType<typeof setInterval>;

onMounted(() => {
    clockInterval = setInterval(() => {
        currentTime.value = new Date().toLocaleTimeString();
    }, 1000);

    // Refresh data every 5 seconds
    pollInterval = setInterval(() => {
        router.reload({ only: ["preparing", "serving"] });
    }, 5000);
});

onUnmounted(() => {
    clearInterval(clockInterval);
    clearInterval(pollInterval);
});
</script>
