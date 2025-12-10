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
                        :storeName="page.props.company_info.company_name"
                        :branch="props.billData.branch"
                        :billNumber="props.billData.bill_number"
                        :tableName="props.billData.table_number"
                        :cashier="props.billData.cashier"
                        :items="props.billData.cart_items || []"
                        :totals="props.billData.totals || {}"
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
import BillLayout from "@/Components/Resto/BillLayout.vue";
import { useModalStyle } from "@/composables/useModalStyle";
import { router, usePage } from "@inertiajs/vue3";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import { useToast } from "primevue";

const props = defineProps<{
    visible: boolean;
    billData: any;
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
        // router.put(
        //     `/resto/update-bill-no/${(page.props as any)?.active_branch?.id}`
        // );

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
            storeName: page.props.company_info?.company_name,
            branch: props.billData.branch,
            billNumber: props.billData.bill_number,
            tableName: props.billData.table_number,
            cashier: props.billData.cashier,
            items: props.billData.cart_items || [],
            totals: props.billData.totals || {},
        };

        console.log("Thermal Bill Data:", thermalBillData);

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
