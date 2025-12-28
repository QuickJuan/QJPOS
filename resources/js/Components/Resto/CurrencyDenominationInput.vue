<template>
    <div class="space-y-6">
        <div
            v-for="currency in currencies"
            :key="currency.id"
            class="border-t border-neutral-200 pt-6 first:border-t-0 first:pt-0"
        >
            <!-- Currency Header -->
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-neutral-900">
                        {{ currency.name }} ({{ currency.code }})
                    </h3>
                    <p class="text-sm text-neutral-600">
                        1 {{ currency.code }} = {{ baseCurrencySymbol
                        }}{{ formatNumber(currency.exchange_rate || 1) }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-neutral-600">Total</p>
                    <p class="text-2xl font-bold text-neutral-900">
                        {{
                            currency.code === defaultCurrency?.code
                                ? baseCurrencySymbol
                                : currency.symbol || currency.code
                        }}{{ formatNumber(getTotalForCurrency(currency.id)) }}
                    </p>
                </div>
            </div>

            <!-- Denominations Input Grid -->
            <div v-if="currency.denominations?.length" class="mb-4">
                <p class="mb-3 text-sm font-semibold text-neutral-700">
                    Enter quantity for each denomination:
                </p>
                <div
                    class="grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                >
                    <div
                        v-for="denom in currency.denominations"
                        :key="`${currency.id}-${denom.id}`"
                        class="rounded-lg border border-neutral-200 p-3 transition-all hover:border-primary-300 hover:bg-primary-50"
                    >
                        <!-- Header with Label and Value -->
                        <div class="mb-2 flex items-start justify-between">
                            <label
                                :for="`denom-${currency.id}-${denom.id}`"
                                class="block text-xs font-semibold text-neutral-600"
                            >
                                {{ denom.label }}
                            </label>
                            <p
                                :id="`denom-value-${currency.id}-${denom.id}`"
                                class="text-xs font-semibold text-primary-600"
                                aria-live="polite"
                            >
                                {{ currency.symbol || currency.code
                                }}{{
                                    formatNumber(
                                        getDenominationCount(
                                            currency.id,
                                            denom.id
                                        ) * denom.value
                                    )
                                }}
                            </p>
                        </div>

                        <!-- Input Field -->
                        <input
                            :id="`denom-${currency.id}-${denom.id}`"
                            type="number"
                            min="0"
                            :aria-label="`${denom.label} quantity for ${currency.name}`"
                            :aria-describedby="`denom-value-${currency.id}-${denom.id}`"
                            :value="getDenominationCount(currency.id, denom.id)"
                            @input="
                                handleDenominationInput(
                                    currency.id,
                                    denom.id,
                                    denom.value,
                                    $event.target.value
                                )
                            "
                            class="w-full rounded border border-neutral-300 px-2 py-2 text-center text-sm font-semibold focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                            placeholder="0"
                        />
                    </div>
                </div>
            </div>

            <!-- Quick Total Input (fallback if no denominations) -->
            <div v-else class="mb-4">
                <label
                    :for="`currency-total-${currency.id}`"
                    class="block text-sm font-semibold text-neutral-700 mb-2"
                >
                    Total Amount ({{ currency.code }})
                </label>
                <input
                    :id="`currency-total-${currency.id}`"
                    type="number"
                    min="0"
                    :step="
                        currency.code?.toLowerCase().includes('jpy') ? 1 : 0.01
                    "
                    :aria-label="`Total amount in ${currency.code}`"
                    :value="currencyValues[buildCurrencyKey(currency.id)]"
                    @input="
                        handleCurrencyInput(currency.id, $event.target.value)
                    "
                    class="w-full rounded border border-neutral-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                    placeholder="0"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

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

interface Props {
    currencies: CurrencyOption[];
    defaultCurrency: any;
    baseCurrencySymbol: string;
    denominationCounts: Record<string, number>;
    currencyValues: Record<string, number>;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    "denomination-input": [
        currencyId: string | number,
        denominationId: string | number,
        denominationValue: number,
        value: string | number
    ];
    "currency-input": [currencyId: string | number, value: string | number];
}>();

const buildCurrencyKey = (identifier: string | number | undefined) => {
    return String(identifier ?? "default");
};

const getDenominationCount = (
    currencyId: string | number,
    denominationId: string | number
) => {
    const key = `${buildCurrencyKey(currencyId)}-${denominationId}`;
    return props.denominationCounts[key] ?? 0;
};

const getTotalForCurrency = (currencyId: string | number) => {
    return props.currencyValues[buildCurrencyKey(currencyId)] ?? 0;
};

const handleDenominationInput = (
    currencyId: string | number,
    denominationId: string | number,
    denominationValue: number,
    value: string | number
) => {
    emit(
        "denomination-input",
        currencyId,
        denominationId,
        denominationValue,
        value
    );
};

const handleCurrencyInput = (
    currencyId: string | number,
    value: string | number
) => {
    emit("currency-input", currencyId, value);
};

function formatNumber(value: number) {
    return Number(value || 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}
</script>
