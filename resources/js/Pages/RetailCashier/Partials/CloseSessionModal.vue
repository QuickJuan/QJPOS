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
            <!-- Sales Summary -->
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
                            :label="`₱${denom}`"
                            v-model.number="cashDenominations[denom]"
                            type="number"
                            min="0"
                            :placeholder="`Count of ₱${denom}`"
                        />
                        <p class="text-xs text-gray-500">
                            Total: ₱{{ (parseFloat(denom) * count).toFixed(2) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Session Summary Report -->
            <div class="bg-white border rounded-lg p-4">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">
                    Session Summary Report
                </h4>
                <div class="bg-gray-50 p-4 rounded border font-mono text-sm">
                    <div class="text-center mb-4">
                        <p class="font-bold text-lg">QUICKJUAN POS</p>
                        <p class="text-xs">Session Close Report</p>
                        <p class="text-xs">{{ new Date().toLocaleDateString() }}</p>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Cashier:</span>
                            <span>{{ props.openSession?.cashier?.name || props.currentUser?.name || 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Session Start:</span>
                            <span>{{ props.openSession ? new Date(props.openSession.started_time).toLocaleDateString() + ' ' + new Date(props.openSession.started_time).toLocaleTimeString() : 'N/A' }}</span>
                        </div>

                        <!-- Sales Summary Table -->
                        <div class="mt-4">
                            <div class="flex justify-between font-bold border-b pb-1 mb-2">
                                <span>Invoice Number</span>
                                <span>Sales</span>
                            </div>
                            <div v-for="order in props.sessionSummary?.orders || []" :key="order.invoice_number" class="flex justify-between">
                                <span>{{ order.invoice_number }}</span>
                                <span>₱{{ order.amount.toFixed(2) }}</span>
                            </div>
                            <div v-if="(props.sessionSummary?.orders || []).length === 0" class="text-center text-gray-500 py-2">
                                No sales data available
                            </div>
                            <div class="border-t pt-2 mt-2 flex justify-between font-bold">
                                <span>total</span>
                                <span>₱{{ (props.sessionSummary?.total_sales || 0).toFixed(2) }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between border-t pt-2">
                            <span>Items Settled:</span>
                            <span>{{ props.sessionSummary?.items_settled || 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Guests Served:</span>
                            <span>{{ props.sessionSummary?.guests_served || 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Cash on Hand:</span>
                            <span>₱{{ totalCashCounted.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-gray-50 rounded-lg p-4 border">
                <div class="text-center">
                    <p class="text-sm text-gray-600">Total Cash Counted</p>
                    <p class="text-xl font-bold text-gray-900">
                        ₱{{ totalCashCounted.toFixed(2) }}
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
</template>

<script setup lang="ts">
import TextField from "@/Components/Form/TextField.vue";
import CashieringSession from "@/Types/CashieringSession";
import { formatMoney } from "@/Utils/FormatMoney";
import { Button, Dialog } from "primevue";
import { computed, ref } from "vue";

const props = defineProps<{
    showCloseDialog: boolean;
    openSession: CashieringSession | null;
    sessionSummary?: any;
    currentUser?: any;
}>();

const emit = defineEmits(["confirmCloseSession", "closeModal"]);

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
