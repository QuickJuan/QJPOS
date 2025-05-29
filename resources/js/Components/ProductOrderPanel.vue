<template>
    <div class="w-full h-full flex flex-col overflow-auto flex-grow">
        <div class="flex-1 flex flex-col">
            <div
                class="flex justify-between items-center relative mb-4 p-4 border-b bg-white"
            >
                <h3 class="text-lg font-bold">Products</h3>
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
            </div>
            <div class="gap-4 p-4 grid grid-cols-1 sm:grid-cols-2 pb-32">
                <ProductCardMilktea
                    v-for="product in products"
                    :key="product.id"
                    :product="product"
                    @add-to-cart="handleAddToCart"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, defineProps, defineEmits } from "vue";
import ProductCardMilktea from "./ProductCardMilktea.vue";

const props = defineProps({
    show: Boolean,
    orders: {
        type: Array as () => any[],
        default: () => [],
    },
});
const emit = defineEmits(["update:orders", "checkout", "close"]);

const tableOrders = ref<any[]>([...props.orders]);

watch(
    () => props.orders,
    (val) => {
        tableOrders.value = [...val];
    }
);

function handleAddToCart(payload: any) {
    const existingOrder = tableOrders.value.find(
        (order) => order.id === payload.id
    );
    if (existingOrder) {
        existingOrder.qty += payload.qty;
    } else {
        tableOrders.value.push(payload);
    }
    emit("update:orders", tableOrders.value);
}

const total = computed(() =>
    tableOrders.value.reduce((sum, item) => {
        const price =
            typeof item.price === "string"
                ? parseFloat(item.price)
                : item.price;
        const qty = item.qty || 1;
        return sum + price * qty;
    }, 0)
);

// Dummy data for testing
const products = ref(
    Array.from({ length: 8 }).map((_, i) => ({
        id: i + 1,
        name: `Milktea ${i + 1}`,
        img: "https://via.placeholder.com/80x80?text=Milktea",
        price: (Math.random() * 100 + 50).toFixed(2),
        sizes: [
            { label: "R", value: "regular" },
            { label: "M", value: "medium" },
            { label: "L", value: "large" },
        ],
        modifiers: [
            {
                name: "Ice",
                options: ["Normal Ice", "Less Ice", "No Ice"],
            },
            {
                name: "Sugar",
                options: ["100%", "75%", "50%", "25%", "0%"],
            },
        ],
        options: [
            { name: "Pearl", price: "10" },
            { name: "Pudding", price: "15" },
            { name: "Grass Jelly", price: "12" },
            { name: "None", price: "0" },
        ],
    }))
);
</script>
