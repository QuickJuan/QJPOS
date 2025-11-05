<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        header="Apply Discount"
        :style="{ width: '70rem' }"
        class="bg-white"
    >
        <div class="grid grid-cols-2 gap-6" style="height: 500px">
            <!-- Left Side: Selected Items -->
            <div class="flex flex-col" style="height: 500px">
                <h4
                    class="text-lg font-semibold text-secondary-900 mb-4 flex-shrink-0"
                >
                    Selected Items ({{ selectedItems.length }})
                </h4>

                <!-- Scrollable Items List -->
                <div class="overflow-y-auto pr-2 mb-4" style="height: 320px">
                    <div class="space-y-3">
                        <div
                            v-for="item in selectedItems"
                            :key="item.id"
                            class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border hover:bg-gray-100 transition-colors"
                        >
                            <div class="flex-1">
                                <p class="font-medium text-secondary-900">
                                    {{ item.name }}
                                </p>
                                <p class="text-sm text-secondary-600">
                                    Qty: {{ item.quantity }} ×
                                    {{ formatMoney(item.price) }}
                                </p>
                            </div>
                            <p class="font-semibold text-secondary-900 ml-4">
                                {{
                                    formatMoney(
                                        (item.quantity * item.price).toFixed(2)
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Items Subtotal - Always Visible at Bottom -->
                <div
                    class="border-t pt-3 space-y-2 flex-shrink-0 bg-white"
                    style="height: 120px"
                >
                    <div class="flex justify-between text-lg font-semibold">
                        <span class="text-secondary-700">Subtotal:</span>
                        <span class="text-secondary-900">
                            {{ formatMoney(selectedItemsSubtotal.toFixed(2)) }}
                        </span>
                    </div>

                    <!-- Discount Amount -->
                    <div
                        v-if="selectedDiscountId && selectedDiscount"
                        class="flex justify-between text-md"
                    >
                        <span class="text-success-700">
                            {{ getDiscountLabel(selectedDiscount) }}:
                        </span>
                        <span class="font-medium text-success-600">
                            -{{
                                formatMoney(calculatedDiscountAmount.toFixed(2))
                            }}
                        </span>
                    </div>

                    <!-- Final Total -->
                    <div
                        v-if="selectedDiscountId && selectedDiscount"
                        class="flex justify-between text-lg font-bold border-t pt-2"
                    >
                        <span class="text-secondary-900">Total:</span>
                        <span class="text-secondary-900"> {{ discountTotal }} </span>
                    </div>
                </div>
            </div>

            <!-- Right Side: Discount Options -->
            <div class="flex flex-col" style="height: 500px">
                <h4
                    class="text-lg font-semibold text-secondary-900 mb-4 flex-shrink-0"
                >
                    Available Discounts
                </h4>

                <div class="overflow-y-auto pr-2 flex-1">
                    <!-- Percentage Discounts -->
                    <div v-if="percentageDiscounts.length > 0" class="mb-6">
                        <h5
                            class="text-md font-medium text-secondary-700 mb-3 flex items-center gap-2"
                        >
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            Percentage Discounts
                        </h5>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                v-for="discount in percentageDiscounts"
                                :key="discount.id"
                                @click="selectDiscount(discount.id)"
                                :class="[
                                    'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
                                    selectedDiscountId === String(discount.id)
                                        ? 'border-primary bg-primary-50'
                                        : 'border-gray-200 bg-white hover:border-gray-300',
                                ]"
                            >
                                <div class="space-y-2">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-2xl font-bold text-blue-600"
                                        >
                                            {{ discount.amount }}%
                                        </span>
                                        <div
                                            v-if="
                                                discount.requires_customer_info
                                            "
                                            class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded"
                                        >
                                            Customer Info
                                        </div>
                                    </div>
                                    <h6
                                        class="font-medium text-secondary-900 text-sm"
                                    >
                                        {{ discount.discount_name }}
                                    </h6>
                                    <p class="text-xs text-secondary-600">
                                        {{
                                            discount.description ||
                                            "Percentage discount on selected items"
                                        }}
                                    </p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Fixed Amount Discounts -->
                    <div v-if="fixedDiscounts.length > 0" class="mb-6">
                        <h5
                            class="text-md font-medium text-secondary-700 mb-3 flex items-center gap-2"
                        >
                            <div class="w-3 h-3 bg-green-500 rounded-full" />
                            Fixed Amount Discounts
                        </h5>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                v-for="discount in fixedDiscounts"
                                :key="discount.id"
                                @click="selectDiscount(discount.id)"
                                :class="[
                                    'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
                                    selectedDiscountId === String(discount.id)
                                        ? 'border-primary bg-primary-50'
                                        : 'border-gray-200 bg-white hover:border-gray-300',
                                ]"
                            >
                                <div class="space-y-2">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-lg font-bold text-green-600"
                                        >
                                            ₱{{ discount.amount }}
                                        </span>
                                        <div
                                            v-if="
                                                discount.requires_customer_info
                                            "
                                            class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded"
                                        >
                                            Customer Info
                                        </div>
                                    </div>
                                    <h6
                                        class="font-medium text-secondary-900 text-sm"
                                    >
                                        {{ discount.discount_name }}
                                    </h6>
                                    <p class="text-xs text-secondary-600">
                                        {{
                                            discount.description ||
                                            "Fixed amount discount"
                                        }}
                                    </p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Other Discounts (if any other types exist) -->
                    <div v-if="otherDiscounts.length > 0">
                        <h5
                            class="text-md font-medium text-secondary-700 mb-3 flex items-center gap-2"
                        >
                            <div class="w-3 h-3 bg-purple-500 rounded-full" />
                            Other Discounts
                        </h5>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                v-for="discount in otherDiscounts"
                                :key="discount.id"
                                @click="selectDiscount(discount.id)"
                                :class="[
                                    'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
                                    selectedDiscountId === String(discount.id)
                                        ? 'border-primary bg-primary-50'
                                        : 'border-gray-200 bg-white hover:border-gray-300',
                                ]"
                            >
                                <div class="space-y-2">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-sm font-bold text-purple-600"
                                        >
                                            {{ discount.type }}
                                        </span>
                                        <div
                                            v-if="
                                                discount.requires_customer_info
                                            "
                                            class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded"
                                        >
                                            Customer Info
                                        </div>
                                    </div>
                                    <h6
                                        class="font-medium text-secondary-900 text-sm"
                                    >
                                        {{ discount.discount_name }}
                                    </h6>
                                    <p class="text-xs text-secondary-600">
                                        {{
                                            discount.description ||
                                            "Special discount"
                                        }}
                                    </p>
                                </div>
                            </button>
                        </div>
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
                    @click="$emit('update:visible', false)"
                />
                <Button
                    type="button"
                    label="Apply Discount"
                    @click="applyDiscount"
                    :disabled="!selectedDiscountId"
                    class="bg-success-600 hover:bg-success-700"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { Button, Dialog } from "primevue";
import { formatMoney } from "@/Utils/FormatMoney";

const props = defineProps<{
    visible: boolean;
    selectedItems: any[];
    availableDiscounts: any[] | { data: any[] };
}>();

const emit = defineEmits<{
    apply: [
        discountData: {
            discountId: string;
            discountName: string;
            selectedItems: number[];
            discountAmount: number;
            discountType: string;
            removeTax?: boolean;
        }
    ];
    "update:visible": [value: boolean];
}>();

const selectedDiscountId = ref("");

// Helper function to extract discounts array from props
const getDiscountsArray = () => {
    if (Array.isArray(props.availableDiscounts)) {
        return props.availableDiscounts;
    } else if (
        props.availableDiscounts &&
        "data" in props.availableDiscounts &&
        Array.isArray(props.availableDiscounts.data)
    ) {
        return props.availableDiscounts.data;
    }
    return [];
};

// Helper function to get discount by ID
const getDiscountById = (discountId: string | number) => {
    const discounts = getDiscountsArray();
    return discounts.find((d) => d.id == discountId);
};
const selectedDiscount = computed(() => {
    return getDiscountById(selectedDiscountId.value);
});

const selectedItemsSubtotal = computed(() => {
    return props.selectedItems.reduce((sum, item) => {
        const itemPrice = parseFloat(item.price || item.average_cost || "0");
        return sum + itemPrice * item.quantity;
    }, 0);
});

const vatExemptSales = ref(0);
const calculatedDiscountAmount = computed(() => {
    if (!selectedDiscount.value) return 0;

    const discount = selectedDiscount.value;
    const isSeniorDiscount = discount.discount_name
        ?.toLowerCase()
        .includes("senior");

    // Calculate subtotal for selected items
    const selectedItemsTotal = props.selectedItems.reduce((sum, item) => {
        const itemPrice = parseFloat(item.price || item.average_cost || "0");
        return sum + itemPrice * item.quantity;
    }, 0);

    if (discount.remove_tax && isSeniorDiscount) {
        // Special calculation for Senior Citizen Discount (20% on VAT-exempt amount)
        const vatableAmount = selectedItemsTotal / 1.12; // Remove VAT first
        vatExemptSales.value = vatableAmount;

        return vatableAmount * 0.2; // 20% discount on VAT-exempt amount
    } else if (discount.remove_tax) {
        // Remove tax first, then apply discount (general case)
        const vatableAmount = selectedItemsTotal / 1.12;
        if (discount.type === "percentage") {
            return vatableAmount * (discount.amount / 100);
        } else {
            return Math.min(discount.amount, vatableAmount);
        }
    } else {
        // Standard calculation based on vatable subtotal
        const selectedItemsVatableSubtotal = props.selectedItems.reduce(
            (sum, item) => {
                const itemPrice = parseFloat(
                    item.price || item.average_cost || "0"
                );
                const quantity = item.quantity;
                const lineTotal = itemPrice * quantity;
                const vatableAmount = lineTotal / 1.12;
                return sum + vatableAmount;
            },
            0
        );

        if (discount.type === "percentage") {
            return selectedItemsVatableSubtotal * (discount.amount / 100);
        } else {
            return Math.min(discount.amount, selectedItemsVatableSubtotal);
        }
    }
});

// Group discounts by type (using props data)
const percentageDiscounts = computed(() => {
    const discounts = getDiscountsArray();
    const percentage = discounts.filter((d) => d.type === "percentage");
    return percentage;
});

const fixedDiscounts = computed(() => {
    const discounts = getDiscountsArray();
    const fixed = discounts.filter(
        (d) => d.type === "fixed" || d.type === "amount"
    );
    return fixed;
});

const otherDiscounts = computed(() => {
    const discounts = getDiscountsArray();
    const other = discounts.filter(
        (d) =>
            d.type !== "percentage" && d.type !== "fixed" && d.type !== "amount"
    );

    return other;
});

// Get discount label based on type
const getDiscountLabel = (discount: any) => {
    const isSeniorDiscount = discount.discount_name?.toLowerCase().includes('senior') ||
                           discount.type === 'senior';

    if (isSeniorDiscount && discount.remove_tax) {
        return 'Senior Citizen Discount (20%)';
    }

    return `Discount (${discount.discount_name})`;
};

const discountTotal = computed(() => {
    const discount = selectedDiscount.value;
    const isSeniorDiscount = discount?.discount_name?.toLowerCase().includes("senior") ||
                           discount?.type === 'senior';

    if (discount?.remove_tax && isSeniorDiscount) {
        return formatMoney((vatExemptSales.value - calculatedDiscountAmount.value).toFixed(2));
    } else {
        return formatMoney((selectedItemsSubtotal.value - calculatedDiscountAmount.value).toFixed(2));
    }
});

// Select discount method
const selectDiscount = (discountId: string | number) => {
    selectedDiscountId.value = String(discountId);
};

const applyDiscount = () => {
    if (!selectedDiscount.value) return;

    const discountData = {
        discountId: selectedDiscountId.value,
        discountName: selectedDiscount.value.discount_name,
        selectedItems: props.selectedItems.map((item) => item.id),
        discountAmount: calculatedDiscountAmount.value,
        discountType: selectedDiscount.value.type,
        removeTax: selectedDiscount.value.remove_tax,
    };

    emit("apply", discountData);
    emit("update:visible", false);
    selectedDiscountId.value = "";
};
</script>
