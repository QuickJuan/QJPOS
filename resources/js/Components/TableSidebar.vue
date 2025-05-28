<template>
    <transition name="slide">
        <div
            v-if="show"
            class="absolute left-0 top-0 z-40 h-full w-80 max-w-full bg-white shadow-xl border-r flex flex-col"
        >
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-lg font-bold">
                    Table Status: {{ tableData.name }}
                </h2>
                <button
                    @click="$emit('close')"
                    class="text-gray-500 hover:text-gray-800 focus:outline-none"
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
            <form @submit.prevent="onSave" class="flex flex-col p-4">
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1"
                        >Customer Name</label
                    >
                    <input
                        v-model="localTable.customer"
                        type="text"
                        class="w-full border rounded px-2 py-1"
                        placeholder="Enter customer name"
                    />
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <span
                        class="inline-block px-2 py-1 rounded text-xs font-semibold"
                        :class="{
                            'bg-green-200 text-green-800':
                                localTable.status === 'vacant',
                            'bg-yellow-200 text-yellow-800':
                                localTable.status === 'reserved',
                            'bg-red-200 text-red-800':
                                localTable.status === 'occupied',
                        }"
                    >
                        {{
                            localTable.status.charAt(0).toUpperCase() +
                            localTable.status.slice(1)
                        }}
                    </span>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    <button
                        v-if="localTable.status === 'vacant'"
                        type="button"
                        @click="onAction('occupy')"
                        class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600"
                    >
                        Occupy
                    </button>
                    <button
                        v-if="localTable.status === 'vacant'"
                        type="button"
                        @click="onAction('reserve')"
                        class="px-3 py-1 rounded bg-yellow-500 text-white hover:bg-yellow-600"
                    >
                        Reserve
                    </button>
                    <button
                        v-if="localTable.status === 'reserved'"
                        type="button"
                        @click="onAction('occupy')"
                        class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600"
                    >
                        Occupy
                    </button>
                    <button
                        v-if="localTable.status === 'reserved'"
                        type="button"
                        @click="onAction('vacant')"
                        class="px-3 py-1 rounded bg-green-500 text-white hover:bg-green-600"
                    >
                        Vacant
                    </button>
                    <button
                        v-if="localTable.status === 'occupied'"
                        type="button"
                        @click="onAction('vacant')"
                        class="px-3 py-1 rounded bg-green-500 text-white hover:bg-green-600"
                    >
                        Vacant
                    </button>
                    <button
                        v-if="localTable.status === 'occupied'"
                        type="button"
                        @click.stop="$emit('take-order')"
                        class="px-3 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-700"
                    >
                        Take Order
                    </button>
                </div>
                <div class="mt-6 flex flex-col flex-grow min-h-0">
                    <h3 class="font-bold mb-2">Table Orders</h3>
                    <ul class="mb-2 h-40 overflow-y-auto flex flex-col-reverse">
                        <li
                            v-for="(item, idx) in (orders as ProductOrder[]).slice().reverse()"
                            :key="item.id + '-' + idx"
                            class="flex justify-between items-center border-b py-1"
                        >
                            <span>{{ item.name }}</span>
                            <span>₱{{ item.price.toFixed(2) }}</span>
                        </li>
                    </ul>
                    <div class="font-bold text-right mb-2">
                        Total: ₱{{
                            (orders as ProductOrder[])
                                .reduce((sum, item) => sum + item.price, 0)
                                .toFixed(2)
                        }}
                    </div>
                    <button
                        v-if="(orders as ProductOrder[]).length"
                        class="w-full px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                        @click="onCheckout"
                    >
                        Checkout
                    </button>
                </div>
                <div class="flex justify-end mt-4">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="px-3 py-1 rounded bg-gray-300 hover:bg-gray-400"
                    >
                        Close
                    </button>
                </div>
            </form>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { ref, watch, defineProps, defineEmits } from "vue";
interface ProductOrder {
    id: number;
    name: string;
    price: number;
}
const props = defineProps({
    show: Boolean,
    tableData: {
        type: Object,
        required: true,
    },
    orders: {
        type: Array as () => ProductOrder[],
        default: () => [],
    },
});
const emit = defineEmits(["close", "save", "action", "take-order", "checkout"]);

const localTable = ref({
    status: "vacant",
    customer: "",
    name: "",
});

watch(
    () => props.tableData,
    (val) => {
        localTable.value.status = val.status || "vacant";
        localTable.value.customer = val.customer || "";
        localTable.value.name = val.name || "";
    },
    { immediate: true, deep: true }
);

const onSave = () => {
    emit("save", { ...localTable.value });
};

const onAction = (action: string) => {
    emit("action", action, { ...localTable.value });
};

const onCheckout = () => {
    const total = (props.orders as ProductOrder[]).reduce(
        (sum, item) => sum + item.price,
        0
    );
    emit("checkout", total);
};
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}
.slide-enter,
.slide-leave-to {
    transform: translateX(-100%);
}
</style>
