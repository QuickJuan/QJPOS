<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        header="Apply Discount"
        :style="{ width: '40rem' }"
        class="bg-white"
    >
        <div class="space-y-6">
            <!-- Selected Items List -->
            <div>
                <h4 class="text-lg font-semibold text-secondary-900 mb-4">
                    Selected Items
                </h4>
                <div class="space-y-3 max-h-48 overflow-y-auto">
                    <div
                        v-for="item in selectedItems"
                        :key="item.id"
                        class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border"
                    >
                        <div>
                            <p class="font-medium text-secondary-900">
                                {{ item.name }}
                            </p>
                            <p class="text-sm text-secondary-600">
                                Qty: {{ item.quantity }} ×
                                {{ formatMoney(item.price) }}
                            </p>
                        </div>
                        <p class="font-semibold text-secondary-900">
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
                <h4 class="text-lg font-semibold text-secondary-900 mb-4">
                    Select Discount
                </h4>
                <div class="space-y-3">
                    <select
                        v-model="selectedDiscountId"
                        class="w-full text-secondary-900 bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:ring-primary focus:border-primary"
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
                v-if="selectedDiscountId && selectedDiscount"
                class="bg-primary-50 rounded-lg p-4 border border-primary-200"
            >
                <h5 class="font-semibold text-primary-900 mb-2">
                    Discount Preview
                </h5>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-primary-700"
                            >Selected Items Subtotal:</span
                        >
                        <span class="font-medium text-primary-900">{{
                            formatMoney(selectedItemsSubtotal.toFixed(2))
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-primary-700">Discount Amount:</span>
                        <span class="font-medium text-success-600"
                            >-{{
                                formatMoney(calculatedDiscountAmount.toFixed(2))
                            }}</span
                        >
                    </div>
                    <div
                        class="flex justify-between border-t border-primary-300 pt-1"
                    >
                        <span class="text-primary-700 font-medium"
                            >After Discount:</span
                        >
                        <span class="font-bold text-primary-900">{{
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
    availableDiscounts: any[];
}>();

const emit = defineEmits<{
    apply: [
        discountData: {
            discountId: string;
            discountName: string;
            selectedItems: number[];
            discountAmount: number;
            discountType: string;
        }
    ];
    "update:visible": [value: boolean];
}>();

const selectedDiscountId = ref("");

const selectedDiscount = computed(() => {
    return props.availableDiscounts.find(
        (d) => d.id == selectedDiscountId.value
    );
});

const selectedItemsSubtotal = computed(() => {
    return props.selectedItems.reduce((sum, item) => {
        const itemPrice = parseFloat(item.price || item.average_cost || "0");
        return sum + itemPrice * item.quantity;
    }, 0);
});

const calculatedDiscountAmount = computed(() => {
    if (!selectedDiscount.value) return 0;

    // Calculate discount based on vatable subtotal
    const selectedItemsVatableSubtotal = props.selectedItems.reduce(
        (sum, item) => {
            const itemPrice = parseFloat(
                item.price || item.average_cost || "0"
            );
            const quantity = item.quantity;
            const lineTotal = itemPrice * quantity;
            // Calculate vatable amount: total / (1 + vat_rate/100)
            const vatableAmount = lineTotal / 1.12;
            return sum + vatableAmount;
        },
        0
    );

    if (selectedDiscount.value.type === "percentage") {
        return (
            selectedItemsVatableSubtotal * (selectedDiscount.value.amount / 100)
        );
    } else {
        // Fixed amount discount
        return Math.min(
            selectedDiscount.value.amount,
            selectedItemsVatableSubtotal
        );
    }
});

const applyDiscount = () => {
    if (!selectedDiscount.value) return;

    const discountData = {
        discountId: selectedDiscountId.value,
        discountName: selectedDiscount.value.discount_name,
        selectedItems: props.selectedItems.map((item) => item.id),
        discountAmount: calculatedDiscountAmount.value,
        discountType: selectedDiscount.value.type,
    };

    emit("apply", discountData);
    emit("update:visible", false);
    selectedDiscountId.value = "";
};
</script>
