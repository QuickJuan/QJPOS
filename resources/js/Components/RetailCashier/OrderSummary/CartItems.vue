<template>
    <div ref="cartContainer" class="flex-1 overflow-auto p-4">
        <div v-if="orderItems.length === 0" class="text-center py-8">
            <ShoppingCartIcon class="w-12 h-12 text-gray-300 mx-auto mb-2" />
            <p class="text-secondary-500">No items in cart</p>
            <p class="text-sm text-secondary-400">Add items to get started</p>
        </div>

        <div v-else ref="cartItemsList" class="space-y-3">
            <div
                v-for="item in orderItems"
                :key="item.id"
                class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors"
            >
                <input
                    type="checkbox"
                    :checked="selectedItemsForDiscount.includes(item.id)"
                    @change="
                        (e) =>
                            $emit(
                                'toggleItemForDiscount',
                                item.id,
                                (e.target as HTMLInputElement).checked
                            )
                    "
                    class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2 mt-1"
                />

                <div class="flex-1 min-w-0">
                    <h4 class="font-medium text-secondary-900 truncate">
                        {{ item.name }}
                    </h4>
                    <p class="text-sm text-secondary-600">
                        Qty: {{ item.quantity }} × {{ formatMoney(item.price) }}
                    </p>

                    <!-- Selected Options -->
                    <div
                        v-if="
                            item.selected_options &&
                            item.selected_options.length > 0
                        "
                        class="mt-1 space-y-1"
                    >
                        <div
                            v-for="option in item.selected_options"
                            :key="option.id"
                            class="text-xs text-primary-600"
                        >
                            + {{ option.product.name }} (+{{
                                formatMoney(option.price)
                            }})
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <p class="font-semibold text-secondary-900">
                        {{
                            formatMoney((item.quantity * item.price).toFixed(2))
                        }}
                    </p>
                    <div class="flex gap-1 mt-1">
                        <button
                            @click="$emit('editItem', item)"
                            class="p-1 text-secondary-400 hover:text-primary-600 transition-colors"
                        >
                            <PencilIcon class="w-4 h-4" />
                        </button>
                        <button
                            @click="$emit('deleteItem', item)"
                            class="p-1 text-secondary-400 hover:text-error-600 transition-colors"
                        >
                            <TrashIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick } from "vue";
import {
    ShoppingCartIcon,
    PencilIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";
import { formatMoney } from "@/Utils/FormatMoney";

const props = defineProps<{
    orderItems: any[];
    selectedItemsForDiscount: number[];
}>();

defineEmits<{
    toggleItemForDiscount: [itemId: number, checked: boolean];
    editItem: [item: any];
    deleteItem: [item: any];
}>();

// Refs for the scrollable container
const cartContainer = ref<HTMLDivElement>();
const cartItemsList = ref<HTMLDivElement>();

// Watch for changes in order items and auto-scroll to bottom when new items are added
watch(
    () => props.orderItems.length,
    (newLength, oldLength) => {
        // Only scroll to bottom if items were added (length increased)
        if (newLength > oldLength && cartContainer.value) {
            nextTick(() => {
                cartContainer.value?.scrollTo({
                    top: cartContainer.value.scrollHeight,
                    behavior: 'smooth'
                });
            });
        }
    }
);
</script>
