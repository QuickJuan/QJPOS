<template>
    <div class="p-4 border-t border-gray-200 bg-gray-50">
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Subtotal</span>
                <span class="font-medium">
                    {{ formatMoney(subTotal) }}
                </span>
            </div>
            <div v-if="props.lessTaxTotal" class="flex justify-between text-sm">
                <span class="text-secondary-600">Less Tax:</span>
                <span class="font-medium">
                    {{ formatMoney(lessTaxTotal.toFixed(2)) }}
                </span>
            </div>
            <div
                v-if="props.lessDiscountTotal"
                class="flex justify-between text-sm"
            >
                <span class="text-secondary-600">Less Discount: </span>
                <span class="font-medium">
                    {{ formatMoney(lessDiscountTotal.toFixed(2)) }}
                </span>
            </div>
            <hr class="border-gray-300" />
            <div class="flex justify-between text-lg font-bold">
                <span>Total</span>
                <span>{{
                    finalTotal ? formatMoney(finalTotal.toFixed(2)) : null
                }}</span>
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
    lessTaxTotal: number;
    lessDiscountTotal: number;
    appliedDiscount: {
        discountName: string;
        discountAmount: number;
        discountType?: string;
        removeTax?: boolean;
    } | null;
}>();
</script>
