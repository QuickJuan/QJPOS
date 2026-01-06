<template>
    <div class="border flex flex-col flex-grow overflow-hidden">
        <div
            class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0"
        >
            <div>
                <p class="text-lg font-semibold text-gray-900">
                    Latest Transactions
                </p>
            </div>
            <span
                class="text-xs font-medium px-3 py-1 bg-primary/10 text-primary rounded-full"
            >
                {{ props.orders?.total }}
            </span>
        </div>
        <div class="divide-y divide-gray-100 flex-1 overflow-y-auto">
            <button
                v-for="(order, index) in props.orders.data"
                :key="order.id"
                @click="selectOrder(order)"
                :class="[
                    'w-full text-left px-5 py-4 transition cursor-pointer hover:bg-primary/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40',
                    activeOrder?.id === order.id
                        ? 'bg-primary/5'
                        : index % 2 === 0
                        ? 'bg-white'
                        : 'bg-gray-50',
                ]"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="h-12 w-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-semibold"
                    >
                        <!-- {{ order.cashier?.name?.charAt(0) }} -->
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-col">
                            <p
                                class="font-semibold text-gray-900 text-base xl:text-lg"
                            >
                                {{ orderListingSubtitle(order) }}
                            </p>
                            <p>
                                Invoice No:
                                <span
                                    class="font-semibold text-gray-900 text-base xl:text-lg"
                                >
                                    {{ order?.invoice_no }}
                                </span>
                            </p>
                            <span class="text-xs md:text-sm text-gray-400">
                                {{
                                    formatDateTime(
                                        order.order_date || order.created_at
                                    )
                                }}
                            </span>
                        </div>
                        <p
                            class="text-sm md:text-base text-gray-500 mt-1 line-clamp-2"
                        >
                            Cashier: {{ order.cashier?.name || "Unknown" }}
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
            v-if="hasOrders && showPagination"
            class="px-5 py-4 border-t border-gray-100 flex flex-col space-y-4 xl:flex-row items-center justify-between text-sm text-gray-500 flex-shrink-0"
        >
            <span>Showing {{ orders?.meta.from }} - {{ orders?.meta.to }}</span>
            <div class="flex gap-2 items-center">
                <button
                    @click="$emit('goToPage', prevPageUrl)"
                    :disabled="!prevPageUrl"
                    :class="[
                        'px-3 py-2 rounded-lg border text-sm flex items-center gap-1',
                        prevPageUrl
                            ? 'border-gray-200 text-gray-700 hover:bg-gray-50'
                            : 'border-gray-100 text-gray-300 cursor-not-allowed',
                    ]"
                >
                    <i class="pi pi-chevron-left text-xs"></i>
                    <span class="hidden sm:inline">Previous</span>
                </button>
                <span class="text-xs text-gray-500">
                    Page {{ orders?.current_page }}
                </span>
                <button
                    @click="$emit('goToPage', nextPageUrl)"
                    :disabled="!nextPageUrl"
                    :class="[
                        'px-3 py-2 rounded-lg border text-sm flex items-center gap-1',
                        nextPageUrl
                            ? 'border-gray-200 text-gray-700 hover:bg-gray-50'
                            : 'border-gray-100 text-gray-300 cursor-not-allowed',
                    ]"
                >
                    <span class="hidden sm:inline">Next</span>
                    <i class="pi pi-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Order from "@/Types/Order/Order";
import OrderResponse from "@/Types/Order/OrderResponse";
import { formatDate } from "@/Utils/FormatDate";
import { computed, ref } from "vue";
import moment from "moment";

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
        order?.customer?.name || order?.table_number || "Walk-in Customer"
    }`;
};

const formatDateTime = (dateString: string) => {
    return moment(dateString).format("MMM. DD, YYYY h:mm A");
};

const hasOrders = computed(
    () => props.orders.data && props.orders.data.length > 0
);

const prevPageUrl = computed(() => {
    // API response has links as object with prev/next properties
    return props.orders?.links?.prev || null;
});

const nextPageUrl = computed(() => {
    // API response has links as object with prev/next properties
    return props.orders?.links?.next || null;
});

const showPagination = computed(() => {
    return prevPageUrl.value || nextPageUrl.value;
});
</script>
