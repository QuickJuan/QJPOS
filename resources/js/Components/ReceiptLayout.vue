<template>
    <div
        class="receipt-container max-w-xs mx-auto bg-white p-4 font-mono text-xs leading-tight"
    >
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="text-lg font-bold mb-1">{{ businessName }}</h1>
            <p class="text-xs">{{ businessAddress }}</p>
            <p class="text-xs">{{ businessPhone }}</p>
            <div class="border-b border-dashed my-2"></div>
        </div>

        <!-- Receipt Info -->
        <div class="mb-3">
            <div class="flex justify-between">
                <span>Receipt #:</span>
                <span>{{ receiptNumber }}</span>
            </div>
            <div class="flex justify-between">
                <span>Date:</span>
                <span>{{ formatDate(receiptDate) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Time:</span>
                <span>{{ formatTime(receiptDate) }}</span>
            </div>
            <div class="flex justify-between" v-if="tableNumber">
                <span>Table:</span>
                <span>{{ tableNumber }}</span>
            </div>
            <div class="flex justify-between" v-if="cashierName">
                <span>Cashier:</span>
                <span>{{ cashierName }}</span>
            </div>
            <div class="flex justify-between" v-if="orderType">
                <span>Order Type:</span>
                <span>{{ orderType }}</span>
            </div>
        </div>

        <div class="border-b border-dashed my-2"></div>

        <!-- Order Items -->
        <div class="mb-3">
            <div class="text-center font-bold mb-2">ORDER ITEMS</div>
            <div v-for="item in orderItems" :key="item.id" class="mb-1">
                <div class="flex justify-between">
                    <span class="flex-1">{{ item.product_name }}</span>
                    <span>{{ formatMoney(item.price) }}</span>
                </div>
                <div
                    class="flex justify-between text-xs text-gray-600 ml-2"
                    v-if="item.quantity > 1"
                >
                    <span
                        >{{ item.quantity }} x
                        {{ formatMoney(item.unit_price) }}</span
                    >
                    <span></span>
                </div>
                <!-- Selected Options -->
                <div
                    v-if="
                        item.selected_options &&
                        item.selected_options.length > 0
                    "
                    class="ml-2 text-xs"
                >
                    <div
                        v-for="option in item.selected_options"
                        :key="option.id"
                        class="flex justify-between"
                    >
                        <span>+ {{ option.name }}</span>
                        <span>{{ formatMoney(option.price) }}</span>
                    </div>
                </div>
                <!-- Discount -->
                <div v-if="item.discount > 0" class="ml-2 text-xs text-red-600">
                    <div class="flex justify-between">
                        <span>Discount</span>
                        <span>-{{ formatMoney(item.discount) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-b border-dashed my-2"></div>

        <!-- Totals -->
        <div class="mb-3">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>{{ formatMoney(subtotal) }}</span>
            </div>
            <div class="flex justify-between" v-if="taxAmount > 0">
                <span>Tax (12%):</span>
                <span>{{ formatMoney(taxAmount) }}</span>
            </div>
            <div class="flex justify-between" v-if="discountAmount > 0">
                <span class="text-red-600">Discount:</span>
                <span class="text-red-600"
                    >-{{ formatMoney(discountAmount) }}</span
                >
            </div>
            <div
                class="border-t border-dashed my-1 pt-1 flex justify-between font-bold"
            >
                <span>TOTAL:</span>
                <span>{{ formatMoney(totalAmount) }}</span>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="mb-3" v-if="paymentInfo">
            <div class="text-center font-bold mb-2">PAYMENT</div>
            <div class="flex justify-between">
                <span>Amount Paid:</span>
                <span>{{ formatMoney(paymentInfo.amount_paid) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Change:</span>
                <span>{{ formatMoney(paymentInfo.change) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Payment Method:</span>
                <span>{{ paymentInfo.method || "Cash" }}</span>
            </div>
        </div>

        <div class="border-b border-dashed my-2"></div>

        <!-- Footer -->
        <div class="text-center text-xs">
            <p class="mb-1">Thank you for dining with us!</p>
            <p class="mb-1">Please come again.</p>
            <p class="text-xs text-gray-500 mt-2">{{ footerMessage }}</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

// Props
const props = defineProps<{
    businessName?: string;
    businessAddress?: string;
    businessPhone?: string;
    receiptNumber?: string;
    receiptDate?: string;
    tableNumber?: string;
    cashierName?: string;
    orderType?: string;
    orderItems?: any[];
    subtotal?: number;
    taxAmount?: number;
    discountAmount?: number;
    totalAmount?: number;
    paymentInfo?: any;
    footerMessage?: string;
}>();

// Default values
const businessName = computed(() => props.businessName || "Quick Juan POS");
const businessAddress = computed(
    () => props.businessAddress || "123 Main Street, City, State"
);
const businessPhone = computed(() => props.businessPhone || "(555) 123-4567");
const receiptNumber = computed(() => props.receiptNumber || "001234");
const receiptDate = computed(
    () => props.receiptDate || new Date().toISOString()
);
const orderItems = computed(() => props.orderItems || []);
const subtotal = computed(() => props.subtotal || 0);
const taxAmount = computed(() => props.taxAmount || 0);
const discountAmount = computed(() => props.discountAmount || 0);
const totalAmount = computed(() => props.totalAmount || 0);
const footerMessage = computed(
    () => props.footerMessage || "Generated by Quick Juan POS System"
);

// Helper functions
const formatMoney = (amount: number) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });
};
</script>

<style scoped>
.receipt-container {
    font-family: "Courier New", monospace;
    line-height: 1.2;
    max-width: 300px;
}

/* Print styles */
@media print {
    .receipt-container {
        max-width: none;
        margin: 0;
        padding: 10px;
        font-size: 10px;
    }
}
</style>
