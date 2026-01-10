<template>
    <aside
        class="h-full lg:h-full flex flex-col bg-gray-50 shadow-lg border-2 border-gray-200 w-full max-h-screen lg:max-h-full"
    >
        <!-- Customer Information -->
        <CustomerInfo
            :table-info="tableInfo"
            :cart="cart"
            @select-table="$emit('selectTable')"
            @customer-selected="handleCustomerSelected"
        />

        <!-- Cart Items Area -->
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Cart Items -->
            <CartItems
                :order-items="orderItems"
                :selected-items-for-discount="selectedItemsForDiscount"
                @edit-item="handleEdit"
                @delete-item="handleDelete"
                @show-item-modifiers="handleShowItemModifiers"
                @remove-modifier="handleRemoveModifier"
                @toggle-item-for-discount="toggleItemForDiscount"
            />

            <!-- Barcode Scanner Section -->
            <div class="px-4 py-3 bg-white border-t border-gray-200">
                <label class="block text-xs font-semibold text-gray-700 mb-2">
                    Scan Product
                </label>
                <div class="flex gap-2 items-center">
                    <label
                        class="text-xs font-medium text-gray-600 whitespace-nowrap"
                    >
                        Qty:
                    </label>
                    <input
                        v-model.number="barcodeQuantity"
                        type="number"
                        min="0.01"
                        step="0.01"
                        class="w-16 px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                    />
                    <input
                        ref="barcodeInputRef"
                        v-model="barcodeInput"
                        type="text"
                        placeholder="Scan barcode or type qty*"
                        class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        @keydown="handleBarcodeKeydown"
                        @keyup.enter="handleBarcodeSearch"
                    />
                    <button
                        @click="handleBarcodeSearch"
                        class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 transition-colors flex-shrink-0"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Order Totals -->
            <OrderTotals
                :order-subtotal="orderSubtotal"
                :tax-amount="taxAmount"
                :final-total="orderItemTotalDue"
                :sub-total="orderItemSubTotal"
                :less-tax-total="orderItemLessTax"
                :less-discount-total="orderItemLessDiscount"
                :applied-discount="appliedDiscount"
                :service-charge="serviceCharge"
            />
        </div>

        <!-- Action Buttons -->
        <ActionButtons
            :order-items="orderItems"
            :table-id="tableId"
            :location-type="locationTypeValue"
            :selected-items-for-discount="selectedItemsForDiscount"
            :cart="cart"
            :total-amount="finalTotal"
            :applied-discount="appliedDiscount"
            :sub-total="orderItemSubTotal"
            :total="orderItemTotalDue"
            :less-tax-total="orderItemLessTax"
            :less-discount-total="orderItemLessDiscount"
            :table-info="tableInfo"
            :general-settings="props.generalSettings"
            @save-order="handleSaveOrder"
            @checkout="handleCheckout"
            @open-discount-modal="openDiscountModal"
            @add-modifier="handleAddModifier"
            @transfer-order-items="handleTransferOrderItems"
            @view-table="handleViewTable"
            @end-of-shift="handleEndOfShift"
            @update-order-type="(type: string) => {
                selectedOrderType = type;
            }"
        />

        <!-- Edit Item Modal -->
        <EditItemModal
            v-model:visible="showEditModal"
            :selected-order-item="selectedOrderItem"
            @save="saveEdit"
            @add-discount="handleAddDiscountToItem"
            @clear-discount="handleClearDiscountFromItem"
            @add-modifier="handleAddModifierToItem"
        />

        <!-- Discount Modal -->
        <DiscountModal
            v-model:visible="showDiscountModal"
            :selected-items="selectedItemsForModal"
            :tax-rate="props.taxRate"
            :available-discounts="page.props.available_discounts"
            :table-pax="props.currentTable?.number_of_pax"
            @apply="handleApplyDiscount"
        />

        <!-- Required Reason Modal -->
        <RequiredReasonModal
            v-model:visible="showRequiredReasonModal"
            :order-item="selectedOrderItem"
            @submit="handleRequiredReason"
        />

        <!-- Add Modifier Modal -->
        <AddModifierModal
            v-model:visible="showAddModifierModal"
            :selected-items="selectedItemsForModal"
            @add="handleModifierAdded"
        />

        <!-- Item Modifiers Modal -->
        <ItemModifiersModal
            v-model:visible="showItemModifiersModal"
            :item="selectedItemForModifiers"
            :modifiers="selectedItemModifiers"
        />

        <!-- Close Session Modal -->
        <CloseSessionModal
            :show-close-dialog="showCloseSessionModal"
            :open-session="page.props.current_cashier_session"
            :current-user="page.props.auth.user"
            @close-modal="showCloseSessionModal = false"
            @confirm-close-session="handleConfirmCloseSession"
        />

        <!-- Session Summary Modal -->
        <SessionSummaryModal
            :show-session-summary-modal="showSessionSummaryModal"
            :session-summary="sessionSummaryData"
            @close-modal="handleConfirmSessionSummary"
        />

        <!-- Transfer Order Items -->
        <TransferOrderItemsModal
            v-model:visible="showTransferOrderItemsModal"
            :occupied-tables="occupiedTables"
            :selected-target="selectedTransferTarget"
            @selectTarget="selectTransferTarget"
            @confirmTransfer="confirmTransferOrderItems"
        />

        <Toast />
        <ConfirmPopup />
    </aside>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useConfirm, useToast } from "primevue";
import PageProps from "@/Types/PageProps";

// Import components
import CustomerInfo from "./OrderSummary/CustomerInfo.vue";
import CartItems from "./OrderSummary/CartItems.vue";
import OrderTotals from "./OrderSummary/OrderTotals.vue";
import ActionButtons from "./OrderSummary/ActionButtons.vue";
import EditItemModal from "./OrderSummary/EditItemModal.vue";
import DiscountModal from "./OrderSummary/DiscountModal.vue";
import RequiredReasonModal from "./OrderSummary/RequiredReasonModal.vue";
import AddModifierModal from "./OrderSummary/AddModifierModal.vue";
import ItemModifiersModal from "./OrderSummary/ItemModifiersModal.vue";
import CloseSessionModal from "@/Pages/Resto/Partials/CloseSessionModal.vue";
import SessionSummaryModal from "@/Pages/Resto/Partials/SessionSummaryModal.vue";
import axios from "axios";
import TransferOrderItemsModal from "@/Pages/Resto/Partials/TransferOrderItemsModal.vue";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import { useCashier } from "@/composables/useCashier";

const props = defineProps<{
    cart: any;
    tableId: number | string;
    locationType: string | null;
    selectedOrderItem: any;
    // availableDiscounts: any[];
    // available_modifiers: any[];
    tax_rate: number;
    currentTable: any;
    generalSettings: {
        company_name: string;
        company_address: string;
        company_phone: string;
        company_logo: string;
    };
}>();

const toast = useToast();
const page = usePage<PageProps>();
const confirm = useConfirm();
const { closeShift } = useCashier();
const emit = defineEmits<{
    changeOrderType: [value: string];
    showReceipt: [data: any];
    printBill: [];
    selectTable: [];
    redirectToTables: [];
}>();

const locationTypeValue = computed(() => props.locationType || "dine-in");

const availableDiscounts = computed(() => {
    return page.props.available_discounts || [];
});

// Get order items from cart
const orderItems = computed(() => {
    return props.cart?.cart_items || page.props.sharedCart?.cart_items || [];
});

const serviceCharge = computed(() => {
    return props.cart?.service_charge || page.props.cart?.service_charge || 0;
});

const orderItemSubTotal = computed(() => {
    return orderItems.value.reduce((sum, item) => {
        const itemPrice = parseFloat(item.price || "0");
        const quantity = item.quantity;
        return sum + itemPrice * quantity;
    }, 0);
});

const orderItemLessTax = computed(() => {
    return orderItems.value.reduce((sum, item) => {
        const lessTax = parseFloat(item.less_tax || "0");
        return sum + lessTax;
    }, 0);
});

const orderItemLessDiscount = computed(() => {
    return orderItems.value.reduce((sum, item) => {
        const lessDiscount = parseFloat(item.discount_amount || "0");
        return sum + lessDiscount;
    }, 0);
});

const orderItemTotalDue = computed(() => {
    return (
        parseFloat(orderItemSubTotal.value) +
        parseFloat(serviceCharge.value) -
        (orderItemLessTax.value + orderItemLessDiscount.value)
    );
});

// // State - Initialize from URL query parameter
// const getInitialOrderType = () => {
//     const urlParams = new URLSearchParams(window.location.search);
//     let orderType = urlParams.get("orderType");
//     alert("orderType from URL: eto intial " + orderType);
//     return orderType;
// };

const selectedOrderType = ref("");
const tableInfo = computed(() => props.cart?.table_room || props.currentTable);

const showEditModal = ref(false);
const showDiscountModal = ref(false);
const showRequiredReasonModal = ref(false);
const showAddModifierModal = ref(false);
const showTransferOrderItemsModal = ref(false);
const showItemModifiersModal = ref(false);
const showCloseSessionModal = ref(false);
const showSessionSummaryModal = ref(false);
const selectedOrderItem = ref(props.selectedOrderItem);
const selectedItemsForDiscount = ref<number[]>([]);
const selectedItemForModifiers = ref<any>(null);
const selectedItemModifiers = ref<any[]>([]);
const sessionSummaryData = ref(null);
const occupiedTables = ref<any[]>([]);
const selectedTransferTarget = ref<any>(null);

// Barcode scanner state
const barcodeInput = ref("");
const barcodeQuantity = ref(1);
const barcodeInputRef = ref<HTMLInputElement | null>(null);

// Discount state
const appliedDiscount = ref<{
    discountId: string;
    discountName: string;
    selectedItems: number[];
    discountAmount: number;
    discountType: string;
    removeTax?: boolean;
} | null>(null);

watch(
    () => selectedOrderType.value,
    (newVal) => {
        emit("changeOrderType", newVal);
    }
);

// Computed values
const subtotal = computed(() => {
    const items = Array.isArray(orderItems.value) ? orderItems.value : [];
    const total = items.reduce((sum, item) => {
        const hasOptions = item.selected_options?.length > 0;

        if (hasOptions) {
            const total = item.selected_options.reduce(
                (optSum: number, option: any) =>
                    optSum + parseFloat(option.price || 0),
                0
            );
            return sum + total;
        }

        return sum + parseFloat(item.price || "0") * item.quantity;
    }, 0);

    return parseFloat(total.toFixed(2));
});

const discountAmount = computed(() => {
    if (!appliedDiscount.value) return 0;
    return appliedDiscount.value.discountAmount || 0;
});

const tax = computed(() => {
    const items = Array.isArray(orderItems.value) ? orderItems.value : [];
    return items.reduce((sum, item) => {
        const itemPrice = parseFloat(item.price || item.average_cost || "0");
        const quantity = item.quantity;
        const lineTotal = itemPrice * quantity;
        return sum + (lineTotal - lineTotal / 1.12);
    }, 0);
});

const total = computed(() => subtotal.value + tax.value - discountAmount.value);

// Aliases for template compatibility
const orderSubtotal = computed(() => subtotal.value);
const taxAmount = computed(() => tax.value);
const finalTotal = computed(() => total.value + serviceCharge.value);

const selectedItemsForModal = computed(() =>
    orderItems.value.filter((item) =>
        selectedItemsForDiscount.value.includes(item.id)
    )
);

// Get current open session from props
const openSession = computed(() => {
    return props.openSession;
});

// Session summary from props
const sessionSummary = computed(() => {
    return props.sessionSummary;
});

const handleEdit = (orderItem: any) => {
    selectedOrderItem.value = orderItem;
    showEditModal.value = true;
};

const handleDelete = (orderItem: any) => {
    // Check if this is a child item (has parent_id)
    const isChildItem = orderItem.parent_id !== null;

    confirm.require({
        message: isChildItem
            ? "Are you sure you want to remove this option?"
            : "Are you sure you want to remove this item?",
        icon: "pi pi-exclamation-triangle",
        rejectProps: {
            label: "Cancel",
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: "Remove",
        },
        accept: () => {
            if (orderItem.placed_order) {
                showRequiredReasonModal.value = true;
                selectedOrderItem.value = orderItem;
            } else {
                removeOrder(orderItem);
            }
        },
    });
};

const removeOrder = (orderItem: any) => {
    router.delete(route("resto.cart.delete", { cartItemId: orderItem.id }), {
        onSuccess: () => {
            toast.add({
                severity: "success",
                summary: "Success",
                detail: page.props.flash.success,
                life: 3000,
            });
        },
        onError: (errors) => {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: page.props.flash.error,
                life: 3000,
            });
        },
    });
};

const saveEdit = (editedItem: any) => {
    if (editedItem) {
        router.put(
            route("resto.cart.update-item", editedItem.id),
            {
                quantity: editedItem.quantity,
                selected_options: editedItem.selected_options || [],
            },
            {
                onSuccess: () => {
                    showEditModal.value = false;
                    toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: page.props.flash.success,
                        life: 3000,
                    });
                },
                onError: (errors) => {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: page.props.flash.error,
                        life: 3000,
                    });
                },
            }
        );
    }
};

const toggleItemForDiscount = (itemId: number, checked: boolean) => {
    if (checked) {
        if (!selectedItemsForDiscount.value.includes(itemId)) {
            selectedItemsForDiscount.value.push(itemId);
        }
    } else {
        selectedItemsForDiscount.value = selectedItemsForDiscount.value.filter(
            (id) => id !== itemId
        );
    }
};

const openDiscountModal = () => {
    showDiscountModal.value = true;
};

const handleSaveOrder = () => {
    if (orderItems.value.length === 0) {
        toast.add({
            severity: "warn",
            summary: "Warning",
            detail: "No items in cart to save",
            life: 3000,
        });
        return;
    }

    toast.add({
        severity: "success",
        summary: "Success",
        detail: "Order saved successfully",
        life: 3000,
    });
};

const handleCheckout = (data: any) => {
    if (orderItems.value.length === 0) {
        toast.add({
            severity: "warn",
            summary: "Warning",
            detail: "No items in cart to checkout",
            life: 3000,
        });
        return;
    }

    router.put(route("resto.cart.place-order", { cartId: data.cart_id }), {
        discountId: data.discount_id,
    });
};

const handleAddModifier = () => {
    showAddModifierModal.value = true;
};

const handleTransferOrderItems = async () => {
    // Fetch occupied tables
    try {
        // Get all table rooms and extract occupied tables
        const response = await axios.get(route("table-rooms.get-all-tables"));
        const tableRooms = response.data.data || [];

        // Flatten all tables from all locations and filter occupied ones, excluding current table
        occupiedTables.value = tableRooms.filter(
            (table: any) =>
                table.status === "occupied" && table.id !== tableInfo.value.id
        );

        selectedTransferTarget.value = null;
        showTransferOrderItemsModal.value = true;
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to load occupied tables",
            life: 3000,
        });
    }
};

const handleModifierAdded = (modifierData: any) => {
    const selectedItemIds = modifierData.selectedCartItems.map(
        (item: any) => item.id
    );

    router.put(
        route("resto.cart.apply-modifier", {
            cartItemIds: selectedItemIds,
        }),
        {
            modifierOptions: modifierData.modifierOptions,
            specialInstructions: modifierData.specialInstructions,
        },
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Added successfully",
                    life: 3000,
                });

                showAddModifierModal.value = false;
                selectedItemsForDiscount.value = [];
            },
            onError: (errors) => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: page.props.flash.error || "Failed to add modifier",
                    life: 3000,
                });
            },
        }
    );
};

const handleRequiredReason = (data: any) => {
    // If it's a placed order with approver and OTP, use the approval workflow
    if (data.orderItem?.placed_order && data.approverId && data.otpCode) {
        axios
            .post(route("resto.cart.delete-with-approval"), {
                cart_item_id: data.orderItem.id,
                approver_id: data.approverId,
                otp_code: data.otpCode,
                reason: data.reason,
            })
            .then((response) => {
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Item deleted successfully with approval",
                    life: 3000,
                });
                // Refresh the cart
                router.reload();
            })
            .catch((error) => {
                let errorMessage = "Failed to delete item";
                if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                }
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errorMessage,
                    life: 3000,
                });
            });
    } else {
        // Regular void operation without OTP
        router.put(
            route("resto.cart.void-cart", {
                cartItemId: data.orderItem.id,
            }),
            {
                reason: data.reason,
            },
            {
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: page.props.flash.success,
                        life: 3000,
                    });
                },
                onError: (errors) => {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: page.props.flash.error,
                        life: 3000,
                    });
                },
            }
        );
    }
};

const handleApplyDiscount = (discountData: any) => {
    appliedDiscount.value = discountData;
    // Clear selected items after applying discount
    selectedItemsForDiscount.value = [];
};

const handleAddDiscountToItem = (item: any) => {
    // Select the item for discount
    selectedItemsForDiscount.value = [item.id];
    showDiscountModal.value = true;
    showEditModal.value = false;
};

const handleClearDiscountFromItem = (item: any) => {
    // Clear discount from the item
    router.put(
        route("resto.cart.clear-discount", { cartItemId: item.id }),
        {},
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Discount cleared successfully",
                    life: 3000,
                });
                showEditModal.value = false;
            },
            onError: (errors) => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        page.props.flash.error || "Failed to clear discount",
                    life: 3000,
                });
            },
        }
    );
};

const handleAddModifierToItem = (item: any) => {
    // Select the item for modifier
    selectedItemsForDiscount.value = [item.id];
    showAddModifierModal.value = true;
    showEditModal.value = false;
};

const handleShowItemModifiers = (item: any) => {
    selectedItemForModifiers.value = item;
    selectedItemModifiers.value = item.meta_data || [];
    showItemModifiersModal.value = true;
};

const handleRemoveModifier = (item: any, modifierValue: number) => {
    router.put(
        route("resto.cart.remove-modifier"),
        {
            cartItemId: item.id,
            modifierValue: modifierValue,
        },
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Modifier removed successfully",
                    life: 3000,
                });
            },
            onError: (errors) => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to remove modifier",
                    life: 3000,
                });
            },
        }
    );
};

// watch(
//     () => props.locationType,
//     (val) => {
//         alert("nag change order type");
//         if (val) selectedOrderType.value = val;
//     },
//     { immediate: true }
// );

// const handlePrintBill = () => {
//     emit("printBill");
// };

const handleViewTable = () => {
    router.visit(route("table-rooms.index"));
};

const handleEndOfShift = () => {
    // Handle end of shift logic - show close session modal
    showCloseSessionModal.value = true;
};

const handleConfirmCloseSession = async (data: any) => {
    const payload = {
        cash_denomination_details: data.currencyBreakdown,
        cash_denomination: data.totalCashCounted,
        shift_no: page.props.current_cashier_session?.id,
        cashier_id: page.props.current_cashier_session?.cashier_id,
    };

    const result = await closeShift(payload);

    console.log("close shift result :", result);

    showCloseSessionModal.value = false;

    if (result.success) {
        sessionSummaryData.value = result.session;

        // Try to print using thermal printer first
        try {
            // Load receipt printer config
            await thermalPrinter.loadPrinterConfig("receipt");

            // Connect to printer if not already connected
            if (!thermalPrinter.isConnected()) {
                const connected = await thermalPrinter.connectToPrinterType(
                    "receipt"
                );
                if (!connected) {
                    // Printer not available, show modal
                    showSessionSummaryModal.value = true;
                    toast.add({
                        severity: "warn",
                        summary: "Printer Unavailable",
                        detail: "Session closed successfully. Printer not available - please print manually.",
                        life: 5000,
                    });
                    return;
                }
            }

            // Print session summary
            await thermalPrinter.printSessionSummary(result.session);

            // Show success message and redirect to home
            toast.add({
                severity: "success",
                summary: "Success",
                detail: "Session closed and printed successfully",
                life: 3000,
            });

            // Redirect to home after successful print
            setTimeout(() => {
                router.visit(route("home"));
            }, 1500);
        } catch (error) {
            console.error("Failed to print session summary:", error);
            // Show modal as fallback
            showSessionSummaryModal.value = true;
            toast.add({
                severity: "warn",
                summary: "Print Failed",
                detail: "Session closed successfully. Failed to print - please print manually.",
                life: 5000,
            });
        }
    } else {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: result.error,
            life: 3000,
        });
    }
};

const handleConfirmSessionSummary = () => {
    showSessionSummaryModal.value = false;
    // Redirect to home after closing modal
    router.visit(route("home"));
};

const selectTransferTarget = (table: any) => {
    selectedTransferTarget.value = table;
};

const confirmTransferOrderItems = () => {
    if (
        !selectedTransferTarget.value ||
        selectedItemsForDiscount.value.length === 0
    ) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Please select a target table and ensure items are selected",
            life: 3000,
        });
        return;
    }

    // Implement the transfer logic here
    // This would typically call an API to transfer the selected items
    router.post(
        route("resto.cart.transfer-items"),
        {
            targetTableId: selectedTransferTarget.value.id,
            cartItemIds: selectedItemsForDiscount.value,
        },
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: `Items transferred to ${selectedTransferTarget.value.name}`,
                    life: 3000,
                });
                showTransferOrderItemsModal.value = false;
                selectedItemsForDiscount.value = [];
            },
            onError: (errors) => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errors?.message || "Failed to transfer items",
                    life: 3000,
                });
            },
        }
    );
};

const handleRedirectToTables = () => {
    router.visit(route("resto.tables"));
};

// Barcode search handler
const handleBarcodeSearch = async () => {
    if (!barcodeInput.value.trim()) {
        toast.add({
            severity: "warn",
            summary: "Invalid Input",
            detail: "Please enter a barcode to search",
            life: 3000,
        });
        return;
    }

    if (!props.tableId) {
        toast.add({
            severity: "warn",
            summary: "No Table Selected",
            detail: "Please select a table first before scanning products",
            life: 3000,
        });
        barcodeInput.value = "";
        return;
    }

    try {
        // Call the search barcode API
        const response = await axios.post(route("resto.cart.search-barcode"), {
            barcode: barcodeInput.value.trim(),
            quantity: barcodeQuantity.value,
            table_id: props.tableId,
            order_type: "dine-in",
        });

        if (response.data.success) {
            toast.add({
                severity: "success",
                summary: "Product Added",
                detail: `${response.data.data.name} added to cart`,
                life: 3000,
            });

            // Refresh the page to show updated cart
            router.reload({ only: ["cart", "cartItems"] });

            // Clear inputs
            barcodeInput.value = "";
            barcodeQuantity.value = 1;

            // Focus back to barcode input
            if (barcodeInputRef.value) {
                setTimeout(() => barcodeInputRef.value?.focus(), 100);
            }
        }
    } catch (error: any) {
        const errorMessage =
            error.response?.data?.message ||
            "Product not found or failed to add to cart";

        toast.add({
            severity: "error",
            summary: "Search Failed",
            detail: errorMessage,
            life: 4000,
        });

        // Clear input on error
        barcodeInput.value = "";

        // Focus back to barcode input
        if (barcodeInputRef.value) {
            setTimeout(() => barcodeInputRef.value?.focus(), 100);
        }
    }
};

// Handle keyboard input for barcode field
const handleBarcodeKeydown = (event: KeyboardEvent) => {
    // Check if asterisk (*) is pressed
    if (event.key === "*") {
        event.preventDefault();

        const currentValue = barcodeInput.value.trim();

        // Check if the current value is a number (including decimals)
        const numValue = parseFloat(currentValue);

        if (!isNaN(numValue) && numValue > 0) {
            // Move the number to quantity field (round to 2 decimal places)
            barcodeQuantity.value = Math.round(numValue * 100) / 100;
            // Clear the barcode input
            barcodeInput.value = "";

            // Show feedback toast
            toast.add({
                severity: "info",
                summary: "Quantity Set",
                detail: `Quantity set to ${barcodeQuantity.value}`,
                life: 1500,
            });
        } else {
            // If not a valid number, just clear the barcode field
            barcodeInput.value = "";
        }

        // Keep focus on barcode input
        if (barcodeInputRef.value) {
            setTimeout(() => barcodeInputRef.value?.focus(), 50);
        }
    }
};

// Handle customer selection
const handleCustomerSelected = async (customer: any | null) => {
    if (!props.cart?.id) {
        toast.add({
            severity: "warn",
            summary: "No Cart",
            detail: "Please create a cart first before selecting a customer.",
            life: 3000,
        });
        return;
    }

    try {
        await axios.post(route("resto.cart.update-customer"), {
            cart_id: props.cart.id,
            customer_id: customer?.id || null,
        });

        toast.add({
            severity: "success",
            summary: customer ? "Customer Selected" : "Customer Cleared",
            detail: customer
                ? `${customer.customer_name} will earn points from this order`
                : "This order will be for a walk-in customer",
            life: 3000,
        });

        // Reload the page to get updated cart data
        router.reload({ only: ["cart"] });
    } catch (error) {
        console.error("Failed to update customer:", error);
        toast.add({
            severity: "error",
            summary: "Update Failed",
            detail: "Failed to update customer. Please try again.",
            life: 3000,
        });
    }
};

// Update cashier state in localStorage
const updateCashierStateInLocalStorage = (
    tableId: number | null,
    cartId?: number | null
) => {
    try {
        const cashierStateKey = "quickjuan_cashier_state";
        const existingState = localStorage.getItem(cashierStateKey);
        let cashierState = existingState ? JSON.parse(existingState) : {};

        // Update the tableId and optionally cartId in the cashier state
        cashierState.tableId = tableId;
        if (cartId) {
            cashierState.cartId = cartId;
        }
        cashierState.lastUpdated = new Date().toISOString();

        // Save back to localStorage
        localStorage.setItem(cashierStateKey, JSON.stringify(cashierState));

        console.log(
            "Updated cashier state with tableId:",
            tableId,
            "cartId:",
            cartId
        );
    } catch (error) {
        console.error("Failed to update cashier state in localStorage:", error);
    }
};
</script>
