<template>
    <Dialog
        :visible="props.visible"
        modal
        header="Settle Bill"
        :style="{ width: '500px' }"
        :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        @update:visible="handleClose"
    >
        <form @submit.prevent="handleSettleBill" class="space-y-6">
            <!-- Total Amount Section -->
            <div
                class="bg-gradient-to-r from-primary-50 to-primary-100 p-6 rounded-lg border border-primary-200"
            >
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Total Amount Due
                </label>
                <div class="text-4xl font-bold text-primary-600">
                    {{ formatMoney(totalAmount) }}
                </div>
            </div>

            <!-- Amount Paid Section -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Amount Paid
                </label>
                <div class="flex gap-2">
                    <input
                        id="amount_paid"
                        v-model.number="amountPaid"
                        type="text"
                        inputmode="decimal"
                        required
                        placeholder="0.00"
                        @input="handleAmountInput"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent text-lg"
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
                <button
                    v-if="totalAmount > 0"
                    type="button"
                    @click="setExactAmount(totalAmount)"
                    class="w-full mt-2 py-2 px-4 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 font-medium text-sm transition-colors"
                >
                    Exact Amount ({{ formatMoney(totalAmount) }})
                </button>
            </div>

            <!-- Change Section -->
            <div
                v-if="amountPaid > 0"
                class="bg-green-50 p-4 rounded-lg border border-green-200"
            >
                <div class="space-y-2">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Amount Paid:</span>
                        <span class="font-semibold">{{
                            formatMoney(amountPaid)
                        }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Total Amount:</span>
                        <span class="font-semibold">{{
                            formatMoney(totalAmount)
                        }}</span>
                    </div>
                    <div
                        class="border-t border-green-200 pt-2 flex justify-between"
                    >
                        <span class="font-semibold text-green-700"
                            >Change:</span
                        >
                        <span
                            :class="[
                                'font-bold text-lg',
                                changeAmount >= 0
                                    ? 'text-green-600'
                                    : 'text-red-600',
                            ]"
                        >
                            {{ formatMoney(changeAmount) }}
                        </span>
                    </div>
                </div>
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
                    :disabled="amountPaid < totalAmount"
                    :class="[
                        'flex-1 py-3 px-4 rounded-lg font-semibold text-white transition-colors',
                        amountPaid >= totalAmount
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
    receiptNumber: string;
}>();

const emit = defineEmits<{
    settleBill: [data: any];
    "update:visible": [value: boolean];
}>();

// Use applied discount from props
const appliedDiscount = computed(() => props.appliedDiscount);

// Settle bill form data
const amountPaid = ref(0);

// Preset amounts for quick selection
const presetAmounts = [100, 200, 500, 1000];

// Computed change amount
const changeAmount = computed(() => {
    return Math.max(0, amountPaid.value - props.totalAmount);
});

const page = usePage();

// Receipt data
const receiptData = ref({
    receiptNumber: props.receiptNumber,
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
    taxInfo: null as any,
});

// Add amount to current amount paid (accumulate)
const addAmountPaid = (amount: number) => {
    amountPaid.value = parseFloat((amountPaid.value + amount).toFixed(2));
};

// Set exact amount
const setExactAmount = (amount: number) => {
    amountPaid.value = parseFloat(amount.toFixed(2));
};

// Handle manual input
const handleAmountInput = (event: Event) => {
    const input = event.target as HTMLInputElement;
    let value = input.value;

    // Remove any non-numeric characters except decimal point
    value = value.replace(/[^0-9.]/g, "");

    // Prevent multiple decimal points
    const parts = value.split(".");
    if (parts.length > 2) {
        value = parts[0] + "." + parts.slice(1).join("");
    }

    // Update the input
    input.value = value;

    // Update the model
    amountPaid.value = value === "" ? 0 : parseFloat(value);
};

// Clear amount
const clearAmount = () => {
    amountPaid.value = 0;
};

// Handle settle bill form submission
const handleSettleBill = () => {
    const taxInfo = computed(() => {
        return props.orderItems.reduce(
            (acc: any, item: any) => {
                acc.vatableSales += Number(item.vatable_sales) || 0;
                acc.nonVatableSales += Number(item.non_vat_sales) || 0;
                acc.vatExemptSales += Number(item.vat_exempt_sales) || 0;
                acc.vatAmount += Number(item.vat_amount) || 0;
                return acc;
            },
            {
                vatableSales: 0,
                nonVatableSales: 0,
                vatExemptSales: 0,
                vatAmount: 0,
            }
        );
    });
    receiptData.value = {
        receiptNumber: props.receiptNumber,
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
        taxInfo: taxInfo.value,
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
