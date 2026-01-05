<template>
    <CashieringLayout>
        <div
            class="min-h-screen bg-gradient-to-br from-neutral-50 to-neutral-100 px-4 py-8"
        >
            <div class="mx-auto max-w-7xl">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-neutral-900">
                        End of Shift
                    </h1>
                    <p class="text-base text-neutral-600">
                        Complete your cash count and close the shift.
                    </p>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-10 gap-6 mb-6">
                    <!-- Currency Denominations Section - 70% -->
                    <div
                        :class="
                            otherPaymentMethods.length > 0
                                ? 'lg:col-span-7'
                                : 'lg:col-span-10'
                        "
                    >
                        <div class="rounded-2xl bg-white p-6 shadow-lg h-full">
                            <h2 class="mb-4 text-xl font-bold text-neutral-900">
                                Cash Count
                            </h2>
                            <CurrencyDenominationInput
                                :currencies="availableCurrencies"
                                :default-currency="defaultCurrency"
                                :base-currency-symbol="baseCurrencySymbol"
                                :denomination-counts="denominationCounts"
                                :currency-values="currencyValues"
                                @denomination-input="handleDenominationInput"
                                @currency-input="handleCurrencyInput"
                            />
                        </div>
                    </div>

                    <!-- Other Payment Methods - 30% -->
                    <div
                        v-if="otherPaymentMethods.length > 0"
                        class="lg:col-span-3"
                    >
                        <div class="rounded-2xl bg-white p-6 shadow-lg h-full">
                            <h2 class="mb-4 text-xl font-bold text-neutral-900">
                                Other Payment Methods
                            </h2>
                            <div
                                class="space-y-4 max-h-[calc(100vh-280px)] overflow-y-auto"
                            >
                                <div
                                    v-for="method in otherPaymentMethods"
                                    :key="method.id"
                                    class="space-y-2"
                                >
                                    <label
                                        :for="`payment-method-${method.id}`"
                                        class="block text-sm font-semibold text-neutral-700"
                                    >
                                        {{ method.name }}
                                    </label>
                                    <input
                                        :id="`payment-method-${method.id}`"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        :aria-label="`Amount for ${method.name}`"
                                        :value="
                                            otherPaymentAmounts[method.id] || 0
                                        "
                                        @input="
                                            handleOtherPaymentInput(
                                                method.id,
                                                $event.target.value
                                            )
                                        "
                                        class="w-full rounded border border-neutral-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>
                            <p class="mt-4 text-xs text-neutral-600">
                                Enter the total amount for each payment method
                                on hand.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 pb-8">
                    <Link
                        :href="route('resto.index')"
                        class="inline-flex items-center justify-center rounded-lg border border-neutral-300 bg-white px-6 py-3 font-semibold text-neutral-900 transition-all hover:bg-neutral-50 active:bg-neutral-100"
                    >
                        Cancel
                    </Link>
                    <button
                        @click="handleConfirmCloseSession"
                        :disabled="isSubmitting"
                        class="flex-1 rounded-lg bg-gradient-to-r from-success-600 to-success-700 px-6 py-3 font-bold text-white transition-all hover:from-success-700 hover:to-success-800 disabled:opacity-50 disabled:cursor-not-allowed active:from-success-800 active:to-success-900"
                    >
                        <span v-if="!isSubmitting">Submit & Close Shift</span>
                        <span v-else>Closing Shift...</span>
                    </button>
                </div>
            </div>
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import CurrencyDenominationInput from "@/Components/Resto/CurrencyDenominationInput.vue";
import CashieringSession from "@/Types/CashieringSession";
import { formatMoney } from "@/Utils/FormatMoney";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import axios from "axios";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import { useToast } from "primevue";

interface CurrencyOption {
    id: string | number;
    code: string;
    name: string;
    symbol?: string;
    exchange_rate?: number;
    is_default?: boolean;
    denominations?: Array<{
        id: string | number;
        value: number;
        label: string;
        sort_order: number;
    }>;
}

const props = defineProps<{
    openSession: CashieringSession | null;
    usedPaymentMethodIds: (string | number)[];
    usedCashPaymentMethodIds: (string | number)[];
}>();

const page = usePage();
const isSubmitting = ref(false);
const toast = useToast();

const defaultCurrency = computed(() => (page.props as any)?.default_currency);
const baseCurrencySymbol = computed(
    () => defaultCurrency.value?.symbol ?? "PHP "
);

const availableCurrencies = computed<CurrencyOption[]>(() => {
    const currencies = ((page.props as any)?.currencies ||
        []) as CurrencyOption[];
    const usedCashIds = props.usedCashPaymentMethodIds || [];

    // Filter to only show currencies that were used in this session
    const filteredCurrencies = currencies.filter((currency) =>
        usedCashIds.includes(currency.id)
    );

    return filteredCurrencies.map((currency) => ({
        ...currency,
        exchange_rate: currency.exchange_rate ?? 1,
    }));
});

const buildCurrencyKey = (identifier: string | number | undefined) => {
    return String(identifier ?? "default");
};

// Denomination tracking: key format "currencyId-denominationId"
const denominationCounts = ref<Record<string, number>>({});
const currencyValues = ref<Record<string, number>>({});
const otherPaymentAmounts = ref<Record<string | number, number>>({});

// Get non-cash payment methods for the right sidebar
const otherPaymentMethods = computed(() => {
    const allPaymentMethods = ((page.props as any)?.payment_methods ||
        []) as any[];
    const usedIds = props.usedPaymentMethodIds || [];

    return allPaymentMethods.filter(
        (method) =>
            method.payment_type !== "cash" && usedIds.includes(method.id)
    );
});

const initializeValues = () => {
    const nextDenoms: Record<string, number> = {};
    const nextCurrencies: Record<string, number> = {};

    availableCurrencies.value.forEach((currency) => {
        const key = buildCurrencyKey(currency.id);
        nextCurrencies[key] = currencyValues.value[key] ?? 0;

        if (currency.denominations?.length) {
            currency.denominations.forEach((denom) => {
                const denomKey = `${key}-${denom.id}`;
                nextDenoms[denomKey] = denominationCounts.value[denomKey] ?? 0;
            });
        }
    });

    denominationCounts.value = nextDenoms;
    currencyValues.value = nextCurrencies;
};

watch(availableCurrencies, () => initializeValues(), { immediate: true });

const handleDenominationInput = (
    currencyId: string | number,
    denominationId: string | number,
    denominationValue: number,
    rawValue: string | number
) => {
    const key = `${buildCurrencyKey(currencyId)}-${denominationId}`;
    const count = Math.max(0, parseInt(String(rawValue), 10) || 0);

    denominationCounts.value = {
        ...denominationCounts.value,
        [key]: count,
    };

    // Auto-calculate total for this currency from denominations
    const currencyKey = buildCurrencyKey(currencyId);
    const currency = availableCurrencies.value.find((c) => c.id === currencyId);

    if (currency?.denominations?.length) {
        let total = 0;
        currency.denominations.forEach((denom) => {
            const denomKey = `${currencyKey}-${denom.id}`;
            const denomCount = denominationCounts.value[denomKey] ?? 0;
            total += denom.value * denomCount;
        });

        currencyValues.value = {
            ...currencyValues.value,
            [currencyKey]: total,
        };
    }
};

const handleCurrencyInput = (
    currencyId: string | number,
    rawValue: string | number
) => {
    const key = buildCurrencyKey(currencyId);
    const value = Math.max(0, parseFloat(String(rawValue)) || 0);

    currencyValues.value = {
        ...currencyValues.value,
        [key]: value,
    };
};

const handleOtherPaymentInput = (
    paymentMethodId: string | number,
    rawValue: string | number
) => {
    const value = Math.max(0, parseFloat(String(rawValue)) || 0);
    otherPaymentAmounts.value = {
        ...otherPaymentAmounts.value,
        [paymentMethodId]: value,
    };
};

const getDenominationCount = (
    currencyId: string | number,
    denominationId: string | number
) => {
    const key = `${buildCurrencyKey(currencyId)}-${denominationId}`;
    return denominationCounts.value[key] ?? 0;
};

const getTotalForCurrency = (currencyId: string | number) => {
    return currencyValues.value[buildCurrencyKey(currencyId)] ?? 0;
};

const currencySummaries = computed(() => {
    return availableCurrencies.value.map((currency) => {
        const key = buildCurrencyKey(currency.id);
        const amountInCurrency = currencyValues.value[key] ?? 0;
        const exchangeRate = currency.exchange_rate ?? 1;

        // Collect denominations if present
        const denominations = currency.denominations?.length
            ? currency.denominations
                  .map((denom) => {
                      const denomKey = `${key}-${denom.id}`;
                      const count = denominationCounts.value[denomKey] ?? 0;
                      return {
                          denomination_id: denom.id,
                          denomination_value: denom.value,
                          denomination_label: denom.label,
                          count,
                          total: denom.value * count,
                      };
                  })
                  .filter((d) => d.count > 0)
            : [];

        return {
            currencyId: currency.id,
            currencyCode: currency.code,
            currencyName: currency.name,
            symbol: currency.symbol ?? currency.code,
            exchangeRate,
            amountInCurrency,
            amountInBase: amountInCurrency * exchangeRate,
            denominations,
        };
    });
});

const buildClosingBreakdown = () => {
    const filteredCurrencies = currencySummaries.value.filter(
        (entry) => entry.amountInCurrency > 0
    );

    // Build other payment methods breakdown
    const otherPayments = otherPaymentMethods.value
        .filter((method) => (otherPaymentAmounts.value[method.id] || 0) > 0)
        .map((method) => ({
            payment_method_id: method.id,
            payment_method_name: method.name,
            payment_type: method.payment_type,
            amount: otherPaymentAmounts.value[method.id] || 0,
        }));

    // Structure matching CashierSessionRequest validation rules
    return {
        currencies: filteredCurrencies.map((entry) => ({
            payment_method_id: entry.currencyId,
            amount_in_currency: entry.amountInCurrency,
            amount_in_base: entry.amountInBase,
            // Additional metadata for backend processing
            currency_code: entry.currencyCode,
            currency_name: entry.currencyName,
            symbol: entry.symbol,
            exchange_rate: parseFloat(Number(entry.exchangeRate).toFixed(4)),
            denominations: entry.denominations,
        })),
        other_payments: otherPayments,
        // Extra metadata for backend
        base_currency_id: defaultCurrency.value?.id ?? null,
        base_currency_code: defaultCurrency.value?.code ?? "CASH",
        base_currency_symbol: baseCurrencySymbol.value,
    };
};

const handleConfirmCloseSession = async () => {
    const closingBreakdown = buildClosingBreakdown();

    isSubmitting.value = true;

    console.log("closing breakdown", closingBreakdown);
    try {
        const response = await axios.post(route("resto.session.close"), {
            cashDenomination: closingBreakdown,
            shiftNo: props.openSession?.id,
        });

        if (response.data?.success && response.data?.session) {
            const sessionData = response.data.session;

            // Prepare session data for printing
            const printData = {
                id: sessionData.id,
                shift_start: sessionData.started_time,
                shift_end: sessionData.closing_time || new Date().toISOString(),
                branch: (page.props as any)?.auth?.user?.branch || {},
                cashier: (page.props as any)?.auth?.user?.name || "Cashier",
                beginning_cash: sessionData.beginning_cash || 0,
                total_sales: sessionData.total_sales || 0,
                cash_denomination_total: sessionData.closing_cash || 0,
                cash_denomination_details:
                    sessionData.cash_denomination || null,
                meta_data: sessionData.meta_data || {},
            };

            // Try to print X Reading
            try {
                await thermalPrinter.printSessionSummary(printData);
                toast.add({
                    severity: "success",
                    summary: "Shift Closed",
                    detail: "X Reading printed successfully. Logging out...",
                    life: 2000,
                });
            } catch (printError) {
                console.error("Failed to print X Reading:", printError);
                toast.add({
                    severity: "warn",
                    summary: "Shift Closed",
                    detail: "Shift closed but printing failed. Logging out...",
                    life: 2000,
                });
            }

            // Logout the user after closing shift
            setTimeout(() => {
                router.post(route("logout"));
            }, 2000);
        }
    } catch (error: any) {
        console.error("Failed to close shift:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail:
                error.response?.data?.message ||
                error.message ||
                "Failed to close shift. Please try again.",
            life: 5000,
        });
        isSubmitting.value = false;
    }
};

function formatNumber(value: number) {
    return Number(value || 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}
</script>
