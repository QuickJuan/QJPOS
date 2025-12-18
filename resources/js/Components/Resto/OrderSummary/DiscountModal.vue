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
                            v-for="(item, index) in selectedItems"
                            :key="item.id"
                            class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border hover:bg-gray-100 transition-colors"
                        >
                            <div class="flex-1">
                                <p class="font-medium text-secondary-900">
                                    {{ item.name }}
                                </p>
                                <div class="text-sm text-secondary-600">
                                    <p>
                                        Qty: {{ item.quantity }} ×
                                        {{ formatMoney(item.price) }}
                                    </p>
                                    <p v-if="lessTaxValue[index]">
                                        Less Tax:
                                        {{
                                            formatMoney(
                                                lessTaxValue[index] || 0
                                            )
                                        }}
                                    </p>
                                    <p v-if="lessDiscountValue[index]">
                                        Less Discount:
                                        {{
                                            formatMoney(
                                                lessDiscountValue[index] || 0
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right ml-4">
                                <p class="font-semibold text-secondary-900">
                                    {{
                                        formatMoney(
                                            (
                                                item.quantity * item.price
                                            ).toFixed(2)
                                        )
                                    }}
                                </p>
                            </div>
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

                    <!-- Total Less Tax -->
                    <div
                        class="flex justify-between text-md"
                        v-if="totalLessTax"
                    >
                        <span class="text-success-700"> Less Tax: </span>
                        <span class="font-medium text-success-600">
                            -{{ formatMoney(totalLessTax.toFixed(2)) }}
                        </span>
                    </div>

                    <!-- Total Less Discount -->
                    <div
                        class="flex justify-between text-md"
                        v-if="selectedDiscountId && selectedDiscount"
                    >
                        <span class="text-success-700">
                            Less Discount ({{
                                selectedDiscount.discount_name
                            }}):
                        </span>
                        <span class="font-medium text-success-600">
                            -{{ formatMoney(totalLessDiscount.toFixed(2)) }}
                        </span>
                    </div>

                    <!-- Final Total -->
                    <div
                        v-if="selectedDiscountId && selectedDiscount"
                        class="flex justify-between text-lg font-bold border-t pt-2"
                    >
                        <span class="text-secondary-900">Total:</span>
                        <span class="text-secondary-900">
                            {{ discountTotal }}
                        </span>
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
                                @click="selectDiscount(discount)"
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
                                @click="selectDiscount(discount)"
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
                                @click="selectDiscount(discount)"
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
import { Button, Dialog, useToast } from "primevue";
import { formatMoney } from "@/Utils/FormatMoney";
import { route } from "ziggy-js";
import { router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import PageProps from "@/Types/PageProps";

const props = defineProps<{
    visible: boolean;
    selectedItems: any[];
    taxRate: number;
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
const toast = useToast();
const page = usePage<PageProps>();

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
        const itemPrice = parseFloat(item.price || "0");
        return sum + itemPrice * item.quantity;
    }, 0);
});

const discountTotal = ref(null);
const previewData = ref(null);
const taxRate = ref(page.props.tax_rate);
const lessTaxValue = ref<number[]>([]);
const lessDiscountValue = ref<number[]>([]);

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

// Select discount method
const selectDiscount = (discount: any) => {
    selectedDiscountId.value = String(discount.id);

    const calculatedDiscountAmount = calculateDiscountAmount(discount);

    lessTaxValue.value = calculatedDiscountAmount.map((d) => d?.lessTax || 0);
    lessDiscountValue.value = calculatedDiscountAmount.map(
        (d) => d?.discountAmount || 0
    );

    discountTotal.value = formatMoney(
        (
            selectedItemsSubtotal.value -
            (totalLessTax.value + totalLessDiscount.value)
        ).toFixed(2)
    );

    // Set previewData with calculated values
    previewData.value = {
        discount_amount: lessDiscountValue.value.reduce(
            (sum, val) => sum + val,
            0
        ),
        item_discounts: calculatedDiscountAmount.map((d, index) => ({
            id: props.selectedItems[index].id,
            discount_amount: d.discountAmount,
        })),
    };
};

const totalLessTax = computed(() =>
    lessTaxValue.value.reduce((sum, val) => sum + (val || 0), 0)
);

const totalLessDiscount = computed(() =>
    lessDiscountValue.value.reduce((sum, val) => sum + (val || 0), 0)
);

const applyDiscount = () => {
    if (!selectedDiscount.value || !previewData.value) return;

    const discountData = {
        discountId: selectedDiscountId.value,
        discountName: selectedDiscount.value.discount_name,
        selectedItems: props.selectedItems.map((item) => item.id),
        discountAmount: previewData.value.discount_amount,
        discountType: selectedDiscount.value.type,
        removeTax: selectedDiscount.value.remove_tax,
    };

    const selectedItemIds = props.selectedItems.map((item: any) => item.id);

    // Send single request with cartItemIds to backend
    router.put(
        route("resto.cart.apply-discount"),
        {
            cartItemIds: selectedItemIds,
            discount_id: selectedDiscountId.value,
            discount_amount: previewData.value.discount_amount,
        },
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: page.props.flash.success,
                    life: 3000,
                });

                // Emit apply event to clear selected items in parent
                emit("apply", discountData);
            },
            onError: () => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to apply discount",
                    life: 3000,
                });
            },
        }
    );

    emit("update:visible", false);
};

const calculateDiscountAmount = (discount: any) => {
    if (!discount) return;
    console.log("items to discount", props.selectedItems);

    return props.selectedItems.map((item: any) => {
        const amount = item.quantity * item.price;
        const itemTax = {
            type: item.tax_type,
            percentage: item.tax_percentage,
            included: item.tax_included,
        };

        if (discount.remove_tax) {
            return calculateDiscountWithTaxRemoval(discount, amount, itemTax);
        }

        switch (discount.type) {
            case "percentage":
                return calculatePercentageDiscount(discount, amount);

            case "fixed":
            case "amount":
                return calculateFixedDiscount(discount, amount);

            default:
                return {
                    vatExempt: 0,
                    taxAmount: 0,
                    vatableSales: 0,
                    lessTax: 0,
                    discountAmount: 0,
                };
        }
    });
};

const calculateDiscountWithTaxRemoval = (
    discount: any,
    amount: number,
    itemTax: any
) => {
    let vatExempt = 0;
    let lessTax = 0;
    let vatableSales = 0;
    let taxAmount = 0;

    if (itemTax.percentage > 0 && itemTax.included) {
        vatExempt = amount / (itemTax.percentage / 100 + 1);
        vatableSales = 0;
        taxAmount = 0;
        lessTax = amount - vatExempt;
    }
    // const vatExempt = amount / (itemTax.percentage / 100 + 1);

    const discountAmount =
        discount.type == "percentage"
            ? vatExempt * (discount.amount / 100)
            : discount.amount;

    return {
        vatExempt: vatExempt,
        taxAmount: taxAmount,
        vatableSales: vatableSales,
        lessTax: lessTax,
        discountAmount: discountAmount,
    };
};

const calculatePercentageDiscount = (discount: any, amount: number) => {
    const vatExempt = 0;
    const vatableSales = amount / taxRate.value;
    const taxAmount = amount - vatableSales;
    const lessTax = 0;

    const discountAmount = amount * (discount.amount / 100);

    return {
        vatExempt: vatExempt,
        taxAmount: taxAmount,
        vatableSales: vatableSales,
        lessTax: lessTax,
        discountAmount: discountAmount,
    };
};

const calculateFixedDiscount = (discount: any, amount: number) => {
    const vatExempt = 0;
    const vatableSales = amount / taxRate.value;
    const taxAmount = amount - vatableSales;
    const lessTax = 0;

    return {
        vatExempt: vatExempt,
        taxAmount: taxAmount,
        vatableSales: vatableSales,
        lessTax: lessTax,
        discountAmount: discount.amount,
    };
};
</script>
