<template>
    <div class="flex flex-col gap-2">
        <button
            type="button"
            class="w-full px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500"
            @click="$emit('toggle-order-panel')"
            aria-label="Take Order"
        >
            {{ showOrderPanel ? "Hide Order Panel" : "Take Order" }}
        </button>
        <div class="flex gap-2">
            <button
                type="button"
                class="flex-1 px-3 py-2 rounded bg-green-600 text-white hover:bg-green-700 focus:ring-2 focus:ring-green-500"
                @click="$emit('checkout')"
                aria-label="Checkout"
            >
                Checkout
            </button>
            <button
                type="button"
                class="flex-1 px-3 py-2 rounded bg-gray-600 text-white hover:bg-gray-700 focus:ring-2 focus:ring-gray-500"
                @click="$emit('print-bill')"
                aria-label="Print Bill"
            >
                Print Bill
            </button>
        </div>
        <button
            v-if="!orders || orders.length === 0"
            type="button"
            class="w-full px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700 focus:ring-2 focus:ring-red-500 mt-1"
            @click="$emit('cancel-table')"
            aria-label="Cancel Table"
        >
            Cancel Table
        </button>
        <button
            v-if="status === 'vacant'"
            type="button"
            class="w-full px-3 py-2 rounded bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-2 focus:ring-yellow-500 mt-1"
            @click="$emit('merge-table')"
            aria-label="Merge Table"
        >
            Merge Table
        </button>
    </div>
</template>

<script setup lang="ts">
const props = defineProps({
    showOrderPanel: {
        type: Boolean,
        default: false,
    },
    orders: {
        type: Array,
        default: () => [],
    },
    status: {
        type: String,
        default: "",
    },
});
const emit = defineEmits([
    "toggle-order-panel",
    "checkout",
    "print-bill",
    "cancel-table",
    "merge-table",
]);
</script>
