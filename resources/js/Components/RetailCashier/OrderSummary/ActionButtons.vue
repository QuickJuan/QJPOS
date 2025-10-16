<template>
    <div class="p-4 border-t border-gray-200 bg-white">
        <div class="space-y-3">
            <!-- Order Type Button -->
            <button
                @click="showOrderTypeModal = true"
                class="w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-secondary-700 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
            >
                <component :is="selectedOrderTypeData.icon" class="w-4 h-4" />
                <span>{{ selectedOrderTypeData.label }}</span>
                <ChevronDownIcon class="w-4 h-4 ml-auto" />
            </button>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-3">
                <button
                    @click="showMoreOptionsModal = true"
                    :disabled="orderItems.length === 0"
                    class="py-3 px-4 bg-secondary-600 text-white rounded-lg font-semibold hover:bg-secondary-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2"
                >
                    <span>More Options</span>
                    <ChevronDownIcon class="w-4 h-4" />
                </button>
                <button
                    @click="$emit('checkout')"
                    :disabled="orderItems.length === 0"
                    class="py-3 px-4 bg-success-600 text-white rounded-lg font-semibold hover:bg-success-700 disabled:bg-success-400 disabled:cursor-not-allowed transition-colors"
                >
                    Checkout
                </button>
            </div>
        </div>

        <!-- Order Type Selection Modal -->
        <Dialog
            v-model:visible="showOrderTypeModal"
            modal
            header="Select Order Type"
            :style="{ width: '20rem' }"
        >
            <div class="space-y-3">
                <button
                    v-for="type in orderTypes"
                    :key="type.value"
                    @click="selectOrderType(type.value)"
                    :class="[
                        'w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3',
                        selectedOrderType === type.value
                            ? 'bg-primary text-white'
                            : 'bg-gray-100 text-secondary-700 hover:bg-gray-200',
                    ]"
                >
                    <component :is="type.icon" class="w-5 h-5" />
                    <div class="text-left">
                        <div class="font-semibold">{{ type.label }}</div>
                        <div class="text-xs opacity-75">
                            {{ type.description }}
                        </div>
                    </div>
                    <CheckIcon
                        v-if="selectedOrderType === type.value"
                        class="w-5 h-5 ml-auto"
                    />
                </button>
            </div>
        </Dialog>

        <!-- More Options Modal -->
        <Dialog
            v-model:visible="showMoreOptionsModal"
            modal
            header="More Options"
            :style="{ width: '20rem' }"
        >
            <div class="space-y-3">
                <button
                    @click="handleSaveOrder"
                    :disabled="orderItems.length === 0"
                    class="w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3 bg-gray-100 text-secondary-700 hover:bg-gray-200 disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                >
                    <BookmarkIcon class="w-5 h-5" />
                    <div class="text-left">
                        <div class="font-semibold">Save Order</div>
                        <div class="text-xs opacity-75">
                            Save order for later processing
                        </div>
                    </div>
                </button>

                <button
                    @click="handleApplyDiscount"
                    :disabled="selectedItemsForDiscount.length === 0"
                    class="w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3 bg-gray-100 text-secondary-700 hover:bg-gray-200 disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                >
                    <TagIcon class="w-5 h-5" />
                    <div class="text-left flex-1">
                        <div class="font-semibold flex items-center gap-2">
                            Apply Discount
                            <span
                                v-if="selectedItemsForDiscount.length > 0"
                                class="bg-yellow-600 text-white text-xs px-1.5 py-0.5 rounded-full"
                            >
                                {{ selectedItemsForDiscount.length }}
                            </span>
                        </div>
                        <div class="text-xs opacity-75">
                            Apply discount to selected items
                        </div>
                    </div>
                </button>
            </div>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { Dialog } from "primevue";
import {
    HomeIcon,
    ShoppingBagIcon,
    TruckIcon,
    ChevronDownIcon,
    CheckIcon,
    TagIcon,
    BookmarkIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps<{
    selectedOrderType: string;
    orderItems: any[];
    selectedItemsForDiscount: number[];
}>();

const emit = defineEmits<{
    updateOrderType: [type: string];
    saveOrder: [];
    checkout: [];
    openDiscountModal: [];
}>();

// Modal visibility states
const showOrderTypeModal = ref(false);
const showMoreOptionsModal = ref(false);

const orderTypes = [
    {
        value: "dine-in",
        label: "Dine-in",
        icon: HomeIcon,
        activeClass: "bg-primary text-white",
        description: "Customer will eat at the restaurant",
    },
    {
        value: "takeout",
        label: "Takeout",
        icon: ShoppingBagIcon,
        activeClass: "bg-success text-white",
        description: "Customer will take food to go",
    },
    {
        value: "delivery",
        label: "Delivery",
        icon: TruckIcon,
        activeClass: "bg-warning text-white",
        description: "Food will be delivered to customer",
    },
];

// Get selected order type data
const selectedOrderTypeData = computed(() => {
    return (
        orderTypes.find((type) => type.value === props.selectedOrderType) ||
        orderTypes[0]
    );
});

// Select order type and close modal
const selectOrderType = (type: string) => {
    emit("updateOrderType", type);
    showOrderTypeModal.value = false;
};

// Handle save order from more options modal
const handleSaveOrder = () => {
    emit("saveOrder");
    showMoreOptionsModal.value = false;
};

// Handle apply discount from more options modal
const handleApplyDiscount = () => {
    console.log(
        "Discount button clicked, selected items:",
        props.selectedItemsForDiscount
    );
    emit("openDiscountModal");
    showMoreOptionsModal.value = false;
};
</script>
