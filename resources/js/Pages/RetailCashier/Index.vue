<template>
    <CashieringLayout :current-user="props.currentUser">
        <!-- Main responsive grid: categories, products, and order panel -->
        <div class="flex flex-col lg:flex-row h-full min-w-0">
            <!-- Categories - Top horizontal on mobile, left sidebar on desktop -->
            <aside
                class="bg-gray-50 flex-shrink-0 overflow-hidden w-full lg:w-28 xl:w-64 lg:h-full"
            >
                <CategoryThumbnails
                    :categories="activeCategories"
                    :selected-category-id="selectedCategoryId"
                    @categorySelected="handleCategorySelection"
                />
            </aside>

            <!-- Center: Products Area -->
            <section class="flex-1 bg-gray-50 overflow-hidden min-h-0">
                <!-- Products - Scrollable area -->
                <div class="h-full p-3 sm:p-4 lg:p-6 overflow-y-auto">
                    <ProductThumbnails
                        v-if="selectedCategoryId"
                        :products="filteredProducts"
                        :category-name="selectedCategoryName"
                        @backToCategories="backToCategories"
                        @addToCart="addToCart"
                    />
                    <div v-else class="flex items-center justify-center h-full">
                        <p
                            class="text-gray-500 text-base sm:text-lg text-center px-4"
                        >
                            Select a category to view products
                        </p>
                    </div>
                </div>
            </section>

            <!-- Right: Order Summary - Full width on mobile, fixed width on desktop -->
            <section
                class="w-full lg:w-[400px] xl:w-[450px] 2xl:w-[500px] flex-shrink-0"
            >
                <OrderSummary
                    :orderItems="orderItems"
                    :selected-order-item="selectedOrderItem"
                    :available-discounts="props.availableDiscounts"
                    :table-id="tableId"
                    :cart="cart"
                />
            </section>
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import Category from "@/Types/Category";
import CategoryThumbnails from "@/Components/RetailCashier/CategoryThumbnails.vue";
import ProductThumbnails from "@/Components/RetailCashier/ProductThumbnails.vue";
import PageProps from "@/Types/PageProps";
import OrderSummary from "@/Components/RetailCashier/OrderSummary.vue";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import { useCashierCache } from "@/composables/useCashierCache";

const props = defineProps<{
    categories: { data: Category[] } | Category[];
    currentUser: any;
    cart: any;
    cartItems: any[];
    availableDiscounts: any[];
}>();

const page = usePage<PageProps>();
const toast = useToast();
const tableId = ref(null);
const selectedOrderItem = ref<any>(null);
const selectedCategoryId = ref<number | null>(null);

// Initialize client-side cache
const {
    categories: cachedCategories,
    discounts: cachedDiscounts,
    loadCategories,
    loadDiscounts,
} = useCashierCache();

const orderItems = computed(() => props.cartItems || []);

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
const handleCategorySelection = (categoryId: number | null) => {
    selectedCategoryId.value = categoryId;
};

// Back to categories
const backToCategories = () => {
    selectedCategoryId.value = null;
};

// Check for pending cashiering on mount
onMounted(() => {
    // Helper to safely extract categories data
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
        // Always update cache with server data on page load to ensure fresh data
        loadCategories(categoriesData);
    } else if (cachedCategories.value.length === 0) {
        console.warn("No categories available from server or cache");
    }

    // Load discounts (keeping existing logic)
    if (props.availableDiscounts && props.availableDiscounts.length > 0) {
        loadDiscounts(props.availableDiscounts);
    }

    // Use nextTick to ensure activeCategories is computed after cache is loaded
    nextTick(() => {
        // Select first category by default if no category is selected
        if (
            selectedCategoryId.value === null &&
            activeCategories.value.length > 0
        ) {
            selectedCategoryId.value = activeCategories.value[0].id;
        }
    });

    // Get the tableId in URL if available
    const params = new URLSearchParams(window.location.search);
    tableId.value = params.get("tableId");
});

const addToCart = (product: any) => {
    // Check if product has options
    if (product.options && product.options.length > 0) {
        // Navigate to options page
        router.visit(route("retail-cashier.product.options", product.id));
    } else {
        // Add directly to cart
        router.post(
            route("retail-cashier.cart.add"),
            {
                quantity: 1,
                product_id: product.id,
                table_id: tableId.value,
                total_price: parseFloat(product.average_cost || "0"),
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
};
</script>
