<template>
    <div
        class="absolute left-0 top-0 w-full h-full z-40 bg-white flex flex-col items-center justify-center overflow-auto"
    >
        <div
            class="relative w-full max-w-3xl rounded-lg shadow-lg bg-white flex flex-col p-4 h-[90vh]"
        >
            <button
                class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 bg-gray-100 rounded-full p-2 focus:outline-none"
                @click="emit('close')"
                aria-label="Close order panel"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
            <h3 class="text-lg font-bold mb-2">Products</h3>
            <div
                class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4 flex-1 overflow-y-auto"
            >
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="border rounded p-2 flex flex-col items-center bg-white shadow-sm"
                >
                    <div class="font-semibold">{{ product.name }}</div>
                    <div class="text-sm text-gray-500 mb-2">
                        ₱{{ product.price.toFixed(2) }}
                    </div>
                    <button
                        class="mt-auto px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 w-full"
                        @click="addToCart(product)"
                    >
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, defineProps, defineEmits } from "vue";
interface ProductOrder {
    id: number;
    name: string;
    price: number;
}
const props = defineProps({
    show: Boolean,
    orders: {
        type: Array as () => ProductOrder[],
        default: () => [],
    },
});
const emit = defineEmits(["update:orders", "checkout", "close"]);

// Generate dummy products
const products = Array.from({ length: 30 }, (_, i) => ({
    id: i + 1,
    name: `Product ${i + 1}`,
    price: Math.floor(Math.random() * 400 + 50), // ₱50 - ₱450
}));

const tableOrders = ref<ProductOrder[]>([...props.orders]);

watch(
    () => props.orders,
    (val) => {
        tableOrders.value = [...val];
    }
);

function addToCart(product: ProductOrder) {
    tableOrders.value.push(product);
    emit("update:orders", tableOrders.value);
}

const total = computed(() =>
    (tableOrders.value as ProductOrder[]).reduce(
        (sum, item) => sum + item.price,
        0
    )
);
</script>
