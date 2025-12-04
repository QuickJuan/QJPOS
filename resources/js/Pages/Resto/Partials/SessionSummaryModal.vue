<template>
    <Dialog
        v-model:visible="props.showSessionSummaryModal"
        modal
        header=""
        :style="{ width: '42rem' }"
        class="bg-white"
        @hide="handleClose"
        @update:visible="handleClose"
    >
        <div
            class="bg-white p-4 rounded border font-mono text-xs text-center"
            ref="printArea"
        >
            <!-- Header -->
            <div class="mb-2 flex flex-col items-center gap-2">
                <div v-if="props.generalSettings?.company_logo">
                    <img
                        :src="props.generalSettings?.company_logo"
                        :alt="props.generalSettings?.company_name"
                        class="w-16 h-auto mx-auto mb-2"
                    />
                </div>
                <p class="font-bold text-sm">
                    {{ props.generalSettings?.company_name || "QUICKJUAN POS" }}
                </p>
                <p class="text-xs">
                    {{ props.generalSettings?.company_address }}
                </p>
                <p class="text-xs">
                    {{ props.generalSettings?.company_phone }}
                </p>
                <p class="text-xs">
                    {{ currentBranch?.address || "" }}
                </p>
                <p class="text-xs">
                    Operated By:
                    {{ currentCashier || "" }}
                </p>
                <p class="text-xs" v-if="currentBranch?.tin">
                    TIN: {{ currentBranch?.tin }}
                </p>
                <p class="text-xs" v-if="currentBranch.registration_number">
                    Registration No.: {{ currentBranch?.registration_number }}
                </p>
            </div>

            <div class="border-t my-4"></div>

            <!-- Body / Content -->
            <div class="text-center font-bold">X Reading Report</div>
            <div class="text-center text-xs mb-2">{{ reportDate }}</div>

            <div class="text-left text-xs">
                <div class="flex justify-between">
                    <span>Session Number:</span>
                    <span>{{ props.sessionSummary?.session_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span>OR Number Start:</span>
                    <span>{{ props.sessionSummary?.or_number_start }}</span>
                </div>
                <div class="flex justify-between">
                    <span>OR Number End:</span>
                    <span>{{ props.sessionSummary?.or_number_end }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Bill Number Start:</span>
                    <span>{{ props.sessionSummary?.bill_number_start }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Bill Number End:</span>
                    <span>{{ props.sessionSummary?.bill_number_end }}</span>
                </div>
            </div>

            <div class="text-left text-xs">
                <div class="flex justify-between">
                    <span>Gross Sales:</span>
                    <span>{{ formatMoney(grossSales) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Regular Discount:</span>
                    <span>{{ formatMoney(regularDiscount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Senior Discount:</span>
                    <span>{{
                        formatMoney(props.sessionSummary?.senior_discount)
                    }}</span>
                </div>
                <div class="flex justify-between">
                    <span>PWD Discount:</span>
                    <span>{{ formatMoney(pwdDiscount) }}</span>
                </div>
                <div class="flex justify-between font-bold border-t pt-1 mt-1">
                    <span>Net Sales:</span>
                    <span>
                        {{ formatMoney(props.sessionSummary?.net_sales) }}
                    </span>
                </div>

                <div class="mt-2"></div>
                <div class="flex justify-between">
                    <span>Non-VAT Sales:</span>
                    <span>
                        {{ formatMoney(props.sessionSummary?.non_vat_sales) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>VAT Sales:</span>
                    <span>
                        {{ formatMoney(props.sessionSummary?.vat_sales) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>VAT:</span>
                    <span>
                        {{ formatMoney(props.sessionSummary?.vat_amount) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Less Tax:</span>
                    <span>
                        {{ formatMoney(props.sessionSummary?.less_tax || 0) }}
                    </span>
                </div>

                <div class="border-t my-2"></div>

                <div class="flex justify-between">
                    <span>Counter ID Start:</span>
                    <span>
                        {{ props.openSession?.counter_id_start || "-" }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Counter ID End:</span>
                    <span>{{ props.openSession?.counter_id_end || "-" }}</span>
                </div>

                <div class="border-t my-2"></div>

                <div class="flex justify-between">
                    <span>Cancelled Tax:</span>
                    <span>
                        {{ props.sessionSummary?.cancelled_tax || 0 }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Cancelled Amount:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.cancelled_amount || 0
                            )
                        }}
                    </span>
                </div>

                <div class="border-t my-2"></div>

                <div class="flex justify-between">
                    <span>No of Transactions:</span>
                    <span>
                        {{ props.sessionSummary?.transactions_count || 0 }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Number of SKU:</span>
                    <span>{{ props.sessionSummary?.sku_count || 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Total Quantity:</span>
                    <span>{{ props.sessionSummary?.total_quantity || 0 }}</span>
                </div>

                <div class="border-t my-2"></div>

                <div class="flex justify-between">
                    <span>Previous Reading:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.previous_reading || 0
                            )
                        }}
                    </span>
                </div>
                <div class="flex justify-between font-bold">
                    <span>Net Sales:</span>
                    <span>{{
                        formatMoney(props.sessionSummary?.net_sales)
                    }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Running Total:</span>
                    <span>{{ formatMoney(runningTotal) }}</span>
                </div>

                <div class="border-t my-2"></div>

                <div class="flex justify-between">
                    <span>Expected Cash:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.expected_cash || 0
                            )
                        }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span>Cash Denomination:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.cash_denomination || 0
                            )
                        }}
                    </span>
                </div>

                <div class="border-t my-2"></div>

                <div class="text-center font-bold">X Reading End</div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-between gap-3">
                <Button
                    type="button"
                    label="Close"
                    severity="secondary"
                    outlined
                    @click="emit('closeModal')"
                />
                <div class="flex gap-3">
                    <Button
                        type="button"
                        label="Confirm Close"
                        @click="emit('confirmClose')"
                        class="bg-green-600 hover:bg-green-700 text-white border-green-600"
                    />
                    <Button
                        type="button"
                        label="Print & Close"
                        @click="printReport"
                        class="bg-blue-600 hover:bg-blue-700 text-white border-blue-600"
                    />
                </div>
            </div>
        </template>
    </Dialog>

    <ConfirmPopup />
    <Toast />
</template>

<script setup lang="ts">
import { formatMoney } from "@/Utils/FormatMoney";
import { usePage } from "@inertiajs/vue3";
import { Button, Dialog, ConfirmPopup, Toast } from "primevue";
import { computed, ref } from "vue";
import axios from "axios";
import { route } from "ziggy-js";
import { useConfirm, useToast } from "primevue";
import { thermalPrinter } from "@/Services/ThermalPrinterService";

const props = defineProps<{
    showSessionSummaryModal: boolean;
    openSession: any;
    sessionSummary?: any;
    currentUser?: any;
    totalCashCounted?: number;
    operatorName?: string;
    merchantTin?: string;
    generalSettings?: any;
}>();

const emit = defineEmits(["closeModal", "confirmClose"]);
const page = usePage();
const confirm = useConfirm();
const toast = useToast();

const handleClose = () => {
    emit("closeModal");
};

const printArea = ref<HTMLElement | null>(null);

const printReport = async () => {
    try {
        // Load receipt printer config (assuming session summary uses receipt printer)
        await thermalPrinter.loadPrinterConfig("receipt");

        // Connect to printer if not already connected
        if (!thermalPrinter.isConnected()) {
            const connected = await thermalPrinter.connectToPrinterType(
                "receipt"
            );
            if (!connected) {
                toast.add({
                    severity: "error",
                    summary: "Printer Error",
                    detail: "Failed to connect to printer. Please check printer configuration.",
                    life: 5000,
                });
                return;
            }
        }

        // Print session summary
        await thermalPrinter.printSessionSummary(
            props.sessionSummary,
            props.generalSettings?.company_name || "QUICKJUAN POS",
            currentBranch?.address || "",
            currentCashier || "",
            currentBranch?.tin || "",
            currentBranch?.registration_number || ""
        );

        // Show success message
        toast.add({
            severity: "success",
            summary: "Print Success",
            detail: "Session summary printed successfully",
            life: 3000,
        });

        // Close the session after successful printing
        emit("confirmClose");
    } catch (error) {
        console.error("Failed to print session summary:", error);
        toast.add({
            severity: "error",
            summary: "Print Error",
            detail: "Failed to print session summary. Please try again.",
            life: 5000,
        });
    }
};

const reportDate = computed(() => new Date().toLocaleDateString());

const grossSales = computed(
    () =>
        props.sessionSummary?.gross_sales ??
        props.sessionSummary?.total_sales ??
        0
);
const regularDiscount = computed(
    () =>
        props.sessionSummary?.regular_discount ??
        props.sessionSummary?.regular_discount_amount ??
        0
);
const pwdDiscount = computed(() => props.sessionSummary?.pwd_discount ?? 0);

const runningTotal = computed(() => props.sessionSummary?.running_total ?? 0);

const refundOrder = async (orderId: number) => {
    confirm.require({
        message: "Are you sure you want to refund this order?",
        header: "Refund Confirmation",
        icon: "pi pi-exclamation-triangle",
        rejectClass: "p-button-secondary p-button-outlined",
        rejectLabel: "Cancel",
        acceptLabel: "Refund",
        accept: async () => {
            try {
                await axios.post(
                    route("transactions.api.orders.refund", orderId),
                    {
                        supervisor_name:
                            props.currentUser?.name || "Supervisor",
                        notes: "Refund requested from session summary",
                    }
                );
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Order refunded successfully",
                    life: 3000,
                });
                // Optionally refetch summary or update UI
            } catch (error) {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to refund order",
                    life: 3000,
                });
            }
        },
    });
};

const currentBranch = page.props.active_branch as any;
const currentCashier = (page.props.auth as any)?.user?.name;
</script>
