<template>
    <Dialog
        :visible="visible"
        modal
        header="Table Orders"
        :style="{ width: '800px' }"
        :closable="true"
        @hide="$emit('update:visible', false)"
        @update:visible="$emit('update:visible', $event)"
    >
        <div class="space-y-4">
            <div v-if="orders.length === 0" class="text-center py-8">
                <i class="pi pi-shopping-cart text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    No orders found
                </h3>
                <p class="text-gray-600">
                    This table doesn't have any orders yet.
                </p>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="order in orders"
                    :key="order.id"
                    class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <i
                                    class="pi pi-shopping-cart text-blue-600"
                                ></i>
                                <span class="font-semibold text-gray-900">
                                    Order #{{ order.id }}
                                </span>
                            </div>
                            <div
                                :class="[
                                    'px-2 py-1 rounded-full text-xs font-medium',
                                    order.status === 'completed'
                                        ? 'bg-green-100 text-green-800'
                                        : order.status === 'pending'
                                        ? 'bg-yellow-100 text-yellow-800'
                                        : 'bg-gray-100 text-gray-800',
                                ]"
                            >
                                {{ order.status }}
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">
                                ₱{{ formatCurrency(order.total_amount) }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="item in getHierarchicalCartItems(
                                order.cart_items
                            )"
                            :key="item.id"
                            :class="[
                                'py-2 border-b border-gray-100 last:border-b-0',
                                item.parent_id
                                    ? 'ml-6 border-l-2 border-l-gray-300 pl-4'
                                    : '',
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span
                                            :class="[
                                                'font-medium text-gray-900',
                                                item.parent_id
                                                    ? 'text-sm'
                                                    : 'text-sm',
                                            ]"
                                        >
                                            {{
                                                item.product?.name ||
                                                "Unknown Product"
                                            }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            ×{{ item.quantity }}
                                        </span>
                                        <!-- Order Type Badge -->
                                        <span
                                            v-if="item.order_type"
                                            :class="[
                                                'text-xs px-2 py-0.5 rounded-full font-medium',
                                                item.order_type === 'dine-in'
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : item.order_type ===
                                                      'takeout'
                                                    ? 'bg-green-100 text-green-800'
                                                    : item.order_type ===
                                                      'delivery'
                                                    ? 'bg-orange-100 text-orange-800'
                                                    : 'bg-gray-100 text-gray-800',
                                            ]"
                                        >
                                            {{ item.order_type }}
                                        </span>
                                        <!-- Product Packaging -->
                                        <span
                                            v-if="item.product_packaging"
                                            class="text-xs bg-purple-100 text-purple-800 px-2 py-0.5 rounded"
                                        >
                                            {{ item.product_packaging.name }}({{
                                                item.product_packaging.qty +
                                                item.product_packaging
                                                    .unit_measure
                                            }})
                                        </span>
                                    </div>

                                    <div v-if="hasModifiers(item)" class="mt-1">
                                        <button
                                            @click="openModifiersModal(item)"
                                            class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded hover:bg-blue-200 cursor-pointer transition-colors"
                                        >
                                            <i
                                                class="pi pi-plus-circle mr-1"
                                            ></i>
                                            Modified
                                        </button>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p
                                        v-if="item.discount > 0"
                                        class="text-xs text-secondary-500 line-through"
                                    >
                                        ₱{{ formatCurrency(item.price * item.quantity) }}
                                    </p>
                                    <p
                                        :class="[
                                            'text-gray-900',
                                            item.parent_id
                                                ? 'text-sm'
                                                : 'text-sm',
                                        ]"
                                    >
                                        ₱{{ formatCurrency(item.sub_total) }}
                                    </p>
                                    <div
                                        v-if="item.discount > 0"
                                        class="text-xs text-green-600"
                                    >
                                        -₱{{ formatCurrency(item.discount) }}
                                        discount
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-4 pt-3 border-t border-gray-200 flex items-center justify-between"
                    >
                        <div
                            class="flex items-center gap-4 text-sm text-gray-600"
                        >
                            <span>
                                <i class="pi pi-user mr-1"></i>
                                {{ order.cashier?.name || "Unknown" }}
                            </span>
                            <span v-if="order.table_room">
                                <i class="pi pi-table mr-1"></i>
                                {{ order.table_room.name }}
                            </span>
                        </div>
                        <button
                            @click="$emit('viewOrderDetails', order)"
                            class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                        >
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Modifiers Modal -->
        <ItemModifiersModal
            :visible="showModifiersModal"
            :item="selectedItemForModifiers"
            :modifiers="selectedItemModifiers"
            @update:visible="showModifiersModal = $event"
        />
    </Dialog>
</template>

<script setup lang="ts">
import Dialog from "primevue/dialog";
import ItemModifiersModal from "@/Components/RetailCashier/OrderSummary/ItemModifiersModal.vue";
import { ref } from "vue";

const props = defineProps<{
    visible: boolean;
    orders: any[];
}>();

const emit = defineEmits<{
    "update:visible": [value: boolean];
    viewOrderDetails: [order: any];
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat("en-PH", {
        style: "decimal",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getOrderTotal = (cartItems: any[]) => {
    return cartItems.reduce((total, item) => {
        return total + parseFloat(item.sub_total || 0);
    }, 0);
};

const showModifiersModal = ref(false);
const selectedItemForModifiers = ref(null);
const selectedItemModifiers = ref([]);

const getHierarchicalCartItems = (cartItems: any[]) => {
    const parentItems = cartItems.filter((item) => !item.parent_id);
    const childItems = cartItems.filter((item) => item.parent_id);

    const result = [];

    parentItems.forEach((parent) => {
        result.push(parent);
        // Add children under their parent
        const children = childItems.filter(
            (child) => child.parent_id === parent.id
        );
        children.forEach((child) => {
            result.push(child);
        });
    });

    return result;
};

const openModifiersModal = (item: any) => {
    selectedItemForModifiers.value = item;
    // Use meta_data for modifiers instead of selected_options
    selectedItemModifiers.value = item.meta_data || [];
    showModifiersModal.value = true;
};

const hasModifiers = (item: any) => {
    return (
        item.meta_data &&
        Array.isArray(item.meta_data) &&
        item.meta_data.length > 0
    );
};
</script>
