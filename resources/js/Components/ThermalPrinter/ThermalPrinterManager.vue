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

        <!-- Test Print Button -->
        <Button
            v-if="isConnected"
            @click="testPrint"
            :loading="printing"
            icon="pi pi-print"
            label="Test Print"
            size="small"
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
    orderNumber: string;
    cashier: string;
    date: string;
    items: ReceiptItem[];
    subtotal: number;
    tax?: number;
    discount?: number;
    total: number;
    payment: {
        method: string;
        amount: number;
        change?: number;
    };
    footer?: string;
}

const props = defineProps<{
    receiptData?: ReceiptData;
    autoConnect?: boolean;
}>();

const emit = defineEmits<{
    connected: [connected: boolean];
    printed: [success: boolean];
}>();

const toast = useToast();

const isConnected = ref(false);
const connecting = ref(false);
const printing = ref(false);
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
        const connected = await thermalPrinter.connectToPrinter();
        isConnected.value = connected;

        if (connected) {
            toast.add({
                severity: "success",
                summary: "Connected",
                detail: "Successfully connected to thermal printer",
                life: 3000,
            });
            emit("connected", true);
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

// Test print
const testPrint = async () => {
    if (!isConnected.value) return;

    printing.value = true;
    try {
        await thermalPrinter.testPrint();
        toast.add({
            severity: "success",
            summary: "Test Print",
            detail: "Test print completed successfully",
            life: 3000,
        });
    } catch (error: any) {
        console.error("Test print error:", error);
        toast.add({
            severity: "error",
            summary: "Print Failed",
            detail: error.message || "Failed to print test page",
            life: 5000,
        });
    } finally {
        printing.value = false;
    }
};

// Print receipt
const printReceipt = async () => {
    if (!isConnected.value || !props.receiptData) return;

    printing.value = true;
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

    if (props.autoConnect && isBluetoothSupported.value && !isConnected.value) {
        await connectPrinter();
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
    testPrint,
    isConnected: computed(() => isConnected.value),
    isBluetoothSupported: computed(() => isBluetoothSupported.value),
});
</script>
