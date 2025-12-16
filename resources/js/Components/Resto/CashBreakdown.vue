<template>
    <div v-if="cashDenominationDetails">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Cash Breakdown</h4>
        <div class="text-base space-y-1">
            <div
                v-for="[denom, count] in sortedDenominations"
                :key="denom"
                class="flex justify-between"
            >
                <span class="text-gray-600"
                    >{{ count }} x ₱{{ formatMoney(denom) }} =</span
                >
                <span class="font-semibold"
                    >₱{{ formatMoney(parseFloat(denom) * Number(count)) }}</span
                >
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
    cashDenominationDetails: Record<string, number> | null | undefined;
}>();

const sortedDenominations = computed(() => {
    if (!props.cashDenominationDetails) return [];
    return Object.entries(props.cashDenominationDetails).sort(
        (a, b) => parseFloat(b[0]) - parseFloat(a[0])
    );
});

const formatMoney = (amount: number | string) => {
    const num = typeof amount === "string" ? parseFloat(amount) : amount;
    return num.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};
</script>
