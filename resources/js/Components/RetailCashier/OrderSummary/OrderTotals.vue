<template>
    <div class="p-4 border-t border-gray-200 bg-gray-50">
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Subtotal</span>
                <span class="font-medium">
                    {{ formatMoney(subTotal) }}
                </span>
            </div>
            <!-- <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Tax (12%)</span>
                <span class="font-medium">
                    {{ formatMoney(taxAmount.toFixed(2)) }}
                </span>
            </div> -->
            <hr class="border-gray-300" />
            <div class="flex justify-between text-lg font-bold">
                <span>Total</span>
                <span>{{ formatMoney(finalTotal.toFixed(2)) }}</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { formatMoney } from "@/Utils/FormatMoney";

const props = defineProps<{
    orderSubtotal: number;
    taxAmount: number;
    subTotal: number;
    finalTotal: number;
    appliedDiscount: {
        discountName: string;
        discountAmount: number;
        discountType?: string;
        removeTax?: boolean;
    } | null;
}>();

// Get discount label based on type
const getDiscountLabel = (discount: any) => {
    const isSeniorDiscount =
        discount.discountType === "senior" ||
        discount.discountName?.toLowerCase().includes("senior");

    if (isSeniorDiscount && discount.removeTax) {
        return "Senior Citizen Discount (20%)"; // TODO: Discount label will be based on backend
    }

    return `Discount (${discount.discountName})`;
};
</script>
