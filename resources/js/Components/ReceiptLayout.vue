<template>
    <div
        class="receipt-container max-w-xs mx-auto bg-white p-4 font-mono text-sm leading-tight border-2 border-gray-300"
    >
        <!-- Header -->
        <div class="text-center mb-4">
            <div v-if="props.businessLogo">
                <img
                    :src="props.businessLogo"
                    alt="Company Logo"
                    class="w-16 h-auto mx-auto mb-2"
                    style="max-width: 64px"
                />
            </div>
            <h1 class="text-lg font-bold mb-1">{{ props.storeName }}</h1>
            <p class="text-sm">{{ branch.name }}</p>
            <p class="text-sm">{{ branch.address }}</p>
            <p class="text-sm">{{ branch.phone }}</p>
            <div class="border-b border-dashed mt-2"></div>
            <h2 class="text-lg font-semibold mt-2" v-if="tableNumber">
                Table #: {{ tableNumber }}
            </h2>
        </div>

        <!-- Receipt Info -->
        <div class="mb-3">
            <div class="flex justify-between">
                <span>Receipt #:</span>
                <span>{{ props.receiptNumber }}</span>
            </div>
            <div class="flex justify-between">
                <span>Date:</span>
                <span>{{ formatDate(props.receiptDate) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Time:</span>
                <span>{{ formatTime(props.receiptDate) }}</span>
            </div>
            <div class="flex justify-between" v-if="tableNumber">
                <span>Table:</span>
                <span>{{ tableNumber }}</span>
            </div>
            <div class="flex justify-between" v-if="props.cashier.name">
                <span>Cashier:</span>
                <span>{{ props.cashier.name }}</span>
            </div>
        </div>

        <div class="border-b border-dashed"></div>

        <!-- Order Items -->
        <div class="my-3">
            <div class="text-center font-bold mb-2">ORDER ITEMS</div>
            <div
                v-for="(items, itemsKey) in props.items"
                :key="itemsKey"
                class="mb-4"
            >
                <!-- Order Type Header -->
                <div class="text-center font-semibold text-sm mb-2 pb-1">
                    {{ getOrderTypeLabel(String(items.orderType)) }}
                </div>

                <div
                    v-for="item in items.orderItems"
                    :key="item.id"
                    class="my-3"
                >
                    <template v-if="item.parent_id == null">
                        <div class="flex justify-between">
                            <span class="flex-1">{{ item.description }}</span>
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
                            v-if="parseFloat(item.lessTax) > 0"
                            class="ml-2 text-xs text-gray-600"
                        >
                            <div class="flex justify-between">
                                <span>Less Tax: </span>
                                <span
                                    >-{{
                                        formatMoney(
                                            parseFloat(item.lessTax)
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <!-- Less Discount -->
                        <div
                            v-if="parseFloat(item.discount_amount) > 0"
                            class="ml-2 text-xs text-gray-600"
                        >
                            <div class="flex justify-between">
                                <span>Less Discount: </span>
                                <span
                                    >-{{
                                        formatMoney(
                                            parseFloat(
                                                item.discount_amount || 0
                                            )
                                        )
                                    }}</span
                                >
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

        <div class="border-b border-dashed my-3"
            v-if="props.totals?.total_amount != props.totals.sub_total"
        />

        <!-- Totals -->
        <div class="mt-3 mb-5">
            <!-- Subtotal -->
            <div class="flex justify-between" v-if="props.totals?.total_amount != props.totals.total_due">
                <span>Subtotal:</span>
                <span>{{ formatMoney(props.totals?.total_amount) }}</span>
            </div>

            <!-- Less Tax -->
            <div class="flex justify-between" v-if="props.totals.less_tax > 0">
                <span>Less Tax:</span>
                <span>-{{ formatMoney(props.totals.less_tax) }}</span>
            </div>

            <!-- Less Discount -->
            <div class="flex justify-between" v-if="props.totals.discount_amount > 0">
                <span>Less Discount:</span>
                <span>-{{ formatMoney(props.totals.discount_amount) }}</span>
            </div>

            <!-- Service Charge -->
            <div
                class="flex justify-between"
                v-if="props.totals.service_charge > 0"
            >
                <span>+ Service Charge:</span>
                <span>{{ formatMoney(props.totals.service_charge) }}</span>
            </div>

            <!-- Total -->
            <div
                class="border-none pt-2 flex justify-between font-bold text-base"
            >
                <span>TOTAL:</span>
                <span>{{ formatMoney(props.totals?.total_due) }}</span>
            </div>
            <div class="flex justify-between" v-if="props.payment">
                <span>Payment Received:</span>
                <span>{{ formatMoney(props.payment.amount_paid) }}</span>
            </div>
            <div class="flex justify-between" v-if="props.payment">
                <span>Change:</span>
                <span>{{ formatMoney(props.payment.change) }}</span>
            </div>
        </div>

        <!-- Tax Info -->
        <div class="mb-3" v-if="props.totals">
            <div class="flex justify-between">
                <span>Vatable Amount:</span>
                <span>{{ formatMoney(props.totals?.vat_amount) }}</span>
            </div>
            <div class="flex justify-between">
                <span>VAT Sales:</span>
                <span>{{ formatMoney(props.totals?.vatable_sales) ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span>Non-VAT Sales:</span>
                <span>{{ formatMoney(props.totals?.non_vat_sales) ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span>VAT Exempt Sales:</span>
                <span>{{ formatMoney(props.totals?.vat_exempt_sales) }}</span>
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
import Branch from "@/Types/Branch";
import { formatDate } from "@/Utils/FormatDate";
import { formatMoney } from "@/Utils/FormatMoney";

// Props
const props = defineProps<{
    businessLogo?: string;
    storeName?: string;
    branch: Branch;
    receiptNumber?: string;
    receiptDate?: string;
    tableNumber?: string;
    cashier?: any;
    items?: any[];
    totals?: any;
    payment?: any;
    footerMessage?: string;
    receiptFooter: any;
}>();


// Create helpers for this one
const getOrderTypeLabel = (orderType: string) => {
    const labels: { [key: string]: string } = {
        "dine-in": "Dine-in",
        takeout: "Takeout",
        delivery: "Delivery",
    };
    return labels[orderType] || orderType;
};

// This one, too.
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
        font-size: 12px;
        border: none;
    }

    .receipt-container img {
        max-width: 48px !important;
        height: auto !important;
    }
}
</style>
