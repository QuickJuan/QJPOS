<template>
    <CashieringLayout :current-user="props.currentUser">
        <!-- Fixed Header for Mobile/Tablet with Toggle Buttons -->
        <div
            class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 shadow-md"
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
                        v-if="orderItems.length > 0"
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"
                    >
                        {{ orderItems.length }}
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
            <section
                :class="[
                    'bg-gray-50 transition-all duration-300 flex flex-col overflow-hidden',
                    'h-full lg:h-full lg:flex-1',
                ]"
            >
                <!-- Categories View - Full Screen -->
                <div
                    v-if="selectedCategoryId === null"
                    class="h-full overflow-y-auto p-3 sm:p-4 lg:p-6"
                >
                    <h2
                        class="hidden lg:block text-2xl sm:text-3xl lg:text-4xl font-bold text-secondary-800 mb-4 sm:mb-6 flex-shrink-0"
                    >
                        Select Category
                    </h2>
                    <div
                        class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-6 gap-3 sm:gap-4 lg:gap-6 pb-4"
                    >
                        <div
                            v-for="category in activeCategories"
                            :key="category.id"
                            @click="handleCategorySelection(category)"
                            class="bg-white rounded-xl cursor-pointer hover:shadow-xl transition-all duration-200 border-2 border-gray-200 hover:border-primary-500 group flex flex-col overflow-hidden"
                        >
                            <!-- Category Image -->
                            <div class="relative aspect-square bg-gray-50">
                                <img
                                    v-if="category.featured_image_url"
                                    :src="category.featured_image_url"
                                    :alt="category.name"
                                    class="w-auto h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                />

                                <div
                                    v-else
                                    class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100"
                                >
                                    <component
                                        :is="
                                            getCategoryIconComponent(
                                                category.name
                                            )
                                        "
                                        class="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 text-primary-500"
                                    />
                                </div>
                            </div>

                            <!-- Category Name -->
                            <div
                                class="p-4 sm:p-5 lg:p-6 bg-white border-t border-gray-100"
                            >
                                <h3
                                    class="font-bold text-base sm:text-lg lg:text-xl text-center text-secondary-800 mb-2 min-h-[3rem] flex items-center justify-center"
                                >
                                    {{ category.name }}
                                </h3>
                                <!-- <p
                                    class="text-sm sm:text-base text-gray-500 text-center"
                                >
                                    {{ category.products?.length || 0 }} items
                                </p> -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products View - Shown when category selected -->
                <div v-else class="h-full overflow-y-auto">
                    <ProductThumbnails
                        :products="filteredProducts"
                        :category-name="selectedCategoryName"
                        @backToCategories="backToCategories"
                        @addToCart="addToCart"
                    />
                </div>
            </section>

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
                    :cart="cart"
                    :current-table="props.currentTable"
                    :sub-total="subTotal"
                    :total="total"
                    :less-tax-total="lessTaxTotal"
                    :less-discount-total="lessDiscountTotal"
                    :tax-rate="taxRate"
                    :bill-footer="billFooter"
                    :receipt-footer="receiptFooter"
                    @selected-order-type="updateOrderType"
                    @show-receipt="handleShowReceipt"
                />
            </aside>
        </div>

        <PackagingSelectionModal
            v-model="showPackagingModal"
            :product="selectedProductForPackaging"
            @confirm="handlePackagingConfirm"
            @cancel="handlePackagingCancel"
        />
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import Category from "@/Types/Category";
import ProductThumbnails from "@/Components/RetailCashier/ProductThumbnails.vue";
import PageProps from "@/Types/PageProps";
import OrderSummary from "@/Components/RetailCashier/OrderSummary.vue";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import PackagingSelectionModal from "@/Components/RetailCashier/PackagingSelectionModal.vue";
import { useCashierCache } from "@/composables/useCashierCache";
import BeverageIcon from "@/Components/icons/CashierIcons/BeverageIcon.vue";
import FoodIcon from "@/Components/icons/CashierIcons/FoodIcon.vue";
import DessertIcon from "@/Components/icons/CashierIcons/DessertIcon.vue";
import ShoppingBagIcon from "@/Components/icons/CashierIcons/ShoppingBagIcon.vue";
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
}>();

const page = usePage<PageProps>();
const toast = useToast();
const tableId = ref(null);
const locationType = ref(null);
const selectedOrderItem = ref<any>(null);
const selectedOrderType = ref<any>("dine-in");
const showPackagingModal = ref(false);
const selectedProductForPackaging = ref<any>(null);
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

const orderItems = computed(() => props.cartItems || []);

// Compute selected category ID from slug in URL
const selectedCategoryId = computed(() => {
    if (!props.selectedCategorySlug) {
        return null;
    }
    const category = activeCategories.value.find(
        (cat) => cat.slug === props.selectedCategorySlug
    );
    return category?.id || null;
});

const activeCategories = computed(() => {
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

    if (cachedCategories.value.length > 0) {
        return cachedCategories.value;
    }

    const filtered = categoriesData.filter(
        (category) => category && category.id && category.name
    );

    return filtered;
});

// Get all products from all categories
const allProducts = computed(() => {
    return activeCategories.value.flatMap(
        (category) => category.products || []
    );
});

// Filter products based on selected category
const filteredProducts = computed(() => {
    if (selectedCategoryId.value === null) {
        return allProducts.value;
    }
    const selectedCategory = activeCategories.value.find(
        (cat) => cat.id === selectedCategoryId.value
    );
    return selectedCategory?.products || [];
});

// Get selected category name
const selectedCategoryName = computed(() => {
    if (selectedCategoryId.value === null) {
        return null;
    }
    const category = activeCategories.value.find(
        (cat) => cat.id === selectedCategoryId.value
    );
    return category?.name || "Unknown Category";
});

// Handle category selection
const handleCategorySelection = (category: Category) => {
    // Get current query params
    const params = new URLSearchParams(window.location.search);
    const queryParams: any = {};

    if (params.get("tableId")) {
        queryParams.tableId = params.get("tableId");
    }
    if (params.get("locationType")) {
        queryParams.locationType = params.get("locationType");
    }

    // Navigate to category URL
    router.visit(
        route("retail-cashier.category", {
            categorySlug: category.slug,
            ...queryParams,
        })
    );
};

// Back to categories
const backToCategories = () => {
    // Get current query params
    const params = new URLSearchParams(window.location.search);
    const queryParams: any = {};

    if (params.get("tableId")) {
        queryParams.tableId = params.get("tableId");
    }
    if (params.get("locationType")) {
        queryParams.locationType = params.get("locationType");
    }

    // Navigate back to main cashier page
    router.visit(route("retail-cashier.index", queryParams));
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

// Update order type
const updateOrderType = (type: string) => {
    selectedOrderType.value = type;
};

// Handle show receipt modal
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
        receiptNumber: `RCP-${Date.now()}`,
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

    // Get the tableId in URL if available
    const params = new URLSearchParams(window.location.search);
    const urlTableId = params.get("tableId");
    const urlLocationType = params.get("locationType");
    tableId.value = urlTableId;
    locationType.value = urlLocationType;
    selectedOrderType.value = urlLocationType;

    // If no tableId in URL and we have a pending cashiering session, redirect to tables page
    // if (!urlTableId && props.pendingCashiering) {
    //     nextTick(() => {
    //         router.visit(route("retail-cashier.tables"));
    //     });
    // }
});

const addToCart = (product: any, packaging?: any) => {
    if (packaging) {
        // Packaging already selected, proceed
        if (product.options && product.options.length > 0) {
            // Has options, redirect to options page with packaging
            router.visit(
                route("retail-cashier.product.options", {
                    product: product.id,
                    orderType: selectedOrderType.value,
                    tableId: tableId.value,
                    packagingId: packaging.id,
                })
            );
        } else {
            // No options, add to cart with packaging
            router.post(
                route("retail-cashier.cart.add"),
                {
                    quantity: 1,
                    product_id: product.id,
                    product_packaging_id: packaging.id,
                    table_id: tableId.value,
                    total_price: parseFloat(packaging.price || 0),
                    order_type: selectedOrderType.value,
                    selected_options: [],
                },
                {
                    preserveScroll: false,
                    onSuccess: () => {
                        toast.add({
                            severity: "success",
                            summary: "Success",
                            detail: page.props.flash.success,
                            life: 3000,
                        });
                    },
                    onError: (errors) => {
                        console.error("Failed to add item to cart:", errors);
                    },
                }
            );
        }
    } else {
        // No packaging provided, check if product has packagings
        if (
            product.product_packagings &&
            product.multiple_packaging && // Check if the multiple packaging is true
            product.product_packagings.length > 0
        ) {
            if (product.product_packagings.length === 1) {
                // Auto-select the single packaging
                const packaging = product.product_packagings[0];
                if (product.options && product.options.length > 0) {
                    // Has options, redirect to options page with packaging
                    router.visit(
                        route("retail-cashier.product.options", {
                            product: product.id,
                            orderType: selectedOrderType.value,
                            tableId: tableId.value,
                            packagingId: packaging.id,
                        })
                    );
                } else {
                    // No options, add to cart with packaging
                    router.post(
                        route("retail-cashier.cart.add"),
                        {
                            quantity: 1,
                            product_id: product.id,
                            product_packaging_id: packaging.id,
                            table_id: tableId.value,
                            total_price: parseFloat(packaging.price || 0),
                            order_type: selectedOrderType.value,
                            selected_options: [],
                        },
                        {
                            preserveScroll: false,
                            onSuccess: () => {
                                toast.add({
                                    severity: "success",
                                    summary: "Success",
                                    detail: page.props.flash.success,
                                    life: 3000,
                                });
                            },
                            onError: (errors) => {
                                console.error(
                                    "Failed to add item to cart:",
                                    errors
                                );
                            },
                        }
                    );
                }
            } else {
                // Multiple packagings, show modal
                selectedProductForPackaging.value = product;
                showPackagingModal.value = true;
            }
        } else {
            // No packaging, check options
            if (product.options && product.options.length > 0) {
                // Redirect to options page
                router.visit(
                    route("retail-cashier.product.options", {
                        product: product.id,
                        orderType: selectedOrderType.value,
                        tableId: tableId.value,
                    })
                );
            } else {
                // No options, add to cart without packaging
                router.post(
                    route("retail-cashier.cart.add"),
                    {
                        quantity: 1,
                        product_id: product.id,
                        product_packaging_id: null,
                        table_id: tableId.value,
                        total_price: parseFloat(product.average_cost || "0"),
                        order_type: selectedOrderType.value,
                        selected_options: [],
                    },
                    {
                        preserveScroll: false,
                        onSuccess: () => {
                            toast.add({
                                severity: "success",
                                summary: "Success",
                                detail: page.props.flash.success,
                                life: 3000,
                            });
                        },
                        onError: (errors) => {
                            console.error(
                                "Failed to add item to cart:",
                                errors
                            );
                        },
                    }
                );
            }
        }
    }
};

const handlePackagingConfirm = (packaging: any) => {
    const product = selectedProductForPackaging.value;
    if (product) {
        addToCart(product, packaging);
    }
    showPackagingModal.value = false;
    selectedProductForPackaging.value = null;
};

const handlePackagingCancel = () => {
    showPackagingModal.value = false;
    selectedProductForPackaging.value = null;
};

const getCategoryIconComponent = (categoryName: string | undefined | null) => {
    if (!categoryName) {
        return ShoppingBagIcon;
    }

    const name = categoryName.toLowerCase();
    if (name.includes("beverage") || name.includes("drink")) {
        return BeverageIcon;
    } else if (name.includes("food") || name.includes("meal")) {
        return FoodIcon;
    } else if (name.includes("dessert") || name.includes("sweet")) {
        return DessertIcon;
    } else {
        return ShoppingBagIcon;
    }
};
</script>
