<template>
    <div class="p-4 border-t border-gray-200 bg-gray-50">
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Subtotal</span>
                <span class="font-medium">
                    {{ formatMoney(orderSubtotal.toFixed(2)) }}
                </span>
            </div>
            <div
                v-if="appliedDiscount"
                class="flex justify-between text-sm text-success-600"
            >
                <span>Discount ({{ appliedDiscount.discountName }})</span>
                <span
                    >-
                    {{ formatMoney(appliedDiscount.discountAmount.toFixed(2)) }}
                </span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Tax (12%)</span>
                <span class="font-medium">
                    {{ formatMoney(taxAmount.toFixed(2)) }}
                </span>
            </div>
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

defineProps<{
    orderSubtotal: number;
    taxAmount: number;
    finalTotal: number;
    appliedDiscount: {
        discountName: string;
        discountAmount: number;
    } | null;
}>();
</script>
