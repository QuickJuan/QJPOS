<template>
    <TransactionsLayout>
        <template #header>
            <div>
                <SearchAndFIlter
                    :search="search"
                    :dateRange="dateRange"
                    :status="filters.status"
                    :cashier_id="filters.cashier_id"
                    :statusOptions="statusOptions"
                    :cashierDropdownOptions="cashierDropdownOptions"
                    @search="(value: string) => { search = value }"
                    @dateRange="handleDateRangeChange"
                    @status="(value: string) => { filters.status = value }"
                    @cashier_id="(value: string) => { filters.cashier_id = value }"
                />
            </div>
        </template>
        <div class="flex flex-col h-full px-4 md:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row gap-4 md:gap-6 h-full">
                <!-- Sidebar -->
                <div
                    class="w-full md:w-3/5 2xl:w-1/4 h-auto md:h-full flex flex-col min-h-[300px] md:min-h-0"
                >
                    <Transactions
                        :orders="orders"
                        :activeOrder="activeOrder"
                        @selectOrder="selectOrder"
                        @goToPage="goToPage"
                    />
                </div>

                <!-- Detail Pane -->
                <div class="flex flex-col w-full h-auto md:h-full">
                    <div v-if="activeOrder" class="flex flex-col h-full">
                        <div
                            class="px-4 py-5 md:px-6 md:py-6 border-b border-gray-100 bg-white flex-shrink-0"
                        >
                            <div
                                class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between"
                            >
                                <div>
                                    <p class="text-sm text-gray-500">
                                        {{
                                            activeOrder.customer?.name ||
                                            activeOrder.table_number ||
                                            "Walk-in Customer"
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="flex flex-wrap items-center gap-3 justify-end"
                                >
                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-xs font-semibold',
                                            getStatusClass(activeOrder.status),
                                        ]"
                                    >
                                        {{ getStatusLabel(activeOrder.status) }}
                                    </span>

                                    <!-- Desktop action buttons -->
                                    <div class="hidden lg:flex flex-wrap gap-2">
                                        <Button
                                            label="Print"
                                            icon="pi pi-print"
                                            outlined
                                            :class="subtleActionButtonClass"
                                            @click="handleThermalPrint"
                                        />
                                        <Button
                                            label="Send to Email"
                                            icon="pi pi-envelope"
                                            outlined
                                            :class="subtleActionButtonClass"
                                            @click="
                                                sendReceiptEmail(activeOrder)
                                            "
                                        />
                                        <Button
                                            v-if="
                                                activeOrder.status === 'settled'
                                            "
                                            label="Refund"
                                            icon="pi pi-undo"
                                            outlined
                                            :class="cautionActionButtonClass"
                                            @click="
                                                openRefundModal(activeOrder)
                                            "
                                        />
                                    </div>

                                    <!-- Mobile hamburger menu -->
                                    <div class="lg:hidden relative z-50">
                                        <Button
                                            icon="pi pi-ellipsis-v"
                                            outlined
                                            :class="subtleActionButtonClass"
                                            @click="toggleActionMenu"
                                        />
                                        <div
                                            v-if="showActionMenu"
                                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-[100]"
                                        >
                                            class="fixed inset-0 z-[90]
                                            lg:hidden" @click="toggleActionMenu"
                                            >
                                        </div>
                                        <!-- Mobile menu -->
                                        <div
                                            v-if="showActionMenu"
                                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-[100]"
                                            data-menu-container
                                        >
                                            <button
                                                class="w-full text-left px-4 py-3 hover:bg-blue-50 flex items-center gap-3 text-sm text-blue-700 transition-colors duration-150"
                                                @click="
                                                    () => {
                                                        handleThermalPrint();
                                                        toggleActionMenu();
                                                    }
                                                "
                                            >
                                                <i class="pi pi-print"></i>
                                                Print
                                            </button>
                                            <button
                                                class="w-full text-left px-4 py-3 hover:bg-gray-50 flex items-center gap-3 text-sm border-t border-gray-100 transition-colors duration-150"
                                                @click="
                                                    () => {
                                                        sendReceiptEmail(
                                                            activeOrder
                                                        );
                                                        toggleActionMenu();
                                                    }
                                                "
                                            >
                                                <i class="pi pi-envelope"></i>
                                                Send to Email
                                            </button>
                                            <button
                                                v-if="
                                                    activeOrder.status ===
                                                    'settled'
                                                "
                                                class="w-full text-left px-4 py-3 hover:bg-amber-50 flex items-center gap-3 text-sm border-t border-gray-100 text-amber-700"
                                                @click="
                                                    () => {
                                                        openRefundModal(
                                                            activeOrder
                                                        );
                                                        toggleActionMenu();
                                                    }
                                                "
                                            >
                                                <i class="pi pi-undo"></i>
                                                Refund
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="px-4 py-5 md:px-6 md:py-6 space-y-8 flex-1 overflow-y-auto"
                        >
                            <div class="flex flex-col lg:flex-row gap-4">
                                <Receipt
                                    :receipt-id="activeOrder.id.toString()"
                                    :receiptData="activeOrder"
                                    :receipt-footer="props.receiptFooter"
                                    :general-settings="props.generalSettings"
                                />
                                <div class="flex flex-col w-full lg:w-auto">
                                    <div
                                        v-if="refundMeta"
                                        class="rounded-2xl border border-red-100 bg-red-50/60 p-6 space-y-3"
                                    >
                                        <p
                                            class="text-xs uppercase tracking-wide text-red-500"
                                        >
                                            Refund Details
                                        </p>
                                        <p class="text-sm text-gray-700">
                                            Requested by
                                            <span
                                                class="font-semibold text-gray-900"
                                            >
                                                {{ refundMeta.requested_by }}
                                            </span>
                                            &middot; Approved by
                                            <span
                                                class="font-semibold text-gray-900"
                                            >
                                                {{ refundMeta.supervisor }}
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Refunded
                                            {{
                                                formatDetailedDate(
                                                    refundMeta.refunded_at
                                                )
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600 italic">
                                            "{{ refundMeta.notes }}"
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="flex flex-col items-center justify-center h-full py-16 text-center text-gray-400"
                    >
                        <p class="text-lg font-semibold">
                            No transaction selected
                        </p>
                        <p class="text-sm mt-2">
                            Use the list on the left to pick a receipt to
                            review.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <RefundDialog
            v-model:visible="showRefundModal"
            v-model:notes="refundForm.notes"
            v-model:supervisor-name="refundForm.supervisor_name"
            :loading="refundLoading"
            @submit="submitRefund"
            @closed="handleRefundDialogClosed"
        />

        <!-- Thermal Printer Dialog -->
        <Dialog
            v-model:visible="showThermalPrinter"
            modal
            header="Thermal Printer"
            :style="{ width: '28rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        >
            <ThermalPrinterManager
                :receipt-data="thermalReceiptData"
                :auto-print="true"
                @connected="handlePrinterConnected"
                @printed="handlePrinterPrinted"
                @open-settings="handleOpenPrinterSettings"
            />
        </Dialog>
    </TransactionsLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, reactive, onMounted, onUnmounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import axios from "axios";
import TransactionsLayout from "@/Layouts/TransactionsLayout.vue";
import PageProps from "@/Types/PageProps";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Order from "@/Types/Order/Order";
import SearchAndFIlter from "./Partials/SearchAndFIlter.vue";
import Transactions from "./Partials/Transactions.vue";
import RefundDialog from "./Partials/RefundDialog.vue";
import Receipt from "../Receipt.vue";
import ThermalPrinterManager from "@/Components/ThermalPrinter/ThermalPrinterManager.vue";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import debounce from "lodash/debounce";
import filter from "lodash/filter";

const props = defineProps<{
    orders: any;
    filters: {
        search: string;
        date_from: string;
        date_to: string;
        status: string;
        cashier_id: string;
    };
    receiptFooter: any;
    generalSettings: {
        company_name: string;
        company_address: string;
        company_phone: string;
        company_logo: string;
    };
    active_branch: any;
}>();

const page = usePage<PageProps>();
const cashierOptions = computed(() => page.props?.cashiers || []);
const cashierDropdownOptions = computed(() =>
    cashierOptions.value.map((cashier: { id: number; name: string }) => ({
        label: cashier.name,
        value: cashier.id,
    }))
);
const statusOptions = [
    { label: "Settled", value: "settled" },
    { label: "Refund", value: "refund" },
    { label: "Credit", value: "credit" },
];

const filters = reactive({
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
    status: props.filters.status || "",
    cashier_id: props.filters.cashier_id || "",
});

const search = ref(props.filters.search || "");

// Compute dateRange from filters
const dateRange = computed<[Date | null, Date | null] | null>(() => {
    if (!filters.date_from && !filters.date_to) return null;
    return [
        filters.date_from ? new Date(filters.date_from) : null,
        filters.date_to ? new Date(filters.date_to) : null,
    ];
});

const handleDateRangeChange = ([start, end]: [
    string | null,
    string | null
]) => {
    filters.date_from = start || "";
    filters.date_to = end || "";
};

const showRefundModal = ref(false);
const activeOrder = ref<Order | null>(null);
const refundOrder = ref<Order | null>(null);
const refundForm = ref({
    notes: "",
    supervisor_name: "",
});
const refundLoading = ref(false);
const refundMeta = computed(() => activeOrder.value?.meta?.refund || null);
const showActionMenu = ref(false);
const showThermalPrinter = ref(false);

// Thermal printer receipt data
const thermalReceiptData = computed(() => {
    if (!activeOrder.value) return null;

    // Format date and time from order_date
    const orderDate = new Date(
        activeOrder.value.order_date || activeOrder.value.created_at
    );
    const dateStr = orderDate.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
    const timeStr = orderDate.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });

    return {
        storeName: page.props.company_info.company_name,
        branch: activeOrder.value.branch,
        orderNumber: activeOrder.value.invoice_no,
        cashier: activeOrder.value.cashier?.name,
        date: dateStr,
        time: timeStr,
        tableNumber: activeOrder.value.table_number,
        items: activeOrder.value.order_items || [],
        totals: activeOrder.value.totals || 0,
        payment: activeOrder.value.payment,
        receiptFooter: activeOrder.value?.branch?.receipt_footer,
        footerMessage: "Generated by Quick Juan POS System",
    };
});

const toggleActionMenu = () => {
    showActionMenu.value = !showActionMenu.value;
};

// Close menu when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (
        !target.closest(".relative") &&
        !target.closest("[data-menu-container]")
    ) {
        showActionMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});

const sendReceiptEmail = (order: Order) => {
    alert(`Sending receipt #${order.id} to customer's email...`);
};

const formatDetailedDate = (dateString: string) => {
    return new Date(dateString).toLocaleString(undefined, {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "numeric",
        minute: "2-digit",
    });
};

const openRefundModal = (order: Order) => {
    refundOrder.value = order;
    refundForm.value = { notes: "", supervisor_name: "" };
    showRefundModal.value = true;
};

const closeRefundModal = () => {
    showRefundModal.value = false;
    handleRefundDialogClosed();
};

const submitRefund = async () => {
    if (!refundOrder.value) return;

    refundLoading.value = true;
    try {
        await axios.post(
            route("transactions.api.orders.refund", {
                order: refundOrder.value.id,
            }),
            refundForm.value
        );
        closeRefundModal();
    } catch (error) {
        console.error("Error refunding order:", error);
    } finally {
        refundLoading.value = false;
    }
};

const goToPage = (url: string | null) => {
    if (!url) return;
    const urlObj = new URL(url);
    const page = urlObj.searchParams.get("page");
    if (page) {
        const data: any = { page: parseInt(page) };
        if (filters.date_from) data.date_from = filters.date_from;
        if (filters.date_to) data.date_to = filters.date_to;
        if (filters.status) data.status = filters.status;
        if (filters.cashier_id) data.cashier_id = filters.cashier_id;
        if (search.value) data.search = search.value;

        router.get(route("transactions.index"), data, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const selectOrder = (order: any) => {
    activeOrder.value = order;
};

const handleRefundDialogClosed = () => {
    refundOrder.value = null;
};

// Thermal print handling
const handleThermalPrint = async () => {
    if (!activeOrder.value) return;

    // Load printer config first
    try {
        const printerConfig = await thermalPrinter.loadPrinterConfig("receipt");

        if (!printerConfig) {
            // No printer configured - go to config page
            console.log("No printer configuration found, redirecting to setup");
            router.get("/printer-config");
            return;
        }

        // Check if already connected
        const isConnected = thermalPrinter.isConnected();

        if (isConnected) {
            // Already connected - print immediately
            console.log("Printer already connected, printing...");
            try {
                await thermalPrinter.printReceipt(thermalReceiptData.value);
                console.log("✅ Receipt printed successfully");
            } catch (error) {
                console.error("Print failed:", error);
                showThermalPrinter.value = true;
            }
        } else {
            // Not connected - try to connect first
            console.log("Printer not connected, attempting to connect...");
            try {
                const connected = await thermalPrinter.connectToPrinter(
                    printerConfig
                );

                if (connected) {
                    // Successfully connected - now print
                    console.log("✅ Connected successfully, printing...");
                    await thermalPrinter.printReceipt(thermalReceiptData.value);
                    console.log("✅ Receipt printed successfully");
                } else {
                    // Connection failed - show modal for manual retry
                    console.log("❌ Connection failed, showing modal");
                    showThermalPrinter.value = true;
                }
            } catch (error) {
                console.error("Connection/print failed:", error);

                // If it's a user cancellation, don't show modal
                if (
                    error.message.includes("User cancelled") ||
                    error.name === "NotFoundError"
                ) {
                    console.log("User cancelled connection");
                    return;
                }

                // Other errors - show modal for manual retry
                showThermalPrinter.value = true;
            }
        }
    } catch (error) {
        console.error("Failed to load printer config:", error);
        // If config loading fails completely, go to config page
        router.get("/printer-config");
    }
};

const handlePrinterConnected = (connected: boolean) => {
    console.log("Printer connection status:", connected);
};

const handlePrinterPrinted = (success: boolean) => {
    if (success) {
        showThermalPrinter.value = false;
    }
};

const handleOpenPrinterSettings = () => {
    // Close thermal printer modal and open printer configuration
    showThermalPrinter.value = false;
    // Navigate to printer config page
    router.get("/printer-config");
};

const subtleActionButtonClass =
    "!rounded-full !border !border-primary/20 !bg-primary/5 !text-primary !px-4 !py-2 !text-sm !font-semibold hover:!bg-primary/10";
const cautionActionButtonClass =
    "!rounded-full !border !border-amber-300 !bg-amber-50 !text-amber-700 !px-4 !py-2 !text-sm !font-semibold hover:!bg-amber-100";

const getStatusClass = (status: string) => {
    switch (status) {
        case "settled":
            return "bg-green-100 text-green-800";
        case "refund":
            return "bg-red-100 text-red-800";
        case "credit":
            return "bg-yellow-100 text-yellow-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case "settled":
            return "Settled";
        case "refund":
            return "Refund";
        case "credit":
            return "Credit";
        default:
            return status;
    }
};

watch(
    search,
    debounce((value: any) => {
        const routeParams = {
            search: value,
        };

        router.visit("/transactions", {
            data: routeParams,
            replace: true,
            only: ["orders"],
            preserveState: true,
            preserveScroll: true,
        });
    }, 250)
);

watch(
    () => filters,
    debounce(() => {
        const data: any = {};
        if (filters.date_from) data.date_from = filters.date_from;
        if (filters.date_to) data.date_to = filters.date_to;
        if (filters.status) data.status = filters.status;
        if (filters.cashier_id) data.cashier_id = filters.cashier_id;
        if (search.value) data.search = search.value;

        router.get(route("transactions.index"), data, {
            preserveScroll: true,
            preserveState: true,
        });
    }, 250),
    { deep: true }
);
</script>
