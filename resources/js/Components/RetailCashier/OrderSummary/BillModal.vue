<template>
    <Dialog
        v-model:visible="visible"
        modal
        header="Print Bill"
        :style="{ width: '90vw', maxWidth: '800px', height: '80vh' }"
        :closable="false"
        @keydown="handleKeydown"
    >
        <div class="flex flex-col h-full">
            <!-- Scrollable Bill Preview -->
            <div class="flex-1 overflow-y-auto mb-4">
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <BillLayout
                        :business-logo="generalSettings.company_logo"
                        :business-name="
                            generalSettings.company_name ??
                            'Quick Juan Restaurant'
                        "
                        :business-address="generalSettings.company_address"
                        :business-phone="generalSettings.company_phone"
                        :bill-number="billData.billNumber"
                        :bill-date="billData.date"
                        :table-info="billData.tableInfo"
                        :cashier-name="billData.cashierName"
                        :order-type="billData.orderType"
                        :order-items="billData.orderItems"
                        :subtotal="billData.subtotal"
                        :less-tax="billData.lessTax"
                        :less-discount="billData.lessDiscount"
                        :discount-amount="billData.discountAmount"
                        :discount-name="billData.discountName"
                        :discount-type="billData.discountType"
                        :remove-tax="billData.removeTax"
                        :total-amount="billData.totalAmount"
                        :payment-info="billData.paymentInfo"
                        :bill-footer="billFooter"
                        footer-message=""
                    />
                </div>
            </div>

            <!-- Fixed Action Buttons -->
            <div class="flex gap-3 pt-4 border-t bg-white sticky bottom-0">
                <button
                    type="button"
                    @click="visible = false"
                    class="flex-1 py-2 px-2 bg-gray-400 text-white rounded-md hover:bg-gray-500 transition-colors"
                >
                    Close
                </button>
                <button
                    ref="printButtonRef"
                    type="button"
                    @click="printBill"
                    class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                >
                    Print
                </button>
            </div>
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, watch } from "vue";
import { Dialog } from "primevue";
import BillLayout from "@/Components/RetailCashier/BillLayout.vue";
import { useModalStyle } from "@/composables/useModalStyle";
import { router, usePage } from "@inertiajs/vue3";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import { useToast } from "primevue";

const props = defineProps<{
    visible: boolean;
    billData: any;
    billFooter: any;
    orderItems: any[];
    generalSettings: {
        company_name: string;
        company_address: string;
        company_phone: string;
        company_logo: string;
    };
}>();

const page = usePage();
const toast = useToast();

const emit = defineEmits<{
    "update:visible": [value: boolean];
}>();

const { getModalStyle } = useModalStyle();

// Refs
const printButtonRef = ref<HTMLButtonElement>();

// Computed
const visible = computed({
    get: () => props.visible,
    set: (value) => emit("update:visible", value),
});

// Handle keyboard events
const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === "Enter") {
        event.preventDefault();
        printBill();
    } else if (event.key === "Escape") {
        event.preventDefault();
        visible.value = false;
    }
};

// Print bill using thermal printer service
const printBill = async () => {
    try {
        // Update the bill no by adding plus 1 to the previous bill no
        router.put(
            `/retail-cashier/update-bill-no/${
                (page.props as any)?.active_branch?.id
            }`
        );

        // Check if printer is connected
        if (!thermalPrinter.isConnected()) {
            // Try to connect to receipt printer
            const connected = await thermalPrinter.connectToPrinterType(
                "receipt"
            );
            if (!connected) {
                toast.add({
                    severity: "error",
                    summary: "Printer Not Connected",
                    detail: "Please connect to a thermal printer first.",
                    life: 5000,
                });
                return;
            }
        }

        // Format bill data for thermal printer
        const thermalBillData = {
            storeName:
                props.generalSettings.company_name || "Quick Juan Restaurant",
            storeAddress: props.generalSettings.company_address || "",
            storePhone: props.generalSettings.company_phone || "",
            billNumber: props.billData.billNumber,
            cashier: props.billData.cashierName || "",
            date: props.billData.date,
            time: props.billData.time,
            tableInfo: props.billData.tableInfo || { name: "N/A" },
            orderType: props.billData.orderType || "dine-in",
            items: props.billData.orderItems || [],
            subtotal: props.billData.subtotal || 0,
            lessTax: props.billData.lessTax || 0,
            lessDiscount: props.billData.lessDiscount || 0,
            discountAmount: props.billData.discountAmount || 0,
            discountName: props.billData.discountName || "",
            discountType: props.billData.discountType || "",
            removeTax: props.billData.removeTax || false,
            total: props.billData.totalAmount || 0,
            payment: props.billData.paymentInfo
                ? {
                      method: props.billData.paymentInfo.method || "Cash",
                      amountPaid: props.billData.paymentInfo.amount_paid || 0,
                      change: props.billData.paymentInfo.change || 0,
                  }
                : undefined,
            billFooter: props.billFooter,
            footerMessage: "Generated by Quick Juan POS System",
        };

        // Print to thermal printer
        await thermalPrinter.printBill(thermalBillData);

        toast.add({
            severity: "success",
            summary: "Bill Printed",
            detail: "Bill has been sent to thermal printer successfully.",
            life: 3000,
        });

        // Close modal after successful printing
        visible.value = false;
    } catch (error) {
        console.error("Print error:", error);
        toast.add({
            severity: "error",
            summary: "Print Error",
            detail:
                error instanceof Error
                    ? error.message
                    : "Failed to print bill. Please try again.",
            life: 5000,
        });
    }
}; // Auto-focus print button when modal opens
watch(
    () => props.visible,
    (newVisible) => {
        if (newVisible) {
            nextTick(() => {
                printButtonRef.value?.focus();
            });
        }
    }
);
</script>
