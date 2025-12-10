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
                    style="max-width: 64px"
                />
            </div>
            <h1 class="text-xl font-bold mb-1">{{ businessName }}</h1>
            <p class="text-sm">{{ branch.name }}</p>
            <p class="text-sm">{{ branch.address }}</p>
            <p class="text-sm">{{ branch.phone }}</p>
            <div class="border-b border-dashed mt-2"></div>
            <h2 v-if="tableName" class="text-lg font-semibold mt-2">
                {{ tableName }}
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
                v-for="(items, itemsKey) in props.items"
                :key="itemsKey"
                class="mb-4"
            >
                <!-- Order Type Header -->
                <div class="text-center font-semibold text-sm mb-2 pb-1">
                    {{ getOrderTypeLabel(String(items.orderType)) }}
                </div>

                <!-- Items in this group -->
                <div
                    v-for="item in items.cartItems"
                    :key="item.id"
                    class="my-3"
                >
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
                            v-if="parseFloat(item.lessTax || 0) > 0"
                            class="ml-2 text-xs text-gray-600"
                        >
                            <div class="flex justify-between">
                                <span>Less Tax: </span>
                                <span
                                    >-{{
                                        formatMoney(
                                            parseFloat(item.lessTax || 0)
                                        )
                                    }}</span
                                >
                            </div>
                        </div>

                        <!-- Less Discount -->
                        <div
                            v-if="parseFloat(item.discount_amount || 0) > 0"
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

        <div
            class="border-b border-dashed my-3"
            v-if="props.totals?.total_amount != props.totals.sub_total"
        ></div>
        <!-- <pre>{{ lessTotalDiscount }}</pre> -->
        <!-- Totals -->
        <div class="mt-3 mb-5">
            <!-- Subtotal -->
            <div
                class="flex justify-between"
                v-if="props.totals?.total_amount != props.totals.sub_total"
            >
                <span>Sub Total:</span>
                <span>{{ formatMoney(totals.total_amount) }}</span>
            </div>

            <!-- Less Tax -->
            <div class="flex justify-between" v-if="props.totals.less_tax">
                <span>Less Tax:</span>
                <span>-{{ formatMoney(props.totals.less_tax) }}</span>
            </div>

            <!-- Less Discount -->
            <div class="flex justify-between" v-if="props.totals.less_discount">
                <span>Less Discount:</span>
                <span>-{{ formatMoney(props.totals.less_discount) }}</span>
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
                class="border-b border-dashed my-3"
                v-if="props.totals?.total_amount != props.totals.sub_total"
            ></div>
            <div
                class="border-none pt-2 flex justify-between font-bold text-base"
            >
                <span>TOTAL:</span>
                <span>{{ formatMoney(props.totals.total_due) }}</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Branch from "@/Types/Branch";
import { formatDate } from "@/Utils/FormatDate";
import { formatMoney } from "@/Utils/FormatMoney";
import { computed } from "vue";

// Props
const props = defineProps<{
    businessLogo?: string;
    storeName?: string;
    branch?: Branch;
    billNumber?: string | number;
    tableName?: string;
    cashier?: string;
    items?: Array<any>;
    totals?: Object;
}>();

// Default values
const businessName = computed(() => props.storeName || "QuickJuan POS");

const branchAddress = computed(
    () => props.branch.address || "Brgy. Kapanikian La Paz, Tarlac"
);
const businessPhone = computed(() => props.branch.phone || "(555) 123-4567");
const billNumber = computed(() => props.billNumber || "001234");
const billDate = computed(() => new Date().toISOString());
const orderItems = computed(() => props.items || []);
const totals = computed(() => props.totals || {});

//compute the total less tax from order items
const lessTotalTax = computed(() => {
    return orderItems.value.reduce((sum, item) => {
        return sum + parseFloat(item.less_tax || 0);
    }, 0);
});

//compute the total less discount from order items
const lessTotalDiscount = computed(() => {
    return orderItems.value.reduce((sum, item) => {
        return sum + parseFloat(item.discount_amount || 0);
    }, 0);
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

    .bill-container img {
        max-width: 48px !important;
        height: auto !important;
    }
}
</style>
