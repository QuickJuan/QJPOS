<template>
    <div
        id="receipt-content"
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
            <div class="flex justify-between" v-if="props.cashier">
                <span>Cashier:</span>
                <span>{{
                    typeof props.cashier === "string"
                        ? props.cashier
                        : props.cashier.name
                }}</span>
            </div>

            <!-- Refund Indicator -->
            <div
                v-if="props.refundMeta"
                class="mt-4 pt-4 border-t-2 border-red-500"
            >
                <div class="text-center font-bold text-red-600 text-sm mb-2">
                    !!! REFUNDED !!!
                </div>
                <div class="flex justify-between text-xs text-red-600">
                    <span>Refunded by:</span>
                    <span>{{ props.refundMeta.supervisor }}</span>
                </div>
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
                <div class="text-center font-semibold text-sm mb-2 pb-1">
                    {{ getOrderTypeLabel(String(items.orderType)) }}
                </div>

                <div
                    v-for="item in items.orderItems"
                    :key="item.id"
                    class="my-3"
                >
                    <template v-if="item.parent_id == null">
                        <div class="flex justify-between gap-2">
                            <div class="flex-1">
                                <span class="block font-medium">
                                    {{ getQuantityPrefix(item) }}
                                    {{ getItemName(item) }}
                                </span>
                            </div>
                            <span class="text-sm font-medium">
                                {{ formatMoney(getItemAmount(item)) }}
                            </span>
                        </div>
                        <div
                            v-if="shouldShowStackedQuantity(item)"
                            class="ml-2 text-xs text-gray-600"
                        >
                            {{ getQuantityPriceText(item) }}
                        </div>

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

                        <div
                            v-if="hasChildItems(item)"
                            class="ml-4 pl-3 border-l-2 border-dashed border-gray-300 mb-4 space-y-1"
                        >
                            <div
                                v-for="option in getChildItems(item)"
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
                                    <template v-else>&nbsp;</template>
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

            <!-- Payment Section - handles both single and mixed payments -->
            <template v-if="isMixedPayment">
                <div class="mt-3 pt-3 border-t border-dashed">
                    <div class="text-center font-bold text-sm mb-2">
                        PAYMENT
                    </div>

                    <div
                        v-for="(payment, index) in mixedPayments"
                        :key="index"
                        class="mb-3 pb-3"
                        :class="{
                            'border-b border-dashed':
                                index < mixedPayments.length - 1,
                        }"
                    >
                        <div class="font-semibold text-xs mb-1">
                            Payment {{ index + 1 }}
                        </div>

                        <div
                            class="flex justify-between text-sm"
                            v-if="
                                payment.method || payment.payment_method?.name
                            "
                        >
                            <span>Payment Method:</span>
                            <span>{{
                                payment.method || payment.payment_method?.name
                            }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span>Amount:</span>
                            <span>{{
                                formatMoney(
                                    parseFloat(
                                        payment.amount_in_payment_currency ||
                                            payment.amount ||
                                            payment.amount_paid ||
                                            0
                                    ),
                                    payment.amount_in_payment_currency
                                        ? payment.currency?.code ||
                                              baseCurrencyCode
                                        : baseCurrencyCode
                                )
                            }}</span>
                        </div>

                        <div
                            class="flex justify-between text-xs text-gray-600"
                            v-if="
                                (payment.exchange_rate ||
                                    payment.currency?.exchange_rate) &&
                                parseFloat(
                                    payment.exchange_rate ||
                                        payment.currency?.exchange_rate ||
                                        1
                                ) > 1
                            "
                        >
                            <span>Exchange Rate:</span>
                            <span
                                >1 {{ payment.currency?.code || "USD" }} =
                                {{
                                    formatMoney(
                                        parseFloat(
                                            payment.exchange_rate ||
                                                payment.currency
                                                    ?.exchange_rate ||
                                                1
                                        ),
                                        baseCurrencyCode
                                    )
                                }}</span
                            >
                        </div>

                        <div
                            class="flex justify-between text-xs text-gray-600"
                            v-if="
                                parseFloat(
                                    payment.amount_in_payment_currency || 0
                                ) > 0 &&
                                parseFloat(
                                    payment.exchange_rate ||
                                        payment.currency?.exchange_rate ||
                                        1
                                ) > 1
                            "
                        >
                            <span>Value in {{ baseCurrencyCode }}:</span>
                            <span>{{
                                formatMoney(
                                    parseFloat(
                                        payment.amount ||
                                            payment.amount_paid ||
                                            0
                                    ),
                                    baseCurrencyCode
                                )
                            }}</span>
                        </div>

                        <div
                            class="flex justify-between"
                            v-if="
                                parseFloat(
                                    payment.change_amount || payment.change || 0
                                ) > 0
                            "
                        >
                            <span>Change:</span>
                            <span>{{
                                formatMoney(
                                    parseFloat(
                                        payment.change_amount ||
                                            payment.change ||
                                            0
                                    ),
                                    baseCurrencyCode
                                )
                            }}</span>
                        </div>

                        <!-- Payment-specific details -->
                        <div class="mt-1 space-y-1 text-xs text-gray-700">
                            <div
                                class="flex justify-between"
                                v-if="
                                    getPaymentType(payment) === 'credit' &&
                                    payment.customer_name
                                "
                            >
                                <span>Customer:</span>
                                <span>{{ payment.customer_name }}</span>
                            </div>
                            <div
                                class="flex justify-between"
                                v-if="
                                    getPaymentType(payment) === 'e-wallet' &&
                                    payment.reference_number
                                "
                            >
                                <span>Reference No.:</span>
                                <span>{{ payment.reference_number }}</span>
                            </div>
                            <div
                                class="flex justify-between"
                                v-if="
                                    getPaymentType(payment) === 'gift-check' &&
                                    (payment.reference_number ||
                                        payment.gift_check_number)
                                "
                            >
                                <span>Reference No.:</span>
                                <span>{{
                                    payment.reference_number ||
                                    payment.gift_check_number
                                }}</span>
                            </div>
                            <div
                                class="flex justify-between"
                                v-if="
                                    getPaymentType(payment) === 'card' &&
                                    payment.approval_code
                                "
                            >
                                <span>Approval Code:</span>
                                <span>{{ payment.approval_code }}</span>
                            </div>
                            <div
                                class="flex justify-between"
                                v-if="
                                    getPaymentType(payment) === 'card' &&
                                    payment.card_holder_name
                                "
                            >
                                <span>Cardholder:</span>
                                <span>{{ payment.card_holder_name }}</span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex justify-between font-bold mt-2 pt-2 border-t border-dashed"
                    >
                        <span>Total Paid:</span>
                        <span>{{
                            formatMoney(totalMixedPayments, baseCurrencyCode)
                        }}</span>
                    </div>
                    <div
                        class="flex justify-between font-bold"
                        v-if="totalMixedChange > 0"
                    >
                        <span>Total Change:</span>
                        <span>{{
                            formatMoney(totalMixedChange, baseCurrencyCode)
                        }}</span>
                    </div>
                </div>
            </template>

            <!-- Single Payment (original display) -->
            <template v-else-if="props.payment">
                <div class="flex justify-between">
                    <span>Payment Received:</span>
                    <span>
                        {{ formatMoney(amountPaidBase, baseCurrencyCode) }}
                    </span>
                </div>
                <div
                    class="flex justify-between"
                    v-if="
                        props.payment?.currency &&
                        !props.payment.currency?.is_default &&
                        paymentCurrencyAmount !== null
                    "
                >
                    <span> Paid in {{ props.payment.currency.code }}: </span>
                    <span>
                        {{
                            formatMoney(
                                paymentCurrencyAmount,
                                props.payment.currency.code
                            )
                        }}
                    </span>
                </div>
                <div
                    class="flex justify-between text-xs text-gray-600"
                    v-if="foreignExchangeRateDisplay"
                >
                    <span>Exchange Rate:</span>
                    <span>{{ foreignExchangeRateDisplay }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Change:</span>
                    <span>
                        {{ formatMoney(customerChange, baseCurrencyCode) }}
                    </span>
                </div>
                <div
                    v-if="paymentDetailRows.length"
                    class="mt-2 space-y-1 text-sm text-gray-700"
                >
                    <div
                        v-for="detail in paymentDetailRows"
                        :key="`${detail.label}-${detail.value}`"
                        class="flex justify-between gap-4"
                    >
                        <span>{{ detail.label }}</span>
                        <span class="font-semibold text-right ml-auto">
                            {{ detail.value }}
                        </span>
                    </div>
                </div>
            </template>
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
import { formatMoney } from "@/Utils/FormatMoney";
import { computed } from "vue";

type PaymentDetailRow = {
    label: string;
    value: string;
};

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
    refundMeta?: {
        requested_by: string;
        supervisor: string;
        refunded_at: string;
        notes: string;
    } | null;
}>();

const branch = computed(() => props.branch ?? ({} as Branch));
const tableNumber = computed(() => props.tableNumber ?? "");

const getOrderTypeLabel = (orderType: string) => {
    const labels: { [key: string]: string } = {
        "dine-in": "Dine-in",
        takeout: "Takeout",
        delivery: "Delivery",
    };
    return labels[orderType] || orderType;
};

const parseNumeric = (value: any): number | null => {
    if (value === null || value === undefined || value === "") {
        return null;
    }

    const parsed = parseFloat(value);
    return Number.isFinite(parsed) ? parsed : null;
};

const getItemQuantity = (item: any): number => {
    return parseNumeric(item?.quantity ?? item?.qty) ?? 1;
};

const getItemUnit = (item: any): string => {
    return (
        item?.unit_measure ||
        item?.unit ||
        item?.unitMeasure ||
        item?.unit_of_measure ||
        ""
    );
};

const getItemName = (item: any): string => {
    return item?.description || item?.name || "Menu Item";
};

const getItemPriceValue = (item: any): number | null => {
    const candidates = [item?.price, item?.unit_price, item?.unitPrice];
    for (const candidate of candidates) {
        const parsed = parseNumeric(candidate);
        if (parsed !== null) {
            return parsed;
        }
    }

    return null;
};

const getItemAmountValue = (item: any): number => {
    const candidates = [item?.amount, item?.total, item?.line_total];
    for (const candidate of candidates) {
        const parsed = parseNumeric(candidate);
        if (parsed !== null) {
            return parsed;
        }
    }

    const quantity = getItemQuantity(item);
    const unitPrice = getItemPriceValue(item) ?? 0;
    return quantity * unitPrice;
};

const getItemUnitPrice = (item: any): number => {
    const directPrice = getItemPriceValue(item);
    if (directPrice !== null) {
        return directPrice;
    }

    const quantity = getItemQuantity(item);
    const amount = getItemAmountValue(item);
    if (quantity <= 0) {
        return amount;
    }

    return amount / quantity;
};

const getItemAmount = (item: any): number => getItemAmountValue(item);

const getQuantityPriceText = (item: any): string => {
    const quantity = getItemQuantity(item);
    const priceText = formatMoney(getItemUnitPrice(item));
    if (!priceText) {
        return "";
    }

    return `${quantity} × ${priceText}`;
};

const formatQuantityValue = (quantity: number): string => {
    if (Number.isInteger(quantity)) {
        return quantity.toString();
    }

    return (
        quantity.toFixed(2).replace(/\.00$/, "").replace(/0$/, "") ||
        quantity.toString()
    );
};

const getQuantityPrefix = (item: any): string => {
    const quantity = getItemQuantity(item);
    const unit = getItemUnit(item);
    const fallbackUnit = quantity > 1 ? "pcs" : "pc";
    const unitLabel = unit?.trim() || fallbackUnit;
    return `${formatQuantityValue(quantity)} ${unitLabel}`.trim();
};

const shouldShowStackedQuantity = (item: any): boolean => {
    return getItemQuantity(item) > 1 && !!getQuantityPriceText(item);
};

const getChildItems = (item: any) => {
    if (Array.isArray(item?.children) && item.children.length) {
        return item.children;
    }

    if (Array.isArray(item?.sub_items) && item.sub_items.length) {
        return item.sub_items;
    }

    if (Array.isArray(item?.subItems) && item.subItems.length) {
        return item.subItems;
    }

    return [];
};

const hasChildItems = (item: any) => getChildItems(item).length > 0;

const getChildName = (option: any) => {
    return (
        option?.product?.name ||
        option?.description ||
        option?.name ||
        "Selected item"
    );
};

const getChildAmount = (option: any) => {
    const amount = parseNumeric(option?.amount);
    if (amount !== null) {
        return amount;
    }

    const price = parseNumeric(option?.price) ?? 0;
    const quantity = parseNumeric(option?.quantity) ?? 0;
    return price * quantity;
};

const totalAmountDue = computed(() => {
    const totalDue = parseFloat(props.totals?.total_due) || 0;
    const serviceCharge = parseFloat(props.totals?.service_charge) || 0;
    return totalDue + serviceCharge;
});

const amountPaidBase = computed(() => {
    if (!props.payment) {
        return 0;
    }

    const amount = parseNumeric(props.payment.amount_paid);
    return amount ?? 0;
});

const paymentCurrencyAmount = computed(() => {
    if (!props.payment) {
        return null;
    }

    return parseNumeric(props.payment.amount_in_payment_currency);
});

const baseCurrencyCode = computed(() => {
    return (
        props.payment?.base_currency?.code ||
        props.payment?.currency?.code ||
        "PHP"
    );
});

const foreignExchangeRateDisplay = computed(() => {
    if (
        !props.payment?.currency ||
        props.payment.currency.is_default ||
        props.payment.currency.exchange_rate === undefined ||
        props.payment.currency.exchange_rate === null
    ) {
        return null;
    }

    const exchangeRate = parseNumeric(props.payment.currency.exchange_rate);
    if (!exchangeRate || exchangeRate <= 0) {
        return null;
    }

    const paymentCurrencyCode = props.payment.currency.code || "";
    const formattedRate = formatMoney(exchangeRate, baseCurrencyCode.value);

    if (paymentCurrencyCode) {
        return `1 ${paymentCurrencyCode} = ${formattedRate}`;
    }

    return formattedRate;
});

const normalizedPaymentType = computed(() => {
    const typeValue =
        props.payment?.payment_type_value ?? props.payment?.payment_type ?? "";
    return typeof typeValue === "string" ? typeValue.toLowerCase() : "";
});

const isCreditPayment = computed(
    () => normalizedPaymentType.value === "credit"
);
const isEWalletPayment = computed(
    () => normalizedPaymentType.value === "e-wallet"
);
const isGiftCheckPayment = computed(
    () => normalizedPaymentType.value === "gift-check"
);
const isCardPayment = computed(() => normalizedPaymentType.value === "card");

// Mixed payment support
const isMixedPayment = computed(() => Array.isArray(props.payment));
const mixedPayments = computed(() =>
    isMixedPayment.value ? props.payment : []
);

const totalMixedPayments = computed(() => {
    if (!isMixedPayment.value) return 0;
    return mixedPayments.value.reduce((sum, payment) => {
        const amount = parseFloat(
            payment.amount_applied || payment.amount || payment.amount_paid || 0
        );
        return sum + amount;
    }, 0);
});

const totalMixedChange = computed(() => {
    if (!isMixedPayment.value) return 0;
    return mixedPayments.value.reduce((sum, payment) => {
        const change = parseFloat(payment.change_amount || payment.change || 0);
        return sum + change;
    }, 0);
});

const getPaymentType = (payment: any): string => {
    const typeValue =
        payment?.payment_type_value ?? payment?.payment_type ?? "";
    return typeof typeValue === "string" ? typeValue.toLowerCase() : "";
};

const paymentDetailRows = computed<PaymentDetailRow[]>(() => {
    if (!props.payment || isMixedPayment.value) {
        return [];
    }

    const rows: PaymentDetailRow[] = [];

    if (props.payment.method) {
        rows.push({ label: "Payment Method:", value: props.payment.method });
    }

    if (isCreditPayment.value && props.payment.customer_name) {
        rows.push({ label: "Customer:", value: props.payment.customer_name });
    }

    if (isEWalletPayment.value) {
        const reference = props.payment.reference_number;
        if (reference) {
            rows.push({ label: "Reference No.:", value: reference });
        }
    }

    if (isGiftCheckPayment.value) {
        const reference =
            props.payment.reference_number || props.payment.gift_check_number;
        if (reference) {
            rows.push({ label: "Reference No.:", value: reference });
        }

        const giftAmount = parseNumeric(props.payment.gift_check_amount);
        if (giftAmount !== null) {
            rows.push({
                label: "Gift Check Amount:",
                value: formatMoney(giftAmount, baseCurrencyCode.value),
            });
        }
    }

    if (isCardPayment.value) {
        if (props.payment.approval_code) {
            rows.push({
                label: "Approval Code:",
                value: props.payment.approval_code,
            });
        }

        if (props.payment.card_holder_name) {
            rows.push({
                label: "Cardholder:",
                value: props.payment.card_holder_name,
            });
        }
    }

    return rows;
});

const customerChange = computed(() => {
    if (props.payment) {
        const change = parseNumeric(props.payment.change);
        if (change !== null) {
            return change;
        }

        return amountPaidBase.value - totalAmountDue.value;
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
