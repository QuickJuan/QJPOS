<template>
    <div class="min-h-screen bg-gray-50">
        <Head title="Settle Payment" />

        <div class="bg-white border-b border-gray-200 shadow-sm">
            <div
                class="max-w-6xl mx-auto px-4 py-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="navigateBack"
                        class="p-2 rounded-full border border-gray-200 text-gray-600 hover:text-primary-600 hover:border-primary-200 transition-colors"
                    >
                        <ArrowLeftIcon class="w-5 h-5" />
                    </button>
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Table
                        </p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ tableInfo?.name || "Unassigned" }}
                        </p>
                        <p
                            v-if="tableInfo?.location"
                            class="text-sm text-gray-500"
                        >
                            {{ tableInfo.location }}
                        </p>
                    </div>
                </div>

                <div class="text-right">
                    <p class="text-sm text-gray-500">
                        Total Due ({{ defaultCurrencyCode }})
                    </p>
                    <p class="text-3xl font-black text-primary-600">
                        {{ formatMoney(totalDue, defaultCurrencyCode) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Cart #{{ props.cart?.bill_number || props.cart?.id }}
                    </p>
                </div>
            </div>
        </div>

        <div v-if="hasCart" class="max-w-6xl mx-auto px-4 py-6">
            <section
                class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 space-y-5"
            >
                <div
                    :class="[
                        'grid gap-4 md:items-start',
                        isCashMethod ? 'md:grid-cols-2' : 'md:grid-cols-1',
                    ]"
                >
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Payment Method
                        </label>
                        <SelectField
                            id="payment_method_id"
                            v-model="selectedPaymentMethodId"
                            :options="cashPaymentMethods"
                            optionLabel="name"
                            optionValue="id"
                            :disabled="cashPaymentMethods.length === 0"
                            placeholder="Select payment method"
                        />
                        <p
                            v-if="cashPaymentMethods.length === 0"
                            class="text-xs text-red-500 mt-1"
                        >
                            Please configure at least one cash payment method in
                            Filament.
                        </p>
                    </div>

                    <div v-if="isCashMethod" class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            Currency
                        </label>
                        <SelectField
                            id="currency_id"
                            v-model="selectedCurrencyId"
                            :options="currencies"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Select currency"
                            :disabled="currencies.length === 0"
                        />
                        <p class="text-xs text-gray-500">
                            1 {{ paymentCurrencyCode }} =
                            {{ formatMoney(exchangeRate, defaultCurrencyCode) }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-[320px_1fr]">
                    <div class="lg:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-3"
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
                                input-class="text-xl py-3"
                            />
                            <button
                                type="button"
                                @click="clearAmount"
                                class="px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 font-semibold text-xs transition-colors"
                            >
                                Clear
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">
                                Keypad
                            </p>
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    v-for="digit in keypadDigits"
                                    :key="digit"
                                    type="button"
                                    @click="appendDigit(digit)"
                                    :class="[
                                        'py-3 rounded-lg text-base font-semibold bg-white border border-gray-200 hover:bg-primary-600 hover:text-white transition-all duration-150 shadow-sm',
                                        digit === '0' ? 'col-span-3' : '',
                                    ]"
                                >
                                    {{ digit }}
                                </button>
                            </div>
                        </div>

                        <button
                            v-if="totalDue > 0"
                            type="button"
                            @click="setExactAmount"
                            class="w-full h-11 py-2 px-4 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 font-medium text-sm transition-colors"
                        >
                            Exact Amount (
                            {{ formatMoney(totalDue, defaultCurrencyCode) }}
                            )
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div v-if="quickAmountOptions.length" class="space-y-2">
                            <p class="text-sm font-medium text-gray-700">
                                Quick Amount Selection (Click to Add)
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <button
                                    v-for="option in quickAmountOptions"
                                    :key="option.base"
                                    type="button"
                                    @click="addAmountPaid(option.amount)"
                                    class="py-3 px-2 rounded-lg font-semibold text-sm transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-primary-600 hover:text-white active:scale-95"
                                >
                                    +{{ option.label }}
                                </button>
                            </div>
                        </div>

                        <div
                            class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3"
                        >
                            <div v-if="showConversionSummary">
                                <p class="text-xs text-gray-600">
                                    Converted Amount ({{ defaultCurrencyCode }})
                                </p>
                                <p class="text-xl font-semibold text-gray-900">
                                    {{
                                        formatMoney(
                                            amountPaidBase,
                                            defaultCurrencyCode
                                        )
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">
                                    Change ({{ defaultCurrencyCode }})
                                </p>
                                <p
                                    class="text-xl font-semibold"
                                    :class="
                                        changeAmount > 0
                                            ? 'text-success-600'
                                            : 'text-gray-900'
                                    "
                                >
                                    {{
                                        formatMoney(
                                            changeAmount,
                                            defaultCurrencyCode
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        type="button"
                        @click="navigateBack"
                        class="flex-1 py-3 px-4 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors font-semibold"
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

        <div
            v-else
            class="max-w-4xl mx-auto px-4 py-24 text-center text-gray-600 space-y-4"
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
import { computed, nextTick, onMounted, ref, watch } from "vue";
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

const paymentMethods = computed(() => page.props.payment_methods ?? []);
const cashPaymentMethods = computed(() =>
    paymentMethods.value.filter((method: any) => method.payment_type === "cash")
);
const currencies = computed(() => page.props.currencies ?? []);
const fallbackCurrency = {
    id: null,
    code: "PHP",
    symbol: "₱",
    exchange_rate: 1,
};
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

const selectedPaymentMethodId = ref<number | null>(
    cashPaymentMethods.value[0]?.id ?? null
);
const selectedPaymentMethod = computed(
    () =>
        cashPaymentMethods.value.find(
            (method: any) => method.id === selectedPaymentMethodId.value
        ) || null
);
const isCashMethod = computed(
    () => selectedPaymentMethod.value?.payment_type === "cash"
);

const selectedCurrencyId = ref<number | null>(
    defaultCurrency.value?.id ?? null
);
const selectedCurrency = computed(() => {
    if (!selectedCurrencyId.value) {
        return defaultCurrency.value;
    }

    return (
        currencies.value.find(
            (currency: any) => currency.id === selectedCurrencyId.value
        ) ?? defaultCurrency.value
    );
});

const paymentCurrencyCode = computed(
    () => selectedCurrency.value?.code || defaultCurrencyCode.value
);

watch(
    cashPaymentMethods,
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
        const method = cashPaymentMethods.value.find(
            (paymentMethod: any) => paymentMethod.id === newId
        );

        if (!method) {
            return;
        }

        if (method.payment_type !== "cash" && method.currency_id) {
            selectedCurrencyId.value = method.currency_id;
        } else if (!selectedCurrencyId.value) {
            selectedCurrencyId.value = defaultCurrency.value?.id ?? null;
        }
    }
);

watch(currencies, (list) => {
    if (
        !list.find((currency: any) => currency.id === selectedCurrencyId.value)
    ) {
        selectedCurrencyId.value = defaultCurrency.value?.id ?? null;
    }
});

const keypadDigits = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];
const presetBaseAmounts = [100, 200, 500, 1000];
const amountTendered = ref<string>("0");
const amountInputRef = ref();

const amountTenderedNumber = computed(() => {
    const value = amountTendered.value;
    const numeric =
        typeof value === "string" ? parseFloat(value) : Number(value);
    return Number.isFinite(numeric) ? numeric : 0;
});

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
    if (!exchangeRate.value) {
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

const canSettle = computed(() => {
    if (!selectedPaymentMethodId.value) {
        return false;
    }

    if (isCashMethod.value && !selectedCurrencyId.value) {
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

const addAmountPaid = (amount: number) => {
    const newAmount = amountTenderedNumber.value + amount;
    amountTendered.value = newAmount.toFixed(2);
};

const setExactAmount = () => {
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
    await Swal.fire({
        title: formatMoney(changeValue, defaultCurrencyCode.value),
        text: "Change to return to customer",
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

    if (isCashMethod.value && !selectedCurrencyId.value) {
        toast.add({
            severity: "error",
            summary: "Currency Required",
            detail: "Please select a currency for this payment.",
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

    try {
        isSubmitting.value = true;
        const response = await settlePayment({
            cart_id: props.cart.id,
            payment_method_id: selectedPaymentMethodId.value,
            currency_id: selectedCurrencyId.value,
            amount_in_payment_currency: Number(
                amountTenderedNumber.value.toFixed(2)
            ),
            amount_paid: amountPaidBase.value,
            total_amount: totalDue.value,
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
