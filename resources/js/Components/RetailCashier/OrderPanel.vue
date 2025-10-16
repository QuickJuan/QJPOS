<template>
    <aside
        class="col-span-1 lg:col-span-4 bg-white rounded-xl shadow-lg p-6 flex flex-col lg:sticky lg:top-6 border border-slate-200"
    >
        <div class="flex items-start justify-between mb-6">
            <div>
                <div
                    class="text-xl font-bold text-slate-800 flex items-center gap-3"
                >
                    <div
                        class="w-3 h-3 bg-green-500 rounded-full animate-pulse"
                    />
                    Order Summary
                </div>
                <div class="text-sm text-slate-600 mt-1">
                    Table 1 • 2 guests
                </div>
            </div>
            <div
                class="text-sm text-slate-500 font-medium bg-slate-100 px-3 py-1 rounded-full"
            >
                #Shift:12
            </div>
        </div>

        <!-- Discount Section -->
        <div class="mb-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-medium text-gray-700">
                    Apply Discount
                </h4>
                <button
                    @click="openDiscountModal"
                    :disabled="selectedItemsForDiscount.length === 0"
                    class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                >
                    Apply Discount ({{ selectedItemsForDiscount.length }})
                </button>
            </div>
        </div>

        <div
            class="flex-1 overflow-auto border border-slate-200 rounded-lg p-4 mb-6 max-h-[40vh] bg-slate-50"
        >
            <div v-if="tab === 'lines'">
                <div class="space-y-2">
                    <div
                        v-for="item in props.orderItems"
                        :key="item.id"
                        class="flex items-center space-x-3 p-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors"
                    >
                        <input
                            type="checkbox"
                            :checked="
                                selectedItemsForDiscount.includes(item.id)
                            "
                            @change="(e) => toggleItemForDiscount(item.id, (e.target as HTMLInputElement).checked)"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                        />
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">
                                {{ item.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Qty: {{ item.quantity }} ×
                                {{ formatMoney(item.price) }}
                            </p>
                            <!-- Display selected options as list -->
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
                                    + {{ option.product.name }} ({{ formatMoney(option.price) }})
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
                        </div>
                        <div class="flex space-x-2">
                            <button
                                @click="handleEdit(item)"
                                class="p-1 text-gray-400 hover:text-blue-600 transition-colors"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                            </button>
                            <button
                                @click="handleDelete(item)"
                                class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-else
                class="flex items-center justify-center h-40 text-slate-400 font-medium"
            >
                No payments view
            </div>
        </div>

        <div class="border-t border-slate-200 pt-6 mt-4 space-y-4">
            <div class="flex justify-between items-center">
                <div class="text-sm text-slate-600 font-medium">Subtotal</div>
                <div class="font-semibold text-slate-800">
                    {{ formatMoney(subtotal.toFixed(2)) }}
                </div>
            </div>
            <div
                v-if="discountAmount > 0"
                class="flex justify-between items-center"
            >
                <div class="text-sm text-green-600 font-medium">Discount</div>
                <div class="font-semibold text-green-600">
                    -{{ formatMoney(discountAmount.toFixed(2)) }}
                </div>
            </div>
            <div class="flex justify-between items-center">
                <div class="text-sm text-slate-600 font-medium">VAT (12%)</div>
                <div class="font-semibold text-slate-800">
                    {{ formatMoney(tax.toFixed(2)) }}
                </div>
            </div>
            <div
                class="flex justify-between items-center text-xl font-bold text-slate-900 border-t border-slate-200 pt-4"
            >
                <div>Total</div>
                <div>{{ formatMoney(total.toFixed(2)) }}</div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-6">
                <button
                    class="py-3 px-4 bg-slate-100 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-200 hover:shadow-md transition-all"
                >
                    Place to table
                </button>
                <button
                    class="py-3 px-4 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 hover:shadow-md transition-all"
                >
                    Proceed to checkout
                </button>
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
</script>
