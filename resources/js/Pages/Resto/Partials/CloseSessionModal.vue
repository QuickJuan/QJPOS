<template>
    <Dialog
        v-model:visible="props.showCloseDialog"
        modal
        header="Close Cashier Session"
        :style="{ width: '50rem' }"
        class="bg-white"
        @hide="handleClose"
        @update:visible="handleClose"
    >
        <div class="space-y-6">
            <!-- Sales Summary - Updated -->
            <div class="bg-gray-50 rounded-lg p-4 border">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">
                    Sales Summary
                </h4>
                <div class="grid grid-cols-2 gap-4">
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
                        <p class="text-sm text-gray-600">Total Sales</p>
                        <p class="text-xl font-bold text-green-600">
                            {{
                                formatMoney(
                                    (
                                        props.openSession?.total_sales || 0
                                    ).toFixed(2)
                                )
                            }}
                        </p>
                    </div>
                    <div class="col-span-2 border-t pt-4">
                        <p class="text-sm text-gray-600">Expected Cash</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ formatMoney(expectedCash.toFixed(2)) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Cash Denomination Input -->
            <div class="bg-white border rounded-lg p-4">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">
                    Cash Count
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div
                        v-for="(count, denom) in cashDenominations"
                        :key="denom"
                        class="space-y-2"
                    >
                        <TextField
                            :label="`${formatMoney(denom)}`"
                            v-model.number="cashDenominations[denom]"
                            type="number"
                            min="0"
                            :placeholder="`Count of ₱${denom}`"
                        />
                        <p class="text-xs text-gray-500">
                            Total:
                            {{
                                formatMoney(
                                    (parseFloat(denom) * count).toFixed(2)
                                )
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-gray-50 rounded-lg p-4 border">
                <div class="text-center">
                    <p class="text-sm text-gray-600">Total Cash Counted</p>
                    <p class="text-xl font-bold text-gray-900">
                        {{ formatMoney(totalCashCounted.toFixed(2)) }}
                    </p>
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
                    label="Proceed"
                    @click="handleConfirmCloseSession"
                    :disabled="totalCashCounted === 0"
                    class="bg-red-600 hover:bg-red-700"
                />
            </div>
        </template>
    </Dialog>

    <SessionSummaryModal
        :showSessionSummaryModal="showSessionSummaryModal"
        :openSession="openSession"
        :sessionSummary="sessionSummaryData"
        :currentUser="currentUser"
        :totalCashCounted="totalCashCounted"
        @closeModal="showSessionSummaryModal = false"
        @confirmClose="handleFinalConfirm"
    />
</template>

<script setup lang="ts">
import TextField from "@/Components/Form/TextField.vue";
import CashieringSession from "@/Types/CashieringSession";
import { formatMoney } from "@/Utils/FormatMoney";
import { Button, Dialog } from "primevue";
import { computed, ref } from "vue";
import SessionSummaryModal from "./SessionSummaryModal.vue";
import axios from "axios";
import { route } from "ziggy-js";

const props = defineProps<{
    showCloseDialog: boolean;
    openSession: CashieringSession | null;
    sessionSummary?: any;
    currentUser?: any;
}>();

const emit = defineEmits(["confirmCloseSession", "closeModal"]);

const showSessionSummaryModal = ref(false);
const sessionSummaryData = ref(null);

const expectedCash = computed(() => {
    if (!props.openSession) return 0;
    return (
        (props.openSession.beginning_cash || 0) +
        (props.openSession.total_sales || 0)
    );
});

const cashDenominations = ref({
    "0.25": 0,
    "1": 0,
    "5": 0,
    "10": 0,
    "20": 0,
    "50": 0,
    "100": 0,
    "200": 0,
    "500": 0,
    "1000": 0,
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

const handleConfirmCloseSession = async () => {
    try {
        const response = await axios.get(route("resto.api.session-summary"));
        sessionSummaryData.value = response.data;
        showSessionSummaryModal.value = true;
    } catch (error) {
        console.error("Failed to fetch session summary", error);
    }
};

const handleFinalConfirm = () => {
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
    showSessionSummaryModal.value = false;
};

const handleClose = () => {
    emit("closeModal");
};
</script>
