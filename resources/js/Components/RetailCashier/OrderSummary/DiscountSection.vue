<template>
    <div class="p-4 border-b border-gray-100 bg-yellow-50">
        <div class="flex items-center justify-between">
            <h4 class="text-sm font-medium text-secondary-700">
                Apply Discount
            </h4>
            <button
                @click="$emit('openDiscountModal')"
                :disabled="selectedItemsForDiscount.length === 0"
                class="px-3 py-1.5 bg-primary text-white text-xs font-medium rounded-md hover:bg-primary-600 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
            >
                Apply ({{ selectedItemsForDiscount.length }})
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    selectedItemsForDiscount: number[];
}>();

defineEmits<{
    openDiscountModal: [];
}>();
</script>
