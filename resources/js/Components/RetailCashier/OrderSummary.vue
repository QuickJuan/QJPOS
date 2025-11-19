<template>
    <aside
        class="h-full lg:h-full flex flex-col bg-gray-50 shadow-lg border-2 border-gray-200 w-full max-h-screen lg:max-h-full"
    >
        <!-- Customer Information -->
        <CustomerInfo :table-info="tableInfo" :cart="cart" />

        <!-- Cart Items Area -->
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Cart Items -->
            <CartItems
                :order-items="orderItems"
                :selected-items-for-discount="selectedItemsForDiscount"
                @toggle-item-for-discount="toggleItemForDiscount"
                @edit-item="handleEdit"
                @delete-item="handleDelete"
                @show-item-modifiers="handleShowItemModifiers"
            />

            <!-- Order Totals -->
            <OrderTotals
                :order-subtotal="orderSubtotal"
                :tax-amount="taxAmount"
                :final-total="props.total"
                :sub-total="props.subTotal"
                :less-tax-total="lessTaxTotal"
                :less-discount-total="lessDiscountTotal"
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
            :sub-total="props.subTotal"
            :total="props.total"
            :less-tax-total="props.lessTaxTotal"
            :less-discount-total="props.lessDiscountTotal"
            :table-info="tableInfo"
            :bill-footer="billFooter"
            :receipt-footer="receiptFooter"
            @update-order-type="updateOrderType"
            @save-order="handleSaveOrder"
            @checkout="handleCheckout"
            @open-discount-modal="openDiscountModal"
            @add-modifier="handleAddModifier"
            @settle-bill="handleSettleBill"
            @print-bill="handlePrintBill"
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
            :tax-rate="taxRate"
            :available-discounts="props.availableDiscounts"
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

const props = defineProps<{
    cart: any;
    tableId: number;
    locationType: string;
    orderItems: any[];
    selectedOrderItem: any;
    availableDiscounts: any[];
    availableModifiers: any[];
    currentTable: any;
    subTotal: number;
    total: number;
    lessTaxTotal: number;
    lessDiscountTotal: number;
    taxRate: number;
    billFooter: any;
    receiptFooter: any;
}>();

const toast = useToast();
const page = usePage<PageProps>();
const confirm = useConfirm();
const emit = defineEmits<{
    selectedOrderType: [value: string];
    showReceipt: [data: any];
    printBill: [];
}>();

console.log(props.locationType);
// State
const selectedOrderType = ref("dine-in");
const tableInfo = ref(props.currentTable);

const showEditModal = ref(false);
const showDiscountModal = ref(false);
const showRequiredReasonModal = ref(false);
const showAddModifierModal = ref(false);
const showItemModifiersModal = ref(false);
const selectedOrderItem = ref(props.selectedOrderItem);
const selectedItemsForDiscount = ref<number[]>([]);
const selectedItemForModifiers = ref<any>(null);
const selectedItemModifiers = ref<any[]>([]);

// Discount state
const appliedDiscount = ref<{
    discountId: string;
    discountName: string;
    selectedItems: number[];
    discountAmount: number;
    discountType: string;
    removeTax?: boolean;
} | null>(null);

// Computed values
const subtotal = computed(() => {
    const items = Array.isArray(props.orderItems) ? props.orderItems : [];
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
    const items = Array.isArray(props.orderItems) ? props.orderItems : [];
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
    props.orderItems.filter((item) =>
        selectedItemsForDiscount.value.includes(item.id)
    )
);

// Methods
const updateOrderType = (type: string) => {
    emit("selectedOrderType", type);
    selectedOrderType.value = type;
};

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
    router.delete(
        route("retail-cashier.cart.delete", { cartItemId: orderItem.id }),
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

const saveEdit = (editedItem: any) => {
    if (editedItem) {
        router.put(
            route("retail-cashier.cart.update", editedItem.id),
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
    if (props.orderItems.length === 0) {
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
    if (props.orderItems.length === 0) {
        toast.add({
            severity: "warn",
            summary: "Warning",
            detail: "No items in cart to checkout",
            life: 3000,
        });
        return;
    }

    router.put(
        route("retail-cashier.cart.place-order", { cartId: data.cart_id }),
        { discountId: data.discount_id }
    );
};

const handleSettleBill = (data: any) => {
    if (props.orderItems.length === 0) {
        toast.add({
            severity: "warn",
            summary: "Warning",
            detail: "No items in cart to settle",
            life: 3000,
        });
        return;
    }

    router.post(
        route("retail-cashier.cart.settle-bill", { cartId: data.cart_id }),
        {
            amount_paid: data.amount_paid,
            total_amount: data.total_amount,
            location_type: selectedOrderType.value,
        },
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Bill settled successfully",
                    life: 3000,
                });
                // Emit event to show receipt modal
                emit("showReceipt", data);
            },
            onError: (errors) => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: page.props.flash.error || "Failed to settle bill",
                    life: 3000,
                });
            },
        }
    );
};

const handleAddModifier = () => {
    showAddModifierModal.value = true;
};

const handleModifierAdded = (modifierData: any) => {
    const selectedItemIds = modifierData.selectedCartItems.map(
        (item: any) => item.id
    );

    router.put(
        route("retail-cashier.cart.apply-modifier", {
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
        route("retail-cashier.cart.void-cart", {
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

const handleAddDiscountToItem = (item: any) => {
    // Select the item for discount
    selectedItemsForDiscount.value = [item.id];
    showDiscountModal.value = true;
    showEditModal.value = false;
};

const handleClearDiscountFromItem = (item: any) => {
    // Clear discount from the item
    router.put(
        route("retail-cashier.cart.clear-discount", { cartItemId: item.id }),
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

watch(
    () => props.locationType,
    (val) => {
        if (val) selectedOrderType.value = val;
    },
    { immediate: true }
);

const handlePrintBill = () => {
    emit("printBill");
};
</script>
