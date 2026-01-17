<template>
    <div class="p-4 border-t border-gray-200 bg-white">
        <div class="space-y-3">
            <!-- Order Type and Place Order Row -->
            <div class="flex gap-2">
                <!-- Order Type Button -->
                <button
                    @click="showOrderTypeModal = true"
                    class="flex-1 py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-secondary-700 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                >
                    <component
                        :is="selectedOrderTypeData.icon"
                        class="w-4 h-4"
                    />
                    <span>{{ selectedOrderTypeData.label }}</span>
                    <ChevronDownIcon class="w-4 h-4 ml-auto" />
                </button>

                <!-- Place Order Button - Show whenever there are items to place -->
                <button
                    @click="handlePlaceOrder"
                    class="px-4 py-2.5 bg-success-600 text-white rounded-lg font-semibold hover:bg-success-700 transition-colors text-sm whitespace-nowrap"
                >
                    Place Order
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-2">
                <button
                    @click="showMoreOptionsModal = true"
                    class="py-2 px-3 bg-secondary-600 text-white rounded-lg font-semibold hover:bg-secondary-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2 text-sm"
                >
                    <span>More Options</span>
                    <ChevronDownIcon class="w-3 h-3" />
                </button>

                <!-- Settle Bill Button - Hide in waiter mode -->
                <button
                    v-if="!isOrderTaking"
                    @click="openSettlePaymentPage"
                    class="py-2 px-3 bg-success-600 text-white rounded-lg font-semibold hover:bg-success-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors text-sm"
                >
                    Settle Bill
                </button>
            </div>
        </div>

        <!-- Order Type Selection Modal -->

        <OrderTypeSelectionModal
            v-model:visible="showOrderTypeModal"
            :selected-order-type="orderStore.selectedOrderType"
            :order-types="orderTypes"
            @update-order-type="selectOrderType"
        />

        <!-- More Options Modal -->
        <MoreOptionsModal
            v-model:visible="showMoreOptionsModal"
            :order-items="orderItems"
            :selected-items-for-discount="selectedItemsForDiscount"
            @save-order="handleSaveOrder"
            @open-discount-modal="handleApplyDiscount"
            @add-modifier="handleAddModifier"
            @print-bill="handlePrintBill"
            @reprint-order="handleReprintOrder"
            @view-table="handleViewTable"
            @printer-config="handlePrinterConfig"
            @end-of-shift="handleEndOfShift"
            @transfer-order-items="handleTransferOrderItems"
        />

        <BillModal
            v-model:visible="showBillModal"
            :bill-data="billData"
            :general-settings="generalSettings"
        />

        <!-- Server Selection Modal -->
        <ServerSelectionModal
            v-model:visible="showServerSelectionModal"
            @confirm="handleServerConfirm"
            @cancel="showServerSelectionModal = false"
        />
    </div>

    <ConfirmPopup />
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useToast } from "primevue";
import { ChevronDownIcon } from "@heroicons/vue/24/outline";
import {
    HomeIcon,
    ShoppingBagIcon,
    TruckIcon,
} from "@heroicons/vue/24/outline";
import OrderTypeSelectionModal from "./OrderTypeSelectionModal.vue";
import MoreOptionsModal from "./MoreOptionsModal.vue";
import BillModal from "./BillModal.vue";
import ServerSelectionModal from "./ServerSelectionModal.vue";
import { useBillNumber } from "@/composables/useBillNumber";
import { usePage, router } from "@inertiajs/vue3";
import { ConfirmPopup, useConfirm } from "primevue";
import { httpGet } from "@/Utils/axiosHelper";
import { route } from "ziggy-js";
import { useTable } from "@/composables/useTable";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import { useOrderStore } from "@/stores/orderStore";
import Swal from "sweetalert2";
import { usePrintBill } from "@/composables/usePrintBill";

const props = defineProps<{
    cart: any;
    tableId: number;
    locationType: string;
    orderItems: any[];
    selectedItemsForDiscount?: number[];
    totalAmount: number;
    appliedDiscount: any;
    subTotal: number;
    total: number;
    lessTaxTotal: number;
    lessDiscountTotal: number;
    tableInfo: any;
    billFooter: any;
    receiptFooter: any;
    billNumber: string;
    receiptNumber: string;
    generalSettings: {
        company_name: string;
        company_address: string;
        company_phone: string;
        company_logo: string;
    };
    isWaiterMode?: boolean; // Hide settle button in waiter mode
}>();

const emit = defineEmits<{
    updateOrderType: [type: string];
    saveOrder: [];
    checkout: [data: any];
    openDiscountModal: [];
    addModifier: [];
    transferOrderItems: [];
    printBill: [];
    viewTable: [];
    printerConfig: [];
    endOfShift: [];
}>();

// Use applied discount from props
const appliedDiscount = computed(() => props.appliedDiscount);

// Check if there are items that can be placed (have placed_order false)
const hasItemsToPlace = computed(() => {
    return props.orderItems.some((item) => item.placed_order === false);
});

const { placeOrder } = useTable();
const toast = useToast();
// Use confirm
const confirm = useConfirm();

// Use order store
const orderStore = useOrderStore();

// UsePage
const page = usePage();

const isOrderTaking = computed(() => {
    const raw = page.props?.auth?.user?.current_role ?? "";
    const normalized = String(raw)
        .toLowerCase()
        .replaceAll(" ", "_")
        .replaceAll("-", "_");

    return normalized === "order_taking";
});

// Modal visibility states
const showOrderTypeModal = ref(false);
const showMoreOptionsModal = ref(false);
const showBillModal = ref(false);
const showServerSelectionModal = ref(false);
const selectedServerId = ref<number | null>(null);
const { getNextBillNumber } = useBillNumber();

const billData = ref({
    billNumber: "",
    date: new Date().toISOString(),
    tableInfo: "",
    cashierName: "",
    orderItems: [] as any[],
    subtotal: 0,
    lessTax: 0,
    lessDiscount: 0,
    totalAmount: 0,
});

const orderTypes = [
    {
        value: "dine-in",
        label: "Dine-in",
        icon: HomeIcon,
        activeClass: "bg-primary text-white",
        description: "Customer will eat at the restaurant",
    },
    {
        value: "takeout",
        label: "Takeout",
        icon: ShoppingBagIcon,
        activeClass: "bg-success text-white",
        description: "Customer will take food to go",
    },
    {
        value: "delivery",
        label: "Delivery",
        icon: TruckIcon,
        activeClass: "bg-warning text-white",
        description: "Food will be delivered to customer",
    },
];

const selectedOrderTypeData = computed(() => {
    return (
        orderTypes.find(
            (orderType) => orderType.value === orderStore.selectedOrderType,
        ) || orderTypes[0]
    );
});

// Select order type and close modal
const selectOrderType = (type: string) => {
    orderStore.setOrderType(type);
    orderStore.updateUrlParams();
    showOrderTypeModal.value = false;
    emit("updateOrderType", type);
};

// Handle save order from more options modal
const handleSaveOrder = () => {
    emit("saveOrder");
    showMoreOptionsModal.value = false;
};

// Handle apply discount from more options modal
const handleApplyDiscount = () => {
    emit("openDiscountModal");
    showMoreOptionsModal.value = false;
};

// Handle add modifier from more options modal
const handleAddModifier = () => {
    emit("addModifier");
    showMoreOptionsModal.value = false;
};

// Handle transfer order items from more options modal
const handleTransferOrderItems = () => {
    emit("transferOrderItems");
    showMoreOptionsModal.value = false;
};

const openSettlePaymentPage = () => {
    if (!props.cart?.id) {
        toast.add({
            severity: "warn",
            summary: "No Active Cart",
            detail: "Please open an order before settling a bill.",
            life: 3000,
        });
        return;
    }

    router.visit(
        route("resto.cart.settle-payment", {
            cart: props.cart.id,
        }),
    );
};

// Handle print bill
const { fetchBillData, sendBillToPrinter } = usePrintBill();

const handlePrintBill = async () => {
    const cartId = page.props.cart?.id;

    if (!cartId) {
        toast.add({
            severity: "warn",
            summary: "No Active Cart",
            detail: "Please open an order before printing a bill.",
            life: 3000,
        });
        return;
    }

    const response = await fetchBillData(cartId);

    if (!response.success || !response.data) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: response.error || "Failed to fetch bill data",
            life: 3000,
        });
        return;
    }

    billData.value = response.data;

    if (!billData.value) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Bill data is unavailable.",
            life: 3000,
        });
        return;
    }

    try {
        await sendBillToPrinter(billData.value);
    } catch (error) {
        console.log("Failed to print bill:", error);
        showBillModal.value = true;
        toast.add({
            severity: "warn",
            summary: "Printer Error",
            detail: "Failed to print bill. Showing Bill modal instead.",
            life: 3000,
        });
    }
};

// Handle view table
const handleViewTable = () => {
    emit("viewTable");
};

// Handle printer configuration
const handlePrinterConfig = () => {
    router.visit(route("printer-config.index", { tableId: props.tableId }));
};

// Handle end of shift
const handleEndOfShift = () => {
    emit("endOfShift");
};

// Handle place order with response handling
const handlePlaceOrder = async () => {
    // Show server selection modal first
    showServerSelectionModal.value = true;
};

const sendPlacedOrderToPrinter = async (
    orderNumber: number | string,
    tableName: string,
    placedOrderItems: any[],
    servedBy: string,
    servingNumber?: number | null,
) => {
    if (!placedOrderItems || placedOrderItems.length === 0) {
        throw new Error("No items available to print.");
    }

    if (!thermalPrinter.isConnected()) {
        const connected = await thermalPrinter.connectToPrinterType("kitchen");
        if (!connected) {
            throw new Error(
                "Printer not connected. Please connect a kitchen printer first.",
            );
        }
    }

    await thermalPrinter.printPlacedOrder(
        orderNumber,
        tableName || "Table",
        placedOrderItems,
        servedBy,
        servingNumber,
    );
};

interface ServerConfirmationPayload {
    serverId: number;
    servingNumber: number | null;
}

const handleServerConfirm = async (payload: ServerConfirmationPayload) => {
    selectedServerId.value = payload.serverId;
    const response = await placeOrder(
        props.tableId,
        props.cart?.id,
        payload.serverId,
        payload.servingNumber,
    );

    if (response.success) {
        toast.add({
            severity: "success",
            summary: "Success",
            detail: response.message,
            life: 3000,
        });

        // Print the placed order items to thermal printer
        if (
            response?.placedOrderItems &&
            response.placedOrderItems.length > 0
        ) {
            try {
                await sendPlacedOrderToPrinter(
                    response.orderNumber,
                    response.tableRoom?.name || "Table",
                    response.placedOrderItems,
                    response.servedBy,
                    response.servingNumber ?? null,
                );
            } catch (printError) {
                console.error("Failed to print order:", printError);
                // Show error but continue to redirect
                toast.add({
                    severity: "warn",
                    summary: "Print Failed",
                    detail: "Order placed successfully but failed to print. You can re-print from the order list.",
                    life: 4000,
                });
            }
        }

        // Always redirect back to tables after placing order (regardless of print success)
        let locationId = response.data?.tableRoom?.table_room_location_id;
        if (locationId) {
            router.visit(
                route("table-rooms.index", { locationId: locationId }),
            );
        } else {
            router.visit(route("table-rooms.index"));
        }

        //redirect to table-rooms index with locationId after delay to show toast
        // setTimeout(() => {
        //     let locationId = response.data?.tableRoom?.table_room_location_id;
        //     if (locationId) {
        //         router.visit(
        //             route("table-rooms.index", { locationId: locationId })
        //         );
        //     } else {
        //         router.visit(route("table-rooms.index"));
        //     }
        // }, 1500);
    } else {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: response.message || "Failed to place order",
            life: 3000,
        });
    }
};

const handleReprintOrder = async () => {
    const result = await Swal.fire({
        title: "Re-print Order",
        input: "text",
        inputLabel: "Enter batch/order number",
        inputPlaceholder: "e.g., 123",
        showCancelButton: true,
        confirmButtonText: "Re-print",
        inputValidator: (value) => {
            if (!value || !value.trim()) {
                return "Batch number is required";
            }
            if (!/^\d+$/.test(value.trim())) {
                return "Batch number must be numeric";
            }
            return null;
        },
    });

    if (!result.isConfirmed || !result.value) {
        return;
    }

    const batchNumber = result.value.trim();

    try {
        const response = await httpGet(
            route("resto.cart.reprint-order", { batchNumber }),
        );

        if (!response?.success || !response?.data) {
            throw new Error(
                response?.error ||
                    "Unable to fetch placed order items. Please try again.",
            );
        }

        const payload: any = response.data;

        if (
            !payload?.placedOrderItems ||
            payload.placedOrderItems.length === 0
        ) {
            throw new Error(
                payload?.message ||
                    "No placed order items found for this batch number.",
            );
        }

        await sendPlacedOrderToPrinter(
            payload.orderNumber ?? batchNumber,
            payload.tableRoom?.name || "Table",
            payload.placedOrderItems,
            payload.servedBy ?? "N/A",
            payload.servingNumber ?? null,
        );

        toast.add({
            severity: "success",
            summary: "Order Re-printed",
            detail: `Order #${
                payload.orderNumber ?? batchNumber
            } sent to kitchen`,
            life: 3000,
        });
    } catch (error: any) {
        console.error("Failed to re-print order:", error);
        toast.add({
            severity: "error",
            summary: "Re-print Failed",
            detail:
                error?.message ||
                "Unable to re-print the order. Please verify the batch number.",
            life: 4000,
        });
    }
};
</script>
