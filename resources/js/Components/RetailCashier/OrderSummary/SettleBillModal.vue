<template>
    <Dialog
        :visible="props.visible"
        modal
        header="Settle Bill"
        :style="{ width: '25rem' }"
        @update:visible="handleClose"
    >
        <form @submit.prevent="handleSettleBill" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Total Amount
                </label>
                <div class="text-2xl font-bold text-gray-900">
                    {{ formatMoney(totalAmount) }}
                </div>
            </div>

            <div>
                <TextField
                    label="Amount Paid"
                    id="amount_paid"
                    v-model="amountPaid"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    placeholder="Enter amount paid"
                />
            </div>

            <div v-if="amountPaid > 0" class="bg-gray-50 p-3 rounded-md">
                <div class="flex justify-between text-sm">
                    <span>Change:</span>
                    <span class="font-semibold">
                        {{ formatMoney(Math.max(0, amountPaid - totalAmount)) }}
                    </span>
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button
                    type="button"
                    @click="emit('update:visible', false)"
                    class="flex-1 py-2 px-4 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="amountPaid < totalAmount"
                    class="flex-1 py-2 px-4 bg-success-600 text-white rounded-md hover:bg-success-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                >
                    Settle Bill
                </button>
            </div>
        </form>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { Dialog } from "primevue";
import { formatMoney } from "@/Utils/FormatMoney";
import TextField from "@/Components/Form/TextField.vue";
import { usePage } from "@inertiajs/vue3";

const props = defineProps<{
    visible: boolean;
    cart: any;
    orderItems: any[];
    selectedOrderType: string;
    totalAmount: number;
    appliedDiscount: any;
    subTotal: number;
    total: number;
    lessTaxTotal: number;
    lessDiscountTotal: number;
    tableInfo: any;
    billFooter: any;
}>();

const emit = defineEmits<{
    settleBill: [data: any];
    "update:visible": [value: boolean];
}>();

// Use applied discount from props
const appliedDiscount = computed(() => props.appliedDiscount);

// Settle bill form data
const amountPaid = ref(0);

const page = usePage();

// Receipt data
const receiptData = ref({
    receiptNumber: "001234",
    date: new Date().toISOString(),
    tableNumber: "",
    cashierName: "",
    orderType: "",
    orderItems: [] as any[],
    subtotal: 0,
    lessTax: 0,
    lessDiscount: 0,
    discountName: null as string | null,
    discountType: null as string | null,
    totalAmount: 0,
    paymentInfo: null as any,
});

// Handle settle bill form submission
const handleSettleBill = () => {
    receiptData.value = {
        receiptNumber: `RCP-${Date.now()}`,
        date: new Date().toISOString(),
        tableNumber: props.tableInfo,
        cashierName: page.props.auth?.user?.name ?? "",
        orderType: props.selectedOrderType,
        orderItems: props.orderItems,
        subtotal: parseFloat(props.subTotal.toFixed(2)),
        lessTax: parseFloat(props.lessTaxTotal.toFixed(2)),
        lessDiscount: parseFloat(props.lessDiscountTotal.toFixed(2)),
        discountName: appliedDiscount.value?.discountName || null,
        discountType: appliedDiscount.value?.discountType || null,
        totalAmount: parseFloat(props.total.toFixed(2)),
        paymentInfo: {
            amount_paid: parseFloat(amountPaid.value.toString()) || 0,
            change:
                (parseFloat(amountPaid.value.toString()) || 0) -
                (parseFloat(props.totalAmount.toString()) || 0),
            method: "Cash",
        },
    };

    emit("settleBill", {
        cart_id: props.cart.id,
        amount_paid: amountPaid.value,
        total_amount: props.totalAmount,
        receipt_data: receiptData.value,
    });

    emit("update:visible", false);
    amountPaid.value = 0;
};

const handleClose = () => {
    emit("update:visible", false);
    amountPaid.value = 0;
};
</script>
