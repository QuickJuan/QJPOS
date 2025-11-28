<template>
    <div class="thermal-printer-manager">
        <!-- Printer Connection Status -->
        <div class="flex items-center gap-3 mb-4">
            <div class="flex items-center gap-2">
                <div
                    :class="[
                        'w-3 h-3 rounded-full',
                        isConnected ? 'bg-green-500' : 'bg-red-500',
                    ]"
                ></div>
                <span class="text-sm font-medium">
                    {{
                        isConnected
                            ? "Printer Connected"
                            : "Printer Disconnected"
                    }}
                </span>
            </div>

            <Button
                v-if="!isConnected"
                @click="connectPrinter"
                :loading="connecting"
                icon="pi pi-bluetooth"
                label="Connect Printer"
                size="small"
                outlined
            />

            <Button
                v-else
                @click="disconnectPrinter"
                icon="pi pi-times"
                label="Disconnect"
                size="small"
                severity="secondary"
                outlined
            />
        </div>

        <!-- Browser Compatibility Warning -->
        <div
            v-if="!isBluetoothSupported"
            class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4"
        >
            <div class="flex items-start gap-3">
                <i class="pi pi-exclamation-triangle text-amber-600 mt-0.5"></i>
                <div>
                    <h4 class="font-semibold text-amber-800">
                        Web Bluetooth Not Supported
                    </h4>
                    <p class="text-sm text-amber-700 mt-1">
                        Please use Chrome, Edge, or Opera browser for Bluetooth
                        thermal printer support. Firefox and Safari are not
                        supported.
                    </p>
                </div>
            </div>
        </div>

        <!-- Printer Settings Button -->
        <Button
            v-if="isConnected"
            @click="openPrinterSettings"
            icon="pi pi-cog"
            label="Printer Settings"
            size="small"
            severity="secondary"
            outlined
            class="mb-4"
        />

        <!-- Print Receipt Button -->
        <Button
            v-if="isConnected && receiptData"
            @click="printReceipt"
            :loading="printing"
            icon="pi pi-receipt"
            label="Print Receipt"
            severity="success"
        />

        <!-- Auto-printing status -->
        <div
            v-if="autoPrinting"
            class="flex items-center gap-2 text-sm text-gray-600 mt-2"
        >
            <i class="pi pi-spin pi-spinner"></i>
            <span>Printing receipt...</span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import Button from "primevue/button";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import { useToast } from "primevue/usetoast";

interface ReceiptItem {
    name: string;
    quantity: number;
    price: number;
    amount: number;
}

interface ReceiptData {
    storeName: string;
    storeAddress: string;
    storePhone?: string;
    orderNumber: string;
    cashier: string;
    date: string;
    time?: string;
    tableNumber?: string;
    orderType?: string;
    items: ReceiptItem[];
    subtotal: number;
    lessTax?: number;
    lessDiscount?: number;
    total: number;
    payment?: {
        method: string;
        amountPaid: number;
        change?: number;
    };
    receiptFooter?: {
        footer_notes?: string;
    };
    footerMessage?: string;
}

const props = defineProps<{
    receiptData?: ReceiptData;
    autoConnect?: boolean;
    autoPrint?: boolean;
}>();

const emit = defineEmits<{
    connected: [connected: boolean];
    printed: [success: boolean];
    openSettings: [];
}>();

const toast = useToast();

const isConnected = ref(false);
const connecting = ref(false);
const printing = ref(false);
const autoPrinting = ref(false);
const isBluetoothSupported = ref(false);

// Check browser compatibility
const checkBluetoothSupport = () => {
    isBluetoothSupported.value = thermalPrinter.isBluetoothSupported();
};

// Connect to printer
const connectPrinter = async () => {
    if (!isBluetoothSupported.value) {
        toast.add({
            severity: "error",
            summary: "Not Supported",
            detail: "Web Bluetooth is not supported in this browser",
            life: 5000,
        });
        return;
    }

    connecting.value = true;
    try {
        // Try to connect using receipt printer configuration first
        let connected = false;

        try {
            connected = await thermalPrinter.connectToPrinterType("receipt");
        } catch (error) {
            console.warn(
                "Failed to connect with receipt config, trying default connection:",
                error
            );
            // Fall back to default connection if receipt config fails
            connected = await thermalPrinter.connectToPrinter();
        }

        isConnected.value = connected;

        if (connected) {
            toast.add({
                severity: "success",
                summary: "Connected",
                detail: "Successfully connected to thermal printer",
                life: 3000,
            });
            emit("connected", true);

            // Auto-print if enabled and receipt data is available
            if (props.autoPrint && props.receiptData) {
                setTimeout(() => {
                    printReceipt();
                }, 500); // Small delay to ensure connection is stable
            }
        } else {
            toast.add({
                severity: "error",
                summary: "Connection Failed",
                detail: "Failed to connect to printer",
                life: 5000,
            });
        }
    } catch (error: any) {
        console.error("Connection error:", error);
        toast.add({
            severity: "error",
            summary: "Connection Error",
            detail: error.message || "Failed to connect to printer",
            life: 5000,
        });
    } finally {
        connecting.value = false;
    }
};

// Disconnect from printer
const disconnectPrinter = async () => {
    try {
        await thermalPrinter.disconnectFromPrinter();
        isConnected.value = false;
        emit("connected", false);

        toast.add({
            severity: "info",
            summary: "Disconnected",
            detail: "Disconnected from thermal printer",
            life: 3000,
        });
    } catch (error) {
        console.error("Disconnect error:", error);
    }
};

// Open printer settings
const openPrinterSettings = () => {
    emit("openSettings");
};

// Print receipt
const printReceipt = async () => {
    if (!isConnected.value || !props.receiptData) return;

    printing.value = true;
    autoPrinting.value = props.autoPrint || false;
    try {
        await thermalPrinter.printReceipt(props.receiptData);
        toast.add({
            severity: "success",
            summary: "Receipt Printed",
            detail: "Receipt printed successfully",
            life: 3000,
        });
        emit("printed", true);
    } catch (error: any) {
        console.error("Print receipt error:", error);
        toast.add({
            severity: "error",
            summary: "Print Failed",
            detail: error.message || "Failed to print receipt",
            life: 5000,
        });
        emit("printed", false);
    } finally {
        printing.value = false;
        autoPrinting.value = false;
    }
};

// Check connection status
const checkConnectionStatus = () => {
    isConnected.value = thermalPrinter.isConnected();
};

// Auto-connect on mount if enabled
onMounted(async () => {
    checkBluetoothSupport();
    checkConnectionStatus();

    // Auto-load receipt printer configuration and connect if auto-connect is enabled
    if (isBluetoothSupported.value) {
        try {
            // Load the receipt printer configuration
            await thermalPrinter.loadPrinterConfig("receipt");

            // Check if already connected and auto-print if needed
            if (isConnected.value && props.autoPrint && props.receiptData) {
                setTimeout(() => {
                    printReceipt();
                }, 500);
            } else if (!isConnected.value && props.autoConnect) {
                await connectPrinter();
            }
        } catch (error) {
            console.error("Failed to load printer config:", error);
            // Continue without config if it fails
            if (!isConnected.value && props.autoConnect) {
                await connectPrinter();
            }
        }
    }
});

// Watch for connection changes
watch(isConnected, (connected) => {
    emit("connected", connected);
});

// Expose methods for parent components
defineExpose({
    connectPrinter,
    disconnectPrinter,
    printReceipt,
    openPrinterSettings,
    isConnected: computed(() => isConnected.value),
    isBluetoothSupported: computed(() => isBluetoothSupported.value),
});
</script>
