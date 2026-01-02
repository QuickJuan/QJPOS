<template>
    <transition name="slide">
        <div
            v-if="show"
            class="h-full w-[400px] max-w-full bg-white shadow-xl border-r flex flex-col min-h-0"
            style="pointer-events: auto"
            role="complementary"
            aria-label="Table Sidebar"
        >
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-lg font-bold" id="sidebar-title">
                    Table Status: {{ tableData.name }}
                </h2>
                <button
                    v-if="!showOrderPanel"
                    @click="$emit('close')"
                    class="text-gray-500 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded"
                    aria-label="Close sidebar"
                    title="Close sidebar"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
            <form
                @submit.prevent="onSave"
                class="flex flex-col flex-grow min-h-0 p-4 gap-2"
                aria-labelledby="sidebar-title"
            >
                <!-- Replace status, customer name, pax, and save section with TableCustomer component -->
                <TableCustomer :value="localTable" @save="onSave" />

                <!-- Remove Take Order from previous location -->
                <div class="flex flex-wrap gap-2">
                    <button
                        v-if="localTable.status === 'vacant'"
                        type="button"
                        @click="onAction('occupy')"
                        class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600 focus:ring-2 focus:ring-indigo-500"
                        aria-label="Mark as occupied"
                    >
                        Occupy
                    </button>
                    <button
                        v-if="localTable.status === 'vacant'"
                        type="button"
                        @click="onAction('reserve')"
                        class="px-3 py-1 rounded bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-2 focus:ring-indigo-500"
                        aria-label="Reserve table"
                    >
                        Reserve
                    </button>
                    <button
                        v-if="localTable.status === 'reserved'"
                        type="button"
                        @click="onAction('occupy')"
                        class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600 focus:ring-2 focus:ring-indigo-500"
                        aria-label="Mark as occupied"
                    >
                        Occupy
                    </button>
                    <button
                        v-if="localTable.status === 'reserved'"
                        type="button"
                        @click="onAction('vacant')"
                        class="px-3 py-1 rounded bg-green-500 text-white hover:bg-green-600 focus:ring-2 focus:ring-indigo-500"
                        aria-label="Mark as vacant"
                    >
                        Vacant
                    </button>
                </div>

                <!-- TableActionButtons is always visible at the bottom, not sticky, no extra margin -->
                <div>
                    <TableActionButtons
                        :showOrderPanel="showOrderPanel"
                        :orders="[]"
                        :status="localTable.status"
                        :runningAmountDue="runningAmountDue"
                        @toggle-order-panel="$emit('toggle-order-panel')"
                        @checkout="$emit('checkout')"
                        @print-bill="$emit('print-bill')"
                        @cancel-table="$emit('cancel-table')"
                        @merge-table="$emit('merge-table')"
                    />
                </div>
            </form>
            <!-- Printable receipt (hidden, for print only) -->
            <PrintBillReceipt
                v-if="showPrintReceipt"
                ref="receiptRef"
                :tableName="tableData.name"
                :customerName="localTable.customer"
                :orders="[]"
                :visible="showPrintReceipt"
            />
        </div>
    </transition>

    <!-- Receipt Preview Modal -->
    <template v-if="showPrintReceipt">
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
        >
            <div
                class="bg-white rounded shadow-lg p-4 w-auto max-w-full border border-gray-200 relative"
            >
                <button
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 bg-gray-100 rounded-full p-1 focus:outline-none"
                    @click="emit('close-receipt')"
                    aria-label="Close receipt preview"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
                <div ref="receiptRef">
                    <PrintBillReceipt
                        :tableName="tableData.name"
                        :customerName="localTable.customer"
                        :orders="[]"
                        :visible="showPrintReceipt"
                    />
                </div>
                <div class="flex justify-end mt-4">
                    <button
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500"
                        @click="emit('close-receipt')"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Inline PrintBillReceipt below TableActionButtons -->
    <div v-if="showPrintReceipt && false" class="mt-4">
        <!-- Disabled: Prevent duplicate rendering and errors -->
        <!--
        <PrintBillReceipt
            :tableName="tableData.name"
            :customerName="localTable.customer"
            :orders="orders"
            :visible="showPrintReceipt"
        />
        -->
    </div>

    <template v-if="showCustomerModal">
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
        >
            <div
                class="bg-white rounded shadow-lg p-4 w-full max-w-xs border border-gray-200 relative"
            >
                <h2 class="text-lg font-bold mb-2">Enter Customer Details</h2>
                <form @submit.prevent="() => handleCustomerSave(localTable)">
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1"
                            >Customer Name</label
                        >
                        <input
                            v-model="localTable.customer"
                            type="text"
                            class="w-full border rounded px-2 py-1"
                            required
                            autofocus
                        />
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1"
                            >Pax</label
                        >
                        <input
                            v-model.number="localTable.pax"
                            type="number"
                            min="1"
                            max="99"
                            class="w-full border rounded px-2 py-1"
                            required
                        />
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button
                            type="button"
                            @click="handleCustomerCancel"
                            class="px-3 py-1 rounded bg-gray-300 hover:bg-gray-400"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700"
                        >
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</template>

<script setup lang="ts">
import { ref, watch, defineProps, defineEmits, nextTick } from "vue";
import TableCustomer from "./TableCustomer.vue";
import TableActionButtons from "./TableActionButtons.vue";
interface ProductOrder {
    id: number;
    name: string;
    price: number;
}
const props = defineProps({
    show: Boolean,
    tableData: {
        type: Object,
        required: true,
    },
    showOrderPanel: {
        type: Boolean,
        default: false,
    },
    showPrintReceipt: {
        type: Boolean,
        default: false,
    },
    runningAmountDue: {
        type: Number,
        default: 0,
    },
});
const emit = defineEmits([
    "close",
    "save",
    "action",
    "toggle-order-panel",
    "checkout",
    "print-bill",
    "cancel-table",
    "merge-table",
    "close-receipt",
]);

const localTable = ref({
    status: "vacant",
    customer: "",
    name: "",
    pax: 1,
});
const showPaxButtons = ref(false);
const customerNameError = ref(false);
const showCustomerModal = ref(false);

watch(
    () => props.tableData,
    (val) => {
        localTable.value.status = val.status || "vacant";
        localTable.value.customer = val.customer || "";
        localTable.value.name = val.name || "";
    },
    { immediate: true, deep: true }
);

function validateCustomerName() {
    customerNameError.value = !localTable.value.customer.trim();
}
function incrementPax() {
    localTable.value.pax = Math.min((localTable.value.pax || 1) + 1, 99);
}
function decrementPax() {
    localTable.value.pax = Math.max((localTable.value.pax || 1) - 1, 1);
}
function setPax(val: number) {
    localTable.value.pax = val;
    showPaxButtons.value = false;
}
function hidePaxButtons(e: FocusEvent) {
    setTimeout(() => {
        showPaxButtons.value = false;
    }, 150);
}

const onSave = () => {
    validateCustomerName();
    if (customerNameError.value) return;
    emit("save", { ...localTable.value });
};

const onAction = (action: string) => {
    if (action === "occupy") {
        // Show customer modal to input name and pax before occupying
        showCustomerModal.value = true;
        return;
    }
    validateCustomerName();
    if (customerNameError.value) return;
    emit("action", action, { ...localTable.value });
};

function handleCustomerSave(data: any) {
    // Save customer info and proceed to occupy
    localTable.value.customer = data.customer;
    localTable.value.pax = data.pax;
    showCustomerModal.value = false;
    validateCustomerName();
    if (!customerNameError.value) {
        emit("action", "occupy", { ...localTable.value });
    }
}

function handleCustomerCancel() {
    showCustomerModal.value = false;
}

const onCheckout = () => {
    const total = 0; // No orders in simplified version
    emit("checkout", total);
};

const receiptRef = ref<any>(null);

const printReceipt = () => {
    const printContents = receiptRef.value?.innerHTML;
    if (printContents) {
        const printWindow = window.open("", "", "width=300,height=600");
        if (printWindow) {
            printWindow.document.write(
                `<!DOCTYPE html><html><head><title>Receipt</title><style>
        body { font-family: monospace; font-size: 12px; margin: 0; padding: 0; background: #fff; color: #000; }
        .receipt-printable { width: 220px; margin: 0 auto; }
        hr { border: none; border-top: 1px dashed #000; margin: 4px 0; }
        @media print { body { margin: 0; } .receipt-printable { width: 220px; } }
      </style></head><body>` +
                    printContents +
                    `</body></html>`
            );
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            setTimeout(() => {
                printWindow.close();
            }, 500);
        }
    }
};

const printBill = async () => {
    emit("print-bill");
};

function getBaseItemTotal(item: any) {
    const price =
        typeof item.price === "string" ? parseFloat(item.price) : item.price;
    const qty = item.qty || 1;
    return (price * qty).toFixed(2);
}
function getItemPrice(item: any) {
    return (
        typeof item.price === "string" ? parseFloat(item.price) : item.price
    ).toFixed(2);
}
function getOptionTotal(opt: any, qty: number) {
    const price =
        typeof opt.price === "string" ? parseFloat(opt.price) : opt.price;
    return (price * qty).toFixed(2);
}

// Add handler for status change
function onStatusChange() {
    // If status is changed via dropdown, treat as an action
    emit("action", localTable.value.status, { ...localTable.value });
}
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}
.slide-enter,
.slide-leave-to {
    transform: translateX(-100%);
}

:global(.product-order-panel) {
    margin-left: 20rem !important; /* 80px * 4 = 320px (w-80) */
}

button:focus {
    outline: 2px solid #6366f1; /* indigo-500 */
    outline-offset: 2px;
}

.receipt-printable {
    font-family: monospace;
    font-size: 12px;
    width: 220px;
    color: #000;
    background: #fff;
    margin: 0 auto;
}
@media print {
    body {
        margin: 0 !important;
    }
    .receipt-printable {
        width: 220px !important;
    }
    .print\:hidden {
        display: none !important;
    }
    .print\:block {
        display: block !important;
    }
}
</style>
