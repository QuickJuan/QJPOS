<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        header="Apply Discount"
        :style="{ width: '70rem' }"
        class="bg-white"
    >
        <div class="grid grid-cols-2 gap-6" style="height: 600px">
            <!-- Left Side: Selected Items -->
            <div class="flex flex-col" style="height: 600px">
                <h4
                    class="text-lg font-semibold text-secondary-900 mb-3 flex-shrink-0"
                >
                    Selected Items ({{ selectedItems.length }})
                </h4>

                <!-- Scrollable Items List -->
                <div class="overflow-y-auto pr-2 mb-3 flex-1">
                    <div class="space-y-2">
                        <div
                            v-for="(item, index) in selectedItems"
                            :key="item.id"
                            class="flex justify-between items-start p-2 bg-gray-50 rounded-lg border hover:bg-gray-100 transition-colors"
                        >
                            <div class="flex-1 min-w-0">
                                <p
                                    class="font-medium text-secondary-900 text-sm truncate"
                                >
                                    {{ item.name }}
                                </p>
                                <div
                                    class="text-xs text-secondary-600 space-y-0.5"
                                >
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
                            <div class="text-right ml-3">
                                <p
                                    class="font-semibold text-secondary-900 text-sm"
                                >
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

                <!-- Fixed Subtotal Section at Bottom -->
                <div class="border-t pt-3 space-y-2 flex-shrink-0 bg-white">
                    <!-- PAX Division Fields (only show if discount requires pax) -->
                    <div
                        v-if="selectedDiscount?.required_pax"
                        class="space-y-2 pb-3 border-b"
                    >
                        <p class="text-sm font-semibold text-secondary-700">
                            PAX Division
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="block text-xs text-secondary-600 mb-1"
                                >
                                    Total PAX
                                </label>
                                <input
                                    v-model.number="paxCount"
                                    type="number"
                                    min="1"
                                    class="w-full px-3 py-2 border rounded-lg text-sm"
                                    placeholder="e.g., 2"
                                    @input="handlePaxChange"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-xs text-secondary-600 mb-1"
                                >
                                    Discounted PAX
                                </label>
                                <input
                                    v-model.number="discountedPax"
                                    type="number"
                                    min="1"
                                    :max="paxCount"
                                    class="w-full px-3 py-2 border rounded-lg text-sm"
                                    placeholder="e.g., 1"
                                    @input="handlePaxChange"
                                />
                            </div>
                        </div>
                        <p
                            v-if="paxCount && discountedPax"
                            class="text-xs text-secondary-500 italic"
                        >
                            Price will be divided by {{ paxCount }} pax.
                            Discount applies to {{ discountedPax }} pax.
                        </p>
                    </div>

                    <div class="flex justify-between text-base font-semibold">
                        <span class="text-secondary-700">Subtotal:</span>
                        <span class="text-secondary-900">
                            {{ formatMoney(selectedItemsSubtotal.toFixed(2)) }}
                        </span>
                    </div>

                    <!-- Total Less Tax -->
                    <div
                        class="flex justify-between text-sm"
                        v-if="totalLessTax"
                    >
                        <span class="text-success-700"> Less Tax: </span>
                        <span class="font-medium text-success-600">
                            -{{ formatMoney(totalLessTax.toFixed(2)) }}
                        </span>
                    </div>

                    <!-- Total Less Discount -->
                    <div
                        class="flex justify-between text-sm"
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
            <div class="flex flex-col" style="height: 600px">
                <h4
                    class="text-lg font-semibold text-secondary-900 mb-3 flex-shrink-0"
                >
                    Available Discounts
                </h4>

                <div class="overflow-y-auto pr-2 flex-1">
                    <!-- Percentage Discounts -->
                    <div v-if="percentageDiscounts.length > 0" class="mb-4">
                        <h5
                            class="text-sm font-semibold text-secondary-700 mb-2 flex items-center gap-2 px-2"
                        >
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            Percentage Discounts
                        </h5>
                        <div class="space-y-1">
                            <button
                                v-for="discount in percentageDiscounts"
                                :key="discount.id"
                                @click="selectDiscount(discount)"
                                :class="[
                                    'w-full px-3 py-2.5 rounded-lg border text-left transition-all hover:shadow-sm flex items-center justify-between',
                                    selectedDiscountId === String(discount.id)
                                        ? 'border-primary-500 bg-primary-50 shadow-sm'
                                        : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50',
                                ]"
                            >
                                <div class="flex items-center gap-3 flex-1">
                                    <span
                                        class="text-lg font-bold text-blue-600 min-w-[50px]"
                                    >
                                        {{ discount.amount }}%
                                    </span>
                                    <div class="flex-1">
                                        <h6
                                            class="font-medium text-secondary-900 text-sm"
                                        >
                                            {{ discount.discount_name }}
                                        </h6>
                                        <p
                                            v-if="discount.description"
                                            class="text-xs text-secondary-500 truncate"
                                        >
                                            {{ discount.description }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        v-if="discount.required_pax"
                                        class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded"
                                    >
                                        PAX
                                    </span>
                                    <span
                                        v-if="discount.requires_customer_info"
                                        class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded"
                                    >
                                        Info
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Fixed Amount Discounts -->
                    <div v-if="fixedDiscounts.length > 0" class="mb-4">
                        <h5
                            class="text-sm font-semibold text-secondary-700 mb-2 flex items-center gap-2 px-2"
                        >
                            <div class="w-2 h-2 bg-green-500 rounded-full" />
                            Fixed Amount Discounts
                        </h5>
                        <div class="space-y-1">
                            <button
                                v-for="discount in fixedDiscounts"
                                :key="discount.id"
                                @click="selectDiscount(discount)"
                                :class="[
                                    'w-full px-3 py-2.5 rounded-lg border text-left transition-all hover:shadow-sm flex items-center justify-between',
                                    selectedDiscountId === String(discount.id)
                                        ? 'border-primary-500 bg-primary-50 shadow-sm'
                                        : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50',
                                ]"
                            >
                                <div class="flex items-center gap-3 flex-1">
                                    <span
                                        class="text-base font-bold text-green-600 min-w-[50px]"
                                    >
                                        ₱{{ discount.amount }}
                                    </span>
                                    <div class="flex-1">
                                        <h6
                                            class="font-medium text-secondary-900 text-sm"
                                        >
                                            {{ discount.discount_name }}
                                        </h6>
                                        <p
                                            v-if="discount.description"
                                            class="text-xs text-secondary-500 truncate"
                                        >
                                            {{ discount.description }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        v-if="discount.required_pax"
                                        class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded"
                                    >
                                        PAX
                                    </span>
                                    <span
                                        v-if="discount.requires_customer_info"
                                        class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded"
                                    >
                                        Info
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Other Discounts (if any other types exist) -->
                    <div v-if="otherDiscounts.length > 0">
                        <h5
                            class="text-sm font-semibold text-secondary-700 mb-2 flex items-center gap-2 px-2"
                        >
                            <div class="w-2 h-2 bg-purple-500 rounded-full" />
                            Other Discounts
                        </h5>
                        <div class="space-y-1">
                            <button
                                v-for="discount in otherDiscounts"
                                :key="discount.id"
                                @click="selectDiscount(discount)"
                                :class="[
                                    'w-full px-3 py-2.5 rounded-lg border text-left transition-all hover:shadow-sm flex items-center justify-between',
                                    selectedDiscountId === String(discount.id)
                                        ? 'border-primary-500 bg-primary-50 shadow-sm'
                                        : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50',
                                ]"
                            >
                                <div class="flex items-center gap-3 flex-1">
                                    <span
                                        class="text-xs font-bold text-purple-600 uppercase min-w-[50px]"
                                    >
                                        {{ discount.type }}
                                    </span>
                                    <div class="flex-1">
                                        <h6
                                            class="font-medium text-secondary-900 text-sm"
                                        >
                                            {{ discount.discount_name }}
                                        </h6>
                                        <p
                                            v-if="discount.description"
                                            class="text-xs text-secondary-500 truncate"
                                        >
                                            {{ discount.description }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        v-if="discount.required_pax"
                                        class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded"
                                    >
                                        PAX
                                    </span>
                                    <span
                                        v-if="discount.requires_customer_info"
                                        class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded"
                                    >
                                        Info
                                    </span>
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
import { ref, computed, watch } from "vue";
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
    tablePax?: number;
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
const paxCount = ref<number | null>(null);
const discountedPax = ref<number | null>(null);
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

    // Reset PAX values when selecting a new discount
    // Use table's number_of_pax as default if available, otherwise default to 1
    const defaultPax = discount.required_pax
        ? props.tablePax && props.tablePax > 0
            ? props.tablePax
            : 1
        : null;

    paxCount.value = defaultPax;
    discountedPax.value = discount.required_pax ? 1 : null;

    recalculateDiscount();
};

// Recalculate discount based on current discount and PAX values
const recalculateDiscount = () => {
    if (!selectedDiscount.value) return;

    const calculatedDiscountAmount = calculateDiscountAmount(
        selectedDiscount.value
    );

    lessTaxValue.value = calculatedDiscountAmount.map((d) => d?.lessTax || 0);
    lessDiscountValue.value = calculatedDiscountAmount.map(
        (d) => d?.discountAmount || 0
    );

    // Calculate totals directly instead of relying on computed properties
    const calculatedLessTax = lessTaxValue.value.reduce(
        (sum, val) => sum + (val || 0),
        0
    );
    const calculatedLessDiscount = lessDiscountValue.value.reduce(
        (sum, val) => sum + (val || 0),
        0
    );

    discountTotal.value = formatMoney(
        (
            selectedItemsSubtotal.value -
            (calculatedLessTax + calculatedLessDiscount)
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

// Handle PAX input changes
const handlePaxChange = () => {
    if (selectedDiscount.value?.required_pax) {
        // Auto-correct: ensure discounted PAX doesn't exceed total PAX
        if (
            discountedPax.value &&
            paxCount.value &&
            discountedPax.value > paxCount.value
        ) {
            discountedPax.value = paxCount.value;
        }
        // Recalculate discount
        recalculateDiscount();
    }
};

// Watch for PAX changes and recalculate
watch(
    [paxCount, discountedPax],
    () => {
        if (selectedDiscount.value?.required_pax) {
            // Auto-correct: ensure discounted PAX doesn't exceed total PAX
            if (
                discountedPax.value &&
                paxCount.value &&
                discountedPax.value > paxCount.value
            ) {
                discountedPax.value = paxCount.value;
            }
            // Recalculate whenever either PAX value changes
            recalculateDiscount();
        }
    },
    { immediate: false }
);

const totalLessTax = computed(() =>
    lessTaxValue.value.reduce((sum, val) => sum + (val || 0), 0)
);

const totalLessDiscount = computed(() =>
    lessDiscountValue.value.reduce((sum, val) => sum + (val || 0), 0)
);

const applyDiscount = () => {
    if (!selectedDiscount.value || !previewData.value) return;

    // Validate PAX inputs if required
    if (selectedDiscount.value.required_pax) {
        if (
            !paxCount.value ||
            !discountedPax.value ||
            paxCount.value < 1 ||
            discountedPax.value < 1
        ) {
            toast.add({
                severity: "error",
                summary: "Validation Error",
                detail: "Please enter valid PAX values",
                life: 3000,
            });
            return;
        }

        if (discountedPax.value > paxCount.value) {
            toast.add({
                severity: "error",
                summary: "Validation Error",
                detail: "Discounted PAX cannot exceed total PAX",
                life: 3000,
            });
            return;
        }
    }

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
            pax_count: paxCount.value,
            discounted_pax: discountedPax.value,
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
    if (!discount) return [];

    const lineTotals = props.selectedItems.map(
        (item: any) => Number(item.quantity) * Number(item.price)
    );

    const allocations =
        discount.type === "fixed" || discount.type === "amount"
            ? allocateFixedDiscount(discount, lineTotals)
            : [];

    return props.selectedItems.map((item: any, index: number) => {
        let amount = lineTotals[index] ?? 0;
        const itemTax = {
            type: item.tax_type,
            percentage: item.tax_percentage,
            included: item.tax_included,
        };
        let allocation = allocations[index] ?? null;

        // Apply PAX division if required
        if (
            discount.required_pax &&
            paxCount.value &&
            discountedPax.value &&
            paxCount.value > 0
        ) {
            const originalAmount = lineTotals[index] ?? 0;
            const perPaxAmount = originalAmount / paxCount.value;
            const discountedPortionAmount = perPaxAmount * discountedPax.value;
            const regularPortionAmount =
                perPaxAmount * (paxCount.value - discountedPax.value);

            const taxRateMultiplier = itemTax.percentage / 100 + 1;

            if (discount.remove_tax) {
                // Discounted portion - remove tax, becomes vat exempt
                const vatExemptDiscounted =
                    discountedPortionAmount / taxRateMultiplier;
                const lessTaxDiscounted =
                    discountedPortionAmount - vatExemptDiscounted;

                const discountAmount =
                    discount.type === "percentage"
                        ? vatExemptDiscounted * (discount.amount / 100)
                        : Math.min(
                              allocation
                                  ? (allocation / paxCount.value) *
                                        discountedPax.value
                                  : discount.amount,
                              vatExemptDiscounted
                          );

                // Regular portion - keeps tax, becomes vatable sales
                const regularVatableSales =
                    regularPortionAmount / taxRateMultiplier;
                const regularTaxAmount =
                    regularPortionAmount - regularVatableSales;

                return {
                    vatExempt: vatExemptDiscounted, // Discounted portion (no tax)
                    taxAmount: regularTaxAmount, // Tax from regular portion only
                    vatableSales: regularVatableSales, // Regular portion (with tax)
                    lessTax: lessTaxDiscounted, // Tax removed from discounted portion
                    discountAmount: discountAmount,
                };
            } else {
                // No tax removal - both portions are vatable sales
                const discountedVatableSales =
                    discountedPortionAmount / taxRateMultiplier;
                const discountedTaxAmount =
                    discountedPortionAmount - discountedVatableSales;

                const discountAmount =
                    discount.type === "percentage"
                        ? discountedPortionAmount * (discount.amount / 100)
                        : Math.min(
                              allocation
                                  ? (allocation / paxCount.value) *
                                        discountedPax.value
                                  : discount.amount,
                              discountedPortionAmount
                          );

                const regularVatableSales =
                    regularPortionAmount / taxRateMultiplier;
                const regularTaxAmount =
                    regularPortionAmount - regularVatableSales;

                return {
                    vatExempt: 0, // No vat exempt when not removing tax
                    taxAmount: discountedTaxAmount + regularTaxAmount, // Combined tax
                    vatableSales: discountedVatableSales + regularVatableSales, // Combined vatable sales
                    lessTax: 0, // No tax removed
                    discountAmount: discountAmount,
                };
            }
        }

        if (discount.remove_tax) {
            return calculateDiscountWithTaxRemoval(
                discount,
                amount,
                itemTax,
                allocation ?? undefined
            );
        }

        switch (discount.type) {
            case "percentage":
                return calculatePercentageDiscount(discount, amount);

            case "fixed":
            case "amount":
                return calculateFixedDiscount(
                    discount,
                    amount,
                    allocation ?? undefined
                );

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

const allocateFixedDiscount = (discount: any, totals: number[]) => {
    const numericAmount = Number(discount.amount) || 0;

    return totals.map((amount) => {
        if (numericAmount <= 0 || amount <= 0) {
            return 0;
        }

        return Math.min(numericAmount, amount);
    });
};

const calculateDiscountWithTaxRemoval = (
    discount: any,
    amount: number,
    itemTax: any,
    allocatedAmount?: number
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
            : Math.min(allocatedAmount ?? discount.amount, vatExempt || amount);

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

const calculateFixedDiscount = (
    discount: any,
    amount: number,
    allocatedAmount?: number
) => {
    const vatExempt = 0;
    const vatableSales = amount / taxRate.value;
    const taxAmount = amount - vatableSales;
    const lessTax = 0;
    const resolvedAmount = Math.min(allocatedAmount ?? discount.amount, amount);

    return {
        vatExempt: vatExempt,
        taxAmount: taxAmount,
        vatableSales: vatableSales,
        lessTax: lessTax,
        discountAmount: resolvedAmount,
    };
};
</script>
