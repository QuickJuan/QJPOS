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
                    :disabled="!hasItemsToPlace"
                    class="px-4 py-2.5 bg-success-600 text-white rounded-lg font-semibold hover:bg-success-700 transition-colors text-sm whitespace-nowrap disabled:bg-gray-300 disabled:text-gray-600 disabled:cursor-not-allowed"
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
            :service-charge-type="props.serviceChargeType"
            :service-charge-label="props.serviceChargeLabel"
            @save-order="handleSaveOrder"
            @open-discount-modal="handleApplyDiscount"
            @add-modifier="handleAddModifier"
            @print-bill="handlePrintBill"
            @reprint-order="handleReprintOrder"
            @view-table="handleViewTable"
            @printer-config="handlePrinterConfig"
            @end-of-shift="handleEndOfShift"
            @transfer-order-items="handleTransferOrderItems"
            @open-service-charge="handleOpenServiceCharge"
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
    serviceChargeType?: string | null;
    serviceChargeLabel?: string;
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
    setServiceCharge: [];
}>();

// Use applied discount from props
const appliedDiscount = computed(() => props.appliedDiscount);

// Check if there are items that can be placed (not yet placed)
const hasItemsToPlace = computed(() => {
    return props.orderItems.some((group) =>
        (group.cartItems ?? group.items ?? []).some(
            (item: any) => !item.placed_order,
        ),
    );
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
    const raw = page.props?.auth?.user?.user_interface ?? "";
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

const handleOpenServiceCharge = () => {
    emit("setServiceCharge");
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
    if (!hasItemsToPlace.value) {
        toast.add({
            severity: "warn",
            summary: "No New Items",
            detail: "Add items before placing an order.",
            life: 2500,
        });
        return;
    }

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

const placedBatches = computed(() => {
    const seen = new Set<string>();
    const batches: Array<{
        batchNumber: string;
        servingNumber: string | null;
    }> = [];
    for (const group of props.orderItems) {
        const items = group.cartItems ?? group.items ?? [];
        for (const item of items) {
            if (
                item.placed_order &&
                item.batch_number &&
                !seen.has(String(item.batch_number))
            ) {
                seen.add(String(item.batch_number));
                batches.push({
                    batchNumber: String(item.batch_number),
                    servingNumber: item.servingNumber || null,
                });
            }
        }
    }
    // Sort descending so the latest batch appears first
    return batches.sort(
        (a, b) => Number(b.batchNumber) - Number(a.batchNumber),
    );
});

const handleReprintOrder = async () => {
    let batchNumber: string | null = null;

    try {
        const apiResponse = await httpGet(
            route("resto.cart.reprint-batches", { limit: 30 }),
        );

        const apiPayload: any = apiResponse?.data;
        const apiBatchesRaw = Array.isArray(apiPayload?.data)
            ? apiPayload.data
            : [];

        const combined = [
            ...apiBatchesRaw.map((b: any) => ({
                batchNumber: String(b.batch_number ?? b.batchNumber ?? ""),
                servingNumber: b.serving_number ?? b.servingNumber ?? null,
                placedOrderTime:
                    b.placed_order_time ?? b.placedOrderTime ?? null,
            })),
            ...placedBatches.value.map((b) => ({
                ...b,
                placedOrderTime: null,
            })),
        ].filter((b) => b.batchNumber);

        const seen = new Set<string>();
        const batches = combined.filter((b) => {
            if (seen.has(b.batchNumber)) return false;
            seen.add(b.batchNumber);
            return true;
        });

        batches.sort((a, b) => {
            const na = Number(a.batchNumber);
            const nb = Number(b.batchNumber);
            if (Number.isFinite(na) && Number.isFinite(nb)) return nb - na;
            return String(b.batchNumber).localeCompare(String(a.batchNumber));
        });

        if (batches.length === 0) {
            await Swal.fire({
                icon: "info",
                title: "Re-print Order",
                text: "No recent batches found to re-print.",
            });
            return;
        }

        const formatPlacedTime = (raw: any): string | null => {
            if (!raw) return null;
            const date = new Date(raw);
            if (Number.isNaN(date.getTime())) return null;
            return date.toLocaleString(undefined, {
                month: "short",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
            });
        };

        const listHtml = batches
            .map((b, index) => {
                const serving = b.servingNumber
                    ? `Serving #${String(b.servingNumber)}`
                    : "Serving #—";
                const placed = formatPlacedTime(b.placedOrderTime);
                const checked = index === 0 ? "checked" : "";

                return `
                    <label class="reprint-item">
                        <input class="reprint-radio" type="radio" name="reprintBatch" value="${b.batchNumber}" ${checked} />
                        <div class="reprint-main">
                            <div class="reprint-title">
                                <span class="reprint-serving">${serving}</span>
                                <span class="reprint-batch">Batch #${b.batchNumber}</span>
                            </div>
                            ${
                                placed
                                    ? `<div class="reprint-sub">Placed ${placed}</div>`
                                    : `<div class="reprint-sub">Recently placed order</div>`
                            }
                        </div>
                    </label>
                `;
            })
            .join("");

        const result = await Swal.fire({
            title: "Re-print Order",
            html: `
                <div class="reprint-help">Select a batch to re-print</div>
                <div class="reprint-list">${listHtml}</div>
            `,
            showCancelButton: true,
            confirmButtonText: "Re-print",
            focusConfirm: false,
            customClass: {
                popup: "reprint-popup",
                confirmButton: "reprint-confirm",
                cancelButton: "reprint-cancel",
            },
            buttonsStyling: false,
            didOpen: () => {
                const popup = Swal.getPopup();
                if (!popup) return;

                const style = document.createElement("style");
                style.textContent = `
                    .swal2-popup.reprint-popup { padding: 22px 22px 18px; border-radius: 18px; }
                    .reprint-help { font-size: 13px; color: #6b7280; margin-top: -4px; margin-bottom: 14px; }
                    .reprint-list { display: flex; flex-direction: column; gap: 10px; max-height: 320px; overflow: auto; padding-right: 4px; }
                    .reprint-item { display: flex; gap: 12px; align-items: flex-start; padding: 12px 12px; border: 1px solid #e5e7eb; border-radius: 14px; background: #ffffff; cursor: pointer; transition: box-shadow 140ms ease, border-color 140ms ease, transform 140ms ease, background 140ms ease; }
                    .reprint-item:hover { border-color: #c7d2fe; box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08); transform: translateY(-1px); }
                    .reprint-radio { margin-top: 3px; accent-color: #4f46e5; }
                    .reprint-main { flex: 1; min-width: 0; }
                    .reprint-title { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
                    .reprint-serving { font-weight: 800; color: #0f172a; }
                    .reprint-batch { font-size: 11px; font-weight: 800; color: #4338ca; background: #eef2ff; border: 1px solid #c7d2fe; padding: 2px 9px; border-radius: 999px; }
                    .reprint-sub { margin-top: 4px; font-size: 12px; color: #64748b; }
                    .reprint-item.is-selected { border-color: #4f46e5; background: #f5f3ff; }
                    .reprint-confirm { background: #4f46e5; color: white; font-weight: 700; border-radius: 10px; padding: 10px 16px; min-width: 110px; }
                    .reprint-confirm:hover { background: #4338ca; }
                    .reprint-cancel { background: #f1f5f9; color: #0f172a; font-weight: 700; border-radius: 10px; padding: 10px 16px; min-width: 110px; }
                    .reprint-cancel:hover { background: #e2e8f0; }
                `;
                popup.appendChild(style);

                const updateSelected = () => {
                    const items = popup.querySelectorAll<HTMLLabelElement>(
                        ".reprint-item",
                    );
                    items.forEach((item) =>
                        item.classList.remove("is-selected"),
                    );
                    const checked = popup.querySelector<HTMLInputElement>(
                        'input[name="reprintBatch"]:checked',
                    );
                    if (checked) {
                        const label = checked.closest(
                            ".reprint-item",
                        ) as HTMLLabelElement | null;
                        label?.classList.add("is-selected");
                    }
                };

                popup.addEventListener("change", (event) => {
                    const target = event.target as HTMLElement;
                    if (
                        target &&
                        (target as HTMLInputElement).name === "reprintBatch"
                    ) {
                        updateSelected();
                    }
                });

                updateSelected();
            },
            preConfirm: () => {
                const popup = Swal.getPopup();
                const selected = popup?.querySelector<HTMLInputElement>(
                    'input[name="reprintBatch"]:checked',
                );
                if (!selected?.value) {
                    Swal.showValidationMessage("Please select a batch");
                    return null;
                }
                return selected.value;
            },
        });

        if (!result.isConfirmed || !result.value) return;
        batchNumber = String(result.value);
    } catch (error) {
        console.error("Failed to load reprint batches:", error);
        toast.add({
            severity: "error",
            summary: "Re-print Failed",
            detail: "Unable to load recent batches. Please try again.",
            life: 4000,
        });
        return;
    }

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
