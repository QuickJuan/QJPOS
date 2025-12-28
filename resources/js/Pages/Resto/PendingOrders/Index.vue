<template>
    <KitchenLayout
        :is-connected="isConnected"
        :pending-count="pendingOrdersWithMinutes.length"
        :longest-wait="longestWaitTime"
    >
        <div class="p-3 lg:p-6 min-h-screen">
            <div
                v-if="pendingOrdersWithMinutes.length === 0"
                class="text-center py-12 lg:py-24"
            >
                <p class="text-gray-700 text-2xl lg:text-4xl font-bold">
                    No pending orders
                </p>
            </div>

            <!-- Grid of Batches -->
            <div
                class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-3 lg:gap-6"
            >
                <div
                    v-for="batch in pendingOrdersWithMinutes"
                    :key="batch.batch_number"
                    class="w-full bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition border border-gray-200"
                >
                    <!-- Batch Header -->
                    <div
                        class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 lg:p-6"
                    >
                        <div
                            class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3 lg:gap-0"
                        >
                            <div class="">
                                <h2
                                    class="text-lg lg:text-xl font-bold text-white mb-1 lg:mb-2"
                                >
                                    Batch #{{ batch.batch_number }}
                                </h2>
                                <p
                                    class="text-sm lg:text-lg text-blue-100 font-semibold"
                                >
                                    {{ batch.table_name }}
                                </p>
                                <p
                                    class="text-xs lg:text-base text-blue-100 mt-1 lg:mt-2"
                                >
                                    {{
                                        formatPlacedOrderTime(
                                            batch.placed_order_time
                                        )
                                    }}
                                </p>
                            </div>
                            <div class="text-left lg:text-right">
                                <div
                                    class="text-2xl lg:text-xl font-bold text-white mb-1"
                                >
                                    {{ batch.minutes_waiting }} min(s)
                                </div>

                                <p
                                    class="text-sm lg:text-lg text-blue-100 mt-2 lg:mt-4 font-semibold"
                                >
                                    {{ batch.items.length }} items
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="max-h-96 overflow-y-auto">
                        <div
                            v-for="(item, index) in batch.items"
                            :key="item.id"
                        >
                            <div
                                class="p-3 lg:p-5 hover:bg-gray-100 transition"
                                :class="
                                    item.is_served ? 'bg-green-50' : 'bg-white'
                                "
                            >
                                <!-- Main Item -->
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4"
                                >
                                    <div class="flex-1">
                                        <div
                                            class="flex items-center gap-2 sm:gap-3"
                                        >
                                            <span
                                                class="text-sm sm:text-lg font-bold bg-blue-100 text-blue-800 px-2 sm:px-3 py-1 sm:py-2 rounded"
                                            >
                                                {{ item.quantity }}x
                                            </span>
                                            <h3
                                                class="font-bold text-base sm:text-xl text-gray-900"
                                            >
                                                {{ item.product_name }}
                                            </h3>
                                        </div>
                                    </div>
                                    <button
                                        @click="
                                            toggleServed(
                                                item.id,
                                                !item.is_served
                                            )
                                        "
                                        :class="[
                                            'px-3 sm:px-6 py-2 sm:py-3 rounded text-sm sm:text-base font-bold whitespace-nowrap transition shadow-md',
                                            item.is_served
                                                ? 'bg-green-600 text-white hover:bg-green-700'
                                                : 'bg-orange-500 text-white hover:bg-orange-600',
                                        ]"
                                    >
                                        {{
                                            item.is_served
                                                ? "✓ Served"
                                                : "Serve"
                                        }}
                                    </button>
                                </div>

                                <!-- Product Options (Children) -->
                                <div
                                    v-if="
                                        item.children &&
                                        item.children.length > 0
                                    "
                                    class="ml-4 sm:ml-6 mt-2 sm:mt-3 space-y-1 sm:space-y-2 text-sm sm:text-xl text-gray-900 font-medium"
                                >
                                    <p
                                        class="text-black font-bold text-xs sm:text-base mb-1 sm:mb-2"
                                    >
                                        Items to prepare:
                                    </p>
                                    <div
                                        v-for="child in item.children"
                                        :key="child.id"
                                        class="flex gap-2 sm:gap-3"
                                    >
                                        <span
                                            class="text-black text-lg sm:text-2xl font-bold"
                                            >•</span
                                        >
                                        <span
                                            class="text-black font-bold text-sm sm:text-lg"
                                            >{{ child.quantity }}x
                                            {{ child.product_name }}</span
                                        >
                                    </div>
                                </div>

                                <!-- Modifiers -->
                                <div
                                    v-if="
                                        item.modifiers &&
                                        item.modifiers.length > 0
                                    "
                                    class="ml-4 sm:ml-6 mt-2 space-y-1 sm:space-y-2 text-xs sm:text-sm text-gray-700 font-medium"
                                >
                                    <div
                                        v-for="modifier in item.modifiers"
                                        :key="modifier.id"
                                        class="flex gap-2 sm:gap-3"
                                    >
                                        <span
                                            class="text-purple-600 text-base sm:text-lg font-bold"
                                            >+</span
                                        >
                                        <span class="text-purple-700">{{
                                            modifier.modifier_name
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-if="index < batch.items.length - 1"
                                class="border-t border-gray-300"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </KitchenLayout>
</template>

<script setup lang="ts">
import KitchenLayout from "@/Layouts/KitchenLayout.vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref, onMounted, onUnmounted } from "vue";
import moment from "moment-timezone";

const props = defineProps<{
    pendingOrders: any[];
}>();

const page = usePage();
const currentTime = ref(moment());
const isConnected = ref(true);

// Get branchId from authenticated user
const branchId = computed(() => {
    return (page.props.auth as any)?.user?.branch_id || 1;
});

// Get timezone from page props
const timezone = computed(() => {
    return (page.props as any)?.generalSettings?.timezone || "UTC";
});

// Calculate longest wait time
const longestWaitTime = computed(() => {
    if (pendingOrdersWithMinutes.value.length === 0) return 0;
    return Math.max(
        ...pendingOrdersWithMinutes.value.map((b) => b.minutes_waiting)
    );
});

// Update current time every second for real-time updates
let timerInterval: NodeJS.Timeout | null = null;
let pollingInterval: NodeJS.Timeout | null = null;

onMounted(() => {
    // Update every second for real-time minute calculation
    timerInterval = setInterval(() => {
        currentTime.value = moment();
    }, 1000);

    // Poll for new orders every 5 seconds
    pollingInterval = setInterval(() => {
        router.get(
            route("resto.pending-orders.index"),
            {},
            {
                preserveState: true,
                replace: true,
                only: ["pendingOrders"],
            }
        );
    }, 5000);
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }

    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

// Compute minutes waiting for each batch in real-time
const pendingOrdersWithMinutes = computed(() => {
    return props.pendingOrders.map((batch) => ({
        ...batch,
        minutes_waiting: batch.placed_order_time
            ? currentTime.value
                  .tz(timezone.value)
                  .diff(
                      moment.utc(batch.placed_order_time).tz(timezone.value),
                      "minutes"
                  )
            : 0,
    }));
});

const toggleServed = (itemId: number, isServed: boolean) => {
    router.put(
        route("resto.pending-orders.toggle-served", {
            itemId,
        }),
        { is_served: isServed },
        {
            preserveScroll: true,
        }
    );
};

const formatPlacedOrderTime = (placedOrderTime: string | null) => {
    if (!placedOrderTime) return "";
    return moment.utc(placedOrderTime).tz(timezone.value).format("h:mm A");
};
</script>
