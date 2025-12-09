<template>
    <Dialog
        :visible="props.visible"
        modal
        header="Settle Bill"
        :style="{ width: '500px' }"
        :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        @update:visible="handleClose"
        @show="focusAmount"
    >
        <form
            @submit.prevent="!showingSwal && handleSettleBill()"
            class="space-y-6"
        >
            <!-- Total Amount Section -->
            <div
                class="bg-gradient-to-r from-primary-50 to-primary-100 p-6 rounded-lg border border-primary-200"
            >
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Total Amount Due
                </label>
                <div class="text-4xl font-bold text-primary-600">
                    {{ formatMoney(total) }}
                </div>
            </div>

            <!-- Amount Paid Section -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Amount Tendered
                </label>
                <div class="flex gap-2">
                    <TextField
                        ref="amountInputRef"
                        id="amount_paid"
                        v-model="amountPaid"
                        type="number"
                        placeholder="0.00"
                        pattern="[0-9]*\.?[0-9]{0,2}"
                        inputmode="decimal"
                        class="flex-1"
                        input-class="text-2xl py-4"
                    />
                    <button
                        type="button"
                        @click="clearAmount"
                        class="px-4 py-3 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 font-semibold text-sm transition-colors"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Preset Amounts -->
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Quick Amount Selection (Click to Add)
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        <button
                            v-for="preset in presetAmounts"
                            :key="preset"
                            type="button"
                            @click="addAmountPaid(preset)"
                            class="py-3 px-2 rounded-lg font-semibold text-sm transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-primary-600 hover:text-white active:scale-95"
                        >
                            +{{ formatMoney(preset) }}
                        </button>
                    </div>
                </div>

                <button
                    v-if="props.total > 0"
                    type="button"
                    @click="setExactAmount(props.total)"
                    class="w-full h-12 mt-2 py-2 px-4 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 font-medium text-sm transition-colors"
                >
                    Exact Amount ({{ formatMoney(props.total) }})
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-6">
                <button
                    type="button"
                    @click="emit('update:visible', false)"
                    class="flex-1 py-3 px-4 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors font-semibold"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="amountPaid < props.total"
                    :class="[
                        'flex-1 py-3 px-4 rounded-lg font-semibold text-white transition-colors',
                        amountPaid >= props.total
                            ? 'bg-success-600 hover:bg-success-700'
                            : 'bg-gray-400 cursor-not-allowed',
                    ]"
                >
                    Settle Bill
                </button>
            </div>
        </form>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from "vue";
import { Dialog } from "primevue";
import { useToast } from "primevue";
import Swal from "sweetalert2";
import { formatMoney } from "@/Utils/FormatMoney";
import TextField from "@/Components/Form/TextField.vue";
import { usePage } from "@inertiajs/vue3";
import { useCashier } from "@/composables/useCashier";

const props = defineProps<{
    visible: boolean;
    cart: any;
    subTotal: number;
    total: number;
    tableInfo: any;
}>();

const emit = defineEmits<{
    "update:visible": [value: boolean];
    "settle-bill": [data: any];
}>();

// Get cashier composable
const { settlePayment } = useCashier();

// Get toast
const toast = useToast();

// Settle bill form data
const amountPaid = ref(0);

// Ref for amount input
const amountInputRef = ref<HTMLInputElement | null>(null);

// Track if Swal is showing to prevent form submission
const showingSwal = ref(false);

// Focus input on modal open
const focusAmount = async () => {
    await nextTick();
    const inputElement = amountInputRef.value?.$el?.querySelector(
        "input"
    ) as HTMLInputElement;
    if (inputElement) {
        inputElement.focus();
    }
};

// Preset amounts for quick selection
const presetAmounts = [100, 200, 500, 1000];

// Computed change amount
const changeAmount = computed(() => {
    return Math.max(0, amountPaid.value - props.totalAmount);
});

const page = usePage();

// Receipt data
const receiptData = ref({
    receiptNumber: null as string | null,
    date: new Date().toISOString(),
    tableNumber: "",
    cashierName: "",
    orderType: "",
    orderItems: [] as any[],
    subtotal: 0,
    taxAmount: 0,
    totalAmount: 0,
    paymentInfo: null as any,
    taxInfo: null as any,
});

// Add amount to current amount paid (accumulate)
const addAmountPaid = (amount: number) => {
    amountPaid.value = parseFloat((amountPaid.value + amount).toFixed(2));
};

// Set exact amount
const setExactAmount = (amount: number) => {
    amountPaid.value = Number(parseFloat(amount.toFixed(2)));
};

// Clear amount
const clearAmount = () => {
    amountPaid.value = 0;
};

// Handle settle bill form submission
const handleSettleBill = async () => {
    try {
        // Validate required data
        if (!props.cart?.id) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Cart ID is missing. Please try again.",
                life: 3000,
            });
            return;
        }

        if (amountPaid.value <= 0) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Please enter a valid amount",
                life: 3000,
            });
            return;
        }

        const response = await settlePayment({
            cart_id: props.cart.id,
            amount_paid: amountPaid.value,
            total_amount: props.total,
        });

        if (response.success) {
            toast.add({
                severity: "success",
                summary: "Success",
                detail: response.message || "Bill settled successfully",
                life: 3000,
            });

            console.log("Settlement response:", response);

            emit("update:visible", false);
            emit("settle-bill", response);

            // const responseData = response.data;

            // // Populate receipt data
            // receiptData.value = {
            //     receiptNumber: responseData.invoice_no,
            //     date: responseData.order_date,
            //     tableNumber: responseData.table_number,
            //     cashierName: responseData.cashier.name,
            //     orderItems: responseData.order_items,
            //     subtotal: responseData.totals.subtotal,
            //     taxAmount: responseData.totals.tax_amount,
            //     totalAmount: responseData.totals.total_amount,
            //     paymentInfo: {
            //         amountPaid: responseData.payment.amount_paid,
            //         change: responseData.payment.change,
            //     },
            //     taxInfo: null,
            // };

            // Show change amount in Swal

            // Swal.fire({
            //     title: formatMoney(
            //         amountPaid.value - responseData.totals.total_due
            //     ),
            //     text: "Change to return to customer",
            //     icon: "success",
            //     confirmButtonText: "OK",
            //     didOpen: () => {
            //         // Focus the OK button when Swal opens
            //         const confirmButton = document.querySelector(
            //             ".swal2-confirm"
            //         ) as HTMLButtonElement;
            //         if (confirmButton) {
            //             confirmButton.focus();
            //         }
            //     },
            // }).then(() => {
            //     amountPaid.value = 0;
            //     // Emit settle bill event to show receipt modal
            //     emit("settle-bill", { receipt_data: receiptData.value });
            // });
        } else {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: response.message || "Failed to settle bill",
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
    }
};

const handleClose = () => {
    emit("update:visible", false);
    amountPaid.value = 0;
};
</script>
