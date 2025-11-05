<template>
    <div class="p-4 border-t border-gray-200 bg-white">
        <div class="space-y-3">
            <!-- Order Type Button -->
            <button
                @click="showOrderTypeModal = true"
                class="w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-secondary-700 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
            >
                <component :is="selectedOrderTypeData.icon" class="w-4 h-4" />
                <span>{{ selectedOrderTypeData.label }}</span>
                <ChevronDownIcon class="w-4 h-4 ml-auto" />
            </button>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-3">
                <button
                    @click="showMoreOptionsModal = true"
                    :disabled="orderItems.length === 0"
                    class="py-3 px-4 bg-secondary-600 text-white rounded-lg font-semibold hover:bg-secondary-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2"
                >
                    <span>More Options</span>
                    <ChevronDownIcon class="w-4 h-4" />
                </button>
                <button
                    v-if="tableId"
                    @click="
                        $emit('checkout', {
                            cart_id: props.cart.id,
                            discount_id: appliedDiscount?.discountId,
                        })
                    "
                    :disabled="orderItems.every((item) => item.is_served)"
                    class="py-3 px-4 bg-success-600 text-white rounded-lg font-semibold hover:bg-success-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                >
                    Place Order
                </button>
                <button
                    @click="showSettleBillModal = true"
                    :disabled="orderItems.length <= 0 || !orderItems.every((item) => item.is_served)"
                    class="py-3 px-4 bg-success-600 text-white rounded-lg font-semibold hover:bg-success-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                >
                    Settle Bill
                </button>
            </div>
        </div>

        <!-- Order Type Selection Modal -->
        <OrderTypeSelectionModal
            v-model:visible="showOrderTypeModal"
            :selected-order-type="selectedOrderType"
            :order-types="orderTypes"
            @update-order-type="selectOrderType"
        />

        <!-- More Options Modal -->
        <MoreOptionsModal
            v-model:visible="showMoreOptionsModal"
            :order-items="orderItems"
            :selected-items-for-discount="selectedItemsForDiscount"
            @save-order="handleSaveOrder"
            @open-discount-modal="handleApplyDiscount"
        />

        <!-- Settle Bill Modal -->
        <SettleBillModal
            v-model:visible="showSettleBillModal"
            :cart="cart"
            :order-items="orderItems"
            :selected-order-type="selectedOrderType"
            :total-amount="totalAmount"
            :applied-discount="appliedDiscount"
            @settle-bill="handleSettleBill"
        />

        <!-- Receipt Modal -->
        <ReceiptModal
            v-model:visible="showReceiptModal"
            :receipt-data="receiptData"
            :order-items="orderItems"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { ChevronDownIcon } from "@heroicons/vue/24/outline";
import {
    HomeIcon,
    ShoppingBagIcon,
    TruckIcon,
} from "@heroicons/vue/24/outline";
import OrderTypeSelectionModal from "./OrderTypeSelectionModal.vue";
import MoreOptionsModal from "./MoreOptionsModal.vue";
import SettleBillModal from "./SettleBillModal.vue";
import ReceiptModal from "./ReceiptModal.vue";

const props = defineProps<{
    cart: any;
    tableId: number;
    orderItems: any[];
    selectedOrderType: string;
    selectedItemsForDiscount: number[];
    totalAmount: number;
    appliedDiscount: any;
}>();

const emit = defineEmits<{
    updateOrderType: [type: string];
    saveOrder: [];
    checkout: [data: any];
    openDiscountModal: [];
    settleBill: [data: any];
}>();

// Use applied discount from props
const appliedDiscount = computed(() => props.appliedDiscount);

// UsePage
const page = usePage();

// Modal visibility states
const showOrderTypeModal = ref(false);
const showMoreOptionsModal = ref(false);
const showSettleBillModal = ref(false);
const showReceiptModal = ref(false);

// Receipt data
const receiptData = ref({
    receiptNumber: "001234",
    date: new Date().toISOString(),
    tableNumber: "",
    cashierName: "",
    orderType: "",
    orderItems: [] as any[],
    subtotal: 0,
    taxAmount: 0,
    discountAmount: 0,
    discountName: null as string | null,
    discountType: null as string | null,
    removeTax: false,
    isSeniorDiscount: false,
    totalAmount: 0,
    paymentInfo: null as any,
});

const orderTypes = [
    {
        value: "dine-in",
        label: "Dine-in",
        icon: HomeIcon,
        activeClass: "bg-primary text-white",
        description: "Customer will eat at the restaurant",
    },
    {
        value: "takeout",
        label: "Takeout",
        icon: ShoppingBagIcon,
        activeClass: "bg-success text-white",
        description: "Customer will take food to go",
    },
    {
        value: "delivery",
        label: "Delivery",
        icon: TruckIcon,
        activeClass: "bg-warning text-white",
        description: "Food will be delivered to customer",
    },
];

// Get selected order type data
const selectedOrderTypeData = computed(() => {
    return (
        orderTypes.find((type) => type.value === props.selectedOrderType) ||
        orderTypes[0]
    );
});

// Select order type and close modal
const selectOrderType = (type: string) => {
    emit("updateOrderType", type);
    showOrderTypeModal.value = false;
};

// Handle save order from more options modal
const handleSaveOrder = () => {
    emit("saveOrder");
    showMoreOptionsModal.value = false;
};

// Handle apply discount from more options modal
const handleApplyDiscount = () => {
    emit("openDiscountModal");
    showMoreOptionsModal.value = false;
};

// Handle settle bill form submission
const handleSettleBill = (data: any) => {
    // Update receipt data from settle bill modal
    if (data.receipt_data) {
        receiptData.value = data.receipt_data;
    }
    emit("settleBill", data);
    showReceiptModal.value = true;
};
</script>
