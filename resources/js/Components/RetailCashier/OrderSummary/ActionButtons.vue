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
                        })
                    "
                    :disabled="orderItems.every((item) => item.is_served)"
                    class="py-3 px-4 bg-success-600 text-white rounded-lg font-semibold hover:bg-success-700 disabled:bg-success-400 disabled:cursor-not-allowed transition-colors"
                >
                    Place Order
                </button>
                <button
                    @click="showSettleBillModal = true"
                    :disabled="orderItems.length <= 0"
                    class="py-3 px-4 bg-success-600 text-white rounded-lg font-semibold hover:bg-success-700 disabled:bg-success-400 disabled:cursor-not-allowed transition-colors"
                >
                    Settle Bill
                </button>
            </div>
        </div>

        <!-- Order Type Selection Modal -->
        <Dialog
            v-model:visible="showOrderTypeModal"
            modal
            header="Select Order Type"
            :style="{ width: '20rem' }"
        >
            <div class="space-y-3">
                <button
                    v-for="type in orderTypes"
                    :key="type.value"
                    @click="selectOrderType(type.value)"
                    :class="[
                        'w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3',
                        selectedOrderType === type.value
                            ? 'bg-primary text-white'
                            : 'bg-gray-100 text-secondary-700 hover:bg-gray-200',
                    ]"
                >
                    <component :is="type.icon" class="w-5 h-5" />
                    <div class="text-left">
                        <div class="font-semibold">{{ type.label }}</div>
                        <div class="text-xs opacity-75">
                            {{ type.description }}
                        </div>
                    </div>
                    <CheckIcon
                        v-if="selectedOrderType === type.value"
                        class="w-5 h-5 ml-auto"
                    />
                </button>
            </div>
        </Dialog>

        <!-- More Options Modal -->
        <Dialog
            v-model:visible="showMoreOptionsModal"
            modal
            header="More Options"
            :style="{ width: '20rem' }"
        >
            <div class="space-y-3">
                <button
                    @click="handleSaveOrder"
                    :disabled="orderItems.length === 0"
                    class="w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3 bg-gray-100 text-secondary-700 hover:bg-gray-200 disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                >
                    <BookmarkIcon class="w-5 h-5" />
                    <div class="text-left">
                        <div class="font-semibold">Save Order</div>
                        <div class="text-xs opacity-75">
                            Save order for later processing
                        </div>
                    </div>
                </button>

                <button
                    @click="handleApplyDiscount"
                    :disabled="selectedItemsForDiscount.length === 0"
                    class="w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3 bg-gray-100 text-secondary-700 hover:bg-gray-200 disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                >
                    <TagIcon class="w-5 h-5" />
                    <div class="text-left flex-1">
                        <div class="font-semibold flex items-center gap-2">
                            Apply Discount
                            <span
                                v-if="selectedItemsForDiscount.length > 0"
                                class="bg-yellow-600 text-white text-xs px-1.5 py-0.5 rounded-full"
                            >
                                {{ selectedItemsForDiscount.length }}
                            </span>
                        </div>
                        <div class="text-xs opacity-75">
                            Apply discount to selected items
                        </div>
                    </div>
                </button>
            </div>
        </Dialog>

        <!-- Settle Bill Modal -->
        <Dialog
            v-model:visible="showSettleBillModal"
            modal
            header="Settle Bill"
            :style="{ width: '25rem' }"
        >
            <form @submit.prevent="handleSettleBill" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Total Amount
                    </label>
                    <div class="text-2xl font-bold text-gray-900">
                        {{ formatMoney(totalAmount) }}
                    </div>
                </div>

                <div>
                    <TextField
                        label="Amount Paide"
                        id="amount_paid"
                        v-model="amountPaid"
                        type="number"
                        step="0.01"
                        min="0"
                        required
                        placeholder="Enter amount paid"
                    />
                </div>

                <div v-if="amountPaid > 0" class="bg-gray-50 p-3 rounded-md">
                    <div class="flex justify-between text-sm">
                        <span>Change:</span>
                        <span class="font-semibold">
                            {{
                                formatMoney(
                                    Math.max(0, amountPaid - totalAmount)
                                )
                            }}
                        </span>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button
                        type="button"
                        @click="showSettleBillModal = false"
                        class="flex-1 py-2 px-4 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="amountPaid < totalAmount"
                        class="flex-1 py-2 px-4 bg-success-600 text-white rounded-md hover:bg-success-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                    >
                        Settle Bill
                    </button>
                </div>
            </form>
        </Dialog>

        <!-- Receipt Modal -->
        <Dialog
            v-model:visible="showReceiptModal"
            modal
            header="Receipt"
            :style="{ width: '80rem' }"
            :closable="false"
        >
            <div class="space-y-4">
                <!-- Receipt Preview -->
                <div
                    class="border border-gray-200 rounded-lg p-4 bg-gray-50 max-h-96 overflow-y-auto"
                >
                    <ReceiptLayout
                        :business-name="'Quick Juan Restaurant'"
                        :business-address="'123 Main Street, Makati City, Philippines'"
                        :business-phone="'(02) 123-4567'"
                        :receipt-number="receiptData.receiptNumber"
                        :receipt-date="receiptData.date"
                        :table-number="tableId ? `Table ${tableId}` : undefined"
                        :cashier-name="'John Doe'"
                        :order-type="selectedOrderType"
                        :order-items="orderItems"
                        :subtotal="receiptData.subtotal"
                        :tax-amount="receiptData.taxAmount"
                        :discount-amount="receiptData.discountAmount"
                        :total-amount="receiptData.totalAmount"
                        :payment-info="receiptData.paymentInfo"
                        :footer-message="'Generated by Quick Juan POS System'"
                    />
                </div>

                <div class="flex gap-3 pt-4 border-t">
                    <button
                        type="button"
                        @click="printReceipt"
                        class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                    >
                        Print Receipt
                    </button>
                    <button
                        type="button"
                        @click="closeReceiptModal"
                        class="flex-1 py-2 px-4 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors"
                    >
                        Close
                    </button>
                </div>
            </div>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { Dialog } from "primevue";
import {
    HomeIcon,
    ShoppingBagIcon,
    TruckIcon,
    ChevronDownIcon,
    CheckIcon,
    TagIcon,
    BookmarkIcon,
} from "@heroicons/vue/24/outline";
import { formatMoney } from "@/Utils/FormatMoney";
import TextInput from "@/Components/Form/TextInput.vue";
import TextField from "@/Components/Form/TextField.vue";
import ReceiptLayout from "@/Components/ReceiptLayout.vue";

const props = defineProps<{
    cart: any;
    tableId: number;
    orderItems: any[];
    selectedOrderType: string;
    selectedItemsForDiscount: number[];
    totalAmount: number;
}>();

const emit = defineEmits<{
    updateOrderType: [type: string];
    saveOrder: [];
    checkout: [data: any];
    openDiscountModal: [];
    settleBill: [data: any];
}>();

// Modal visibility states
const showOrderTypeModal = ref(false);
const showMoreOptionsModal = ref(false);
const showSettleBillModal = ref(false);
const showReceiptModal = ref(false);

// Settle bill form data
const amountPaid = ref(0);

// Receipt data
const receiptData = ref({
    receiptNumber: "001234",
    date: new Date().toISOString(),
    subtotal: 0,
    taxAmount: 0,
    discountAmount: 0,
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
const handleSettleBill = () => {
    // Prepare receipt data
    const subtotal = props.orderItems.reduce(
        (sum, item) => sum + (item.price || 0),
        0
    );
    const discountAmount = props.orderItems.reduce(
        (sum, item) => sum + (item.discount || 0),
        0
    );
    const discountedSubtotal = subtotal - discountAmount;
    const taxAmount = discountedSubtotal * 0.12; // 12% VAT

    receiptData.value = {
        receiptNumber: `RCP-${Date.now()}`,
        date: new Date().toISOString(),
        subtotal: subtotal,
        taxAmount: taxAmount,
        discountAmount: discountAmount,
        totalAmount: props.totalAmount,
        paymentInfo: {
            amount_paid: amountPaid.value,
            change: amountPaid.value - props.totalAmount,
            method: "Cash",
        },
    };

    emit("settleBill", {
        cart_id: props.cart.id,
        amount_paid: amountPaid.value,
        total_amount: props.totalAmount,
    });

    showSettleBillModal.value = false;
    showReceiptModal.value = true;
    amountPaid.value = 0;
};

// Print receipt
const printReceipt = () => {
    window.print();
};

// Close receipt modal
const closeReceiptModal = () => {
    showReceiptModal.value = false;
};
</script>
