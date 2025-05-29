<template>
    <transition name="slide">
        <div
            v-if="show"
            class="h-full w-[400px] max-w-full bg-white shadow-xl border-r flex flex-col"
            style="pointer-events: auto"
            role="complementary"
            aria-label="Table Sidebar"
        >
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-lg font-bold" id="sidebar-title">
                    Table Status: {{ tableData.name }}
                </h2>
                <button
                    @click="$emit('close')"
                    class="text-gray-500 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded"
                    aria-label="Close sidebar"
                    title="Close sidebar"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        aria-hidden="true"
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
            <form
                @submit.prevent="onSave"
                class="flex flex-col p-4"
                aria-labelledby="sidebar-title"
            >
                <div class="mb-3">
                    <label
                        class="block text-sm font-medium mb-1"
                        for="customer-name-input"
                        >Customer Name</label
                    >
                    <input
                        id="customer-name-input"
                        v-model="localTable.customer"
                        type="text"
                        class="w-full border rounded px-2 py-1 focus:ring-2 focus:ring-indigo-500"
                        placeholder="Enter customer name"
                        aria-label="Customer Name"
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
                        :aria-label="'Table status: ' + localTable.status"
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
                        class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600 focus:ring-2 focus:ring-indigo-500"
                        aria-label="Mark as occupied"
                    >
                        Occupy
                    </button>
                    <button
                        v-if="localTable.status === 'vacant'"
                        type="button"
                        @click="onAction('reserve')"
                        class="px-3 py-1 rounded bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-2 focus:ring-indigo-500"
                        aria-label="Reserve table"
                    >
                        Reserve
                    </button>
                    <button
                        v-if="localTable.status === 'reserved'"
                        type="button"
                        @click="onAction('occupy')"
                        class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600 focus:ring-2 focus:ring-indigo-500"
                        aria-label="Mark as occupied"
                    >
                        Occupy
                    </button>
                    <button
                        v-if="localTable.status === 'reserved'"
                        type="button"
                        @click="onAction('vacant')"
                        class="px-3 py-1 rounded bg-green-500 text-white hover:bg-green-600 focus:ring-2 focus:ring-indigo-500"
                        aria-label="Mark as vacant"
                    >
                        Vacant
                    </button>
                    <button
                        v-if="localTable.status === 'occupied'"
                        type="button"
                        @click="onAction('vacant')"
                        class="px-3 py-1 rounded bg-green-500 text-white hover:bg-green-600 focus:ring-2 focus:ring-indigo-500"
                        aria-label="Mark as vacant"
                    >
                        Vacant
                    </button>
                    <button
                        v-if="localTable.status === 'occupied'"
                        type="button"
                        @click.stop="$emit('take-order')"
                        class="px-3 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500"
                        aria-label="Take order for this table"
                    >
                        Take Order
                    </button>
                </div>
                <div class="mt-6 flex flex-col flex-grow min-h-0">
                    <h3 class="font-bold mb-2" id="orders-title">
                        Table Orders
                    </h3>
                    <ul
                        class="mb-2 h-40 overflow-y-auto flex flex-col-reverse"
                        aria-labelledby="orders-title"
                        aria-live="polite"
                    >
                        <li
                            v-for="(item, idx) in (orders as any[]).slice().reverse()"
                            :key="(item.product?.id || item.id) + '-' + idx"
                            class="flex flex-col border-b py-1 gap-1"
                        >
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-semibold">
                                        {{ item.product?.name || item.name }}
                                    </span>
                                    <span
                                        v-if="item.qty"
                                        class="ml-2 text-xs text-gray-500"
                                        >x{{ item.qty }}</span
                                    >
                                </div>
                                <div class="flex items-center gap-1">
                                    <button
                                        @click="
                                            $emit(
                                                'edit-order',
                                                item,
                                                orders.length - 1 - idx
                                            )
                                        "
                                        class="text-blue-500 hover:underline text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded"
                                        aria-label="Edit order for {{ item.product?.name || item.name }}"
                                        title="Edit order"
                                        tabindex="0"
                                        @keydown.enter.prevent="
                                            $emit(
                                                'edit-order',
                                                item,
                                                orders.length - 1 - idx
                                            )
                                        "
                                        @keydown.space.prevent="
                                            $emit(
                                                'edit-order',
                                                item,
                                                orders.length - 1 - idx
                                            )
                                        "
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="
                                            $emit(
                                                'remove-order',
                                                orders.length - 1 - idx
                                            )
                                        "
                                        class="text-red-500 hover:underline text-xs ml-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded"
                                        aria-label="Remove order for {{ item.product?.name || item.name }}"
                                        title="Remove order"
                                        tabindex="0"
                                        @keydown.enter.prevent="
                                            $emit(
                                                'remove-order',
                                                orders.length - 1 - idx
                                            )
                                        "
                                        @keydown.space.prevent="
                                            $emit(
                                                'remove-order',
                                                orders.length - 1 - idx
                                            )
                                        "
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <div
                                v-if="
                                    Array.isArray(item.options) &&
                                    item.options.length
                                "
                                class="text-xs text-gray-700 ml-2"
                            >
                                <span class="font-semibold">Options:</span>
                                <span>
                                    {{
                                        item.options
                                            .map(
                                                (opt) =>
                                                    opt.name +
                                                    (opt.price &&
                                                    opt.price !== "0"
                                                        ? ` (+₱${opt.price})`
                                                        : "")
                                            )
                                            .join(", ")
                                    }}
                                </span>
                            </div>
                            <div
                                v-if="
                                    item.modifiers &&
                                    Object.keys(item.modifiers).length
                                "
                                class="text-xs text-gray-700 ml-2"
                            >
                                <span class="font-semibold">Modifiers:</span>
                                <span>
                                    {{
                                        Object.entries(item.modifiers)
                                            .map(
                                                ([mod, val]) => `${mod}: ${val}`
                                            )
                                            .join(", ")
                                    }}
                                </span>
                            </div>
                            <div class="text-right text-sm">
                                <span>
                                    ₱{{
                                        (
                                            item.total ||
                                            item.price * (item.qty || 1) ||
                                            item.price
                                        ).toFixed(2)
                                    }}
                                </span>
                            </div>
                        </li>
                    </ul>
                    <div class="font-bold text-right mb-2" aria-live="polite">
                        Total: ₱{{
                            (orders as any[])
                                .reduce(
                                    (sum, item) =>
                                        sum +
                                        (item.total ||
                                            item.price * (item.qty || 1) ||
                                            item.price),
                                    0
                                )
                                .toFixed(2)
                        }}
                    </div>
                    <button
                        v-if="(orders as ProductOrder[]).length"
                        class="w-full px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:ring-2 focus:ring-indigo-500"
                        @click="onCheckout"
                        aria-label="Checkout orders for this table"
                    >
                        Checkout
                    </button>
                </div>
                <div class="flex justify-end mt-4">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="px-3 py-1 rounded bg-gray-300 hover:bg-gray-400 focus:ring-2 focus:ring-indigo-500"
                        aria-label="Close sidebar"
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
const emit = defineEmits([
    "close",
    "save",
    "action",
    "take-order",
    "checkout",
    "edit-order",
    "remove-order",
]);

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

:global(.product-order-panel) {
    margin-left: 20rem !important; /* 80px * 4 = 320px (w-80) */
}

button:focus {
    outline: 2px solid #6366f1; /* indigo-500 */
    outline-offset: 2px;
}
</style>
