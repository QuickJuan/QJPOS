<template>
    <CashieringLayout>
        <div class="p-6 bg-gray-50 min-h-screen">
            <h1 class="text-3xl font-bold mb-8">Pending Orders</h1>

            <div v-if="pendingOrders.length === 0" class="text-center py-24">
                <p class="text-gray-500 text-xl">No pending orders</p>
            </div>

            <!-- Grid of Batches -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="batch in pendingOrdersWithMinutes"
                    :key="batch.batch_number"
                    class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition"
                >
                    <!-- Batch Header -->
                    <div
                        class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h2 class="text-lg font-bold">
                                    Batch #{{ batch.batch_number }}
                                </h2>
                                <p class="text-base text-blue-100">
                                    {{ batch.table_name }}
                                </p>
                                <p class="text-base text-blue-200 mt-1">
                                    {{
                                        formatPlacedOrderTime(
                                            batch.placed_order_time
                                        )
                                    }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold">
                                    {{ batch.minutes_waiting }}
                                    <span class="text-xs text-blue-100"
                                        >Min(s)</span
                                    >
                                </div>

                                <p>{{ batch.items.length }} items</p>
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
                                class="p-4 hover:bg-gray-50 transition"
                                :class="item.is_served ? 'bg-green-50' : ''"
                            >
                                <!-- Main Item -->
                                <div
                                    class="flex items-start justify-between gap-2 mb-2"
                                >
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-sm font-semibold bg-gray-200 text-gray-800 px-2 py-1 rounded"
                                            >
                                                {{ item.quantity }}x
                                            </span>
                                            <h3 class="font-semibold text-sm">
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
                                            'px-3 py-1.5 rounded text-xs font-semibold whitespace-nowrap transition',
                                            item.is_served
                                                ? 'bg-green-600 text-white hover:bg-green-700'
                                                : 'bg-gray-300 text-gray-700 hover:bg-gray-400',
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
                                    class="ml-4 mt-2 space-y-1 text-xs text-gray-600"
                                >
                                    <div
                                        v-for="child in item.children"
                                        :key="child.id"
                                        class="flex gap-2"
                                    >
                                        <span class="text-gray-400">•</span>
                                        <span
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
                                    class="ml-4 mt-1 space-y-1 text-xs text-gray-600"
                                >
                                    <div
                                        v-for="modifier in item.modifiers"
                                        :key="modifier.id"
                                        class="flex gap-2"
                                    >
                                        <span class="text-gray-400">+</span>
                                        <span>{{
                                            modifier.modifier_name
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-if="index < batch.items.length - 1"
                                class="border-t border-gray-200"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import { router, usePage } from "@inertiajs/vue3";
import { computed, ref, onMounted, onUnmounted } from "vue";
import moment from "moment-timezone";

const props = defineProps<{
    pendingOrders: any[];
}>();

const page = usePage();
const currentTime = ref(moment());

// Get timezone from page props
const timezone = computed(() => {
    return (page.props as any)?.generalSettings?.timezone || "UTC";
});

// Update current time every second for real-time updates
let timerInterval: NodeJS.Timeout | null = null;

onMounted(() => {
    // Update every second for real-time minute calculation
    timerInterval = setInterval(() => {
        currentTime.value = moment();
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
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
        route("resto.pending-orders.toggle-served", { itemId }),
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
