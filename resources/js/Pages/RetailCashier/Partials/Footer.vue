<template>
    <footer
        class="bg-white border-t shadow flex items-end justify-between px-8 py-6 gap-8"
    >
        <div class="flex flex-col gap-2">
            <div class="flex gap-8 text-sm text-gray-600">
                <div>
                    LINES:
                    <span class="font-semibold text-gray-800">{{
                        orderItems.length
                    }}</span>
                </div>
                <div>
                    DISCOUNTS:
                    <span class="font-semibold text-gray-800">$0.00</span>
                </div>
                <div>
                    SUBTOTAL:
                    <span class="font-semibold text-gray-800"
                        >${{ subtotal.toFixed(2) }}</span
                    >
                </div>
                <div>
                    TAX:
                    <span class="font-semibold text-gray-800"
                        >${{ tax.toFixed(2) }}</span
                    >
                </div>
                <div>
                    PAYMENTS:
                    <span class="font-semibold text-gray-800">$0.00</span>
                </div>
            </div>
            <div class="text-3xl font-bold text-green-600">
                ${{ total.toFixed(2) }}
            </div>
        </div>
        <div class="flex gap-4">
            <button
                class="bg-secondary-light hover:bg-secondary-light text-white px-8 py-4 rounded-lg text-xl font-bold flex items-center gap-2"
            >
                <span>💵</span> Cash
            </button>
            <button
                class="bg-secondary-light hover:bg-secondary-light text-white px-8 py-4 rounded-lg text-xl font-bold flex items-center gap-2"
            >
                <span>💳</span> Card
            </button>
            <button
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-8 py-4 rounded-lg text-xl font-bold flex items-center gap-2"
            >
                <span>🌐</span> Other
            </button>
        </div>
    </footer>
</template>

<script setup lang="ts">
const props = defineProps<{
    orderItems: Array<{
        id: number;
        name: string;
        quantity: number;
        price: number;
    }>;
    subtotal: number;
    tax: number;
    total: number;
}>();
</script>
