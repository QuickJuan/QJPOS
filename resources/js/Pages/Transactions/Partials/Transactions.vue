<template>
    <div class="border h-full">
        <div
            class="flex items-center justify-between px-5 py-4 border-b border-gray-100"
        >
            <div>
                <p class="text-lg font-semibold text-gray-900">
                    Latest Transactions
                </p>
            </div>
            <span
                class="text-xs font-medium px-3 py-1 bg-primary/10 text-primary rounded-full"
            >
                {{ props.orders.total }}
            </span>
        </div>
        <div
            class="divide-y divide-gray-100 max-h-[60vh] md:max-h-[75vh] lg:max-h-[80vh] overflow-y-auto"
        >
            <button
                v-for="order in props.orders.data"
                :key="order.id"
                @click="selectOrder(order)"
                :class="[
                    'w-full text-left px-5 py-4 transition hover:bg-primary/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40',
                    activeOrder?.id === order.id ? 'bg-primary/5' : 'bg-white',
                ]"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="h-12 w-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-semibold"
                    >
                        <!-- {{ order.cashier?.name?.charAt(0) }} -->
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <p
                                class="font-semibold text-gray-900 text-base md:text-lg"
                            >
                                {{ orderListingSubtitle(order) }}
                            </p>
                            <span class="text-xs md:text-sm text-gray-400">
                                {{ formatDate(order.created_at) }}
                            </span>
                        </div>
                        <p
                            class="text-sm md:text-base text-gray-500 mt-1 line-clamp-2"
                        >
                            {{ order.cashier?.name || "Unknown" }}
                        </p>
                    </div>
                </div>
            </button>
            <div
                v-if="!hasOrders"
                class="px-5 py-12 text-center text-gray-400 text-sm"
            >
                No transactions found with the current filters.
            </div>
        </div>
        <div
            v-if="hasOrders"
            class="px-5 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500"
        >
            <span>Showing {{ orders.from }} - {{ orders.to }}</span>
            <div class="flex gap-2">
                <button
                    v-for="link in orders.links"
                    :key="link.label"
                    v-html="link.label"
                    @click="$emit('goToPage', link.url)"
                    :class="[
                        'px-3 py-1 rounded-full border text-xs',
                        link.active
                            ? 'bg-primary text-white border-primary'
                            : 'border-gray-200 text-gray-500 hover:bg-gray-50',
                    ]"
                ></button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Order from "@/Types/Order/Order";
import OrderResponse from "@/Types/Order/OrderResponse";
import { formatDate } from "@/Utils/FormatDate";
import { computed, ref } from "vue";

const props = defineProps<{
    orders: OrderResponse;
    activeOrder: Order;
}>();

const emit = defineEmits(["goToPage", "selectOrder"]);
const activeOrder = ref<Order | null>(null);

const selectOrder = (order: Order) => {
    emit("selectOrder", order);
    activeOrder.value = order;
};

const orderListingSubtitle = (order: Order) => {
    return `${
        order?.customer?.customer_name || order?.table_room?.name || "Walk-in"
    }`;
};

const hasOrders = computed(
    () => props.orders.data && props.orders.data.length > 0
);
</script>
