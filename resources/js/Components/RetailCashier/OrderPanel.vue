<template>
    <aside
        class="h-full flex flex-col bg-red-400 shadow-lg border-2 border-gray-200"
    >
        <!-- Row 1: Customer Information -->
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-bold text-gray-800">Order Summary</h2>
                <div
                    class="text-sm text-gray-500 font-medium bg-gray-200 px-3 py-1 rounded-full"
                >
                    #Shift: {{ currentShift }}
                </div>
            </div>

            <!-- Customer Info -->
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <UserIcon class="w-4 h-4 text-gray-500" />
                    <span class="text-sm text-gray-600">Customer:</span>
                    <span class="text-sm font-medium text-gray-800">
                        {{ customerInfo.name || "Walk-in Customer" }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <MapPinIcon class="w-4 h-4 text-gray-500" />
                    <span class="text-sm text-gray-600">Table:</span>
                    <span class="text-sm font-medium text-gray-800">
                        {{ customerInfo.table || "No Table" }}
                    </span>
                    <span class="text-xs text-gray-500">•</span>
                    <span class="text-sm text-gray-600">
                        {{ customerInfo.guests || 1 }} guests
                    </span>
                </div>
            </div>
        </div>

        <!-- Row 2: Cart Items (Expandable) -->
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Discount Section -->
            <div class="p-4 border-b border-gray-100 bg-yellow-50">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-medium text-gray-700">
                        Apply Discount
                    </h4>
                    <button
                        @click="openDiscountModal"
                        :disabled="selectedItemsForDiscount.length === 0"
                        class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                    >
                        Apply ({{ selectedItemsForDiscount.length }})
                    </button>
                </div>
            </div>

            <!-- Cart Items Scrollable Area -->
            <div class="flex-1 overflow-auto p-4">
                <div
                    v-if="props.orderItems.length === 0"
                    class="text-center py-8"
                >
                    <ShoppingCartIcon
                        class="w-12 h-12 text-gray-300 mx-auto mb-2"
                    />
                    <p class="text-gray-500">No items in cart</p>
                    <p class="text-sm text-gray-400">
                        Add items to get started
                    </p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="item in props.orderItems"
                        :key="item.id"
                        class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors"
                    >
                        <input
                            type="checkbox"
                            :checked="
                                selectedItemsForDiscount.includes(item.id)
                            "
                            @change="(e) => toggleItemForDiscount(item.id, (e.target as HTMLInputElement).checked)"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 mt-1"
                        />

                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-gray-900 truncate">
                                {{ item.name }}
                            </h4>
                            <p class="text-sm text-gray-600">
                                Qty: {{ item.quantity }} ×
                                {{ formatMoney(item.price) }}
                            </p>

                            <!-- Selected Options -->
                            <div
                                v-if="
                                    item.selected_options &&
                                    item.selected_options.length > 0
                                "
                                class="mt-1 space-y-1"
                            >
                                <div
                                    v-for="option in item.selected_options"
                                    :key="option.id"
                                    class="text-xs text-blue-600"
                                >
                                    + {{ option.product.name }} (+{{
                                        formatMoney(option.price)
                                    }})
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="font-semibold text-gray-900">
                                {{
                                    formatMoney(
                                        (item.quantity * item.price).toFixed(2)
                                    )
                                }}
                            </p>
                            <div class="flex gap-1 mt-1">
                                <button
                                    @click="handleEdit(item)"
                                    class="p-1 text-gray-400 hover:text-blue-600 transition-colors"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button
                                    @click="handleDelete(item)"
                                    class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">{{
                            formatMoney(orderSubtotal.toFixed(2))
                        }}</span>
                    </div>
                    <div
                        v-if="appliedDiscount"
                        class="flex justify-between text-sm text-green-600"
                    >
                        <span
                            >Discount ({{ appliedDiscount.discountName }})</span
                        >
                        <span
                            >-{{
                                formatMoney(
                                    appliedDiscount.discountAmount.toFixed(2)
                                )
                            }}</span
                        >
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax (12%)</span>
                        <span class="font-medium">{{
                            formatMoney(taxAmount.toFixed(2))
                        }}</span>
                    </div>
                    <hr class="border-gray-300" />
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total</span>
                        <span>{{ formatMoney(finalTotal.toFixed(2)) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Action Buttons -->
        <div class="p-4 border-t border-gray-200 bg-white">
            <div class="space-y-3">
                <!-- Order Type Buttons -->
                <div class="grid grid-cols-3 gap-2">
                    <button
                        v-for="type in orderTypes"
                        :key="type.value"
                        @click="selectedOrderType = type.value"
                        :class="[
                            'py-2 px-3 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-1',
                            selectedOrderType === type.value
                                ? type.activeClass
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                        ]"
                    >
                        <component :is="type.icon" class="w-4 h-4" />
                        {{ type.label }}
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-3">
                    <button
                        @click="handleSaveOrder"
                        :disabled="props.orderItems.length === 0"
                        class="py-3 px-4 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                    >
                        Save Order
                    </button>
                    <button
                        @click="handleCheckout"
                        :disabled="props.orderItems.length === 0"
                        class="py-3 px-4 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 disabled:bg-green-400 disabled:cursor-not-allowed transition-colors"
                    >
                        Checkout
                    </button>
                </div>
            </div>
        </div>
    </aside>

    <!-- Edit Dialog -->
    <Dialog
        v-model:visible="visible"
        modal
        :header="`Edit ${selectedOrderItem?.name || ''}`"
        :style="{ width: '25rem' }"
    >
        <div class="flex flex-col gap-4 mb-4">
            <label for="username" class="font-semibold w-24">Quantity</label>
            <InputNumber
                v-model="selectedOrderItem.quantity"
                showButtons
                buttonLayout="horizontal"
                style="width: 1rem"
                :min="0"
                :max="99"
            >
                <template #incrementicon>
                    <span class="pi pi-plus" />
                </template>
                <template #decrementicon>
                    <span class="pi pi-minus" />
                </template>
            </InputNumber>
        </div>
        <div class="flex justify-end gap-2">
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                @click="visible = false"
            />
            <Button type="button" label="Save" @click="saveEdit" />
        </div>
    </Dialog>

    <!-- Discount Modal -->
    <Dialog
        v-model:visible="showDiscountModal"
        modal
        header="Apply Discount"
        :style="{ width: '40rem' }"
        class="bg-white"
    >
        <div class="space-y-6">
            <!-- Selected Items List -->
            <div>
                <h4 class="text-lg font-semibold text-gray-900 mb-4">
                    Selected Items
                </h4>
                <div class="space-y-3 max-h-48 overflow-y-auto">
                    <div
                        v-for="item in props.orderItems.filter((item) =>
                            selectedItemsForDiscount.includes(item.id)
                        )"
                        :key="item.id"
                        class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border"
                    >
                        <div>
                            <p class="font-medium text-gray-900">
                                {{ item.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Qty: {{ item.quantity }} ×
                                {{ formatMoney(item.price) }}
                            </p>
                        </div>
                        <p class="font-semibold text-gray-900">
                            {{
                                formatMoney(
                                    (item.quantity * item.price).toFixed(2)
                                )
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Discount Selection -->
            <div>
                <h4 class="text-lg font-semibold text-gray-900 mb-4">
                    Select Discount
                </h4>
                <div class="space-y-3">
                    <select
                        v-model="selectedDiscountForModal"
                        class="w-full text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-0"
                    >
                        <option value="">Choose a discount...</option>
                        <option
                            v-for="discount in availableDiscounts"
                            :key="discount.id"
                            :value="discount.id"
                        >
                            {{ discount.discount_name }} ({{
                                discount.type === "percentage"
                                    ? discount.amount + "%"
                                    : "₱" + discount.amount
                            }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Discount Preview -->
            <div
                v-if="selectedDiscountForModal"
                class="bg-blue-50 rounded-lg p-4 border border-blue-200"
            >
                <h5 class="font-semibold text-blue-900 mb-2">
                    Discount Preview
                </h5>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-blue-700"
                            >Selected Items Subtotal:</span
                        >
                        <span class="font-medium text-blue-900">{{
                            formatMoney(selectedItemsSubtotal.toFixed(2))
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-blue-700">Discount Amount:</span>
                        <span class="font-medium text-green-600"
                            >-{{
                                formatMoney(calculatedDiscountAmount.toFixed(2))
                            }}</span
                        >
                    </div>
                    <div
                        class="flex justify-between border-t border-blue-300 pt-1"
                    >
                        <span class="text-blue-700 font-medium"
                            >After Discount:</span
                        >
                        <span class="font-bold text-blue-900">{{
                            formatMoney(
                                (
                                    selectedItemsSubtotal -
                                    calculatedDiscountAmount
                                ).toFixed(2)
                            )
                        }}</span>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button
                    type="button"
                    label="Cancel"
                    severity="secondary"
                    @click="showDiscountModal = false"
                />
                <Button
                    type="button"
                    label="Apply Discount"
                    @click="applySelectedDiscount"
                    :disabled="!selectedDiscountForModal"
                    class="bg-green-600 hover:bg-green-700"
                />
            </div>
        </template>
    </Dialog>

    <Toast />
    <ConfirmPopup />
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import OrderItemList from "../OrderItemList.vue";
import { Button, Dialog, InputNumber, useConfirm, useToast } from "primevue";
import { formatMoney } from "@/Utils/FormatMoney";
import PageProps from "@/Types/PageProps";
import DiscountSelector from "./DiscountSelector.vue";
import {
    UserIcon,
    MapPinIcon,
    ShoppingCartIcon,
    PencilIcon,
    TrashIcon,
    HomeIcon,
    ShoppingBagIcon,
    TruckIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps<{
    orderItems: any[];
    selectedOrderItem: any;
    availableDiscounts: any[];
}>();

const toast = useToast();
const page = usePage<PageProps>();
const confirm = useConfirm();
const visible = ref(false);
const tab = ref<"lines" | "payments">("lines");

// New reactive data for the improved layout
const selectedOrderType = ref<string>("dine-in");
const currentShift = ref("12");
const customerInfo = ref({
    name: "Walk-in Customer",
    table: "Table 1",
    guests: 2,
});

// Order types configuration
const orderTypes = ref([
    {
        value: "dine-in",
        label: "Dine-in",
        icon: HomeIcon,
        activeClass: "bg-blue-600 text-white",
    },
    {
        value: "takeout",
        label: "Takeout",
        icon: ShoppingBagIcon,
        activeClass: "bg-green-600 text-white",
    },
    {
        value: "delivery",
        label: "Delivery",
        icon: TruckIcon,
        activeClass: "bg-orange-600 text-white",
    },
]);

// Discount state
const appliedDiscount = ref<{
    discountId: string;
    discountName: string;
    selectedItems: number[];
    discountAmount: number;
    discountType: string;
} | null>(null);

const selectedItemsForDiscount = ref<number[]>([]);
const showDiscountModal = ref(false);
const selectedDiscountForModal = ref("");

const availableDiscounts = computed(() => props.availableDiscounts || []);

const selectedItemsSubtotal = computed(() => {
    return props.orderItems
        .filter((item) => selectedItemsForDiscount.value.includes(item.id))
        .reduce((sum, item) => {
            const itemPrice = parseFloat(
                item.price || item.average_cost || "0"
            );
            return sum + itemPrice * item.quantity;
        }, 0);
});

const calculatedDiscountAmount = computed(() => {
    if (!selectedDiscountForModal.value) return 0;

    const discount = availableDiscounts.value.find(
        (d) => d.id == selectedDiscountForModal.value
    );
    if (!discount) return 0;

    // Calculate discount based on vatable subtotal of selected items
    const selectedItemsVatableSubtotal = props.orderItems
        .filter((item) => selectedItemsForDiscount.value.includes(item.id))
        .reduce((sum, item) => {
            const itemPrice = parseFloat(
                item.price || item.average_cost || "0"
            );
            const quantity = item.quantity;
            const lineTotal = itemPrice * quantity;
            // Calculate vatable amount: total / (1 + vat_rate/100)
            const vatableAmount = lineTotal / 1.12;
            return sum + vatableAmount;
        }, 0);

    if (discount.type === "percentage") {
        return selectedItemsVatableSubtotal * (discount.amount / 100);
    } else {
        // Fixed amount discount
        return Math.min(discount.amount, selectedItemsVatableSubtotal);
    }
});

const subtotal = computed(() =>
    props.orderItems.reduce(
        (sum, item) =>
            sum +
            parseFloat(item.price || item.average_cost || "0") * item.quantity,
        0
    )
);

// Calculate VAT-exclusive amount for discount calculation
const vatableSubtotal = computed(() => {
    return props.orderItems.reduce((sum, item) => {
        const itemPrice = parseFloat(item.price || item.average_cost || "0");
        const quantity = item.quantity;
        const lineTotal = itemPrice * quantity;

        // If VAT inclusive, calculate vatable amount: total / (1 + vat_rate/100)
        // Using 12% VAT rate as mentioned
        const vatableAmount = lineTotal / 1.12;
        return sum + vatableAmount;
    }, 0);
});

// Calculate discount amount
const discountAmount = computed(() => {
    if (!appliedDiscount.value) return 0;
    return appliedDiscount.value.discountAmount;
});

// Calculate final amounts after discount
const discountedVatableSubtotal = computed(
    () => vatableSubtotal.value - discountAmount.value
);
const tax = computed(() => discountedVatableSubtotal.value * 0.12); // 12% VAT
const total = computed(() => discountedVatableSubtotal.value + tax.value);

// Aliases for template compatibility
const orderSubtotal = computed(() => subtotal.value);
const taxAmount = computed(() => tax.value);
const finalTotal = computed(() => total.value);

const selectedOrderItem = ref(props.selectedOrderItem);

const handleEdit = (orderItem: any) => {
    visible.value = true;
    selectedOrderItem.value = orderItem;
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

const handleSelectionChange = (selectedItems: any[]) => {
    // TODO: Implement selection change handling
    console.log("Selected items:", selectedItems);
};

const saveEdit = () => {
    if (selectedOrderItem.value) {
        console.log(selectedOrderItem.value);

        router.put(
            route("retail-cashier.cart.update", selectedOrderItem.value.id),
            {
                quantity: selectedOrderItem.value.quantity,
                selected_options:
                    selectedOrderItem.value.selected_options || [],
            },
            {
                onSuccess: () => {
                    visible.value = false;

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

const handleDiscountApplied = (discountData: {
    discountId: string;
    discountName: string;
    selectedItems: number[];
    discountAmount: number;
    discountType: string;
}) => {
    appliedDiscount.value = discountData;
    selectedItemsForDiscount.value = []; // Clear selections after applying
    toast.add({
        severity: "success",
        summary: "Success",
        detail: `Discount of ₱${discountData.discountAmount.toFixed(
            2
        )} applied successfully`,
        life: 3000,
    });
};

const openDiscountModal = () => {
    showDiscountModal.value = true;
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

const applySelectedDiscount = () => {
    if (!selectedDiscountForModal.value) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Please select a discount",
            life: 3000,
        });
        return;
    }

    const discount = availableDiscounts.value.find(
        (d) => d.id == selectedDiscountForModal.value
    );
    if (!discount) return;

    // Calculate discount amount based on vatable subtotal of selected items
    const selectedItemsVatableSubtotal = props.orderItems
        .filter((item) => selectedItemsForDiscount.value.includes(item.id))
        .reduce((sum, item) => {
            const itemPrice = parseFloat(
                item.price || item.average_cost || "0"
            );
            const quantity = item.quantity;
            const lineTotal = itemPrice * quantity;
            // Calculate vatable amount: total / (1 + vat_rate/100)
            const vatableAmount = lineTotal / 1.12;
            return sum + vatableAmount;
        }, 0);

    let discountAmount = 0;
    if (discount.type === "percentage") {
        discountAmount = selectedItemsVatableSubtotal * (discount.amount / 100);
    } else {
        // Fixed amount discount
        discountAmount = Math.min(
            discount.amount,
            selectedItemsVatableSubtotal
        );
    }

    appliedDiscount.value = {
        discountId: selectedDiscountForModal.value,
        discountName: discount.discount_name,
        selectedItems: [...selectedItemsForDiscount.value],
        discountAmount: discountAmount,
        discountType: discount.type,
    };

    // Reset state
    showDiscountModal.value = false;
    selectedDiscountForModal.value = "";
    selectedItemsForDiscount.value = [];

    toast.add({
        severity: "success",
        summary: "Success",
        detail: `Discount of ₱${discountAmount.toFixed(
            2
        )} applied successfully`,
        life: 3000,
    });
};

// New methods for the improved layout
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

    // Save order logic here
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

    // Navigate to checkout/payment page
    router.visit(route("retail-cashier.preview"));
};
</script>
