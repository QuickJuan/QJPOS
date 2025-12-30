<template>
    <div class="min-h-screen bg-neutral-50">
        <Head title="Settle Payment" />

        <div class="bg-white border-b border-neutral-200 shadow-sm">
            <div
                class="max-w-6xl mx-auto px-4 py-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="navigateBack"
                        class="p-2 rounded-full border border-neutral-200 text-neutral-600 hover:text-primary-600 hover:border-primary-200 transition-colors"
                    >
                        <ArrowLeftIcon class="w-5 h-5" />
                    </button>
                    <div>
                        <p class="text-sm font-semibold text-neutral-800">
                            Settle Payment
                        </p>
                        <p class="text-xs text-neutral-500">
                            Choose a payment method and enter the required
                            details below.
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-neutral-600">
                        Total Due ({{ defaultCurrencyCode }})
                    </p>
                    <p class="text-3xl font-black text-primary-600">
                        {{ formatMoney(totalDue, defaultCurrencyCode) }}
                    </p>
                    <p class="text-xs text-neutral-500 mt-1">
                        Cart #{{ formattedCartNumber }}
                    </p>
                </div>
            </div>
        </div>

        <div v-if="hasCart" class="max-w-6xl mx-auto px-4 py-6">
            <div class="grid gap-6 lg:grid-cols-[360px_1fr] items-start">
                <section
                    class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-5 space-y-6"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-neutral-800">
                                Payment Method
                            </h3>
                            <span
                                class="text-xs text-neutral-500"
                                v-if="availablePaymentMethods.length"
                            >
                                {{ availablePaymentMethods.length }} configured
                            </span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <button
                                v-for="method in availablePaymentMethods"
                                :key="method.id"
                                type="button"
                                @click="selectedPaymentMethodId = method.id"
                                :class="[
                                    'w-full rounded-2xl border text-left p-3 transition-all flex flex-col gap-1',
                                    selectedPaymentMethodId === method.id
                                        ? 'bg-primary-50 text-primary-800 border-primary-300 ring-1 ring-primary-200 shadow-sm'
                                        : 'bg-neutral-50 text-neutral-700 border-neutral-200 hover:bg-white',
                                ]"
                                :aria-pressed="
                                    selectedPaymentMethodId === method.id
                                "
                            >
                                <span class="text-sm font-semibold">
                                    {{ method.name }}
                                </span>
                                <span class="text-xs text-neutral-500">
                                    {{
                                        getPaymentTypeLabel(method.payment_type)
                                    }}
                                    <template
                                        v-if="
                                            method?.currency?.code ||
                                            method?.currency_code
                                        "
                                    >
                                        ·
                                        {{
                                            method?.currency?.code ||
                                            method?.currency_code
                                        }}
                                    </template>
                                </span>
                            </button>
                        </div>
                        <div
                            v-if="requiresAdditionalFields"
                            class="space-y-4 border border-dashed border-neutral-200 rounded-xl p-4 bg-neutral-50"
                        >
                            <p class="text-sm font-semibold text-neutral-800">
                                Payment Details
                            </p>

                            <template v-if="isEWalletMethod">
                                <TextField
                                    id="ewallet_reference"
                                    v-model="paymentDetails.referenceNumber"
                                    label="Reference Number"
                                    placeholder="Enter transaction reference"
                                    required
                                />
                                <p class="text-xs text-neutral-500">
                                    Provide the reference number from the
                                    e-wallet confirmation.
                                </p>
                            </template>

                            <template v-else-if="isCardMethod">
                                <TextField
                                    id="card_approval_code"
                                    v-model="paymentDetails.cardApprovalCode"
                                    label="Approval Code"
                                    placeholder="Enter terminal approval code"
                                    required
                                />
                                <TextField
                                    id="card_holder_name"
                                    v-model="paymentDetails.cardHolderName"
                                    label="Cardholder Name"
                                    placeholder="Name printed on the card"
                                    required
                                />
                            </template>

                            <template v-else-if="isCreditMethod">
                                <div class="space-y-2">
                                    <TextField
                                        id="credit_customer_search"
                                        v-model="customerSearchQuery"
                                        label="Find Customer"
                                        placeholder="Search name, phone, or email"
                                        type="search"
                                        :disabled="isCustomerSearchDisabled"
                                    />
                                    <p class="text-xs text-neutral-500">
                                        Start typing (min. 2 characters) to
                                        search existing customers.
                                    </p>
                                    <div
                                        v-if="customerSearchLoading"
                                        class="text-xs text-neutral-500"
                                    >
                                        Searching customers...
                                    </div>
                                    <div
                                        v-else-if="customerSearchError"
                                        class="text-xs text-error-500"
                                    >
                                        {{ customerSearchError }}
                                    </div>
                                    <ul
                                        v-if="
                                            customerResults.length > 0 &&
                                            !customerSearchLoading
                                        "
                                        class="border border-neutral-200 rounded-lg divide-y divide-gray-100 max-h-48 overflow-y-auto"
                                    >
                                        <li
                                            v-for="customer in customerResults"
                                            :key="customer.id"
                                        >
                                            <button
                                                type="button"
                                                class="w-full text-left px-3 py-2 hover:bg-primary-50"
                                                @click="
                                                    selectCustomer(customer)
                                                "
                                            >
                                                <p
                                                    class="text-sm font-semibold text-neutral-800"
                                                >
                                                    {{ customer.customer_name }}
                                                </p>
                                                <p
                                                    class="text-xs text-neutral-500"
                                                >
                                                    {{
                                                        customer.contact_no ||
                                                        customer.email ||
                                                        "No contact info"
                                                    }}
                                                </p>
                                            </button>
                                        </li>
                                    </ul>
                                    <p
                                        v-else-if="
                                            customerSearchQuery.length >= 2 &&
                                            !customerSearchLoading &&
                                            !customerSearchError
                                        "
                                        class="text-xs text-neutral-500"
                                    >
                                        No customers found.
                                    </p>
                                </div>
                                <TextField
                                    id="credit_customer_name"
                                    v-model="paymentDetails.creditCustomerName"
                                    label="Customer Name"
                                    placeholder="Enter customer's full name"
                                    required
                                />
                                <TextField
                                    id="credit_customer_contact"
                                    v-model="
                                        paymentDetails.creditCustomerContact
                                    "
                                    label="Customer Contact"
                                    placeholder="Phone or email"
                                    required
                                />
                            </template>

                            <template v-else-if="isGiftCheckMethod">
                                <TextField
                                    id="gift_check_number"
                                    v-model="paymentDetails.giftCheckNumber"
                                    label="Gift Check Number"
                                    placeholder="Enter GC number"
                                    required
                                />
                                <TextField
                                    id="gift_check_amount"
                                    v-model="paymentDetails.giftCheckAmount"
                                    label="Gift Check Amount"
                                    type="number"
                                    inputmode="decimal"
                                    placeholder="0.00"
                                    required
                                />
                            </template>
                        </div>

                        <div class="space-y-2">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="text-neutral-600">
                                    Amount Tendered ({{ paymentCurrencyCode }})
                                </span>
                                <span class="font-semibold text-neutral-900">
                                    {{
                                        formatMoney(
                                            amountTenderedNumber,
                                            paymentCurrencyCode
                                        )
                                    }}
                                </span>
                            </div>
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="text-neutral-600">
                                    Converted Amount ({{ defaultCurrencyCode }})
                                </span>
                                <span class="font-semibold text-neutral-900">
                                    {{
                                        formatMoney(
                                            amountPaidBase,
                                            defaultCurrencyCode
                                        )
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-5 space-y-5"
                >
                    <div>
                        <label
                            class="block text-sm font-medium text-neutral-700 mb-3"
                        >
                            Amount Tendered ({{ paymentCurrencyCode }})
                        </label>
                        <div class="flex gap-2">
                            <TextField
                                ref="amountInputRef"
                                id="amount_paid"
                                v-model="amountTendered"
                                type="number"
                                placeholder="0.00"
                                pattern="[0-9]*\\.?[0-9]{0,2}"
                                inputmode="decimal"
                                class="flex-1"
                                input-class="text-2xl py-4"
                                :readonly="!isCashMethod"
                                :disabled="!isCashMethod"
                                :helper="
                                    !isCashMethod
                                        ? 'Auto-filled for non-cash payments.'
                                        : undefined
                                "
                            />
                        </div>
                    </div>

                    <div class="">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="w-full">
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="digit in keypadDigits"
                                        :key="digit"
                                        type="button"
                                        @click="appendDigit(digit)"
                                        :class="[
                                            'py-4 rounded-xl text-lg font-semibold bg-white border border-neutral-200 hover:bg-primary-600 hover:text-white transition-all duration-150 shadow-sm',

                                            !isCashMethod
                                                ? 'opacity-50 cursor-not-allowed hover:bg-white hover:text-neutral-500'
                                                : '',
                                        ]"
                                        :disabled="!isCashMethod"
                                    >
                                        {{ digit }}
                                    </button>
                                    <button
                                        type="button"
                                        @click="backSpace"
                                        :class="[
                                            'py-4 rounded-xl text-lg font-semibold bg-error-50 text-red-600 border border-red-200 hover:bg-red-100 transition-all duration-150 shadow-sm',
                                            !isCashMethod
                                                ? 'opacity-50 cursor-not-allowed hover:bg-error-50 hover:text-red-600'
                                                : '',
                                        ]"
                                        :disabled="!isCashMethod"
                                    >
                                        <-
                                    </button>
                                    <button
                                        type="button"
                                        @click="clearAmount"
                                        :class="[
                                            'py-4 rounded-xl text-lg font-semibold bg-error-50 text-red-600 border border-red-200 hover:bg-red-100 transition-all duration-150 shadow-sm',
                                            !isCashMethod
                                                ? 'opacity-50 cursor-not-allowed hover:bg-error-50 hover:text-red-600'
                                                : '',
                                        ]"
                                        :disabled="!isCashMethod"
                                    >
                                        Clear
                                    </button>
                                </div>
                            </div>

                            <div
                                v-if="isCashMethod && quickAmountOptions.length"
                                class="space-y-2 w-full"
                            >
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-3"
                                >
                                    <button
                                        v-for="option in quickAmountOptions"
                                        :key="option.base"
                                        type="button"
                                        @click="addAmountPaid(option.amount)"
                                        class="py-3 px-2 rounded-lg font-semibold text-sm transition-all duration-200 bg-neutral-100 text-neutral-700 hover:bg-primary-600 hover:text-white active:scale-95"
                                    >
                                        +{{ option.label }}
                                    </button>
                                </div>
                                <div class="space-y-4">
                                    <button
                                        v-if="isCashMethod && totalDue > 0"
                                        type="button"
                                        @click="setExactAmount"
                                        class="mt-2 w-full h-11 py-2 px-4 bg-primary-50 text-primary-600 rounded-lg hover:bg-blue-100 font-medium text-sm transition-colors"
                                    >
                                        Exact Amount (
                                        {{
                                            formatMoney(
                                                totalDue,
                                                defaultCurrencyCode
                                            )
                                        }}
                                        )
                                    </button>

                                    <div
                                        v-if="showConversionSummary"
                                        class="bg-neutral-50 border border-neutral-200 rounded-lg p-4 space-y-1"
                                    >
                                        <p class="text-xs text-neutral-600">
                                            Converted Amount ({{
                                                defaultCurrencyCode
                                            }})
                                        </p>
                                        <p
                                            class="text-base font-semibold text-neutral-900"
                                        >
                                            {{
                                                formatMoney(
                                                    amountPaidBase,
                                                    defaultCurrencyCode
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            @click="navigateBack"
                            class="flex-1 py-3 px-4 bg-gray-200 text-neutral-800 rounded-lg hover:bg-gray-300 transition-colors font-semibold"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            :disabled="!canSettle || isSubmitting"
                            @click="handleSettlePayment"
                            :class="[
                                'flex-1 py-3 px-4 rounded-lg font-semibold text-white transition-colors',
                                canSettle && !isSubmitting
                                    ? 'bg-success-600 hover:bg-success-700'
                                    : 'bg-gray-400 cursor-not-allowed',
                            ]"
                        >
                            {{ isSubmitting ? "Processing..." : "Settle Bill" }}
                        </button>
                    </div>
                </section>
            </div>
        </div>

        <div
            v-else
            class="max-w-4xl mx-auto px-4 py-24 text-center text-neutral-600 space-y-4"
        >
            <p class="text-2xl font-semibold">No active cart found.</p>
            <p>
                Please return to the ordering screen and select a cart to
                settle.
            </p>
            <button
                type="button"
                class="px-6 py-3 bg-primary text-white rounded-lg font-semibold"
                @click="navigateBack"
            >
                Go Back
            </button>
        </div>

        <ReceiptModal
            v-if="receiptData"
            v-model:visible="showReceiptModal"
            :receipt-data="receiptData"
        />
        <Toast />
    </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, reactive, ref, watch } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Toast, useToast } from "primevue";
import TextField from "@/Components/Form/TextField.vue";
import SelectField from "@/Components/Form/SelectField.vue";
import ReceiptModal from "@/Components/Resto/OrderSummary/ReceiptModal.vue";
import { useCashier } from "@/composables/useCashier";
import { formatMoney } from "@/Utils/FormatMoney";
import Swal from "sweetalert2";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import moment from "moment-timezone";
import { ArrowLeftIcon } from "@heroicons/vue/24/outline";
import axios from "axios";

const props = defineProps<{ cart: any }>();

const page = usePage<any>();
const toast = useToast();
const { settlePayment } = useCashier();

const hasCart = computed(() => Boolean(props.cart?.id));
const tableInfo = computed(() => props.cart?.meta?.table_info ?? null);
const totals = computed(() => props.cart?.totals ?? {});
const totalDue = computed(() =>
    Number(totals.value?.total_due ?? totals.value?.total_amount ?? 0)
);
const cartIdentifier = computed(
    () => props.cart?.bill_number ?? props.cart?.id ?? null
);
const formattedCartNumber = computed(() => {
    const raw = cartIdentifier.value;
    if (raw === null || raw === undefined) {
        return "0000";
    }

    const numeric = Number(raw);
    if (Number.isFinite(numeric)) {
        return numeric.toString().padStart(4, "0");
    }

    const digitsOnly = String(raw).replace(/[^0-9]/g, "");
    if (digitsOnly) {
        return digitsOnly.padStart(4, "0");
    }

    return String(raw);
});

const paymentMethods = computed(() => page.props.payment_methods ?? []);
const availablePaymentMethods = computed(() => {
    const methods = Array.isArray(paymentMethods.value)
        ? paymentMethods.value
        : [];

    return [...methods].sort((a: any, b: any) => {
        const orderA = typeof a.sort_order === "number" ? a.sort_order : 999;
        const orderB = typeof b.sort_order === "number" ? b.sort_order : 999;

        if (orderA === orderB) {
            return (a.name || "").localeCompare(b.name || "");
        }

        return orderA - orderB;
    });
});
const fallbackCurrency = {
    id: null,
    code: "PHP",
    name: "Philippine Peso",
    symbol: "₱",
    exchange_rate: 1,
    is_default: true,
};
const currencies = computed(() => {
    const sharedCurrencies = page.props.currencies;

    if (Array.isArray(sharedCurrencies) && sharedCurrencies.length > 0) {
        return sharedCurrencies;
    }

    if (page.props.default_currency) {
        return [page.props.default_currency];
    }

    return [fallbackCurrency];
});
const defaultCurrency = computed(() => {
    if (page.props.default_currency) {
        return page.props.default_currency;
    }

    const preferred = currencies.value.find(
        (currency: any) => currency.is_default
    );
    return preferred ?? currencies.value[0] ?? fallbackCurrency;
});
const defaultCurrencyCode = computed(
    () => defaultCurrency.value?.code || "PHP"
);

const hasMultipleCurrencies = computed(() => currencies.value.length > 1);

const selectedPaymentMethodId = ref<number | null>(
    availablePaymentMethods.value[0]?.id ?? null
);
const selectedPaymentMethod = computed(
    () =>
        availablePaymentMethods.value.find(
            (method: any) => method.id === selectedPaymentMethodId.value
        ) || null
);
const paymentType = computed(
    () => selectedPaymentMethod.value?.payment_type || null
);
const paymentTypeLabels: Record<string, string> = {
    cash: "Cash",
    card: "Card",
    "e-wallet": "E-Wallet",
    credit: "Credit",
    "gift-check": "Gift Check",
};
const isCashMethod = computed(() => paymentType.value === "cash");
const isEWalletMethod = computed(() => paymentType.value === "e-wallet");
const isCardMethod = computed(() => paymentType.value === "card");
const isCreditMethod = computed(() => paymentType.value === "credit");
const isGiftCheckMethod = computed(() => paymentType.value === "gift-check");
const requiresAdditionalFields = computed(
    () =>
        isEWalletMethod.value ||
        isCardMethod.value ||
        isCreditMethod.value ||
        isGiftCheckMethod.value
);
const isCustomerSearchDisabled = computed(() => !isCreditMethod.value);
const getPaymentTypeLabel = (type?: string | null) => {
    if (!type) {
        return "Payment";
    }

    if (paymentTypeLabels[type]) {
        return paymentTypeLabels[type];
    }

    return type
        .split("-")
        .map((segment) =>
            segment ? segment[0].toUpperCase() + segment.slice(1) : segment
        )
        .join(" ");
};

// Currency is now determined by the selected payment method
const selectedCurrency = computed(() => {
    const method = availablePaymentMethods.value.find(
        (pm: any) => pm.id === selectedPaymentMethodId.value
    );

    if (!method) {
        return defaultCurrency.value;
    }

    // For cash payment methods, use their embedded currency info
    if (method.payment_type === "cash" && method.currency_code) {
        return {
            id: method.id,
            code: method.currency_code,
            name: method.currency_name,
            symbol: method.symbol,
            exchange_rate: method.exchange_rate ?? 1,
            is_default: method.is_default_cash,
        };
    }

    // For non-cash methods, use default currency
    return defaultCurrency.value;
});

const paymentCurrencyCode = computed(
    () => selectedCurrency.value?.code || defaultCurrencyCode.value
);

const paymentDetails = reactive({
    referenceNumber: "",
    creditCustomerName: "",
    creditCustomerContact: "",
    cardApprovalCode: "",
    cardHolderName: "",
    giftCheckNumber: "",
    giftCheckAmount: "",
});
const customerSearchQuery = ref("");
const customerResults = ref<any[]>([]);
const customerSearchLoading = ref(false);
const customerSearchError = ref<string | null>(null);
let customerSearchTimeout: ReturnType<typeof setTimeout> | null = null;

const resetPaymentDetails = () => {
    paymentDetails.referenceNumber = "";
    paymentDetails.creditCustomerName = "";
    paymentDetails.creditCustomerContact = "";
    paymentDetails.cardApprovalCode = "";
    paymentDetails.cardHolderName = "";
    paymentDetails.giftCheckNumber = "";
    paymentDetails.giftCheckAmount = "";
};

const giftCheckAmountNumber = computed(() => {
    const amount = parseFloat(paymentDetails.giftCheckAmount || "0");
    return Number.isFinite(amount) ? amount : 0;
});

const keypadDigits = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];
const presetBaseAmounts = [50, 100, 200, 500, 1000, 2000, 5000, 10000];
const amountTendered = ref<string>("0");
const amountInputRef = ref();

const amountTenderedNumber = computed(() => {
    const value = amountTendered.value;
    const numeric =
        typeof value === "string" ? parseFloat(value) : Number(value);
    return Number.isFinite(numeric) ? numeric : 0;
});

const syncAmountForNonCashMethods = () => {
    if (isCashMethod.value) {
        return;
    }

    const rate = exchangeRate.value || 1;
    if (!rate) {
        return;
    }

    const safeRate = rate === 0 ? 1 : rate;
    const targetAmount = totalDue.value / safeRate;
    amountTendered.value = targetAmount.toFixed(2);
};

const exchangeRate = computed(
    () => Number(selectedCurrency.value?.exchange_rate ?? 1) || 1
);
const amountPaidBase = computed(() =>
    Number((amountTenderedNumber.value * exchangeRate.value).toFixed(2))
);
const changeAmount = computed(() =>
    Math.max(0, amountPaidBase.value - totalDue.value)
);
const showConversionSummary = computed(
    () => Math.abs(exchangeRate.value - 1) > 0.0001
);

const quickAmountOptions = computed(() => {
    if (!exchangeRate.value || !isCashMethod.value) {
        return [];
    }

    return presetBaseAmounts.map((base) => {
        const amount = Number((base / exchangeRate.value).toFixed(2));
        return {
            base,
            amount,
            label: formatMoney(amount, paymentCurrencyCode.value),
        };
    });
});

watch(
    availablePaymentMethods,
    (methods) => {
        if (
            !methods.find(
                (method: any) => method.id === selectedPaymentMethodId.value
            )
        ) {
            selectedPaymentMethodId.value = methods[0]?.id ?? null;
        }
    },
    { immediate: true }
);

watch(
    () => selectedPaymentMethodId.value,
    (newId) => {
        const method = availablePaymentMethods.value.find(
            (paymentMethod: any) => paymentMethod.id === newId
        );

        if (!method) {
            return;
        }

        resetPaymentDetails();
        syncAmountForNonCashMethods();
    }
);

watch(
    [isCashMethod, exchangeRate, totalDue],
    () => {
        syncAmountForNonCashMethods();
    },
    { immediate: true }
);

const resetCustomerSearch = () => {
    if (customerSearchTimeout) {
        clearTimeout(customerSearchTimeout);
        customerSearchTimeout = null;
    }
    customerSearchQuery.value = "";
    customerResults.value = [];
    customerSearchLoading.value = false;
    customerSearchError.value = null;
};

watch(isCreditMethod, (isCredit) => {
    if (!isCredit) {
        resetCustomerSearch();
    }
});

const fetchCustomers = async (query: string) => {
    if (!query || !isCreditMethod.value) {
        return;
    }

    customerSearchLoading.value = true;
    customerSearchError.value = null;

    try {
        const { data } = await axios.get(route("customers.search"), {
            params: { query },
        });
        customerResults.value = Array.isArray(data) ? data : [];
    } catch (error) {
        console.error("Customer search failed", error);
        customerSearchError.value = "Unable to search customers right now.";
        customerResults.value = [];
    } finally {
        customerSearchLoading.value = false;
    }
};

watch(
    () => customerSearchQuery.value,
    (query) => {
        if (!isCreditMethod.value) {
            return;
        }

        if (customerSearchTimeout) {
            clearTimeout(customerSearchTimeout);
            customerSearchTimeout = null;
        }

        if (!query || query.trim().length < 2) {
            customerResults.value = [];
            customerSearchError.value = null;
            return;
        }

        customerSearchTimeout = setTimeout(() => {
            fetchCustomers(query.trim());
        }, 300);
    }
);

const selectCustomer = (customer: any) => {
    if (!customer) {
        return;
    }

    paymentDetails.creditCustomerName = customer.customer_name || "";
    paymentDetails.creditCustomerContact =
        customer.contact_no || customer.email || "";
    customerSearchQuery.value = customer.customer_name || "";
    customerResults.value = [];
};

const cleanString = (value?: string | null) =>
    typeof value === "string"
        ? value.trim()
        : value !== null && value !== undefined
        ? String(value).trim()
        : "";

const additionalFieldsValid = computed(() => {
    if (isEWalletMethod.value) {
        return cleanString(paymentDetails.referenceNumber).length > 0;
    }

    if (isCardMethod.value) {
        return (
            cleanString(paymentDetails.cardApprovalCode).length > 0 &&
            cleanString(paymentDetails.cardHolderName).length > 0
        );
    }

    if (isCreditMethod.value) {
        return (
            cleanString(paymentDetails.creditCustomerName).length > 0 &&
            cleanString(paymentDetails.creditCustomerContact).length > 0
        );
    }

    if (isGiftCheckMethod.value) {
        return (
            cleanString(paymentDetails.giftCheckNumber).length > 0 &&
            giftCheckAmountNumber.value > 0
        );
    }

    return true;
});

const additionalFieldErrorMessage = computed(() => {
    if (isEWalletMethod.value) {
        return "Reference number is required for e-wallet payments.";
    }
    if (isCardMethod.value) {
        return "Approval code and cardholder name are required for card payments.";
    }
    if (isCreditMethod.value) {
        return "Customer name and contact information are required for credit payments.";
    }
    if (isGiftCheckMethod.value) {
        return "Gift check number and amount are required for gift-check payments.";
    }
    return "";
});

const buildPaymentDetailsPayload = () => {
    const details: Record<string, any> = {};

    if (isEWalletMethod.value) {
        details.reference_number = cleanString(paymentDetails.referenceNumber);
    }

    if (isCardMethod.value) {
        details.approval_code = cleanString(paymentDetails.cardApprovalCode);
        details.card_holder_name = cleanString(paymentDetails.cardHolderName);
    }

    if (isCreditMethod.value) {
        details.customer_name = cleanString(paymentDetails.creditCustomerName);
        details.customer_contact = cleanString(
            paymentDetails.creditCustomerContact
        );
    }

    if (isGiftCheckMethod.value) {
        details.gift_check_number = cleanString(paymentDetails.giftCheckNumber);
        details.gift_check_amount = giftCheckAmountNumber.value;
    }

    return details;
};

const resolveReferenceNumber = () => {
    if (isEWalletMethod.value) {
        return cleanString(paymentDetails.referenceNumber) || null;
    }

    if (isCardMethod.value) {
        return cleanString(paymentDetails.cardApprovalCode) || null;
    }

    if (isGiftCheckMethod.value) {
        return cleanString(paymentDetails.giftCheckNumber) || null;
    }

    return null;
};

const canSettle = computed(() => {
    if (!selectedPaymentMethodId.value) {
        return false;
    }

    if (isCashMethod.value && !selectedCurrency.value) {
        return false;
    }

    if (!additionalFieldsValid.value) {
        return false;
    }

    return (
        amountPaidBase.value >= totalDue.value && amountTenderedNumber.value > 0
    );
});

const showReceiptModal = ref(false);
const receiptData = ref<any>(null);
const isSubmitting = ref(false);

const focusAmount = async () => {
    await nextTick();
    const inputElement = amountInputRef.value?.$el?.querySelector(
        "input"
    ) as HTMLInputElement;
    if (inputElement) {
        inputElement.focus();
        inputElement.select();
    }
};

onMounted(() => {
    focusAmount();
});

const appendDigit = (digit: string) => {
    if (!isCashMethod.value) {
        return;
    }
    let current =
        typeof amountTendered.value === "string"
            ? amountTendered.value
            : amountTendered.value?.toString() || "";

    if (current === "0") {
        current = "";
    }

    const [, fraction] = current.split(".");
    if (fraction && fraction.length >= 2) {
        return;
    }

    amountTendered.value = `${current}${digit}`;
};

const backSpace = () => {
    if (!isCashMethod.value) {
        return;
    }

    const currentValue = amountTendered.value?.toString() ?? "0";
    if (currentValue.length <= 1) {
        amountTendered.value = "0";
        return;
    }

    const nextValue = currentValue.slice(0, -1);
    amountTendered.value =
        nextValue === "" || nextValue === "-" ? "0" : nextValue;
};

const addAmountPaid = (amount: number) => {
    if (!isCashMethod.value) {
        return;
    }
    const newAmount = amountTenderedNumber.value + amount;
    amountTendered.value = newAmount.toFixed(2);
};

const setExactAmount = () => {
    if (!isCashMethod.value) {
        return;
    }
    if (!exchangeRate.value) {
        toast.add({
            severity: "error",
            summary: "Exchange Rate Missing",
            detail: "Set a valid exchange rate before using exact amount.",
            life: 3000,
        });
        return;
    }

    const exactAmount = totalDue.value / exchangeRate.value;
    amountTendered.value = exactAmount.toFixed(2);
};

const clearAmount = () => {
    if (!isCashMethod.value) {
        return;
    }
    amountTendered.value = "0";
};

const navigateBack = () => {
    const query: Record<string, any> = {};
    if (tableInfo.value?.id) {
        query.tableId = tableInfo.value.id;
    }

    router.visit(route("resto.index"), {
        data: query,
        preserveScroll: true,
    });
};

const showChangeDialog = async (changeValue: number) => {
    const normalizedChange = Math.max(0, Number(changeValue) || 0);
    const hasChange = normalizedChange > 0;

    await Swal.fire({
        title: hasChange
            ? formatMoney(normalizedChange, defaultCurrencyCode.value)
            : "Payment received exact amount",
        text: hasChange
            ? "Change to return to customer"
            : "Payment received exact amount",
        icon: "success",
        confirmButtonText: "OK",
        didOpen: () => {
            const confirmButton = document.querySelector(
                ".swal2-confirm"
            ) as HTMLButtonElement;
            confirmButton?.focus();
        },
    });

    router.visit(route("table-rooms.index"));
};

const processReceipt = async (data: any) => {
    if (!data) {
        await showChangeDialog(changeAmount.value);
        return;
    }

    const thermalReceiptData = {
        storeName: page.props.company_info?.company_name,
        branch: data.branch,
        orderNumber: data.invoice_no,
        cashier: data.cashier?.name,
        dateTime: moment(data.order_date)
            .tz("Asia/Manila")
            .format("MM/DD/YYYY hh:mm A"),
        tableNumber: data.table_number,
        items: data.order_items || [],
        totals: data.totals || 0,
        payment: data.payment,
        receiptFooter: data?.branch?.receipt_footer,
        receiptHeader: data?.branch?.receipt_headers,
        birAccreditationFooter: data?.branch?.bir_accreditation_footer,
        isReprint: false,
    };

    try {
        await thermalPrinter.printReceipt(thermalReceiptData, false);
    } catch (error) {
        console.error("Failed to print receipt:", error);
        showReceiptModal.value = true;
        toast.add({
            severity: "warn",
            summary: "Printer Error",
            detail: "Failed to print receipt. Showing receipt modal instead.",
            life: 3000,
        });
    } finally {
        await showChangeDialog(
            Number(data.payment?.change ?? changeAmount.value)
        );
    }
};

const handleSettlePayment = async () => {
    if (!hasCart.value) {
        toast.add({
            severity: "error",
            summary: "No Cart",
            detail: "Cart information is missing.",
            life: 3000,
        });
        return;
    }

    if (!selectedPaymentMethodId.value) {
        toast.add({
            severity: "error",
            summary: "Payment Method",
            detail: "Please select a payment method.",
            life: 3000,
        });
        return;
    }

    if (amountTenderedNumber.value <= 0) {
        toast.add({
            severity: "error",
            summary: "Invalid Amount",
            detail: "Please enter a valid amount.",
            life: 3000,
        });
        return;
    }

    if (amountPaidBase.value < totalDue.value) {
        toast.add({
            severity: "error",
            summary: "Insufficient Amount",
            detail: "Amount tendered does not cover the total due.",
            life: 3000,
        });
        return;
    }

    if (!additionalFieldsValid.value) {
        toast.add({
            severity: "error",
            summary: "Additional Details Required",
            detail:
                additionalFieldErrorMessage.value ||
                "Please complete the required payment details.",
            life: 3000,
        });
        return;
    }

    try {
        isSubmitting.value = true;
        const paymentDetailsPayload = buildPaymentDetailsPayload();
        const referenceNumber = resolveReferenceNumber();
        const response = await settlePayment({
            cart_id: props.cart.id,
            payment_method_id: selectedPaymentMethodId.value,
            amount_in_payment_currency: Number(
                amountTenderedNumber.value.toFixed(2)
            ),
            amount_paid: amountPaidBase.value,
            total_amount: totalDue.value,
            reference_number: referenceNumber ?? undefined,
            payment_details:
                Object.keys(paymentDetailsPayload).length > 0
                    ? paymentDetailsPayload
                    : undefined,
        });

        if (response.success) {
            toast.add({
                severity: "success",
                summary: "Success",
                detail: response.message || "Bill settled successfully",
                life: 3000,
            });
            receiptData.value = response.data;
            await processReceipt(response.data);
        } else {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: response.error || "Failed to settle bill",
                life: 3000,
            });
        }
    } catch (error) {
        console.error("Settlement error:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail:
                error instanceof Error ? error.message : "An error occurred",
            life: 3000,
        });
    } finally {
        isSubmitting.value = false;
    }
};
</script>
