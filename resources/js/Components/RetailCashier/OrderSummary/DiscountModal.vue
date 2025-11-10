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
                                <div class="text-sm text-secondary-600">
                                    <p>
                                        Qty: {{ item.quantity }} ×
                                        {{ formatMoney(item.price) }}
                                    </p>
                                    <div
                                        v-if="
                                            selectedDiscountId &&
                                            selectedDiscount &&
                                            previewData &&
                                            previewData.item_discounts
                                        "
                                        class="mt-1"
                                    >
                                        <span class="line-through text-red-500">
                                            {{
                                                formatMoney(
                                                    (
                                                        item.quantity *
                                                        item.price
                                                    ).toFixed(2)
                                                )
                                            }}
                                        </span>
                                        <span
                                            class="ml-2 text-green-600 font-medium"
                                        >
                                            {{
                                                formatMoney(
                                                    previewData.item_discounts
                                                        .find(
                                                            (d) =>
                                                                d.id === item.id
                                                        )
                                                        ?.discounted_price?.toFixed(
                                                            2
                                                        ) ||
                                                        (
                                                            item.quantity *
                                                            item.price
                                                        ).toFixed(2)
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right ml-4">
                                <p
                                    v-if="
                                        selectedDiscountId && selectedDiscount
                                    "
                                    class="text-xs text-secondary-500 line-through"
                                >
                                    {{
                                        formatMoney(
                                            (
                                                item.quantity * item.price
                                            ).toFixed(2)
                                        )
                                    }}
                                </p>
                                <p class="font-semibold text-secondary-900">
                                    {{
                                        selectedDiscountId &&
                                        selectedDiscount &&
                                        previewData &&
                                        previewData.item_discounts
                                            ? formatMoney(
                                                  previewData.item_discounts
                                                      .find(
                                                          (d) =>
                                                              d.id === item.id
                                                      )
                                                      ?.discounted_price?.toFixed(
                                                          2
                                                      ) ||
                                                      (
                                                          item.quantity *
                                                          item.price
                                                      ).toFixed(2)
                                              )
                                            : formatMoney(
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
import { Button, Dialog } from "primevue";
import { formatMoney } from "@/Utils/FormatMoney";
import { route } from "ziggy-js";
import { router } from "@inertiajs/vue3";

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

const calculatedDiscountAmount = ref(0);
const discountTotal = ref(null);
const previewData = ref(null);

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
    const isSeniorDiscount =
        discount.discount_name?.toLowerCase().includes("senior") ||
        discount.type === "senior";

    if (isSeniorDiscount && discount.remove_tax) {
        return "Senior Citizen Discount (20%)";
    }

    return `Discount (${discount.discount_name})`;
};

// Select discount method
const selectDiscount = async (discount: any) => {
    selectedDiscountId.value = String(discount.id);

    // Calculate discount preview on backend
    try {
        const response = await fetch(
            route("retail-cashier.cart.calculate-discount"),
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
                body: JSON.stringify({
                    discount_id: discount.id,
                    items: props.selectedItems,
                }),
            }
        );

        const result = await response.json();

        if (response.ok) {
            calculatedDiscountAmount.value = result.discount_amount;
            discountTotal.value =
                discount.discount_type == "senior" && discount.remove_tax
                    ? result.total
                    : formatMoney(
                          (
                              selectedItemsSubtotal.value -
                              result.discount_amount
                          ).toFixed(2)
                      );
            previewData.value = result;
        }
    } catch (error) {
        console.error("Failed to calculate discount preview:", error);
    }
};

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

    // Apply discount to each selected cart item
    const promises = props.selectedItems.map((item) =>
        fetch(route("retail-cashier.cart.apply-discount", { cartItemId: item.id }), {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
            },
            body: JSON.stringify({
                discount_id: selectedDiscountId.value,
                discount_amount: previewData.value.item_discounts.find((d: any) => d.id === item.id)?.discount_amount || 0,
            }),
        })
    );

    Promise.all(promises)
        .then(() => {
            emit("apply", discountData);
            emit("update:visible", false);
            selectedDiscountId.value = "";
            previewData.value = null;
            // Reload the page to reflect the updated cart totals
            router.reload();
        })
        .catch((error) => {
            console.error("Failed to apply discount:", error);
            emit("apply", discountData);
            emit("update:visible", false);
            selectedDiscountId.value = "";
            previewData.value = null;
            // Reload even on error to show any partial changes
            router.reload();
        });
};
</script>
