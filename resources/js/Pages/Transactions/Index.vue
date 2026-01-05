<template>
    <CashieringLayout :current-user="page.props.currentUser">
        <div class="flex flex-col h-full overflow-hidden">
            <!-- Filters Section -->
            <div
                class="flex-shrink-0 px-4 md:px-6 lg:px-8 py-4 bg-white border-b border-neutral-200"
            >
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
            <div class="flex flex-col flex-1 px-4 md:px-6 lg:px-8 py-4">
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
                    <div class="flex flex-col w-full h-[700px] overflow-auto">
                        <div v-if="activeOrder" class="flex flex-col h-full">
                            <div
                                class="px-4 py-5 md:px-6 md:py-6 border-b border-neutral-100 bg-white flex-shrink-0"
                            >
                                <div
                                    class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between"
                                >
                                    <div>
                                        <p class="text-sm text-neutral-500">
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
                                                getStatusClass(
                                                    activeOrder.status
                                                ),
                                            ]"
                                        >
                                            {{
                                                getStatusLabel(
                                                    activeOrder.status
                                                )
                                            }}
                                        </span>

                                        <!-- Desktop action buttons -->
                                        <div
                                            class="hidden lg:flex flex-wrap gap-2"
                                        >
                                            <Button
                                                label="Print"
                                                icon="pi pi-print"
                                                outlined
                                                :class="subtleActionButtonClass"
                                                @click="handleThermalPrint"
                                            />
                                            <Button
                                                label="Print Receipt Slip"
                                                icon="pi pi-receipt"
                                                outlined
                                                :class="subtleActionButtonClass"
                                                @click="handlePrintReceiptSlips"
                                            />
                                            <Button
                                                label="Send to Email"
                                                icon="pi pi-envelope"
                                                outlined
                                                :class="subtleActionButtonClass"
                                                @click="
                                                    sendReceiptEmail(
                                                        activeOrder
                                                    )
                                                "
                                            />
                                            <Button
                                                v-if="
                                                    activeOrder.status ===
                                                    'settled'
                                                "
                                                label="Refund"
                                                icon="pi pi-undo"
                                                outlined
                                                :class="
                                                    cautionActionButtonClass
                                                "
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
                                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-neutral-200 z-[100]"
                                            >
                                                class="fixed inset-0 z-[90]
                                                lg:hidden"
                                                @click="toggleActionMenu" >
                                            </div>
                                            <!-- Mobile menu -->
                                            <div
                                                v-if="showActionMenu"
                                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-neutral-200 z-[100]"
                                                data-menu-container
                                            >
                                                <button
                                                    class="w-full text-left px-4 py-3 hover:bg-primary-50 flex items-center gap-3 text-sm text-primary font-medium transition-colors duration-150"
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
                                                    class="w-full text-left px-4 py-3 hover:bg-primary-50 flex items-center gap-3 text-sm border-t border-neutral-100 transition-colors duration-150"
                                                    @click="
                                                        () => {
                                                            handlePrintReceiptSlips();
                                                            toggleActionMenu();
                                                        }
                                                    "
                                                >
                                                    <i
                                                        class="pi pi-receipt"
                                                    ></i>
                                                    Print Receipt Slip
                                                </button>
                                                <button
                                                    class="w-full text-left px-4 py-3 hover:bg-neutral-50 flex items-center gap-3 text-sm border-t border-neutral-100 transition-colors duration-150"
                                                    @click="
                                                        () => {
                                                            sendReceiptEmail(
                                                                activeOrder
                                                            );
                                                            toggleActionMenu();
                                                        }
                                                    "
                                                >
                                                    <i
                                                        class="pi pi-envelope"
                                                    ></i>
                                                    Send to Email
                                                </button>
                                                <button
                                                    v-if="
                                                        activeOrder.status ===
                                                        'settled'
                                                    "
                                                    class="w-full text-left px-4 py-3 hover:bg-warning-50 flex items-center gap-3 text-sm border-t border-neutral-100 text-warning-700"
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

                            <div class="px-4 md:px-6 flex-1 overflow-auto">
                                <div class="flex flex-col lg:flex-row gap-4">
                                    <!-- <Receipt
                                    :receipt-id="activeOrder.id.toString()"
                                    :receiptData="activeOrder"
                                    :receipt-footer="props.receiptFooter"
                                    :general-settings="props.generalSettings"
                                /> -->
                                    <ReceiptLayout
                                        :store-name="
                                            props.generalSettings.company_name
                                        "
                                        :branch="activeOrder.branch"
                                        :invoice-number="activeOrder.invoice_no"
                                        :receipt-date="receiptDate"
                                        :table-number="activeOrder.table_number"
                                        :cashier="activeOrder.cashier?.name"
                                        :items="activeOrder.order_items"
                                        :totals="activeOrder.totals"
                                        :payment="activeOrder.payment"
                                        :receipt-footer="
                                            activeOrder?.branch
                                                ?.receipt_footer ||
                                            props.receiptFooter
                                        "
                                        :receipt-header="
                                            activeOrder?.branch?.receipt_headers
                                        "
                                        :bir-accreditation-footer="
                                            activeOrder.branch
                                                .bir_accreditation_footer
                                        "
                                        :refund-meta="refundMeta"
                                    />
                                    <div class="flex flex-col w-full lg:w-auto">
                                        <div
                                            v-if="refundMeta"
                                            class="rounded-2xl border border-error-100 bg-error-50 p-6 space-y-3"
                                        >
                                            <p
                                                class="text-xs uppercase tracking-wide text-error-500"
                                            >
                                                Refund Details
                                            </p>
                                            <p class="text-sm text-neutral-700">
                                                Requested by
                                                <span
                                                    class="font-semibold text-neutral-900"
                                                >
                                                    {{
                                                        refundMeta.requested_by
                                                    }}
                                                </span>
                                                &middot; Approved by
                                                <span
                                                    class="font-semibold text-neutral-900"
                                                >
                                                    {{ refundMeta.supervisor }}
                                                </span>
                                            </p>
                                            <p class="text-xs text-neutral-500">
                                                Refunded
                                                {{
                                                    formatDetailedDate(
                                                        refundMeta.refunded_at
                                                    )
                                                }}
                                            </p>
                                            <p
                                                class="text-sm text-neutral-600 italic"
                                            >
                                                "{{ refundMeta.notes }}"
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex flex-col items-center justify-center h-full py-16 text-center text-neutral-400"
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
                v-model:supervisor-id="refundForm.supervisor_id"
                v-model:otp-code="refundForm.otp_code"
                :supervisors="availableApprovers"
                :loading="refundLoading"
                :error="refundError"
                @submit="submitRefund"
                @closed="handleRefundDialogClosed"
            />

            <EmailReceiptDialog
                v-model:visible="showEmailModal"
                v-model:emails="emailForm.emails"
                :loading="emailLoading"
                @submit="submitEmailReceipt"
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
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, reactive, onMounted, onUnmounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue/usetoast";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import PageProps from "@/Types/PageProps";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Order from "@/Types/Order/Order";
import SearchAndFIlter from "./Partials/SearchAndFIlter.vue";
import Transactions from "./Partials/Transactions.vue";
import RefundDialog from "./Partials/RefundDialog.vue";
import EmailReceiptDialog from "./Partials/EmailReceiptDialog.vue";
import Receipt from "../Receipt.vue";
import ThermalPrinterManager from "@/Components/ThermalPrinter/ThermalPrinterManager.vue";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import debounce from "lodash/debounce";
import filter from "lodash/filter";
import ReceiptLayout from "@/Components/ReceiptLayout.vue";
import moment from "moment-timezone";

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
const toast = useToast();
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
    supervisor_id: null as number | null,
    otp_code: "",
});
const availableApprovers = ref<Array<{ id: number; name: string }>>([]);
const refundLoading = ref(false);
const refundError = ref<string | null>(null);
const refundMeta = computed(() => activeOrder.value?.meta?.refund || null);
const showActionMenu = ref(false);
const showThermalPrinter = ref(false);
const showEmailModal = ref(false);
const emailOrder = ref<Order | null>(null);
const emailForm = ref({
    emails: "",
});
const emailLoading = ref(false);

const receiptDate = computed(() => {
    if (!activeOrder.value) return null;
    return moment(activeOrder.value.order_date)
        .tz("Asia/Manila")
        .format("MM/DD/YYYY hh:mm A");
});

// Thermal printer receipt data
const thermalReceiptData = computed(() => {
    if (!activeOrder.value) return null;

    return {
        storeName: page.props.company_info.company_name,
        branch: activeOrder.value.branch,
        orderNumber: activeOrder.value.invoice_no,
        cashier: activeOrder.value.cashier?.name,
        dateTime: receiptDate.value,
        tableNumber: activeOrder.value.table_number,
        items: activeOrder.value.order_items || [],
        totals: activeOrder.value.totals || 0,
        payment: activeOrder.value.payment,
        receiptFooter: activeOrder.value?.branch?.receipt_footer,
        receiptHeader: activeOrder.value?.branch?.receipt_headers,
        birAccreditationFooter:
            activeOrder.value?.branch?.bir_accreditation_footer,
        isReprint: true,
        refundMeta: refundMeta.value,
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
    emailOrder.value = order;
    emailForm.value = {
        emails: order.customer?.email || "",
    };
    showEmailModal.value = true;
};

const submitEmailReceipt = async () => {
    if (!emailOrder.value) return;

    emailLoading.value = true;
    try {
        // Parse emails from comma-separated string
        const emails = emailForm.value.emails
            .split(",")
            .map((e) => e.trim())
            .filter((e) => e);

        await axios.post(
            route("transactions.api.send-receipt-email", {
                order: emailOrder.value.id,
            }),
            {
                emails,
                exportAsPdf: emailForm.value.exportAsPdf,
            }
        );

        showEmailModal.value = false;
        toast.add({
            severity: "success",
            summary: "Success",
            detail: `Receipt sent to ${emails.length} email(s)`,
            life: 3000,
        });
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to send receipt email",
            life: 3000,
        });
        console.error("Error sending receipt:", error);
    } finally {
        emailLoading.value = false;
    }
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

const openRefundModal = async (order: Order) => {
    refundOrder.value = order;
    refundForm.value = { notes: "", supervisor_id: null, otp_code: "" };

    // Fetch available approvers for current branch
    try {
        const response = await axios.get(route("transactions.api.approvers"));
        availableApprovers.value = response.data;
    } catch (error) {
        console.error("Error fetching approvers:", error);
        availableApprovers.value = [];
    }

    showRefundModal.value = true;
};

const closeRefundModal = () => {
    showRefundModal.value = false;
    refundError.value = null;
    handleRefundDialogClosed();
};

const submitRefund = async () => {
    if (!refundOrder.value) return;

    refundLoading.value = true;
    try {
        router.post(
            route("transactions.api.orders.refund", {
                order: refundOrder.value.id,
            }),
            refundForm.value,
            {
                onSuccess: () => {
                    // Check if there are errors in the page props (from withErrors redirect)
                    if (
                        page.props.errors &&
                        Object.keys(page.props.errors).length > 0
                    ) {
                        // Extract error messages from the errors object
                        const errorMessages = Object.values(page.props.errors)
                            .flat()
                            .filter(Boolean);

                        refundError.value =
                            errorMessages.join(", ") ||
                            "Failed to refund order";
                        refundLoading.value = false;
                        return;
                    }

                    // Success case
                    toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Order refunded successfully",
                        life: 3000,
                    });
                    closeRefundModal();
                    // router.reload();
                },
                onError: (errors) => {
                    console.error("Error refunding order:", errors);
                    // Extract error messages from error object
                    let errorDetail = "Failed to refund order";

                    if (errors && typeof errors === "object") {
                        // Check for direct message property
                        if (errors.message) {
                            errorDetail = errors.message;
                        } else {
                            // Try to extract from nested error structure
                            const errorMessages = Object.values(errors)
                                .flat()
                                .filter(Boolean);
                            if (errorMessages.length > 0) {
                                errorDetail = errorMessages.join(", ");
                            }
                        }
                    }

                    refundError.value = errorDetail;
                },
                onFinish: () => {
                    refundLoading.value = false;
                },
            }
        );
    } catch (error) {
        console.error("Error refunding order:", error);
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

// Print receipt slips for non-default payment methods
const handlePrintReceiptSlips = async () => {
    if (!activeOrder.value) return;

    try {
        // Check if thermal printer is connected
        if (!thermalPrinter.isConnected()) {
            toast.add({
                severity: "warn",
                summary: "Printer Not Connected",
                detail: "Please connect thermal printer first.",
                life: 3000,
            });
            return;
        }

        // Normalize payment data to array
        const paymentData = activeOrder.value.payment;
        const payments = Array.isArray(paymentData)
            ? paymentData
            : [paymentData];

        // Get default payment method using is_default_cash flag
        const availablePaymentMethods = page.props.payment_methods || [];
        const defaultPaymentMethod = availablePaymentMethods.find(
            (method: any) =>
                method.payment_type === "cash" && method.is_default_cash
        );

        console.log("Default payment method:", defaultPaymentMethod);
        console.log("All payments:", payments);

        // Filter payments that are NOT the default payment method
        const nonDefaultPayments = payments.filter((payment: any) => {
            // Only skip if this payment matches the default payment method
            if (defaultPaymentMethod) {
                // Check by method name (case-insensitive)
                const paymentMethodName = (payment.method || "").toLowerCase();
                const defaultMethodName = (
                    defaultPaymentMethod.name || ""
                ).toLowerCase();

                if (paymentMethodName === defaultMethodName) {
                    console.log("Excluding default payment:", payment.method);
                    return false;
                }

                // Also check if payment type is cash and matches default
                if (
                    payment.payment_type_value === "cash" &&
                    defaultPaymentMethod.payment_type === "cash"
                ) {
                    // If it's a cash payment, check if it's using the default currency
                    if (paymentMethodName === defaultMethodName) {
                        console.log(
                            "Excluding default cash payment:",
                            payment.method
                        );
                        return false;
                    }
                }
            }
            console.log("Including payment:", payment.method);
            return true;
        });

        console.log("Non-default payments:", nonDefaultPayments);

        if (nonDefaultPayments.length === 0) {
            toast.add({
                severity: "info",
                summary: "No Receipt Slips",
                detail: "This order only has default payment method. No receipt slips to print.",
                life: 3000,
            });
            return;
        }

        // Print a receipt slip for each non-default payment
        let successCount = 0;
        let failCount = 0;

        // Check if this is a mixed payment (multiple payments)
        const isMixedPayment = payments.length > 1;

        // Create single slip data with all non-default payments
        const slipData = {
            storeName: page.props.company_info?.company_name || "",
            branch: activeOrder.value.branch,
            invoiceNumber: String(activeOrder.value.invoice_no),
            dateTime: receiptDate.value,
            payments: nonDefaultPayments.map((payment: any) => ({
                paymentMethod:
                    payment.method || payment.payment_type || "Payment",
                amountPaid: payment.amount_paid || payment.amount_applied || 0,
                referenceNumber: payment.reference_number || undefined,
                customerName: payment.customer_name || undefined,
                customerContact: payment.customer_contact || undefined,
                paymentType: payment.payment_type_value || undefined,
                approvalCode: payment.approval_code || undefined,
                cardHolderName: payment.card_holder_name || undefined,
                giftCheckNumber: payment.gift_check_number || undefined,
                giftCheckAmount: payment.gift_check_amount || undefined,
            })),
            isMixedPayment: isMixedPayment,
        };

        try {
            await thermalPrinter.printReceiptSlip(slipData);
            successCount = nonDefaultPayments.length;
            console.log(
                `Receipt slip printed for ${nonDefaultPayments.length} payment(s)`
            );
        } catch (slipError) {
            failCount = nonDefaultPayments.length;
            console.error(`Failed to print receipt slip:`, slipError);
        }

        // Show summary toast
        if (successCount > 0) {
            toast.add({
                severity: "success",
                summary: "Receipt Slips Printed",
                detail: `Successfully printed ${successCount} receipt slip${
                    successCount > 1 ? "s" : ""
                }.${failCount > 0 ? ` ${failCount} failed.` : ""}`,
                life: 3000,
            });
        } else if (failCount > 0) {
            toast.add({
                severity: "error",
                summary: "Print Failed",
                detail: `Failed to print ${failCount} receipt slip${
                    failCount > 1 ? "s" : ""
                }.`,
                life: 3000,
            });
        }
    } catch (error) {
        console.error("Error in handlePrintReceiptSlips:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to print receipt slips.",
            life: 3000,
        });
    }
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
                await thermalPrinter.printReceipt(
                    thermalReceiptData.value,
                    true
                );
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
                    await thermalPrinter.printReceipt(
                        thermalReceiptData.value,
                        true
                    );
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
    "!rounded-full !border !border-warning-300 !bg-warning-50 !text-warning-700 !px-4 !py-2 !text-sm !font-semibold hover:!bg-warning-100";

const getStatusClass = (status: string) => {
    switch (status) {
        case "settled":
            return "bg-success-100 text-success-800";
        case "refund":
            return "bg-error-100 text-error-800";
        case "credit":
            return "bg-tertiary-100 text-tertiary-800";
        default:
            return "bg-neutral-100 text-neutral-800";
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
