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
            <CategoryProductSelection
                :categories="props.categories"
                :selected-category-slug="props.selectedCategorySlug"
                :cached-categories="cachedCategories"
                :table-id="tableId"
                :location-type="locationType"
                :selected-order-type="selectedOrderType"
            />

            <!-- Right: Order Summary - Sidebar on mobile/tablet, fixed position on desktop -->
            <aside
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
                    :orderItems="orderItems"
                    :selected-order-item="selectedOrderItem"
                    :available-discounts="props.availableDiscounts"
                    :available-modifiers="props.availableModifiers"
                    :table-id="tableId"
                    :location-type="locationType"
                    :cart="selectedCart || props.cart"
                    :current-table="props.currentTable"
                    :sub-total="selectedCart?.sub_total || subTotal"
                    :total="selectedCart?.total_amount || total"
                    :less-tax-total="lessTaxTotal"
                    :less-discount-total="lessDiscountTotal"
                    :tax-rate="taxRate"
                    :bill-footer="billFooter"
                    :receipt-footer="receiptFooter"
                    :bill-number="props.billNumber"
                    :receipt-number="props.receiptNumber"
                    :general-settings="props.generalSettings"
                    :open-carts="openCarts"
                    :selected-cart-id="selectedCart?.id || null"
                    @selected-order-type="updateOrderType"
                    @show-receipt="handleShowReceipt"
                    @switch-cart="setSelectedCart"
                    @select-table="handleSelectTable"
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
import OrderSummary from "@/Components/RetailCashier/OrderSummary.vue";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import CategoryProductSelection from "@/Components/RetailCashier/CategoryProductSelection.vue";
import { useCashierCache } from "@/composables/useCashierCache";
import { useCashier } from "@/composables/useCashier";
import { ShoppingCartIcon } from "@heroicons/vue/24/outline";

const props = defineProps<{
    categories: { data: Category[] } | Category[];
    currentUser: any;
    cart: any;
    cartItems: any[];
    availableDiscounts: any[];
    availableModifiers: any[];
    currentTable: any;
    pendingCashiering: any;
    subTotal: number;
    total: number;
    lessTaxTotal: number;
    lessDiscountTotal: number;
    taxRate: number;
    billFooter: any;
    receiptFooter: any;
    selectedCategorySlug?: string | null;
    sessionSummary?: any;
    billNumber: string;
    receiptNumber: string;
    generalSettings: {
        company_name: string;
        company_address: string;
        company_phone: string;
        company_logo: string;
    };
}>();

const page = usePage<PageProps>();
const toast = useToast();
const tableId = ref(null);
const locationType = ref(null);
const selectedOrderItem = ref<any>(null);
const selectedOrderType = ref<any>("dine-in");
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

// Initialize cashier state
const {
    selectedCart,
    openCarts,
    hasOpenCarts,
    cartCount,
    addCart,
    updateCart,
    setSelectedCart,
    setTableInfo,
    setCashierSession,
    initializeFromServerData,
} = useCashier();

const orderItems = computed(() => {
    // Use selected cart items if available, fallback to props
    return selectedCart.value?.items || props.cartItems || [];
});

// Toggle order summary sidebar
const toggleOrderSummary = () => {
    showOrderSummary.value = !showOrderSummary.value;
};

// Toggle main sidebar (emit to parent layout)
const toggleSidebar = () => {
    // Dispatch a custom event that CashieringLayout can listen to
    window.dispatchEvent(new CustomEvent("toggle-sidebar"));
};

// Update order type
const updateOrderType = (type: string) => {
    selectedOrderType.value = type;
};

// Handle show receipt modal
const handleSelectTable = () => {
    // Navigate to tables page with current cart context
    router.visit(route("retail-cashier.tables"), {
        data: {
            from_cashier: true,
            current_cart_id: selectedCart.value?.id || props.cart?.id,
        },
    });
};

const handleShowReceipt = (data: any) => {
    // Prepare receipt data
    const subtotal = orderItems.value.reduce(
        (sum, item) => sum + (item.price || 0),
        0
    );
    const discountAmount = orderItems.value.reduce(
        (sum, item) => sum + (item.discount || 0),
        0
    );
    const discountedSubtotal = subtotal - discountAmount;
    const taxAmount = discountedSubtotal * 0.12; // 12% VAT

    receiptData.value = {
        receiptNumber: props.receiptNumber,
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
    if (props.availableDiscounts && props.availableDiscounts.length > 0) {
        loadDiscounts(props.availableDiscounts);
    }

    // Initialize cashier state from server data
    if (props.cart) {
        addCart(props.cart);
        setSelectedCart(props.cart.id);
    }

    if (props.pendingCashiering?.id) {
        setCashierSession(props.pendingCashiering.id);
    }

    // Get the tableId in URL if available
    const params = new URLSearchParams(window.location.search);
    const urlTableId = params.get("tableId");
    const urlLocationType = params.get("locationType");
    tableId.value = urlTableId;
    locationType.value = urlLocationType;
    selectedOrderType.value = urlLocationType;

    // Update cashier state with table info
    setTableInfo(urlTableId, urlLocationType);

    // If no tableId in URL and we have a pending cashiering session, redirect to tables page
    // if (!urlTableId && props.pendingCashiering) {
    //     nextTick(() => {
    //         router.visit(route("retail-cashier.tables"));
    //     });
    // }
});
</script>
