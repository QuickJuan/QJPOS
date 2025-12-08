<template>
    <aside
        class="h-full lg:h-full flex flex-col bg-gray-50 shadow-lg border-2 border-gray-200 w-full max-h-screen lg:max-h-full"
    >
        <!-- Customer Information -->
        <CustomerInfo
            :table-info="tableInfo"
            :cart="cart"
            @select-table="$emit('selectTable')"
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

            <!-- Order Totals -->
            <OrderTotals
                :order-subtotal="orderSubtotal"
                :tax-amount="taxAmount"
                :final-total="orderItemTotalDue"
                :sub-total="orderItemSubTotal"
                :less-tax-total="orderItemLessTax"
                :less-discount-total="orderItemLessDiscount"
                :applied-discount="appliedDiscount"
            />
        </div>

        <!-- Action Buttons -->
        <ActionButtons
            :selected-order-type="selectedOrderType"
            :order-items="orderItems"
            :table-id="tableId"
            :location-type="locationType"
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
            @settle-bill="handleSettleBill"
            @print-bill="handlePrintBill"
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
            :modifiers="props.availableModifiers"
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
            :open-session="openSession"
            :session-summary="sessionSummary"
            :current-user="page.props.auth.user"
            :general-settings="props.generalSettings"
            @close-modal="showCloseSessionModal = false"
            @confirm-close-session="handleConfirmCloseSession"
        />

        <!-- Session Summary Modal -->
        <SessionSummaryModal
            :show-session-summary-modal="showSessionSummaryModal"
            :open-session="openSession"
            :session-summary="sessionSummaryData"
            :current-user="page.props.auth.user"
            :general-settings="props.generalSettings"
            @close-modal="showSessionSummaryModal = false"
            @confirm-close="handleConfirmSessionSummary"
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

const props = defineProps<{
    cart: any;
    tableId: number;
    locationType: string;
    selectedOrderItem: any;
    availableDiscounts: any[];
    availableModifiers: any[];
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
const emit = defineEmits<{
    changeOrderType: [value: string];
    showReceipt: [data: any];
    printBill: [];
    selectTable: [];
    redirectToTables: [];
}>();

// Get order items from cart
const orderItems = computed(() => {
    return props.cart?.cart_items || page.props.sharedCart?.cart_items || [];
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
        const lessDiscount = parseFloat(item.less_discount || "0");
        return sum + lessDiscount;
    }, 0);
});

const orderItemTotalDue = computed(() => {
    return (
        orderItemSubTotal.value -
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
const showItemModifiersModal = ref(false);
const showCloseSessionModal = ref(false);
const showSessionSummaryModal = ref(false);
const selectedOrderItem = ref(props.selectedOrderItem);
const selectedItemsForDiscount = ref<number[]>([]);
const selectedItemForModifiers = ref<any>(null);
const selectedItemModifiers = ref<any[]>([]);
const sessionSummaryData = ref(null);

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
const finalTotal = computed(() => total.value);

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

const handleSettleBill = (data: any) => {
    if (orderItems.value.length === 0) {
        toast.add({
            severity: "warn",
            summary: "Warning",
            detail: "No items in cart to settle",
            life: 3000,
        });
        return;
    }
};

const handleAddModifier = () => {
    showAddModifierModal.value = true;
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
};

const handleApplyDiscount = (discountData: any) => {
    appliedDiscount.value = discountData;
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

const handlePrintBill = () => {
    emit("printBill");
};

const handleViewTable = () => {
    router.visit(route("table-rooms.index"));
};

const handleEndOfShift = () => {
    // Handle end of shift logic - show close session modal
    showCloseSessionModal.value = true;
};

const handleConfirmCloseSession = (data: any) => {
    router.post(
        route("resto.session.close"),
        {
            cash_denomination_details: data.denominationData,
            cash_denomination: data.totalCashCounted,
        },
        {
            onSuccess: () => {
                showCloseSessionModal.value = false;
                // Fetch session summary after successful close
                axios
                    .get(route("resto.api.session-summary"))
                    .then((response) => {
                        sessionSummaryData.value = response.data;
                        showSessionSummaryModal.value = true;
                        toast.add({
                            severity: "success",
                            summary: "Success",
                            detail: "Session closed successfully",
                            life: 3000,
                        });
                    })
                    .catch((error) => {
                        console.error("Failed to fetch session summary", error);
                        toast.add({
                            severity: "error",
                            summary: "Error",
                            detail: "Session closed but failed to load summary",
                            life: 3000,
                        });
                    });
            },
            onError: (errors) => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errors.error || "Failed to close session",
                    life: 3000,
                });
            },
        }
    );
};

const handleConfirmSessionSummary = () => {
    showSessionSummaryModal.value = false;
    // Redirect to home after confirming the session summary
    router.visit(route("home"));
};

const handleRedirectToTables = () => {
    router.visit(route("resto.tables"));
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
