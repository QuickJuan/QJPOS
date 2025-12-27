<template>
    <div v-if="hasBreakdown">
        <h4 class="mb-3 text-sm font-semibold text-gray-700">
            Closing Breakdown
        </h4>

        <template v-if="structuredCurrencies.length || giftCheckTotal > 0">
            <div
                v-for="currency in structuredCurrencies"
                :key="currency.currency_code"
                class="mb-4 space-y-2"
            >
                <div
                    class="flex items-center justify-between text-sm font-semibold text-gray-700"
                >
                    <span>
                        {{ currency.currency_name || currency.currency_code }}
                    </span>
                    <span class="text-right text-gray-900">
                        {{ currency.symbol || "" }}
                        {{ formatNumber(currency.amount_in_currency) }}
                        <span class="text-xs font-normal text-gray-500">
                            ≈ {{ baseCurrencySymbol }}
                            {{ formatNumber(currency.amount_in_base) }}
                        </span>
                    </span>
                </div>

                <div
                    v-if="currency.denominations?.length"
                    class="space-y-1 text-sm"
                >
                    <div
                        v-for="denomination in currency.denominations"
                        :key="`${currency.currency_code}-${denomination.value}`"
                        class="flex justify-between"
                    >
                        <span class="text-gray-600">
                            {{ denomination.count }} x
                            {{
                                denomination.label ||
                                formatNumber(denomination.value)
                            }}
                            =
                        </span>
                        <span class="font-semibold text-gray-900">
                            {{ currency.symbol || "" }}
                            {{
                                formatNumber(
                                    denomination.total ??
                                        denomination.value * denomination.count
                                )
                            }}
                        </span>
                    </div>
                </div>

                <div
                    v-if="hasComparison(currency)"
                    class="rounded-lg border border-dashed border-gray-200 bg-gray-50 p-2 text-xs"
                >
                    <div
                        class="flex items-center justify-between text-gray-600"
                    >
                        <span>Expected</span>
                        <span class="font-semibold text-gray-900">
                            {{ currency.symbol || "" }}
                            {{
                                formatNumber(currency.expected_in_currency || 0)
                            }}
                            <span
                                class="ml-1 text-[10px] font-normal text-gray-500"
                            >
                                ≈ {{ baseCurrencySymbol }}
                                {{
                                    formatNumber(currency.expected_in_base || 0)
                                }}
                            </span>
                        </span>
                    </div>
                    <div
                        class="flex items-center justify-between font-semibold"
                        :class="varianceClass(currency.variance_in_currency)"
                    >
                        <span>Variance</span>
                        <span>
                            {{ currency.symbol || "" }}
                            {{
                                formatNumber(currency.variance_in_currency || 0)
                            }}
                            <span
                                class="ml-1 text-[10px] font-normal text-gray-500"
                            >
                                ≈ {{ baseCurrencySymbol }}
                                {{
                                    formatNumber(currency.variance_in_base || 0)
                                }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <div v-if="giftCheckTotal > 0" class="border-t pt-3 text-sm">
                <div
                    class="flex items-center justify-between font-semibold text-gray-700"
                >
                    <span>Gift Checks</span>
                    <span class="text-gray-900">
                        {{ baseCurrencySymbol }}
                        {{ formatNumber(giftCheckTotal) }}
                    </span>
                </div>
            </div>
        </template>

        <div
            v-if="totalsSummary"
            class="mt-4 space-y-2 rounded-lg border border-gray-200 bg-white p-3 text-sm"
        >
            <div class="flex items-center justify-between text-gray-600">
                <span>Actual Cash ({{ baseCurrencySymbol.trim() }})</span>
                <span class="font-semibold text-gray-900">
                    {{ baseCurrencySymbol }}
                    {{ formatNumber(totalsSummary.actual) }}
                </span>
            </div>
            <div class="flex items-center justify-between text-gray-600">
                <span>Expected Cash</span>
                <span class="font-semibold text-gray-900">
                    {{ baseCurrencySymbol }}
                    {{ formatNumber(totalsSummary.expected) }}
                </span>
            </div>
            <div
                class="flex items-center justify-between font-semibold"
                :class="varianceClass(totalsSummary.variance)"
            >
                <span>Variance</span>
                <span>
                    {{ baseCurrencySymbol }}
                    {{ formatNumber(totalsSummary.variance) }}
                </span>
            </div>
        </div>

        <template v-else>
            <div class="space-y-1 text-sm">
                <div
                    v-for="[denom, count] in legacyDenominations"
                    :key="denom"
                    class="flex justify-between"
                >
                    <span class="text-gray-600">
                        {{ count }} x {{ formatNumber(Number(denom)) }} =
                    </span>
                    <span class="font-semibold text-gray-900">
                        {{ formatNumber(parseFloat(denom) * Number(count)) }}
                    </span>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import type { ClosingCashBreakdownPayload } from "@/composables/useCashier";
import { computed } from "vue";

type BreakdownPayload =
    | ClosingCashBreakdownPayload
    | Record<string, number>
    | null
    | undefined;

const props = defineProps<{
    cashDenominationDetails: BreakdownPayload;
}>();

const isStructured = (
    details: BreakdownPayload
): details is ClosingCashBreakdownPayload => {
    return (
        !!details &&
        typeof details === "object" &&
        "currencies" in details &&
        Array.isArray(details.currencies)
    );
};

const structuredCurrencies = computed(() => {
    if (!isStructured(props.cashDenominationDetails)) {
        return [];
    }

    return (props.cashDenominationDetails.currencies ?? []).map((currency) => {
        const amountInCurrency =
            currency.amount_in_currency ?? currency.total_amount ?? 0;
        const exchangeRate = currency.exchange_rate ?? 1;
        const amountInBase =
            currency.amount_in_base ??
            currency.total_in_base ??
            amountInCurrency * exchangeRate;

        const expectedInCurrency =
            typeof currency.expected_in_currency === "number"
                ? Number(currency.expected_in_currency)
                : null;
        const expectedInBase =
            typeof currency.expected_in_base === "number"
                ? Number(currency.expected_in_base)
                : expectedInCurrency !== null
                ? expectedInCurrency * exchangeRate
                : null;
        const varianceInCurrency =
            typeof currency.variance_in_currency === "number"
                ? Number(currency.variance_in_currency)
                : expectedInCurrency !== null
                ? amountInCurrency - expectedInCurrency
                : null;
        const varianceInBase =
            typeof currency.variance_in_base === "number"
                ? Number(currency.variance_in_base)
                : expectedInBase !== null
                ? amountInBase - expectedInBase
                : null;

        return {
            ...currency,
            amount_in_currency: amountInCurrency,
            amount_in_base: amountInBase,
            expected_in_currency: expectedInCurrency,
            expected_in_base: expectedInBase,
            variance_in_currency: varianceInCurrency,
            variance_in_base: varianceInBase,
        };
    });
});

const legacyDenominations = computed(() => {
    if (
        !props.cashDenominationDetails ||
        isStructured(props.cashDenominationDetails)
    ) {
        return [];
    }

    return Object.entries(props.cashDenominationDetails).sort(
        (a, b) => parseFloat(b[0]) - parseFloat(a[0])
    );
});

const hasBreakdown = computed(() => {
    return (
        structuredCurrencies.value.length > 0 ||
        legacyDenominations.value.length > 0 ||
        giftCheckTotal.value > 0
    );
});

const totalsSummary = computed(() => {
    if (!isStructured(props.cashDenominationDetails)) {
        return null;
    }

    const totals = props.cashDenominationDetails.totals ?? {};
    const hasExpected = typeof totals.expected_cash_in_base === "number";
    const hasVariance = typeof totals.variance_in_base === "number";

    if (!hasExpected || !hasVariance) {
        return null;
    }

    return {
        actual: Number(totals.cash_in_base ?? totals.combined_in_base ?? 0),
        expected: Number(totals.expected_cash_in_base ?? 0),
        variance: Number(totals.variance_in_base ?? 0),
    };
});

const baseCurrencySymbol = computed(() => {
    if (!isStructured(props.cashDenominationDetails)) {
        return "";
    }

    return props.cashDenominationDetails.base_currency_symbol ?? "PHP ";
});

const giftCheckTotal = computed(() => {
    if (!isStructured(props.cashDenominationDetails)) {
        return 0;
    }

    const details = props.cashDenominationDetails;
    return (
        Number(
            details.gift_check_total ?? details.totals?.gift_check_in_base ?? 0
        ) || 0
    );
});

const hasComparison = (currency: any) => {
    return (
        typeof currency.expected_in_currency === "number" ||
        typeof currency.variance_in_currency === "number"
    );
};

const varianceClass = (value?: number | null) => {
    if (typeof value !== "number" || Math.abs(value) < 0.005) {
        return "text-gray-600";
    }

    return value > 0 ? "text-green-600" : "text-red-600";
};

const formatNumber = (amount: number | string) => {
    const num = typeof amount === "string" ? parseFloat(amount) : amount;
    return Number(num || 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};
</script>
