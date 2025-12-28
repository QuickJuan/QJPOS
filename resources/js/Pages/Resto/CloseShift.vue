<template>
    <CashieringLayout>
        <div
            class="min-h-screen bg-gradient-to-br from-neutral-50 to-neutral-100 px-4 py-8"
        >
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-neutral-900">
                        End of Shift
                    </h1>
                    <p class="text-lg text-neutral-600">
                        Complete your cash count and close the shift
                    </p>
                </div>

                <!-- Currency Denominations Section -->
                <div class="mb-8 rounded-2xl bg-white p-6 shadow-lg">
                    <h2 class="mb-6 text-xl font-bold text-neutral-900">
                        Cash Count by Currency
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

                <!-- Gift Check Section -->
                <div class="mb-8 rounded-2xl bg-white p-6 shadow-lg">
                    <h2 class="mb-4 text-xl font-bold text-neutral-900">
                        Gift Checks
                    </h2>
                    <label
                        for="gift-check-input"
                        class="block text-sm font-semibold text-neutral-700 mb-2"
                    >
                        Total Gift Checks ({{ baseCurrencySymbol.trim() }})
                    </label>
                    <input
                        id="gift-check-input"
                        type="number"
                        min="0"
                        step="0.01"
                        aria-label="Total gift checks in base currency"
                        aria-describedby="gift-check-help"
                        :value="giftCheckAmount"
                        @input="handleGiftCheckInput($event.target.value)"
                        class="w-full rounded border border-neutral-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        placeholder="0"
                    />
                    <p
                        id="gift-check-help"
                        class="mt-2 text-xs text-neutral-600"
                    >
                        Only include physical gift checks on hand. Credit and
                        card payments are tracked automatically.
                    </p>
                </div>

                <!-- Summary & Variance -->
                <div class="mb-8 rounded-2xl bg-white p-6 shadow-lg">
                    <h2 class="mb-6 text-xl font-bold text-neutral-900">
                        Closing Summary
                    </h2>
                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between rounded-lg bg-neutral-50 p-4"
                        >
                            <p class="font-semibold text-neutral-900">
                                Physical Cash Counted
                            </p>
                            <p class="text-2xl font-bold text-neutral-900">
                                {{ baseCurrencySymbol
                                }}{{ formatNumber(totalCashInBase) }}
                            </p>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg bg-neutral-50 p-4"
                        >
                            <p class="font-semibold text-neutral-900">
                                Gift Checks
                            </p>
                            <p class="text-2xl font-bold text-neutral-900">
                                {{ baseCurrencySymbol
                                }}{{ formatNumber(giftCheckAmountNumber) }}
                            </p>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg border-2 border-primary-200 bg-primary-50 p-4"
                        >
                            <p class="font-bold text-primary-900">
                                Total Closing Amount
                            </p>
                            <p class="text-3xl font-bold text-primary-900">
                                {{ baseCurrencySymbol
                                }}{{ formatNumber(totalClosingAmount) }}
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
                        :disabled="totalClosingAmount <= 0 || isSubmitting"
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
}>();

const page = usePage();
const isSubmitting = ref(false);

const defaultCurrency = computed(() => (page.props as any)?.default_currency);
const baseCurrencySymbol = computed(
    () => defaultCurrency.value?.symbol ?? "PHP "
);

const availableCurrencies = computed<CurrencyOption[]>(() => {
    const currencies = ((page.props as any)?.currencies ||
        []) as CurrencyOption[];
    return currencies.map((currency) => ({
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
const giftCheckAmount = ref<string | number>(0);

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

const handleGiftCheckInput = (rawValue: string | number) => {
    const value = Math.max(0, parseFloat(String(rawValue)) || 0);
    giftCheckAmount.value = value;
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

const totalCashInBase = computed(() => {
    return currencySummaries.value.reduce(
        (sum, entry) => sum + entry.amountInBase,
        0
    );
});

const giftCheckAmountNumber = computed(() => {
    return Number(giftCheckAmount.value) || 0;
});

const totalClosingAmount = computed(() => {
    return totalCashInBase.value + giftCheckAmountNumber.value;
});

const expectedCash = computed(() => {
    if (!props.openSession) return 0;
    return (
        (props.openSession.beginning_cash || 0) +
        (props.openSession.total_sales || 0)
    );
});

const cashVariance = computed(() => {
    return totalClosingAmount.value - expectedCash.value;
});

const buildClosingBreakdown = () => {
    const filteredCurrencies = currencySummaries.value.filter(
        (entry) => entry.amountInCurrency > 0
    );

    return {
        base_currency_id: defaultCurrency.value?.id ?? null,
        base_currency_code: defaultCurrency.value?.code ?? "CASH",
        base_currency_symbol: baseCurrencySymbol.value,
        gift_check_total: giftCheckAmountNumber.value,
        // Cash movement (only for default currency)
        cash_movement: {
            opening_balance: props.openSession?.beginning_cash || 0,
            sales_revenue: props.openSession?.total_sales || 0,
            expected_total: expectedCash.value,
        },
        totals: {
            cash_in_base: totalCashInBase.value,
            gift_check_in_base: giftCheckAmountNumber.value,
            combined_in_base: totalClosingAmount.value,
            variance_in_base: cashVariance.value,
        },
        currencies: filteredCurrencies.map((entry) => ({
            currency_id: entry.currencyId,
            currency_code: entry.currencyCode,
            currency_name: entry.currencyName,
            symbol: entry.symbol,
            exchange_rate: entry.exchangeRate,
            amount_in_currency: entry.amountInCurrency,
            amount_in_base: entry.amountInBase,
            denominations: entry.denominations,
        })),
    };
};

const handleConfirmCloseSession = () => {
    const closingBreakdown = buildClosingBreakdown();

    isSubmitting.value = true;

    router.post(
        route("resto.session.close"),
        {
            currencyBreakdown: closingBreakdown,
            totalCashCounted: totalClosingAmount.value,
            cashDifference: cashVariance.value,
        },
        {
            onSuccess: () => {
                isSubmitting.value = false;
            },
            onError: () => {
                isSubmitting.value = false;
            },
        }
    );
};

function formatNumber(value: number) {
    return Number(value || 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}
</script>
