<template>
    <div class="w-full flex flex-col gap-4">
        <!-- Header with bulk actions -->
        <div
            v-if="selectedItems.length > 0"
            class="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg"
        >
            <span class="text-sm font-medium text-blue-700">
                {{ selectedItems.length }} item(s) selected
            </span>
            <button
                @click="deleteSelected"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
                Delete Selected
            </button>
        </div>

        <!-- Order items list -->
        <ul class="w-full flex flex-col gap-4">
            <li
                v-for="orderItem in orderItems"
                :key="orderItem.id"
                class="w-full flex items-center gap-4 p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
                <!-- Checkbox -->
                <div class="flex-shrink-0 pt-1">
                    <Checkbox
                        v-model="orderItem.checked"
                        :value="orderItem.id.toString()"
                        class="w-5 h-5"
                        @change="
                            (checked: any) => handleItemSelection(orderItem, checked)
                        "
                    />
                </div>

                <!-- Item details -->
                <div class="flex-1 min-w-0">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Item info -->
                        <div class="col-span-2">
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-2"
                            >
                                {{ orderItem.name }}
                            </h3>
                            <!-- Selected Options -->
                            <div
                                v-if="orderItem.selected_options"
                                class="mb-2 space-y-1"
                            >
                                <div
                                    v-for="option in orderItem.selected_options"
                                    :key="option.id"
                                    class="text-xs text-gray-600 flex justify-between"
                                >
                                    <span>+ {{ option.product.name }}</span>
                                    <span class="font-medium">
                                        {{ formatMoney(option.price) }}
                                    </span>
                                </div>
                            </div>
                            <div
                                class="flex items-center gap-3 text-sm text-gray-600"
                            >
                                <span class="font-medium">
                                    Qty: {{ orderItem.quantity }}
                                </span>
                                <span>×</span>
                                <span class="font-medium">
                                    {{ formatMoney(orderItem.price) }}
                                </span>
                            </div>
                        </div>

                        <!-- Total and actions -->
                        <div class="flex flex-col items-end gap-3">
                            <div class="text-right">
                                <span class="text-lg font-bold text-gray-900">
                                    {{
                                        formatMoney(
                                            orderItem.quantity * orderItem.price
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button
                                @click="$emit('edit', orderItem)"
                                class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                            >
                                Edit
                            </button>
                            <button
                                @click="$emit('delete', orderItem)"
                                class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700 transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Empty state -->
        <div
            v-if="orderItems.length === 0"
            class="text-center py-8 text-gray-500"
        >
            <p>No order items found.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import Checkbox from "./Form/Checkbox.vue";
import { formatMoney } from "@/Utils/FormatMoney";

interface OrderItem {
    id: number;
    name: string;
    quantity: number;
    price: number;
    checked?: boolean;
    selected_options?: any[];
}

const props = defineProps<{
    orderItems: OrderItem[];
}>();

const emit = defineEmits<{
    edit: [item: OrderItem];
    delete: [item: OrderItem];
    "delete-selected": [items: OrderItem[]];
    "selection-change": [selectedItems: OrderItem[]];
}>();

// Computed property for selected items
const selectedItems = computed(() => {
    return props.orderItems.filter((item) => item.checked);
});

// Handle item selection
const handleItemSelection = (item: OrderItem, checked: boolean) => {
    // Create a new array with updated selection state
    const updatedItems = props.orderItems.map((orderItem) =>
        orderItem.id === item.id ? { ...orderItem, checked } : orderItem
    );

    // Emit the selected items
    const selected = updatedItems.filter((item) => item.checked);
    emit("selection-change", selected);
};

// Delete selected items
const deleteSelected = () => {
    if (selectedItems.value.length > 0) {
        emit("delete-selected", selectedItems.value);
    }
};
</script>
