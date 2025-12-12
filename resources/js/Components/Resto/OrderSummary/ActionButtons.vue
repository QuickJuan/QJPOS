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

                <!-- Settle Bill Button -->
                <button
                    @click="showSettleBillModal = true"
                    class="py-2 px-3 bg-success-600 text-white rounded-lg font-semibold hover:bg-success-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors text-sm"
                >
                    Settle Bill
                </button>
            </div>
        </div>

        <!-- Order Type Selection Modal -->

        <OrderTypeSelectionModal
            v-model:visible="showOrderTypeModal"
            :selected-order-type="selectedOrderType"
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
            @view-table="handleViewTable"
            @printer-config="handlePrinterConfig"
            @end-of-shift="handleEndOfShift"
            @transfer-order-items="handleTransferOrderItems"
        />

        <!-- Settle Bill Modal -->
        <SettleBillModal
            v-model:visible="showSettleBillModal"
            :cart="cart"
            :sub-total="props.subTotal"
            :total="props.total"
            :table-info="tableInfo"
            @settle-bill="handleSettleBill"
        />

        <!-- Receipt Modal -->
        <ReceiptModal
            v-model:visible="showReceiptModal"
            :receipt-data="receiptData"
            :order-items="orderItems"
            :receipt-footer="receiptFooter"
            :general-settings="generalSettings"
            :active-branch="page.props.active_branch"
        />

        <BillModal
            v-model:visible="showBillModal"
            :bill-data="billData"
            :general-settings="generalSettings"
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
import SettleBillModal from "./SettleBillModal.vue";
import ReceiptModal from "./ReceiptModal.vue";
import BillModal from "./BillModal.vue";
import { useBillNumber } from "@/composables/useBillNumber";
import { usePage, router } from "@inertiajs/vue3";
import { ConfirmPopup, useConfirm } from "primevue";
import { httpGet } from "@/Utils/axiosHelper";
import { route } from "ziggy-js";
import { useTable } from "@/composables/useTable";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import Swal from "sweetalert2";
import { formatMoney } from "@/Utils/FormatMoney";
import moment from "moment-timezone";

const props = defineProps<{
    cart: any;
    tableId: number;
    locationType: string;
    orderItems: any[];
    selectedOrderType: string;
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
}>();

const emit = defineEmits<{
    updateOrderType: [type: string];
    saveOrder: [];
    checkout: [data: any];
    openDiscountModal: [];
    addModifier: [];
    transferOrderItems: [];
    settleBill: [data: any];
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

// UsePage
const page = usePage();

// Modal visibility states
const showOrderTypeModal = ref(false);
const showMoreOptionsModal = ref(false);
const showSettleBillModal = ref(false);
const showReceiptModal = ref(false);
const showBillModal = ref(false);
const { getNextBillNumber } = useBillNumber();

// Receipt data
const receiptData = ref({
    receiptNumber: props.receiptNumber,
    date: new Date().toISOString(),
    tableNumber: "",
    cashierName: "",
    orderType: "",
    orderItems: [] as any[],
    subtotal: 0,
    taxAmount: 0,
    discountAmount: 0,
    discountName: null as string | null,
    discountType: null as string | null,
    removeTax: false,
    isSeniorDiscount: false,
    totalAmount: 0,
    paymentInfo: null as any,
    taxInfo: null as any,
});

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
            (orderType) => orderType.value === props.selectedOrderType
        ) || orderTypes[0]
    );
});

// Select order type and close modal
const selectOrderType = (type: string) => {
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

// Handle settle bill form submission
const handleSettleBill = async (response: any) => {
    // Update receipt data from settle bill modal
    // emit("settleBill", response.data);
    receiptData.value = response?.data || null;
    showSettleBillModal.value = false;
    if (receiptData.value) {
        // Format receipt data for thermal printer
        const thermalReceiptData = {
            storeName: page.props.company_info.company_name,
            branch: receiptData.value.branch,
            orderNumber: receiptData.value.invoice_no,
            cashier: receiptData.value.cashier?.name,
            dateTime: moment(receiptData.value.order_date)
                .tz("Asia/Manila")
                .format("MM/DD/YYYY hh:mm A"),
            tableNumber: receiptData.value.table_number,
            items: receiptData.value.order_items || [],
            totals: receiptData.value.totals || 0,
            payment: receiptData.value.payment,
            receiptFooter: receiptData.value?.branch?.receipt_footer,
            receiptHeader: receiptData.value?.branch?.receipt_headers,
            birAccreditationFooter:
                receiptData.value?.branch?.bir_accreditation_footer,
            isReprint: false,
        };

        // Print to thermal printer
        try {
            await thermalPrinter.printReceipt(thermalReceiptData, false);

            // Show change amount in Swal only if print was successful
            Swal.fire({
                title: formatMoney(receiptData.value.payment.change),
                text: "Change to return to customer",
                icon: "success",
                confirmButtonText: "OK",
                didOpen: () => {
                    // Focus the OK button when Swal opens
                    const confirmButton = document.querySelector(
                        ".swal2-confirm"
                    ) as HTMLButtonElement;
                    if (confirmButton) {
                        confirmButton.focus();
                    }
                },
            }).then((result) => {
                // Redirect to table-rooms index when OK is clicked
                if (result.isConfirmed) {
                    router.visit(route("table-rooms.index"));
                }
            });
        } catch (error) {
            console.error("Failed to print receipt:", error);

            // Show receipt modal only if thermal printer fails
            showReceiptModal.value = true;

            toast.add({
                severity: "warn",
                summary: "Printer Error",
                detail: "Failed to print receipt. Showing receipt modal instead.",
                life: 3000,
            });

            // Show change amount in Swal only if print was successful
            Swal.fire({
                title: formatMoney(receiptData.value.payment.change),
                text: "Change to return to customer",
                icon: "success",
                confirmButtonText: "OK",
                didOpen: () => {
                    // Focus the OK button when Swal opens
                    const confirmButton = document.querySelector(
                        ".swal2-confirm"
                    ) as HTMLButtonElement;
                    if (confirmButton) {
                        confirmButton.focus();
                    }
                },
            }).then((result) => {
                // Redirect to table-rooms index when OK is clicked
                if (result.isConfirmed) {
                    router.visit(route("table-rooms.index"));
                }
            });
        }
    }
};

// Handle print bill
const handlePrintBill = async () => {
    const response = await httpGet(
        `/api/carts/${page.props.cart?.id}/print-bill`
    );

    if (!response.success) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: response.error || "Failed to fetch bill data",
            life: 3000,
        });
        return;
    }

    billData.value = response.data;

    if (billData.value) {
        const thermalBillData = {
            storeName: page.props.company_info?.company_name,
            branch: billData.value.branch,
            billNumber: billData.value.bill_number,
            tableName: billData.value.table_number,
            cashier: billData.value.cashier,
            items: billData.value.cart_items || [],
            totals: billData.value.totals || {},
        };

        try {
            await thermalPrinter.printBill(thermalBillData);
        } catch (error) {
            console.log("Failed to print bill:", error);
            showBillModal.value = true;
            toast.add({
                severity: "warn",
                summary: "Printer Error",
                detail: "Failed to print billl. Showing Bill modal instead.",
                life: 3000,
            });
        }
    }

    // Populate bill data
    // billData.value = {
    //     billNumber: responseData.bill_number,
    //     date: responseData.cart_date,
    //     tableInfo: responseData.table_number,
    //     cashierName: responseData.cashier?.name,
    //     orderItems: responseData.cart_items,
    //     subtotal: responseData.totals.subtotal,
    //     lessTax: responseData.totals.less_tax,
    //     lessDiscount: responseData.totals.less_discount,
    //     totalAmount: parseFloat(props.total.toFixed(2)),
    // };
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
    const response = await placeOrder(props.tableId, props.cart?.id);

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
                if (!thermalPrinter.isConnected()) {
                    const connected = await thermalPrinter.connectToPrinterType(
                        "kitchen"
                    );
                    if (!connected) {
                        console.warn("Printer not connected, skipping print");
                    } else {
                        await thermalPrinter.printPlacedOrder(
                            response.orderNumber,
                            response.tableRoom?.name || "Table",
                            response.placedOrderItems
                        );
                    }
                } else {
                    await thermalPrinter.printPlacedOrder(
                        response.orderNumber,
                        response.tableRoom?.name || "Table",
                        response.placedOrderItems
                    );
                }
                //redirect back to table-rooms index after printing
                let locationId =
                    response.data?.tableRoom?.table_room_location_id;
                if (locationId) {
                    router.visit(
                        route("table-rooms.index", { locationId: locationId })
                    );
                } else {
                    router.visit(route("table-rooms.index"));
                }
            } catch (printError) {
                console.error("Failed to print order:", printError);
                // Don't block the redirect if print fails
            }
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
</script>
