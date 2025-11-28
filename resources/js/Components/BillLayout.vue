<template>
    <div
        class="bill-container max-w-xs mx-auto bg-white p-4 font-mono text-sm leading-tight border-2 border-gray-300"
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
            <h1 class="text-xl font-bold mb-1">{{ businessName }}</h1>
            <p class="text-sm">{{ businessAddress }}</p>
            <p class="text-sm">{{ businessPhone }}</p>
            <div class="border-b border-dashed mt-2"></div>
            <h2 class="text-lg font-semibold mt-2">
                Table #: {{ tableInfo.name }}
            </h2>
        </div>

        <!-- Bill Info -->
        <div class="mb-3">
            <div class="flex justify-between">
                <span>Date:</span>
                <span>{{ formatDate(billDate) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Time:</span>
                <span>{{ formatTime(billDate) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Bill #:</span>
                <span>
                    {{ billNumber }}
                </span>
            </div>
            <div class="flex justify-between" v-if="cashierName">
                <span>Cashier:</span>
                <span>{{ cashierName }}</span>
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

                <!-- Items in this group -->
                <div v-for="item in items" :key="item.id" class="mb-3">
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
                </div>
            </div>
        </div>

        <div class="border-b border-dashed my-3"></div>

        <!-- Totals -->
        <div class="mt-3 mb-5">
            <!-- Subtotal -->
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>{{ formatMoney(subtotal) }}</span>
            </div>

            <!-- Less Tax -->
            <div class="flex justify-between" v-if="lessTax">
                <span>Less Tax:</span>
                <span>-{{ formatMoney(lessTax) }}</span>
            </div>

            <!-- Less Discount -->
            <div class="flex justify-between" v-if="lessDiscount">
                <span>Less Discount:</span>
                <span>-{{ formatMoney(lessDiscount) }}</span>
            </div>

            <!-- Total -->
            <div
                class="border-none pt-2 flex justify-between font-bold text-base"
            >
                <span>TOTAL:</span>
                <span>{{ formatMoney(totalAmount) }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs">
            <p class="mb-1">
                {{
                    billFooter?.footer_notes ??
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
    billNumber?: string;
    billDate?: string;
    tableInfo?: any;
    cashierName?: string;
    orderType?: string;
    orderItems?: any[];
    subtotal?: number;
    lessTax?: number;
    lessDiscount?: number;
    discountAmount?: number;
    discountName?: string;
    discountType?: string;
    removeTax?: boolean;
    isSeniorDiscount?: boolean;
    totalAmount?: number;
    footerMessage?: string;
    billFooter: any;
}>();

// Default values
const businessName = computed(() => props.businessName || "Quick Juan POS");
const businessAddress = computed(
    () => props.businessAddress || "123 Main Street, City, State"
);
const businessPhone = computed(() => props.businessPhone || "(555) 123-4567");
const billNumber = computed(() => props.billNumber || "001234");
const billDate = computed(() => props.billDate || new Date().toISOString());
const orderItems = computed(() => props.orderItems || []);
const subtotal = computed(() => props.subtotal || 0);
const totalAmount = computed(() => props.totalAmount || 0);
const footerMessage = computed(
    () => props.footerMessage || "Generated by Quick Juan POS System"
);

// Group items by order type
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

// Get order type label
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
.bill-container {
    font-family: "Monospace", monospace;
    line-height: 1.2;
    max-width: 300px;
}

/* Print styles */
@media print {
    .bill-container {
        max-width: none;
        margin: 0;
        padding: 10px;
        font-size: 12px;
        border: none;
    }
}
</style>
