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

            <!-- loop through receipt header array of string -->
            <div>
                <p
                    v-for="(line, index) in props.receiptHeader || []"
                    :key="index"
                    class="text-sm"
                >
                    {{ line }}
                </p>
            </div>
            <div class="border-b border-dashed mt-2"></div>
            <!-- <h2 class="text-lg font-semibold mt-2" v-if="tableNumber">
                Table #: {{ tableNumber }}
            </h2> -->
        </div>

        <!-- Receipt Info -->
        <div class="mb-3">
            <div class="flex justify-between">
                <span>Invoice #:</span>
                <span>{{ props.invoiceNumber }}</span>
            </div>
            <div class="flex justify-between">
                <span>Date Time:</span>
                <span>{{ props.receiptDate }}</span>
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
                                        formatMoney(parseFloat(item.lessTax))
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
                            class="ml-4 pl-3 border-l-2 border-dashed border-gray-300 mb-4 space-y-1"
                        >
                            <div
                                v-for="option in item.children"
                                :key="option.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-2 flex-1">
                                    <span class="text-xs font-medium"> • </span>
                                    <span class="text-xs flex-1 ml-2">
                                        {{ option.quantity }} ×
                                        {{ getChildName(option) }}
                                    </span>
                                </div>
                                <span class="text-xs font-medium">
                                    <template v-if="getChildAmount(option) > 0">
                                        +{{
                                            formatMoney(getChildAmount(option))
                                        }}
                                    </template>
                                    <template v-else>Included</template>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div
            class="border-b border-dashed my-3"
            v-if="totalAmountDue != props.totals.sub_total"
        />

        <!-- Totals -->
        <div class="mt-3 mb-5">
            <!-- Subtotal -->
            <div
                class="flex justify-between"
                v-if="totalAmountDue != props.totals.sub_total"
            >
                <span>Subtotal:</span>
                <span>{{ formatMoney(props.totals?.total_amount) }}</span>
            </div>

            <!-- Less Tax -->
            <div class="flex justify-between" v-if="props.totals.less_tax > 0">
                <span>Less Tax:</span>
                <span>-{{ formatMoney(props.totals.less_tax) }}</span>
            </div>

            <!-- Less Discount -->
            <div
                class="flex justify-between"
                v-if="props.totals.discount_amount > 0"
            >
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
            <div class="border-b border-dashed mt-2"></div>
            <!-- Total -->
            <div
                class="border-none pt-2 flex justify-between font-bold text-base"
            >
                <span>TOTAL:</span>
                <span>{{ formatMoney(totalAmountDue) }}</span>
            </div>
            <div class="flex justify-between" v-if="props.payment">
                <span>Payment Received:</span>
                <span>{{ formatMoney(props.payment.amount_paid) }}</span>
            </div>
            <div class="flex justify-between" v-if="props.payment">
                <span>Change:</span>
                <span>{{ formatMoney(customerChange) }}</span>
            </div>
        </div>

        <!-- Tax Info -->
        <div class="mb-3" v-if="props.totals">
            <div
                v-if="props.totals.vatable_sales > 0"
                class="flex justify-between"
            >
                <span>VAT Sales:</span>
                <span>{{ formatMoney(props.totals?.vatable_sales) ?? 0 }}</span>
            </div>
            <div
                v-if="props.totals.vat_amount > 0"
                class="flex justify-between"
            >
                <span>Vatable Amount:</span>
                <span>{{ formatMoney(props.totals?.vat_amount) }}</span>
            </div>
            <div
                v-if="props.totals.non_vat_sales > 0"
                class="flex justify-between"
            >
                <span>Non-VAT Sales:</span>
                <span>{{ formatMoney(props.totals?.non_vat_sales) ?? 0 }}</span>
            </div>
            <div
                v-if="props.totals.vat_exempt_sales > 0"
                class="flex justify-between"
            >
                <span>VAT Exempt Sales:</span>
                <span>{{ formatMoney(props.totals?.vat_exempt_sales) }}</span>
            </div>
        </div>

        <div class="border-b border-dashed"></div>

        <!-- BIR ACCREDITATON NOTES -->
        <div class="my-3" v-if="props.birAccreditationFooter">
            <p
                v-for="line in props.birAccreditationFooter"
                class="text-center text-xs"
            >
                {{ line }}
            </p>
        </div>

        <!-- Receipt Footer loop through array-->
        <div>
            <p
                v-for="(line, index) in props.receiptFooter || []"
                :key="index"
                class="text-sm text-center"
            >
                {{ line }}
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import Branch from "@/Types/Branch";
import { formatDate } from "@/Utils/FormatDate";
import { formatMoney } from "@/Utils/FormatMoney";
import { computed } from "vue";
import moment from "moment-timezone";

// Props
const props = defineProps<{
    businessLogo?: string;
    storeName?: string;
    branch: Branch;
    invoiceNumber?: string | number;
    receiptDate?: string;
    tableNumber?: string;
    cashier?: any;
    items?: any[];
    totals?: any;
    payment?: any;
    receiptHeader?: any;
    receiptFooter: any;
    birAccreditationFooter?: any;
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

const totalAmountDue = computed(() => {
    return (
        parseFloat(props.totals?.total_due) +
            parseFloat(props.totals?.service_charge) || 0
    );
});

// const invoiceDate = computed(() => {
//     return moment(props.receiptDate)
//         .tz("Asia/Manila")
//         .format("MM/DD/YYYY hh:mm A");
// });

const customerChange = computed(() => {
    if (props.payment) {
        return (
            parseFloat(props.payment.amount_paid) -
            parseFloat(totalAmountDue.value)
        );
    }
    return 0;
});
</script>

<style scoped>
.receipt-container {
    font-family: "Monospace", monospace;
    line-height: 1.2;
    max-width: 400px;
}

/* Print styles */
@media print {
    .receipt-container {
        max-width: none;
        margin: 0;
        padding: 10px;
        font-size: 12px;
        border: none;
        width: 400px;
    }

    .receipt-container img {
        max-width: 48px !important;
        height: auto !important;
    }
}
</style>
