<template>
    <div ref="receiptRef" class="receipt-printable" v-if="visible">
        <div class="text-center font-mono text-xs">
            <div class="font-bold">RECEIPT</div>
            <div>Table: {{ tableName }}</div>
            <div v-if="customerName">Customer: {{ customerName }}</div>
            <hr class="my-1 border-black" />
            <div>
                <div
                    v-for="(item, idx) in orders"
                    :key="(item.product?.id || item.id) + '-' + idx"
                    class="mb-1"
                >
                    <div class="flex justify-between items-center">
                        <div class="font-semibold text-left">
                            {{ item.product?.name || item.name }}
                        </div>
                        <div class="text-right min-w-[60px]">
                            ₱{{ getBaseItemTotal(item) }}
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
                </div>
            </div>
            <hr class="my-1 border-black" />
            <div class="font-bold text-right">TOTAL: ₱{{ getTotal }}</div>
            <div class="mt-2">Thank you!</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { defineProps, ref, computed, defineExpose } from "vue";

const props = defineProps({
    tableName: String,
    customerName: String,
    orders: {
        type: Array as () => any[],
        default: () => [],
    },
    visible: {
        type: Boolean,
        default: false,
    },
});

const receiptRef = ref<HTMLElement | null>(null);
defineExpose({ receiptRef });

function getBaseItemTotal(item: any) {
    const price =
        typeof item.price === "string" ? parseFloat(item.price) : item.price;
    const qty = item.qty || 1;
    return (price * qty).toFixed(2);
}

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

const getTotal = computed(() => {
    return props.orders
        .reduce((sum, item) => {
            const price =
                typeof item.price === "string"
                    ? parseFloat(item.price)
                    : item.price;
            const qty = item.qty || 1;
            return sum + price * qty;
        }, 0)
        .toFixed(2);
});
</script>

<style scoped>
.receipt-printable {
    font-family: monospace;
    font-size: 12px;
    width: 220px;
    color: #000;
    background: #fff;
    margin: 0 auto;
}
@media print {
    body {
        margin: 0 !important;
    }
    .receipt-printable {
        width: 220px !important;
    }
}
</style>
