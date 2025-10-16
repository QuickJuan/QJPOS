<template>
    <aside
        class="h-full flex flex-col bg-gray-50 shadow-lg border-2 border-gray-200"
    >
        <!-- Customer Information -->
        <CustomerInfo
            :current-shift="currentShift"
            :customer-info="customerInfo"
        />

        <!-- Cart Items Area -->
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Cart Items -->
            <CartItems
                :order-items="orderItems"
                :selected-items-for-discount="selectedItemsForDiscount"
                @toggle-item-for-discount="toggleItemForDiscount"
                @edit-item="handleEdit"
                @delete-item="handleDelete"
            />

            <!-- Order Totals -->
            <OrderTotals
                :order-subtotal="orderSubtotal"
                :tax-amount="taxAmount"
                :final-total="finalTotal"
                :applied-discount="appliedDiscount"
            />
        </div>

        <!-- Action Buttons -->
        <ActionButtons
            :selected-order-type="selectedOrderType"
            :order-items="orderItems"
            :selected-items-for-discount="selectedItemsForDiscount"
            @update-order-type="updateOrderType"
            @save-order="handleSaveOrder"
            @checkout="handleCheckout"
            @open-discount-modal="openDiscountModal"
        />

        <!-- Edit Item Modal -->
        <EditItemModal
            v-model:visible="showEditModal"
            :selected-order-item="selectedOrderItem"
            @save="saveEdit"
        />

        <!-- Discount Modal -->
        <DiscountModal
            v-model:visible="showDiscountModal"
            :selected-items="selectedItemsForModal"
            :available-discounts="props.availableDiscounts"
            @apply="handleDiscountApplied"
        />

        <Toast />
        <ConfirmPopup />
    </aside>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
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

const props = defineProps<{
    orderItems: any[];
    selectedOrderItem: any;
    availableDiscounts: any[];
}>();

const toast = useToast();
const page = usePage<PageProps>();
const confirm = useConfirm();

// State
const selectedOrderType = ref<string>("dine-in");
const currentShift = ref("12");
const customerInfo = ref({
    name: "Walk-in Customer",
    table: "Table 1",
    guests: 2,
});

const showEditModal = ref(false);
const showDiscountModal = ref(false);
const selectedOrderItem = ref(props.selectedOrderItem);
const selectedItemsForDiscount = ref<number[]>([]);

// Debug: Check what discounts we have from props
console.log("=== ORDER SUMMARY DISCOUNT DEBUG ===");
console.log("Available discounts from props:", props.availableDiscounts);
console.log("Type of availableDiscounts:", typeof props.availableDiscounts);
console.log("Is array:", Array.isArray(props.availableDiscounts));

// Discount state
const appliedDiscount = ref<{
    discountId: string;
    discountName: string;
    selectedItems: number[];
    discountAmount: number;
    discountType: string;
} | null>(null);

// Computed values
const subtotal = computed(() =>
    props.orderItems.reduce(
        (sum, item) =>
            sum +
            parseFloat(item.price || item.average_cost || "0") * item.quantity,
        0
    )
);

const vatableSubtotal = computed(() => {
    return props.orderItems.reduce((sum, item) => {
        const itemPrice = parseFloat(item.price || item.average_cost || "0");
        const quantity = item.quantity;
        const lineTotal = itemPrice * quantity;
        const vatableAmount = lineTotal / 1.12;
        return sum + vatableAmount;
    }, 0);
});

const discountAmount = computed(() => {
    if (!appliedDiscount.value) return 0;
    return appliedDiscount.value.discountAmount;
});

const discountedVatableSubtotal = computed(
    () => vatableSubtotal.value - discountAmount.value
);
const tax = computed(() => discountedVatableSubtotal.value * 0.12);
const total = computed(() => discountedVatableSubtotal.value + tax.value);

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
    selectedOrderType.value = type;
};

const handleEdit = (orderItem: any) => {
    selectedOrderItem.value = orderItem;
    showEditModal.value = true;
};

const handleDelete = (orderItem: any) => {
    confirm.require({
        message: "Are you sure you want to remove this?",
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
            router.delete(route("retail-cashier.cart.delete", orderItem.id), {
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
        },
    });
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
    console.log("Toggle item for discount:", itemId, "checked:", checked);
    if (checked) {
        if (!selectedItemsForDiscount.value.includes(itemId)) {
            selectedItemsForDiscount.value.push(itemId);
        }
    } else {
        selectedItemsForDiscount.value = selectedItemsForDiscount.value.filter(
            (id) => id !== itemId
        );
    }
    console.log("Selected items for discount:", selectedItemsForDiscount.value);
};

const openDiscountModal = () => {
    console.log(
        "Opening discount modal, selected items:",
        selectedItemsForDiscount.value
    );
    showDiscountModal.value = true;
};

const handleDiscountApplied = (discountData: {
    discountId: string;
    discountName: string;
    selectedItems: number[];
    discountAmount: number;
    discountType: string;
}) => {
    appliedDiscount.value = discountData;
    selectedItemsForDiscount.value = [];
    showDiscountModal.value = false;

    toast.add({
        severity: "success",
        summary: "Success",
        detail: `Discount of ₱${discountData.discountAmount.toFixed(
            2
        )} applied successfully`,
        life: 3000,
    });
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

const handleCheckout = () => {
    if (props.orderItems.length === 0) {
        toast.add({
            severity: "warn",
            summary: "Warning",
            detail: "No items in cart to checkout",
            life: 3000,
        });
        return;
    }

    router.visit(route("retail-cashier.preview"));
};
</script>
