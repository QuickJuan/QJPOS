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

            <!-- Flex Layout of Batches -->
            <div class="flex flex-wrap gap-3 lg:gap-6">
                <div
                    v-for="batch in pendingOrdersWithMinutes"
                    :key="batch.batch_number"
                    class="w-full sm:w-[calc(50%-0.375rem)] lg:w-[calc(50%-0.75rem)] xl:w-[calc(33.333%-1rem)] 2xl:w-[calc(25%-1.125rem)] 3xl:w-[calc(20%-1.2rem)] bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition border border-gray-200"
                >
                    <!-- Batch Header -->
                    <div
                        :class="[
                            'text-white p-2 lg:p-3',
                            batch.minutes_waiting > 60
                                ? 'bg-gradient-to-r from-red-600 to-red-700'
                                : batch.minutes_waiting > 30
                                ? 'bg-gradient-to-r from-orange-500 to-orange-600'
                                : 'bg-gradient-to-r from-blue-500 to-blue-600',
                        ]"
                    >
                        <div class="flex items-start justify-between mb-1">
                            <div class="flex-1">
                                <h2
                                    class="text-sm lg:text-sm font-bold text-white mb-0.5"
                                >
                                    Batch #{{ batch.batch_number }}
                                </h2>
                                <p
                                    class="text-xs lg:text-xs text-blue-100 font-medium"
                                >
                                    {{ batch.table_name }}
                                </p>
                                <p
                                    class="text-xs lg:text-xs text-blue-100 mt-0.5"
                                >
                                    {{
                                        formatPlacedOrderTime(
                                            batch.placed_order_time
                                        )
                                    }}
                                </p>
                            </div>
                            <button
                                @click="printBatch(batch)"
                                class="bg-white/20 hover:bg-white/30 p-1.5 rounded transition"
                                title="Print Order"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                    />
                                </svg>
                            </button>
                        </div>
                        <div
                            class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-1 lg:gap-0"
                        >
                            <div class=""></div>
                            <div class="text-left lg:text-right">
                                <div
                                    class="text-lg lg:text-base font-bold text-white mb-0.5"
                                >
                                    {{ batch.minutes_waiting }} min(s)
                                </div>

                                <p
                                    class="text-xs lg:text-xs text-blue-100 mt-1 font-medium"
                                >
                                    {{ batch.items.length }} items
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="">
                        <div
                            v-for="(item, index) in batch.items"
                            :key="item.id"
                        >
                            <div
                                class="p-2 lg:px-4"
                                :class="
                                    item.is_served ? 'bg-green-50' : 'bg-white'
                                "
                            >
                                <!-- Main Item -->
                                <div
                                    class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-1 sm:gap-2"
                                >
                                    <div class="flex-1">
                                        <div class="flex items-start gap-1.5">
                                            <span
                                                class="text-base font-semibold text-gray-900 mt-0.5"
                                            >
                                                {{ item.quantity }}
                                            </span>
                                            <h3
                                                class="font-normal text-base text-gray-900"
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
                                            'px-2 py-1 rounded text-base font-medium whitespace-nowrap',
                                            item.is_served
                                                ? 'bg-green-600 text-white'
                                                : 'bg-orange-500 text-white',
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
                                    class="ml-3 mt-0.5 space-y-0 text-gray-800"
                                >
                                    <div
                                        v-for="child in item.children"
                                        :key="child.id"
                                        class="flex gap-1 text-base"
                                    >
                                        <span class="font-semibold">{{
                                            child.quantity
                                        }}</span>
                                        <span class="font-normal">{{
                                            child.product_name
                                        }}</span>
                                    </div>
                                </div>

                                <!-- Modifiers -->
                                <div
                                    v-if="
                                        item.modifiers &&
                                        item.modifiers.length > 0
                                    "
                                    class="ml-3 mt-0.5 space-y-0 text-sm text-gray-500"
                                >
                                    <div
                                        v-for="modifier in item.modifiers"
                                        :key="modifier.id"
                                        class="flex gap-1"
                                    >
                                        <span
                                            class="text-purple-600 font-medium"
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
                                class="border-t border-gray-200"
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

const printBatch = async (batch: any) => {
    try {
        // Filter out served items
        const unservedItems = batch.items.filter(
            (item: any) => !item.is_served
        );

        if (unservedItems.length === 0) {
            throw new Error(
                "All items in this batch have already been served."
            );
        }

        // Transform batch.items to match the printer's expected format
        const placedOrderItems = [
            {
                orderType:
                    unservedItems.length > 0
                        ? unservedItems[0].order_type || "dine-in"
                        : "dine-in",
                items: unservedItems.map((item: any) => ({
                    id: item.id,
                    description: item.product_name,
                    packaging: item.unit_measure || "pc",
                    qty: item.quantity.toString(),
                    servingNumber: batch.serving_number || null,
                    modifiers: item.modifiers || [],
                    notes: item.notes || null,
                    orderType: item.order_type || "dine-in",
                    preparationLocation: item.preparation_location || "",
                    printable: true,
                    showOnScreen: true,
                    children:
                        item.children?.map((child: any) => ({
                            id: child.id,
                            description: child.product_name,
                            packaging: child.unit_measure || "pc",
                            qty: child.quantity.toString(),
                        })) || [],
                })),
                totalItems: unservedItems.length,
            },
        ];

        // Connect to thermal printer
        const { thermalPrinter } = await import(
            "@/Services/ThermalPrinterService"
        );

        if (!thermalPrinter.isConnected()) {
            const connected = await thermalPrinter.connectToPrinterType(
                "kitchen"
            );
            if (!connected) {
                throw new Error(
                    "Printer not connected. Please connect a kitchen printer first."
                );
            }
        }

        // Print using the same method as reprint
        await thermalPrinter.printPlacedOrder(
            batch.batch_number,
            batch.table_name,
            placedOrderItems,
            batch.served_by || "N/A",
            batch.serving_number || null
        );

        console.log(`✅ Batch #${batch.batch_number} printed successfully`);
    } catch (error: any) {
        console.error("Print error:", error);
        alert(error?.message || "Failed to print order. Please try again.");
    }
};
</script>
