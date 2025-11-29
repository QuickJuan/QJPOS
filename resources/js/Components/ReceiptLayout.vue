<template>
    <div
        :class="[
            'receipt-container bg-white p-4 font-mono text-xs leading-tight',
            embedded ? '' : 'max-w-xs mx-auto',
        ]"
    >
        <!-- Header -->
        <div class="text-center mb-4">
            <div v-if="businessLogo">
                <img
                    :src="businessLogo"
                    alt="Company Logo"
                    class="w-16 h-auto mx-auto mb-2"
                />
            </div>
            <h1 class="text-lg font-bold mb-1">{{ businessName }}</h1>
            <p class="text-xs">{{ businessAddress }}</p>
            <p class="text-xs">{{ businessPhone }}</p>
            <div class="border-b border-dashed"></div>
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

        <div class="border-b border-dashed"></div>

        <!-- Order Items -->
        <div class="my-3">
            <div class="text-center font-bold mb-2">ORDER ITEMS</div>
            <div
                v-for="(items, orderType) in groupedOrderItems"
                :key="orderType"
                class="mb-4"
            >
                <!-- Order Type Header -->
                <div class="text-center font-semibold text-sm mb-2 pb-1">
                    {{ getOrderTypeLabel(String(orderType)) }}
                </div>

                <div v-for="item in items" :key="item.id" class="my-3">
                    <template v-if="item.parent_id == null">
                        <div class="flex justify-between">
                            <span class="flex-1">{{ item.name }}</span>
                        </div>
                        <div
                            v-if="item.quantity >= 1"
                            class="ml-2 text-xs text-gray-600"
                        >
                            <div class="flex justify-between">
                                <span>
                                    {{ item.quantity }} x
                                    {{ formatMoney(item.price) }}
                                </span>
                                <span>{{ formatMoney(item.amount) }}</span>
                            </div>
                        </div>

                        <!-- Less Tax -->
                        <div
                            v-if="item.less_tax > 0"
                            class="ml-2 text-xs text-gray-600"
                        >
                            <div class="flex justify-between">
                                <span>Less Tax: </span>
                                <span>-{{ formatMoney(item.less_tax) }}</span>
                            </div>
                        </div>

                        <!-- Less Discount -->
                        <div
                            v-if="item.discount > 0"
                            class="ml-2 text-xs text-gray-600"
                        >
                            <div class="flex justify-between">
                                <span>Less Discount: </span>
                                <span>-{{ formatMoney(item.discount) }}</span>
                            </div>
                        </div>

                        <!-- Selected Options -->
                        <div
                            v-if="item.children && item.children.length > 0"
                            class="ml-2 mb-4 space-y-1"
                        >
                            <div
                                v-for="option in item.children"
                                :key="option.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-2 flex-1">
                                    <span class="text-xs font-medium"> • </span>
                                    <span class="text-xs flex-1 ml-2">
                                        {{ option.product.name }}
                                    </span>
                                </div>
                                <span class="text-xs font-medium">
                                    +{{ formatMoney(option.price) }}
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="border-b border-dashed my-3"></div>

        <!-- Totals -->
        <div class="mt-3 mb-5">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>{{ formatMoney(subtotal) }}</span>
            </div>
            <div
                class="border-t border-dashed pt-1 flex justify-between font-bold"
            >
                <span>TOTAL:</span>
                <span>{{ formatMoney(totalAmount) }}</span>
            </div>
            <div class="flex justify-between" v-if="paymentInfo">
                <span>Payment Received:</span>
                <span>{{ formatMoney(paymentInfo.amount_paid) }}</span>
            </div>
            <div class="flex justify-between" v-if="paymentInfo">
                <span>Change:</span>
                <span>{{ formatMoney(paymentInfo.change) }}</span>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="mb-3" v-if="taxInfo">
            <div class="flex justify-between">
                <span>VAT Sales:</span>
                <span>{{ formatMoney(taxInfo.vatSales) ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span>Non-VAT Sales:</span>
                <span>{{ formatMoney(taxInfo.nonVatSales) ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span>VAT Exempt Sales:</span>
                <span>{{ formatMoney(taxInfo.vatExemptSales) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Total Vat Amount:</span>
                <span>{{ formatMoney(taxInfo.vatAmount) }}</span>
            </div>
            <div class="flex justify-between" v-if="lessTax">
                <span>Total Less Tax:</span>
                <span>-{{ formatMoney(lessTax) ?? 0 }}</span>
            </div>
            <div class="flex justify-between" v-if="lessDiscount">
                <span>Total Less Discount:</span>
                <span>-{{ formatMoney(lessDiscount) ?? 0 }}</span>
            </div>
        </div>

        <div class="border-b border-dashed"></div>

        <!-- Footer -->
        <div class="text-center text-xs mt-10">
            <p class="mb-1">
                {{
                    receiptFooter?.footer_notes ??
                    "Thank you for dining with us! Please settle your bill at the counter."
                }}
            </p>
            <p class="text-xs text-gray-500 mt-2">{{ footerMessage }}</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { formatDate } from "@/Utils/FormatDate";
import { formatMoney } from "@/Utils/FormatMoney";
import { computed } from "vue";

// Props
const props = defineProps<{
    businessLogo?: string;
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
    lessTax?: number;
    lessDiscount?: number;
    discountName?: string;
    discountType?: string;
    removeTax?: boolean;
    isSeniorDiscount?: boolean;
    totalAmount?: number;
    paymentInfo?: any;
    taxInfo?: any;
    footerMessage?: string;
    receiptFooter: any;
    embedded?: boolean;
}>();

// Default values
const businessLogo = computed(() => props.businessLogo);
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
const totalAmount = computed(() => props.totalAmount || 0);
const footerMessage = computed(
    () => props.footerMessage || "Generated by Quick Juan POS System"
);

const groupedOrderItems = computed(() => {
    const groups: { [key: string]: any[] } = {};
    orderItems.value.forEach((item) => {
        const orderType = item.order_type || "dine-in";
        if (!groups[orderType]) {
            groups[orderType] = [];
        }
        groups[orderType].push(item);
    });
    return groups;
});

const getOrderTypeLabel = (orderType: string) => {
    const labels: { [key: string]: string } = {
        "dine-in": "Dine-in",
        takeout: "Takeout",
        delivery: "Delivery",
    };
    return labels[orderType] || orderType;
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
    font-family: "Monospace", monospace;
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
