<template>
    <div class="mb-4">
        <div class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Apply Discount
                </label>
                <select
                    v-model="selectedDiscount"
                    class="w-full text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-0"
                    @change="handleDiscountChange"
                >
                    <option value="">No Discount</option>
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
            <div class="flex items-end mb-0.5">
                <button
                    @click="openDiscountDialog"
                    :disabled="!selectedDiscount"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                >
                    Select Items
                </button>
            </div>
        </div>
    </div>

    <!-- Discount Item Selection Dialog -->
    <Dialog
        v-model:visible="showDiscountDialog"
        modal
        header="Select Items for Discount"
        :style="{ width: '50rem' }"
        class="bg-white"
    >
        <div class="space-y-4">
            <p class="text-sm text-gray-600">
                Select which items should receive the discount:
                <strong>{{ selectedDiscountName }}</strong>
            </p>

            <div class="max-h-96 overflow-y-auto space-y-3">
                <div
                    v-for="item in orderItems"
                    :key="item.id"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border"
                >
                    <div class="flex items-center space-x-3">
                        <input
                            type="checkbox"
                            :checked="discountedItems.includes(item.id)"
                            @change="
                                (e) =>
                                    toggleItemDiscount(
                                        item.id,
                                        (e.target as HTMLInputElement).checked
                                    )
                            "
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                        />
                        <div>
                            <p class="font-medium text-gray-900">
                                {{ item.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Qty: {{ item.quantity }} ×
                                {{ formatMoney(item.price) }}
                            </p>
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
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button
                    type="button"
                    label="Cancel"
                    severity="secondary"
                    @click="showDiscountDialog = false"
                />
                <Button
                    type="button"
                    label="Apply Discount"
                    @click="confirmApplyDiscount"
                    :disabled="discountedItems.length === 0"
                    class="bg-green-600 hover:bg-green-700"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { Dialog, Button } from "primevue";
import { formatMoney } from "@/Utils/FormatMoney";

const props = defineProps<{
    orderItems: any[];
    availableDiscounts: any[];
}>();

const emit = defineEmits<{
    discountApplied: [
        discountData: {
            discountId: string;
            discountName: string;
            selectedItems: number[];
            discountAmount: number;
            discountType: string;
        }
    ];
}>();

const selectedDiscount = ref("");
const availableDiscounts = computed(() => props.availableDiscounts || []);
const showDiscountDialog = ref(false);
const selectedDiscountName = ref("");
const discountedItems = ref<number[]>([]);

const handleDiscountChange = () => {
    if (selectedDiscount.value) {
        const discount = availableDiscounts.value.find(
            (d) => d.id == selectedDiscount.value
        );
        selectedDiscountName.value = discount ? discount.discount_name : "";
        discountedItems.value = [];
    }
};

const openDiscountDialog = () => {
    if (selectedDiscount.value) {
        showDiscountDialog.value = true;
    }
};

const toggleItemDiscount = (itemId: number, checked: boolean) => {
    if (checked) {
        if (!discountedItems.value.includes(itemId)) {
            discountedItems.value.push(itemId);
        }
    } else {
        discountedItems.value = discountedItems.value.filter(
            (id) => id !== itemId
        );
    }
};

const confirmApplyDiscount = () => {
    if (discountedItems.value.length === 0) {
        // TODO: Show error toast
        return;
    }

    const discount = availableDiscounts.value.find(
        (d) => d.id == selectedDiscount.value
    );
    if (!discount) return;

    // Calculate discount amount based on vatable subtotal of selected items
    const selectedItemsSubtotal = props.orderItems
        .filter((item) => discountedItems.value.includes(item.id))
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
        discountAmount = selectedItemsSubtotal * (discount.amount / 100);
    } else {
        // Fixed amount discount
        discountAmount = Math.min(discount.amount, selectedItemsSubtotal);
    }

    emit("discountApplied", {
        discountId: selectedDiscount.value,
        discountName: discount.discount_name,
        selectedItems: [...discountedItems.value],
        discountAmount: discountAmount,
        discountType: discount.type,
    });

    // Reset state
    showDiscountDialog.value = false;
    selectedDiscount.value = "";
    selectedDiscountName.value = "";
    discountedItems.value = [];
};
</script>
