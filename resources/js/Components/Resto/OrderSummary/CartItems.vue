<template>
    <div ref="cartContainer" class="flex-1 overflow-auto p-4">
        <div v-if="!tableSelected" class="text-center py-8">
            <ShoppingCartIcon class="w-12 h-12 text-gray-300 mx-auto mb-2" />
            <p class="text-secondary-500">No table selected</p>
            <p class="text-sm text-secondary-400">
                Please select a table to start ordering.
            </p>
            <button
                class="mt-4 px-4 py-2 bg-primary text-white rounded-lg font-semibold hover:bg-primary-700 transition-colors"
                @click="redirectToTablePage"
            >
                Start Order
            </button>
        </div>
        <div v-else-if="orderItems.length === 0" class="text-center py-8">
            <ShoppingCartIcon class="w-12 h-12 text-gray-300 mx-auto mb-2" />
            <p class="text-secondary-500">No items in cart</p>
            <p class="text-sm text-secondary-400">Add items to get started</p>
        </div>

        <div v-else ref="cartItemsList">
            <div
                v-for="item in orderItems"
                :key="item.id"
                class="hover:bg-gray-50 transition-colors"
            >
                <template v-if="item.parent_id == null" class="py-2">
                    <div class="flex items-start gap-3 mb-10">
                        <!-- Checkbox -->
                        <input
                            type="checkbox"
                            :checked="
                                selectedItemsForDiscount?.includes(item.id) ||
                                false
                            "
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
                                            {{ item.description }}
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
                                            {{
                                                getOrderTypeLabel(
                                                    item.order_type
                                                )
                                            }}
                                        </span>
                                        <span
                                            v-if="item.product_packaging"
                                            class="text-xs px-2 py-0.5 rounded-full font-medium ml-2 flex-shrink-0 bg-green-100 text-green-800"
                                        >
                                            {{ item.product_packaging.name }}
                                            ({{
                                                item.product_packaging.qty +
                                                item.product_packaging
                                                    ?.unit_measure
                                            }})
                                        </span>
                                        <span
                                            v-if="
                                                item.product?.unit_measure &&
                                                !item.product.multiple_packaging
                                            "
                                            class="text-xs px-2 py-0.5 rounded-full font-medium ml-2 flex-shrink-0 bg-green-100 text-green-800"
                                        >
                                            {{ item.product?.unit_measure }}
                                        </span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div class="flex flex-col">
                                            <p
                                                class="text-xs text-secondary-600"
                                            >
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
                                                v-if="item.less_tax > 0"
                                                class="text-xs text-gray-600 font-medium"
                                            >
                                                Less Tax:
                                            </p>
                                            <p
                                                v-if="item.discount_amount > 0"
                                                class="text-xs text-gray-600 font-medium"
                                            >
                                                Less Discount:
                                            </p>
                                        </div>

                                        <div class="flex flex-col items-end">
                                            <p
                                                class="text-xs text-secondary-500 font-medium"
                                            >
                                                {{ formatMoney(item.amount) }}
                                            </p>
                                            <!-- Less Tax -->
                                            <p
                                                v-if="item.less_tax > 0"
                                                class="text-xs text-gray-600 font-medium"
                                            >
                                                {{ formatMoney(item.less_tax) }}
                                            </p>

                                            <!-- Discount -->
                                            <p
                                                v-if="item.discount_amount > 0"
                                                class="text-xs text-gray-600 font-medium"
                                            >
                                                {{
                                                    formatMoney(
                                                        item.discount_amount
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Selected Options as Sub-items -->
                            <div
                                v-if="item.children && item.children.length > 0"
                                class="mt-2 ml-4 space-y-1"
                            >
                                <div
                                    v-for="option in item.children"
                                    :key="option.id"
                                    class="flex items-center justify-between py-1"
                                >
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-1 h-1 bg-secondary-400 rounded-full flex-shrink-0"
                                        ></div>
                                        <span
                                            class="text-xs text-secondary-600"
                                        >
                                            {{ option.quantity }} ×
                                            {{ getChildName(option) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs text-secondary-600"
                                        >
                                            <template
                                                v-if="
                                                    getChildAmount(option) > 0
                                                "
                                            >
                                                +{{
                                                    formatMoney(
                                                        getChildAmount(option)
                                                    )
                                                }}
                                            </template>
                                            <template v-else>
                                                Included
                                            </template>
                                        </span>
                                        <button
                                            @click.stop="
                                                $emit('deleteItem', option)
                                            "
                                            class="p-0.5 text-secondary-400 hover:text-error-600 transition-colors"
                                            title="Remove option"
                                        >
                                            <svg
                                                class="w-3 h-3"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                ></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Modifiers -->
                            <div v-if="hasModifiers(item)" class="mt-2">
                                <div class="text-xs text-gray-600 mb-1">
                                    Modifiers:
                                </div>
                                <div
                                    v-for="(
                                        modifierData, index
                                    ) in item.meta_data"
                                    :key="index"
                                    class="text-xs text-gray-700 space-y-1 ml-3 flex gap-2 items-center"
                                >
                                    <div>
                                        <!-- Modifier Options -->
                                        <div
                                            v-for="(value, key) in modifierData"
                                            :key="key"
                                            class="flex items-center gap-2"
                                        >
                                            <span class="text-xs text-gray-700">
                                                {{ formatModifierValue(value) }}
                                            </span>
                                            <!-- Remove Modifier Button -->
                                            <button
                                                @click.stop="
                                                    $emit(
                                                        'remove-modifier',
                                                        item,
                                                        value
                                                    )
                                                "
                                                class="p-1 rounded-md text-red-400 hover:text-red-600 hover:bg-red-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-opacity-50"
                                                title="Remove modifier"
                                                :aria-label="`Remove ${formatModifierValue(
                                                    value
                                                )} modifier`"
                                            >
                                                <XMarkIcon
                                                    class="w-3.5 h-3.5"
                                                />
                                            </button>
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
                </template>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, computed } from "vue";
import { ShoppingCartIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { formatMoney } from "@/Utils/FormatMoney";
import { router } from "@inertiajs/vue3";
import XMarkIcon from "@/Components/icons/XMarkIcon.vue";

const props = defineProps<{
    orderItems: any[];
    selectedItemsForDiscount?: number[];
    appliedDiscount?: {
        discountId: string;
        discountName: string;
        selectedItems: number[];
        discountAmount: number;
        discountType: string;
        removeTax?: boolean;
    } | null;
    tableInfo?: any;
    tableId?: number | null;
}>();

const emit = defineEmits<{
    toggleItemForDiscount?: [itemId: number, checked: boolean];
    editItem: [item: any];
    deleteItem: [item: any];
    showItemModifiers: [item: any];
    "remove-modifier": [item: any, modifierIndex: number];
}>();

const tableSelected = computed(() => !props.tableId);

const redirectToTablePage = () => {
    router.visit("table-rooms.index");
};

// Helper functions for discount display
const hasDiscount = (itemId: number) => {
    return props.appliedDiscount?.selectedItems?.includes(itemId) || false;
};

const hasModifiers = (item: any) => {
    return (
        item.meta_data &&
        item.meta_data.modifier &&
        Array.isArray(item.meta_data.modifier) &&
        item.meta_data.modifier.length > 0
    );
};

// Helper methods for improved modifiers display
const getSpecialInstructions = (modifierData: any) => {
    return modifierData?.modifier?.specialInstructions || "";
};

const hasModifierOptions = (modifierData: any) => {
    const modifier = modifierData?.modifier;
    return modifier && Object.keys(modifier).length > 1;
};

const getModifierOptions = (modifierData: any) => {
    const modifier = modifierData?.modifier || {};
    const options: any = {};

    Object.entries(modifier).forEach(([key, value]) => {
        if (key !== "specialInstructions") {
            options[key] = value;
        }
    });

    return options;
};

const formatModifierKey = (key: string | number) => {
    return String(key).replace(/([A-Z])/g, " $1");
};

const formatModifierValue = (value: any) => {
    if (Array.isArray(value)) {
        return value.map((item) => item.name || item).join(", ");
    }
    return String(value);
};

const getChildName = (option: any) => {
    return option?.product?.name || option?.description || "Selected item";
};

const getChildAmount = (option: any) => {
    if (option?.amount !== undefined && option?.amount !== null) {
        return parseFloat(String(option.amount)) || 0;
    }

    const price = parseFloat(String(option?.price ?? 0));
    const quantity = parseFloat(String(option?.quantity ?? 0));

    return price * quantity;
};

const showItemModifiers = (item: any) => {
    emit("showItemModifiers", item);
};

const getBasePrice = (item: any) => {
    // The item.price is already the total price including options
    // We need to calculate the base price by subtracting options
    let optionsTotal = 0;
    if (item.selected_options && typeof item.selected_options === "object") {
        optionsTotal = Object.values(
            item.selected_options as Record<string, any>
        ).reduce((sum: number, option: any) => {
            return sum + parseFloat(String(option.price || 0));
        }, 0);
    }
    const basePrice = parseFloat(String(item.price)) - optionsTotal;
    return basePrice;
};

const getDiscountedPrice = (item: any) => {
    if (!props.appliedDiscount || !hasDiscount(item.id)) {
        return formatMoney(item.price);
    }

    const discount = props.appliedDiscount;
    const itemPrice = parseFloat(item.price || "0");
    const quantity = item.quantity;
    const lineTotal = itemPrice * quantity;

    if (discount.removeTax) {
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

// Returns the per-line discount amount saved on the cart item (assumed stored in cart_items.discount)
const getItemDiscountAmount = (item: any) => {
    if (!item) return 0;
    const d = item.discount || 0;
    const parsed = parseFloat(d as any) || 0;
    return parsed;
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
    const hasOptions =
        item.selected_options && Object.keys(item.selected_options).length > 0;
    const discounted = hasDiscount(item.id);

    if (discounted && hasOptions) {
        return getDiscountedTotal(item);
    }

    if (discounted) {
        return getDiscountedTotal(item);
    }

    if (hasOptions) {
        const total = Object.values(
            item.selected_options as Record<string, any>
        ).reduce(
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
                if (cartContainer.value) {
                    cartContainer.value.scrollTo({
                        top: cartContainer.value.scrollHeight,
                        behavior: "smooth",
                    });
                }
            });
        }
    }
);

// Also watch for changes in any individual item (e.g., quantity updates)
watch(
    () => props.orderItems,
    (newItems, oldItems) => {
        // Check if there are actual changes beyond just re-ordering
        if (newItems && oldItems && newItems.length >= oldItems.length) {
            // Small delay to ensure DOM has updated
            nextTick(() => {
                if (cartContainer.value) {
                    const isNearBottom =
                        cartContainer.value.scrollHeight -
                            cartContainer.value.scrollTop -
                            cartContainer.value.clientHeight <
                        100;

                    // Auto-scroll if user is near the bottom or if items were just added
                    if (isNearBottom || newItems.length > oldItems.length) {
                        cartContainer.value.scrollTo({
                            top: cartContainer.value.scrollHeight,
                            behavior: "smooth",
                        });
                    }
                }
            });
        }
    },
    { deep: true }
);
</script>
