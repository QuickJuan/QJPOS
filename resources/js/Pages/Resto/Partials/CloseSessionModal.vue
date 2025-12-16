<template>
    <Dialog
        v-model:visible="props.showCloseDialog"
        modal
        header="End of Shift - Cash Denomination"
        :style="{ width: '50rem' }"
        class="bg-white"
        @hide="handleClose"
        @update:visible="handleClose"
    >
        <div class="space-y-6">
            <!-- Sales Summary - Updated -->
            <div
                class="flex md:flex-row md:justify-between bg-gray-50 rounded-lg p-4 border"
            >
                <div>
                    <p class="text-sm text-gray-600">Beginning Cash</p>
                    <p class="text-xl font-bold text-gray-900">
                        {{
                            formatMoney(
                                (
                                    props.openSession?.beginning_cash || 0
                                ).toFixed(2)
                            )
                        }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Cash Denomination Total</p>
                    <p class="text-xl font-bold text-gray-900">
                        {{ formatMoney(totalCashCounted.toFixed(2)) }}
                    </p>
                </div>
            </div>

            <!-- Cash Denomination Input -->
            <div class="bg-white border rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 md:gap-20">
                    <!-- First Column -->
                    <div class="space-y-2">
                        <div
                            class="grid grid-cols-3 gap-2 font-semibold text-sm text-right mb-2"
                        >
                            <span class="text-center">Quantity</span>
                            <span>Money Value</span>
                            <span>Total</span>
                        </div>
                        <div
                            v-for="[denom, count] in denominationsFirstColumn"
                            :key="denom"
                            class="grid grid-cols-3 gap-2 items-center"
                        >
                            <TextField
                                v-model.number="cashDenominations[denom]"
                                type="number"
                                min="0"
                                :placeholder="`Count of ₱${denom}`"
                                class="w-full"
                            />
                            <p class="text-sm text-gray-900 text-right">
                                {{ formatMoney(denom) }}
                            </p>
                            <p class="text-sm text-gray-900 text-right">
                                {{
                                    formatMoney(
                                        (parseFloat(denom) * count).toFixed(2)
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Second Column -->
                    <div class="space-y-2">
                        <div
                            class="hidden md:grid grid-cols-3 gap-2 font-semibold text-sm text-right mb-2"
                        >
                            <span class="text-center">Quantity</span>
                            <span>Money Value</span>
                            <span>Total</span>
                        </div>
                        <div
                            v-for="[denom, count] in denominationsSecondColumn"
                            :key="denom"
                            class="grid grid-cols-3 gap-2 items-center"
                        >
                            <TextField
                                v-model.number="cashDenominations[denom]"
                                type="number"
                                min="0"
                                :placeholder="`Count of ₱${denom}`"
                                class="w-full"
                            />
                            <p class="text-sm text-gray-900 text-right">
                                {{ formatMoney(denom) }}
                            </p>
                            <p class="text-sm text-gray-900 text-right">
                                {{
                                    formatMoney(
                                        (parseFloat(denom) * count).toFixed(2)
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>
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
                    :disabled="totalCashCounted === 0"
                    class="bg-red-600 hover:bg-red-700"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import TextField from "@/Components/Form/TextField.vue";
import CashieringSession from "@/Types/CashieringSession";
import { formatMoney } from "@/Utils/FormatMoney";
import { Button, Dialog } from "primevue";
import { computed, ref } from "vue";
import { useToast } from "primevue";

const props = defineProps<{
    showCloseDialog: boolean;
    openSession: CashieringSession | null;
    sessionSummary?: any;
    // currentUser?: any;
    // generalSettings: any;
}>();

const emit = defineEmits([
    "confirmCloseSession",
    "closeModal",
    "sessionClosed",
]);

const toast = useToast();

const expectedCash = computed(() => {
    if (!props.openSession) return 0;
    return (
        (props.openSession.beginning_cash || 0) +
        (props.openSession.total_sales || 0)
    );
});

const cashDenominations = ref({
    "1000": 0,
    "500": 0,
    "200": 0,
    "100": 0,
    "50": 0,
    "20": 0,
    "10": 0,
    "5": 0,
    "1": 0,
    "0.25": 0,
    "0.5": 0,
});

// Split denominations into two columns for display
const denominationsFirstColumn = computed(() => {
    const entries = Object.entries(cashDenominations.value).sort(
        ([a], [b]) => parseFloat(b) - parseFloat(a)
    );
    const half = Math.ceil(entries.length / 2);
    return entries.slice(0, half);
});

const denominationsSecondColumn = computed(() => {
    const entries = Object.entries(cashDenominations.value).sort(
        ([a], [b]) => parseFloat(b) - parseFloat(a)
    );
    const half = Math.ceil(entries.length / 2);
    return entries.slice(half);
});

const totalCashCounted = computed(() => {
    return Object.entries(cashDenominations.value).reduce(
        (total, [denom, count]) => {
            return total + parseFloat(denom) * count;
        },
        0
    );
});
const cashDifference = computed(() => {
    return totalCashCounted.value - expectedCash.value;
});

const handleConfirmCloseSession = () => {
    const denominationData = Object.entries(cashDenominations.value).reduce(
        (acc, [denom, count]) => {
            if (count > 0) {
                acc[denom] = count;
            }
            return acc;
        },
        {} as Record<string, number>
    );

    emit("confirmCloseSession", {
        denominationData: denominationData,
        totalCashCounted: totalCashCounted.value,
    });
};

const handleClose = () => {
    emit("closeModal");
};
</script>
