<template>
    <Dialog
        v-model:visible="props.showCloseDialog"
        modal
        header="End of Shift"
        :style="{ width: '46rem' }"
        class="bg-white"
        @hide="handleClose"
        @update:visible="handleClose"
    >
        <div class="space-y-6">
            <div
                class="grid gap-4 rounded-lg border bg-gray-50 p-4 md:grid-cols-2"
            >
                <div>
                    <p class="text-sm text-gray-600">Beginning Cash</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{
                            formatMoney(
                                (
                                    props.openSession?.beginning_cash || 0
                                ).toFixed(2)
                            )
                        }}
                    </p>
                </div>
                <div class="space-y-1">
                    <div
                        class="flex items-center justify-between text-sm text-gray-600"
                    >
                        <span>Physical Cash (Converted)</span>
                        <span class="font-semibold text-gray-900">
                            {{ baseCurrencySymbol }}
                            {{ formatNumber(totalCashInBase) }}
                        </span>
                    </div>
                    <div
                        class="flex items-center justify-between text-sm text-gray-600"
                    >
                        <span>Gift Checks</span>
                        <span class="font-semibold text-gray-900">
                            {{ baseCurrencySymbol }}
                            {{ formatNumber(giftCheckAmountNumber) }}
                        </span>
                    </div>
                    <div
                        class="flex items-center justify-between text-sm font-semibold text-gray-900"
                    >
                        <span>Total Closing Amount</span>
                        <span>
                            {{ baseCurrencySymbol }}
                            {{ formatNumber(totalClosingAmount) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="space-y-4 rounded-lg border bg-white p-4">
                <p class="text-sm font-semibold text-gray-800">
                    Enter cash on hand for each currency
                </p>
                <div class="space-y-3">
                    <div
                        v-for="currency in availableCurrencies"
                        :key="currency.id"
                        class="flex items-center justify-between rounded-lg border border-gray-200 p-3"
                    >
                        <div class="flex items-center justify-between text-sm">
                            <div class="">
                                <p class="font-semibold text-gray-900">
                                    {{ currency.name }} ({{ currency.code }})
                                </p>
                                <p class="text-xs text-gray-500">
                                    1 {{ currency.code }} =
                                    {{ baseCurrencySymbol }}
                                    {{
                                        formatNumber(
                                            currency.exchange_rate || 1
                                        )
                                    }}
                                </p>
                            </div>
                            <!-- <div class="text-right text-xs text-gray-500">
                                Converted: {{ baseCurrencySymbol }}
                                {{
                                    formatNumber(
                                        (currencyValues[
                                            buildCurrencyKey(currency.id)
                                        ] || 0) * (currency.exchange_rate || 1)
                                    )
                                }}
                            </div> -->
                        </div>
                        <div
                            class="grid gap-2 md:grid-cols-[220px_auto] md:items-center"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500"
                                    >Amount</span
                                >
                                <input
                                    type="number"
                                    min="0"
                                    :step="currency.step"
                                    class="w-full flex-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    :value="
                                        currencyValues[
                                            buildCurrencyKey(currency.id)
                                        ]
                                    "
                                    @input="
                                        handleCurrencyInput(
                                            currency.id,
                                            $event.target.value
                                        )
                                    "
                                />
                            </div>
                            <!-- <p class="text-xs text-gray-500">
                                {{ currency.symbol || currency.code }} totals
                                are converted automatically
                            </p> -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border bg-white p-4">
                <label
                    class="flex flex-col gap-1 text-sm font-semibold text-gray-800"
                >
                    Gift Check Total ({{ baseCurrencySymbol.trim() }})
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        class="rounded border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        :value="giftCheckAmount"
                        @input="handleGiftCheckInput($event.target.value)"
                    />
                    <span class="text-xs font-normal text-gray-500">
                        Only include physical gift checks on hand. Credit and
                        card payments are tracked automatically.
                    </span>
                </label>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button
                    type="button"
                    label="Cancel"
                    severity="secondary"
                    @click="emit('closeModal')"
                />
                <Button
                    type="button"
                    label="Submit & Close Shift"
                    @click="handleConfirmCloseSession"
                    :disabled="totalClosingAmount <= 0"
                    class="bg-red-600 hover:bg-red-700"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import CashieringSession from "@/Types/CashieringSession";
import { formatMoney } from "@/Utils/FormatMoney";
import { usePage } from "@inertiajs/vue3";
import { Button, Dialog } from "primevue";
import { computed, ref, watch } from "vue";

type CurrencyIdentifier = number | string;

interface CurrencyOption {
    id: CurrencyIdentifier;
    code: string;
    name: string;
    symbol?: string;
    exchange_rate?: number;
    step?: number;
}

const props = defineProps<{
    showCloseDialog: boolean;
    openSession: CashieringSession | null;
    sessionSummary?: any;
}>();

const emit = defineEmits([
    "confirmCloseSession",
    "closeModal",
    "sessionClosed",
]);

const page = usePage();

const defaultCurrency = computed(() => (page.props as any)?.default_currency);
const baseCurrencySymbol = computed(
    () => defaultCurrency.value?.symbol ?? "PHP "
);

const fallbackCurrency = computed<CurrencyOption>(() => ({
    id: defaultCurrency.value?.id ?? "default",
    code: defaultCurrency.value?.code ?? "CASH",
    name: defaultCurrency.value?.name ?? "Primary Currency",
    symbol: defaultCurrency.value?.symbol ?? "PHP ",
    exchange_rate: 1,
    step: 0.01,
}));

const availableCurrencies = computed<CurrencyOption[]>(() => {
    const shared = ((page.props as any)?.currencies || []) as CurrencyOption[];

    if (!shared.length) {
        return [fallbackCurrency.value];
    }

    return shared.map((currency) => ({
        ...currency,
        exchange_rate: currency.exchange_rate ?? 1,
        step: currency.code?.toLowerCase().includes("jpy") ? 1 : 0.01,
    }));
});

const buildCurrencyKey = (identifier: CurrencyIdentifier | undefined) => {
    return String(identifier ?? "default");
};

const currencyValues = ref<Record<string, number>>({});
const giftCheckAmount = ref<string | number>(0);

const initializeCurrencyValues = () => {
    const next: Record<string, number> = {};
    availableCurrencies.value.forEach((currency) => {
        const key = buildCurrencyKey(currency.id);
        next[key] = currencyValues.value[key] ?? 0;
    });
    currencyValues.value = next;
};

watch(
    availableCurrencies,
    () => {
        initializeCurrencyValues();
    },
    { immediate: true }
);

watch(
    () => props.showCloseDialog,
    (visible) => {
        if (visible) {
            initializeCurrencyValues();
            giftCheckAmount.value = 0;
        }
    }
);

const handleCurrencyInput = (
    currencyId: CurrencyIdentifier,
    rawValue: string | number
) => {
    const key = buildCurrencyKey(currencyId);
    const sanitized = Math.max(0, Number.parseFloat(String(rawValue)) || 0);
    currencyValues.value = {
        ...currencyValues.value,
        [key]: sanitized,
    };
};

const handleGiftCheckInput = (rawValue: string | number) => {
    const sanitized = Math.max(0, Number.parseFloat(String(rawValue)) || 0);
    giftCheckAmount.value = sanitized;
};

const currencySummaries = computed(() => {
    return availableCurrencies.value.map((currency) => {
        const key = buildCurrencyKey(currency.id);
        const amountInCurrency = currencyValues.value[key] ?? 0;
        const exchangeRate = currency.exchange_rate ?? 1;
        return {
            currencyId: currency.id,
            currencyCode: currency.code,
            currencyName: currency.name,
            symbol: currency.symbol ?? currency.code,
            exchangeRate,
            amountInCurrency,
            amountInBase: amountInCurrency * exchangeRate,
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

const cashDifference = computed(() => {
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
        totals: {
            cash_in_base: totalCashInBase.value,
            gift_check_in_base: giftCheckAmountNumber.value,
            combined_in_base: totalClosingAmount.value,
            variance_in_base: cashDifference.value,
        },
        currencies: filteredCurrencies.map((entry) => ({
            currency_id: entry.currencyId,
            currency_code: entry.currencyCode,
            currency_name: entry.currencyName,
            symbol: entry.symbol,
            exchange_rate: entry.exchangeRate,
            amount_in_currency: entry.amountInCurrency,
            amount_in_base: entry.amountInBase,
        })),
    };
};

const handleConfirmCloseSession = () => {
    const closingBreakdown = buildClosingBreakdown();

    emit("confirmCloseSession", {
        currencyBreakdown: closingBreakdown,
        totalCashCounted: totalClosingAmount.value,
        cashDifference: cashDifference.value,
    });
};

const handleClose = () => {
    emit("closeModal");
};

function formatNumber(value: number) {
    return Number(value || 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}
</script>
