<template>
    <CashieringLayout
        :current-user="props.currentUser"
        :open-session="props.pendingCashiering"
        :session-summary="props.sessionSummary"
    >
        <!-- Fixed Header for Mobile/Tablet with Toggle Buttons -->
        <div
            class="lg:hidden fixed top-0 left-0 right-0 z-[60] bg-white border-b border-gray-200 shadow-md"
        >
            <div class="flex items-center justify-between px-3 py-2">
                <!-- Left: Menu Toggle -->
                <button
                    @click="toggleSidebar"
                    class="bg-primary text-white rounded-lg p-2.5 shadow hover:bg-primary-600 transition-all"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        ></path>
                    </svg>
                </button>

                <!-- Center: Title -->
                <h1 class="text-base font-bold text-gray-800">QuickJuan POS</h1>

                <!-- Right: Cart Toggle -->
                <button
                    @click="toggleOrderSummary"
                    class="bg-primary text-white rounded-lg p-2.5 shadow hover:bg-primary-600 transition-all relative"
                >
                    <ShoppingCartIcon class="w-5 h-5" />
                    <span
                        v-if="cartCount > 0"
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"
                    >
                        {{ cartCount }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Order Summary Overlay (Mobile/Tablet) -->
        <div
            v-if="showOrderSummary"
            @click="toggleOrderSummary"
            class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-20 transition-opacity"
        ></div>

        <!-- Main responsive grid -->
        <div
            class="flex flex-col lg:flex-row h-full min-w-0 overflow-hidden pt-[56px] lg:pt-0"
        >
            <!-- Categories & Products Area -->
            <div
                class="flex-1 flex items-center justify-center relative overflow-hidden"
                v-if="!tableId"
            >
                <!-- Decorative Background -->
                <div
                    class="absolute inset-0 opacity-5 -z-10"
                    :style="{
                        backgroundImage: `url('${generalSettings.company_logo}')`,
                        backgroundSize: 'cover',
                        backgroundPosition: 'center',
                        backgroundAttachment: 'fixed',
                    }"
                ></div>

                <!-- Gradient Overlay -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-secondary/5 -z-10"
                ></div>

                <!-- Content -->
                <div
                    class="flex flex-col items-center justify-center text-center px-4"
                >
                    <!-- Large Logo Background Circle -->
                    <div
                        class="relative mb-8"
                        v-if="generalSettings.company_logo"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-primary/10 to-secondary/10 rounded-full blur-xl"
                            style="width: 300px; height: 300px; margin: auto"
                        ></div>
                        <img
                            :src="generalSettings.company_logo"
                            alt="Company Logo"
                            class="relative w-48 h-48 object-contain drop-shadow-lg"
                        />
                    </div>

                    <!-- Title -->
                    <h2
                        class="text-4xl md:text-5xl font-bold text-gray-800 mb-3"
                    >
                        Ready to Order?
                    </h2>

                    <!-- Subtitle -->
                    <p class="text-xl text-gray-600 font-medium mb-8 max-w-md">
                        Select a table to start ordering
                    </p>

                    <!-- Button with Enhanced Styling -->
                    <button
                        @click="handleSelectTable"
                        class="px-8 py-4 bg-gradient-to-r from-primary to-primary-600 text-white rounded-xl font-bold hover:shadow-2xl hover:scale-105 transition-all duration-300 shadow-lg border-0"
                    >
                        <span class="flex items-center gap-2">
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"
                                ></path>
                            </svg>
                            Start Ordering
                        </span>
                    </button>

                    <!-- Decorative Elements -->
                    <div class="mt-16 flex gap-8 text-gray-400">
                        <div class="flex flex-col items-center">
                            <svg
                                class="w-8 h-8 mb-2 text-primary/40"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                                ></path>
                            </svg>
                            <span class="text-sm font-medium"
                                >Fast Service</span
                            >
                        </div>
                        <div class="flex flex-col items-center">
                            <svg
                                class="w-8 h-8 mb-2 text-primary/40"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                                ></path>
                            </svg>
                            <span class="text-sm font-medium"
                                >Easy Checkout</span
                            >
                        </div>
                        <div class="flex flex-col items-center">
                            <svg
                                class="w-8 h-8 mb-2 text-primary/40"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                                ></path>
                            </svg>
                            <span class="text-sm font-medium"
                                >Great Experience</span
                            >
                        </div>
                    </div>
                </div>
            </div>
            <CategoryProductSelection
                v-if="tableId"
                :categories="props.categories"
                :selected-category-slug="props.selectedCategorySlug"
                :products="props.products"
                :category-name="props.categoryName"
                :cached-categories="cachedCategories"
                :table-id="tableId"
                :location-type="locationType"
                :selected-order-type="orderStore.selectedOrderType"
                :is-waiter-mode="props.isWaiterMode"
            />

            <!-- Right: Order Summary - Sidebar on mobile/tablet, fixed position on desktop -->
            <aside
                v-if="tableId"
                :class="[
                    'lg:relative fixed right-0 bottom-0 z-30 transform transition-transform duration-300 ease-in-out overflow-hidden',
                    'top-[56px] lg:top-0',
                    'w-full sm:w-96 lg:w-[400px] xl:w-[450px] 2xl:w-[500px] flex-shrink-0',
                    'bg-white lg:bg-transparent shadow-2xl lg:shadow-none',
                    showOrderSummary
                        ? 'translate-x-0'
                        : 'translate-x-full lg:translate-x-0',
                ]"
            >
                <OrderSummary
                    :selected-order-item="selectedOrderItem"
                    :table-id="tableId"
                    :location-type="locationType"
                    :cart="sharedCart"
                    :current-table="props.currentTable"
                    :general-settings="generalSettings"
                    :is-waiter-mode="props.isWaiterMode"
                    @show-receipt="handleShowReceipt"
                    @select-table="handleSelectTable"
                    @change-order-type="handleOrderType"
                />
            </aside>
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import Category from "@/Types/Category";
import PageProps from "@/Types/PageProps";
import OrderSummary from "@/Components/Resto/OrderSummary.vue";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import CategoryProductSelection from "@/Components/Resto/CategoryProductSelection.vue";
import { useCashierCache } from "@/composables/useCashierCache";
import { useOrderStore } from "@/stores/orderStore";
import { ShoppingCartIcon } from "@heroicons/vue/24/outline";

const props = defineProps<{
    categories: { data: Category[] } | Category[];
    selectedCategorySlug?: string | null;
    products?: any[];
    categoryName?: string;
    currentTable?: any;
    tableId?: string | number;
    orderType?: string;
    currentUser?: any;
    pendingCashiering?: any;
    sessionSummary?: any;
    isWaiterMode?: boolean; // Hide settle button for waiter users
}>();

const page = usePage<PageProps>();
const toast = useToast();

// Use order store for managing order type and other order-related state
const orderStore = useOrderStore();

const isWaiterMode = computed(() => !!props.isWaiterMode);

// Get company settings from shared data
const generalSettings = computed(
    () =>
        page.props.company_info || {
            company_name: "",
            company_address: "",
            company_phone: "",
            company_logo: "",
        },
);

// Get cart from shared data
const sharedCart = computed(() => page.props.cart);
const tableId = ref(props.tableId || null);
const locationType = ref(null);
const selectedOrderItem = ref<any>(null);
const showOrderSummary = ref(false);
const receiptData = ref({
    receiptNumber: "",
    date: "",
    subtotal: 0,
    taxAmount: 0,
    discountAmount: 0,
    totalAmount: 0,
    paymentInfo: null as any,
});

// Initialize client-side cache
const {
    categories: cachedCategories,
    discounts: cachedDiscounts,
    loadCategories,
    loadDiscounts,
} = useCashierCache();

const orderItems = computed(() => {
    // Use shared cart items
    return sharedCart.value?.cart_items || sharedCart.value?.cartItems || [];
});
const cartCount = computed(() => {
    return orderItems.value.reduce(
        (total: number, item: any) => total + (item.quantity || 0),
        0,
    );
});
// handle order type selection
const handleOrderType = (type: string) => {
    orderStore.setOrderType(type);
    orderStore.updateUrlParams();
};

// Toggle order summary sidebar
const toggleOrderSummary = () => {
    showOrderSummary.value = !showOrderSummary.value;
};

// Toggle main sidebar (emit to parent layout)
const toggleSidebar = () => {
    // Dispatch a custom event that CashieringLayout can listen to
    window.dispatchEvent(new CustomEvent("toggle-sidebar"));
};

// Handle show receipt modal
const handleSelectTable = () => {
    // Navigate to tables page with current cart context as query params
    const params: any = {
        from_cashier: true,
    };

    if (sharedCart.value?.id) {
        params.current_cart_id = sharedCart.value.id;
    }

    router.visit(route("table-rooms.index", params));
};

const handleShowReceipt = (data: any) => {
    // Prepare receipt data
    const subtotal = orderItems.value.reduce(
        (sum, item) => sum + (item.price || 0),
        0,
    );
    const discountAmount = orderItems.value.reduce(
        (sum, item) => sum + (item.discount || 0),
        0,
    );
    const discountedSubtotal = subtotal - discountAmount;
    const taxAmount = discountedSubtotal * 0.12; // 12% VAT

    receiptData.value = {
        receiptNumber: props.currentUser?.receipt_number || "",
        date: new Date().toISOString(),
        subtotal: subtotal,
        taxAmount: taxAmount,
        discountAmount: discountAmount,
        totalAmount: data.total_amount,
        paymentInfo: {
            amount_paid: data.amount_paid,
            change: data.amount_paid - data.total_amount,
            method: "Cash",
        },
    };
};

// Check for pending cashiering on mount
onMounted(() => {
    // Initialize order store from URL params
    orderStore.initializeFromUrl();

    // Get the tableId and locationType from URL if available (only if not already set from props)
    const params = new URLSearchParams(window.location.search);
    const urlTableId = params.get("tableId");
    const urlLocationType = params.get("locationType");

    if (!tableId.value && urlTableId) {
        tableId.value = urlTableId;
    }
    if (!locationType.value && urlLocationType) {
        locationType.value = urlLocationType;
    }

    const getCategoriesData = (): Category[] => {
        if (Array.isArray(props.categories)) {
            return props.categories;
        } else if (
            props.categories &&
            typeof props.categories === "object" &&
            "data" in props.categories &&
            Array.isArray(props.categories.data)
        ) {
            return props.categories.data;
        }
        return [];
    };

    const categoriesData = getCategoriesData();

    // First, try to load from localStorage
    loadCategories();

    // If we have fresh server data and no cached data, or server data is newer
    if (categoriesData && categoriesData.length > 0) {
        loadCategories(categoriesData);
    } else if (cachedCategories.value.length === 0) {
        console.warn("No categories available from server or cache");
    }

    // Load discounts (keeping existing logic)
    if (
        props.availableDiscounts &&
        Array.isArray(props.availableDiscounts) &&
        props.availableDiscounts.length > 0
    ) {
        loadDiscounts(props.availableDiscounts);
    }
});
</script>
