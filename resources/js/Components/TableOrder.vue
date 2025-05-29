<template>
    <div class="flex flex-col h-full space-y-6">
        <div class="flex-col flex-grow overflow-hidden pb-10">
            <h3 class="font-bold mb-2" id="orders-title">Table Orders</h3>
            <ul
                class="h-full flex-grow overflow-auto"
                ref="orderListRef"
                aria-labelledby="orders-title"
                aria-live="polite"
            >
                <li
                    v-for="(item, idx) in (orders as any[]).slice().reverse()"
                    :key="(item.product?.id || item.id) + '-' + idx"
                    class="flex flex-col border-b py-1 gap-1"
                >
                    <div class="flex justify-between items-center">
                        <div class="font-semibold text-left">
                            {{ item.product?.name || item.name }}
                            <div
                                v-if="!item.qty || item.qty <= 1"
                                class="text-xs text-gray-500 font-normal"
                            >
                                Base: ₱{{ getItemPrice(item) }}
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-1 min-w-[60px] justify-end"
                        >
                            <span>
                                ₢{{
                                    (
                                        (parseFloat(getItemPrice(item)) +
                                            (Array.isArray(item.options)
                                                ? item.options.reduce(
                                                      (sum, opt) =>
                                                          sum +
                                                          (parseFloat(
                                                              opt.price
                                                          ) || 0),
                                                      0
                                                  )
                                                : 0)) *
                                        (item.qty || 1)
                                    ).toFixed(2)
                                }}
                            </span>
                            <button
                                type="button"
                                class="ml-1 p-1 rounded hover:bg-indigo-100 focus:ring-2 focus:ring-indigo-500"
                                @click="
                                    $emit(
                                        'edit-order',
                                        item,
                                        orders.length - 1 - idx
                                    )
                                "
                                aria-label="Edit order item"
                                title="Edit"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-indigo-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-2.828 0L9 13zm-6 6h6"
                                    />
                                </svg>
                            </button>
                            <button
                                type="button"
                                class="ml-1 p-1 rounded hover:bg-red-100 focus:ring-2 focus:ring-red-500"
                                @click="$emit('remove-order', item)"
                                aria-label="Remove order item"
                                title="Remove"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-red-600"
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
                    </div>
                    <div
                        v-if="
                            Array.isArray(item.options) && item.options.length
                        "
                        class="ml-2 mt-1"
                    >
                        <div
                            class="text-xs text-gray-500 font-semibold text-left"
                        >
                            Options:
                        </div>
                        <ul class="pl-2">
                            <li
                                v-for="(opt, oidx) in item.options"
                                :key="oidx"
                                class="flex justify-between text-[10px] text-gray-500 text-left leading-tight"
                            >
                                <span>• {{ opt.name }}</span>
                                <span v-if="opt.price && opt.price !== '0'">
                                    +₱{{ getOptionTotal(opt, item.qty || 1) }}
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div
                        v-if="
                            item.modifiers && Object.keys(item.modifiers).length
                        "
                        class="ml-2 mt-1"
                    >
                        <div
                            class="text-xs text-gray-500 font-semibold text-left"
                        >
                            Modifiers:
                        </div>
                        <ul class="pl-2">
                            <li
                                v-for="(val, mod) in item.modifiers"
                                :key="mod"
                                class="text-[10px] text-gray-500 text-left leading-tight"
                            >
                                • {{ mod }}: {{ val }}
                            </li>
                        </ul>
                    </div>
                    <div
                        class="flex justify-between text-xs text-gray-700 ml-2"
                    >
                        <div v-if="item.qty && item.qty > 1">
                            Qty: {{ item.qty }} x ₱{{ getItemPrice(item) }}
                        </div>
                        <div></div>
                    </div>
                </li>
            </ul>
        </div>
        <div
            class="font-bold flex justify-between bg-gray-100 py-4 px-2"
            aria-live="polite"
        >
            <span> Total: </span>
            <span class="">
                ₱{{
                    (orders as any[])
                        .reduce((sum, item) => {
                            const price =
                                typeof item.price === "string"
                                    ? parseFloat(item.price)
                                    : item.price;
                            const qty = item.qty || 1;
                            let total = price * qty;
                            if (Array.isArray(item.options)) {
                                total += item.options.reduce((optSum, opt) => {
                                    const optPrice =
                                        typeof opt.price === "string"
                                            ? parseFloat(opt.price)
                                            : opt.price;
                                    return optSum + optPrice * qty;
                                }, 0);
                            }
                            return sum + total;
                        }, 0)
                        .toFixed(2)
                }}
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
});
// Update emit to accept index as second argument
const emit = defineEmits(["edit-order", "remove-order"]);
function getItemPrice(item: any) {
    return (
        typeof item.price === "string" ? parseFloat(item.price) : item.price
    ).toFixed(2);
}
function getOptionTotal(opt: any, qty: number) {
    const price =
        typeof opt.price === "string" ? parseFloat(opt.price) : opt.price;
    return (price * qty).toFixed(2);
}
// Removed auto-scroll logic since new items are added at the top
</script>
