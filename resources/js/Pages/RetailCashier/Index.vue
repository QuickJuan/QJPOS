<template>
    <CashieringLayout :current-user="props.currentUser">
        <!-- Main grid: content and order panel -->
        <div class="flex flex-col md:flex-row h-full min-w-0">
            <!-- Left: Main Content Area -->
            <section
                class="flex flex-col bg-red-400 flex-1 min-w-0 overflow-hidden"
            >
                <!-- Categories - Fixed at top (outside scroll area) -->
                <div class="flex-shrink-0 p-6 pb-0">
                    <CategoryThumbnails
                        :categories="activeCategories"
                        :selected-category-id="selectedCategoryId"
                        @categorySelected="handleCategorySelection"
                    />
                </div>

                <!-- Products - Scrollable area only -->
                <div class="flex-1 px-6 pb-6 overflow-y-auto">
                    <ProductThumbnails
                        v-if="selectedCategoryId"
                        :products="filteredProducts"
                        :category-name="selectedCategoryName"
                        @backToCategories="backToCategories"
                        @addToCart="addToCart"
                    />
                </div>
            </section>
            <section class="w-[500px] bg-blue-200 md:w-[30%]">
                <!-- Right: Order panel -->
                <OrderPanel
                    :orderItems="orderItems"
                    :selected-order-item="selectedOrderItem"
                    :available-discounts="activeDiscounts"
                />
            </section>
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { ChartBarIcon, CogIcon } from "@heroicons/vue/24/outline";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import CashieringSession from "@/Types/CashieringSession";
import Category from "@/Types/Category";
import CategoryThumbnails from "@/Components/RetailCashier/CategoryThumbnails.vue";
import Product from "@/Types/Product";
import ProductThumbnails from "@/Components/RetailCashier/ProductThumbnails.vue";
import PageProps from "@/Types/PageProps";
import OrderPanel from "@/Components/RetailCashier/OrderPanel.vue";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import { useCashierCache } from "@/composables/useCashierCache";

const props = defineProps<{
    pendingCashiering: CashieringSession;
    categories: Category[];
    currentUser: any;
    cartItems: any[];
    availableDiscounts: any[];
}>();

const page = usePage<PageProps>();
const toast = useToast();
const showPendingCashieringDialog = ref(false);
const selectedOrderItem = ref<any>(null);
const selectedCategoryId = ref<number | null>(null);

// Initialize client-side cache
const {
    categories: cachedCategories,
    discounts: cachedDiscounts,
    loadCategories,
    loadDiscounts,
} = useCashierCache();

// Use cart items from props instead of local state
const orderItems = computed(() => props.cartItems || []);

// Use cached categories if available, otherwise use props
const activeCategories = computed(() => {
    const cached =
        cachedCategories.value.length > 0
            ? cachedCategories.value
            : props.categories.data;
    // Ensure we have valid category data and it's an array
    const validCached = Array.isArray(cached) ? cached : [];
    return validCached.filter(
        (category) => category && category.id && category.name
    );
});

// Use cached discounts if available, otherwise use props
const activeDiscounts = computed(() => {
    const cached =
        cachedDiscounts.value.length > 0
            ? cachedDiscounts.value
            : props.availableDiscounts;
    // Ensure we have valid discount data and it's an array
    const validCached = Array.isArray(cached) ? cached : [];
    return validCached.filter((discount) => discount && discount.id);
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
    // Load data into cache if we have fresh server data
    if (props.categories && props.categories.length > 0) {
        loadCategories(props.categories);
    }

    if (props.availableDiscounts && props.availableDiscounts.length > 0) {
        loadDiscounts(props.availableDiscounts);
    }

    const pendingCashiering = props.pendingCashiering;
    if (pendingCashiering != null) {
        showPendingCashieringDialog.value = true;
    }
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
