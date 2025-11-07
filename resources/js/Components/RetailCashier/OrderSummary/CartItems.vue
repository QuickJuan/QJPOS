<template>
    <div ref="cartContainer" class="flex-1 overflow-auto p-4">
        <div v-if="orderItems.length === 0" class="text-center py-8">
            <ShoppingCartIcon class="w-12 h-12 text-gray-300 mx-auto mb-2" />
            <p class="text-secondary-500">No items in cart</p>
            <p class="text-sm text-secondary-400">Add items to get started</p>
        </div>

        <div v-else ref="cartItemsList" class="divide-y divide-gray-200">
            <div
                v-for="(item, index) in orderItems"
                :key="item.id"
                class="py-3 hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-start gap-3">
                    <!-- Checkbox -->
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
                        @click.stop
                        class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2 mt-1 flex-shrink-0"
                    />

                    <!-- Clickable Main Item Content -->
                    <div
                        @click="$emit('editItem', item)"
                        class="flex-1 cursor-pointer"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div
                                    class="flex items-start justify-between mb-1"
                                >
                                    <h4
                                        class="font-medium text-secondary-900 text-sm leading-tight flex-1"
                                    >
                                        {{ item.name }}{{ item.selected_options && Object.keys(item.selected_options).length > 0 ? ' - ' + Object.values(item.selected_options).map((opt: any) => opt.product?.name || opt.name).join(', ') : '' }}
                                    </h4>
                                    <!-- Order Type Badge -->
                                    <span
                                        v-if="item.order_type"
                                        :class="
                                            getOrderTypeBadgeClass(
                                                item.order_type
                                            )
                                        "
                                        class="text-xs px-2 py-0.5 rounded-full font-medium ml-2 flex-shrink-0"
                                    >
                                        {{ getOrderTypeLabel(item.order_type) }}
                                    </span>
                                    <span
                                        v-if="item.product_packaging"
                                        class="text-xs px-2 py-0.5 rounded-full font-medium ml-2 flex-shrink-0 bg-green-100 text-green-800"
                                    >
                                        {{
                                            item.product_packaging.unit_measure
                                        }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col">
                                        <p class="text-xs text-secondary-600">
                                            {{ item.quantity }} ×
                                            <span
                                                v-if="hasDiscount(item.id)"
                                                class="line-through text-red-500"
                                            >
                                                {{
                                                    formatMoney(
                                                        getBasePrice(item)
                                                    )
                                                }}
                                            </span>
                                            <span v-else>
                                                {{
                                                    formatMoney(
                                                        getBasePrice(item)
                                                    )
                                                }}
                                            </span>
                                        </p>
                                        <p
                                            v-if="hasDiscount(item.id)"
                                            class="text-xs text-green-600 font-medium"
                                        >
                                            Discounted:
                                            {{ getDiscountedPrice(item) }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <p
                                            v-if="hasDiscount(item.id)"
                                            class="text-xs text-secondary-500 line-through"
                                        >
                                            {{
                                                formatMoney(
                                                    (
                                                        item.quantity *
                                                        item.price
                                                    ).toFixed(2)
                                                )
                                            }}
                                        </p>
                                        <p
                                            class="text-xs text-secondary-500 font-medium"
                                        >
                                            {{ totalOfItems(item) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Delete Button -->
                    <button
                        @click="$emit('deleteItem', item)"
                        class="p-1 text-secondary-400 hover:text-error-600 transition-colors flex-shrink-0 mt-0.5"
                        title="Remove item"
                    >
                        <TrashIcon class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick } from "vue";
import { ShoppingCartIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { formatMoney } from "@/Utils/FormatMoney";

const props = defineProps<{
    orderItems: any[];
    selectedItemsForDiscount: number[];
    appliedDiscount?: {
        discountId: string;
        discountName: string;
        selectedItems: number[];
        discountAmount: number;
        discountType: string;
        removeTax?: boolean;
    } | null;
}>();

defineEmits<{
    toggleItemForDiscount: [itemId: number, checked: boolean];
    editItem: [item: any];
    deleteItem: [item: any];
}>();

// Helper functions for discount display
const hasDiscount = (itemId: number) => {
    return props.appliedDiscount?.selectedItems?.includes(itemId) || false;
};

const getBasePrice = (item: any) => {
    // The item.price is already the total price including options
    // We need to calculate the base price by subtracting options
    let optionsTotal = 0;
    if (item.selected_options && typeof item.selected_options === 'object') {
        optionsTotal = Object.values(item.selected_options as Record<string, any>).reduce(
            (sum: number, option: any) => {
                return sum + parseFloat(String(option.price || 0));
            },
            0
        );
    }
    const basePrice = parseFloat(String(item.price)) - optionsTotal;
    return basePrice;
};

const getDiscountedPrice = (item: any) => {
    if (!props.appliedDiscount || !hasDiscount(item.id)) {
        return formatMoney(item.price);
    }

    const discount = props.appliedDiscount;
    const itemPrice = parseFloat(item.price || item.average_cost || "0");
    const quantity = item.quantity;
    const lineTotal = itemPrice * quantity;

    const isSeniorDiscount =
        discount.discountName?.toLowerCase().includes("senior") ||
        discount.discountType === "senior";

    if (discount.removeTax && isSeniorDiscount) {
        // Special calculation for Senior Citizen Discount (20% on VAT-exempt amount)
        const vatableAmount = lineTotal / 1.12;
        const discountedPrice =
            (vatableAmount - vatableAmount * 0.2) / quantity;
        return formatMoney(discountedPrice.toFixed(2));
    } else if (discount.removeTax) {
        // Remove tax first, then apply discount
        const vatableAmount = lineTotal / 1.12;
        let discountedAmount = vatableAmount;
        if (discount.discountType === "percentage") {
            discountedAmount =
                vatableAmount -
                (vatableAmount * (discount.discountAmount / 100)) / quantity;
        } else {
            discountedAmount =
                vatableAmount - discount.discountAmount / quantity;
        }
        return formatMoney(discountedAmount.toFixed(2));
    } else {
        // Standard calculation based on vatable amount
        const vatableAmount = lineTotal / 1.12;
        let discountedAmount = vatableAmount;
        if (discount.discountType === "percentage") {
            discountedAmount =
                vatableAmount -
                (vatableAmount * (discount.discountAmount / 100)) / quantity;
        } else {
            discountedAmount =
                vatableAmount - discount.discountAmount / quantity;
        }
        return formatMoney(discountedAmount.toFixed(2));
    }
};

const getDiscountedTotal = (item: any) => {
    if (!props.appliedDiscount || !hasDiscount(item.id)) {
        return formatMoney((item.quantity * item.price).toFixed(2));
    }

    const discount = props.appliedDiscount;
    const itemPrice = parseFloat(item.price || item.average_cost || "0");
    const quantity = item.quantity;
    const lineTotal = itemPrice * quantity;

    const isSeniorDiscount =
        discount.discountName?.toLowerCase().includes("senior") ||
        discount.discountType === "senior";

    let discountedLineTotal = lineTotal;

    if (discount.removeTax && isSeniorDiscount) {
        // Special calculation for Senior Citizen Discount (20% on VAT-exempt amount)
        const vatableAmount = lineTotal / 1.12;
        discountedLineTotal = vatableAmount - vatableAmount * 0.2;
    } else if (discount.removeTax) {
        // Remove tax first, then apply discount
        const vatableAmount = lineTotal / 1.12;
        if (discount.discountType === "percentage") {
            discountedLineTotal =
                vatableAmount - vatableAmount * (discount.discountAmount / 100);
        } else {
            discountedLineTotal = vatableAmount - discount.discountAmount;
        }
    } else {
        // Standard calculation based on vatable amount
        const vatableAmount = lineTotal / 1.12;
        if (discount.discountType === "percentage") {
            discountedLineTotal =
                lineTotal - vatableAmount * (discount.discountAmount / 100);
        } else {
            discountedLineTotal = lineTotal - discount.discountAmount;
        }
    }

    return formatMoney(discountedLineTotal.toFixed(2));
};

// Refs for the scrollable container
const cartContainer = ref<HTMLDivElement>();
const cartItemsList = ref<HTMLDivElement>();

// Order type utilities
const getOrderTypeBadgeClass = (orderType: string) => {
    switch (orderType) {
        case "dine-in":
            return "bg-blue-100 text-blue-800";
        case "takeout":
            return "bg-green-100 text-green-800";
        case "delivery":
            return "bg-orange-100 text-orange-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

const getOrderTypeLabel = (orderType: string) => {
    switch (orderType) {
        case "dine-in":
            return "Dine-in";
        case "takeout":
            return "Takeout";
        case "delivery":
            return "Delivery";
        default:
            return orderType;
    }
};

const totalOfItems = (item: any) => {
    const hasOptions = item.selected_options && Object.keys(item.selected_options).length > 0;
    const discounted = hasDiscount(item.id);

    if (discounted && hasOptions) {
        return getDiscountedTotal(item);
    }

    if (discounted) {
        return getDiscountedTotal(item);
    }

    if (hasOptions) {
        const total = Object.values(item.selected_options as Record<string, any>).reduce(
            (sum: number, option: any) => sum + parseFloat(option.price || 0),
            0
        );
        return formatMoney(total);
    }

    return formatMoney(item.sub_total);
};

// Watch for changes in order items and auto-scroll to bottom when new items are added
watch(
    () => props.orderItems.length,
    (newLength, oldLength) => {
        // Only scroll to bottom if items were added (length increased)
        if (newLength > oldLength && cartContainer.value) {
            nextTick(() => {
                cartContainer.value?.scrollTo({
                    top: cartContainer.value.scrollHeight,
                    behavior: "smooth",
                });
            });
        }
    }
);
</script>
