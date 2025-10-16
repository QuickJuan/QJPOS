<template>
    <div class="p-4 border-t border-gray-200 bg-white">
        <div class="space-y-3">
            <!-- Order Type Buttons -->
            <div class="grid grid-cols-3 gap-2">
                <button
                    v-for="type in orderTypes"
                    :key="type.value"
                    @click="$emit('updateOrderType', type.value)"
                    :class="[
                        'py-2 px-3 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-1',
                        selectedOrderType === type.value
                            ? type.activeClass
                            : 'bg-gray-100 text-secondary-700 hover:bg-gray-200',
                    ]"
                >
                    <component :is="type.icon" class="w-4 h-4" />
                    {{ type.label }}
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-3">
                <button
                    @click="$emit('saveOrder')"
                    :disabled="orderItems.length === 0"
                    class="py-3 px-4 bg-secondary-600 text-white rounded-lg font-semibold hover:bg-secondary-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                >
                    Save Order
                </button>
                <button
                    @click="$emit('checkout')"
                    :disabled="orderItems.length === 0"
                    class="py-3 px-4 bg-success-600 text-white rounded-lg font-semibold hover:bg-success-700 disabled:bg-success-400 disabled:cursor-not-allowed transition-colors"
                >
                    Checkout
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import {
    HomeIcon,
    ShoppingBagIcon,
    TruckIcon,
} from "@heroicons/vue/24/outline";

const orderTypes = [
    {
        value: "dine-in",
        label: "Dine-in",
        icon: HomeIcon,
        activeClass: "bg-primary text-white",
    },
    {
        value: "takeout",
        label: "Takeout",
        icon: ShoppingBagIcon,
        activeClass: "bg-success text-white",
    },
    {
        value: "delivery",
        label: "Delivery",
        icon: TruckIcon,
        activeClass: "bg-warning text-white",
    },
];

defineProps<{
    selectedOrderType: string;
    orderItems: any[];
}>();

defineEmits<{
    updateOrderType: [type: string];
    saveOrder: [];
    checkout: [];
}>();
</script>
