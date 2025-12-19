<template>
    <!-- Categories & Products Area -->
    <section
        :class="[
            'bg-gray-50 transition-all duration-300 flex flex-col overflow-hidden',
            'h-full lg:h-full lg:flex-1',
        ]"
    >
        <!-- Categories View - Full Screen -->
        <div
            v-if="!selectedCategorySlug"
            class="h-full overflow-y-auto p-3 sm:p-4 lg:p-6"
        >
            <h2
                class="hidden lg:block text-2xl sm:text-3xl lg:text-4xl font-bold text-secondary-800 mb-4 sm:mb-6 flex-shrink-0"
            >
                Select Category
            </h2>
            <div
                class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4 lg:gap-6 pb-4"
            >
                <div
                    v-for="category in activeCategories"
                    :key="category.id"
                    @click="handleCategorySelection(category)"
                    class="bg-white rounded-xl cursor-pointer hover:shadow-xl transition-all duration-200 border-2 border-gray-200 hover:border-primary-500 group flex flex-col overflow-hidden"
                >
                    <!-- Category Image -->
                    <div class="relative bg-gray-50 h-36">
                        <img
                            v-if="category.featured_image_url"
                            :src="category.featured_image_url"
                            :alt="category.name"
                            class="w-auto h-full object-contain group-hover:scale-110 transition-transform duration-300"
                        />

                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100"
                        >
                            <component
                                :is="getCategoryIconComponent(category.name)"
                                class="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 text-primary-500"
                            />
                        </div>
                    </div>

                    <!-- Category Name -->
                    <div
                        class="flex items-center p-2 bg-white border-t border-gray-100 justify-center"
                    >
                        <h3
                            class="font-bold text-base sm:text-lg text-secondary-800"
                        >
                            {{ category.name }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products View - Shown when category selected -->
        <div v-else class="h-full overflow-y-auto">
            <!-- Products Display -->
            <ProductThumbnails
                :products="filteredProducts"
                :category-name="selectedCategoryName"
                @backToCategories="backToCategories"
                @addToCart="handleAddToCart"
            />
        </div>

        <!-- Packaging Selection Modal -->
        <PackagingSelectionModal
            v-model="showPackagingModal"
            :product="selectedProductForPackaging"
            @confirm="handlePackagingConfirm"
            @cancel="handlePackagingCancel"
        />
    </section>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue/usetoast";
import Category from "@/Types/Category";
import ProductThumbnails from "@/Components/Resto/ProductThumbnails.vue";
import PackagingSelectionModal from "@/Components/Resto/PackagingSelectionModal.vue";
import BeverageIcon from "@/Components/icons/CashierIcons/BeverageIcon.vue";
import FoodIcon from "@/Components/icons/CashierIcons/FoodIcon.vue";
import DessertIcon from "@/Components/icons/CashierIcons/DessertIcon.vue";
import ShoppingBagIcon from "@/Components/icons/CashierIcons/ShoppingBagIcon.vue";
import { useProduct } from "@/composables/useProduct";
import { useProductStore } from "@/stores/productStore";
import { useOrderStore } from "@/stores/orderStore";

const props = defineProps<{
    categories: { data: Category[] } | Category[];
    selectedCategorySlug?: string | null;
    products?: any[];
    categoryName?: string;
    cachedCategories: Category[];
    tableId: string | number;
    locationType: string | null;
    selectedOrderType?: string;
}>();

// Initialize stores and composables
const productStore = useProductStore();
const orderStore = useOrderStore();
const toast = useToast();
const {
    addToCart,
    showPackagingModal,
    selectedProductForPackaging,
    handlePackagingConfirm: baseHandlePackagingConfirm,
    handlePackagingCancel,
} = useProduct();

// Local state for selected category
const selectedCategorySlug = ref(props.selectedCategorySlug);

// Load categories into store on mount
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
if (categoriesData.length > 0 && !productStore.isCategoryLoaded()) {
    productStore.loadCategories(categoriesData);
}

const activeCategories = computed(() => {
    if (props.cachedCategories.length > 0) {
        return props.cachedCategories;
    }

    const filtered = productStore.getCategories.filter(
        (category) => category && category.id && category.name
    );

    return filtered;
});

// Compute selected category ID from slug
const selectedCategoryId = computed(() => {
    if (!selectedCategorySlug.value) {
        return null;
    }
    const category = activeCategories.value.find(
        (cat) => cat.slug === selectedCategorySlug.value
    );
    return category?.id || null;
});

// Get filtered products from props
const filteredProducts = computed(() => {
    // Handle both direct array and data wrapper from API resources
    if (Array.isArray(props.products)) {
        return props.products;
    } else if (
        props.products &&
        typeof props.products === "object" &&
        "data" in props.products
    ) {
        return props.products.data;
    }
    return [];
});

// Get selected category name
const selectedCategoryName = computed(() => {
    return props.categoryName || "Unknown Category";
});

// Handle category selection - Navigate via Inertia
const handleCategorySelection = (category: Category) => {
    if (category.slug) {
        router.get(
            route("resto.category", { categorySlug: category.slug }),
            {
                tableId: props.tableId,
                orderType: orderStore.selectedOrderType || "dine-in",
            },
            {
                preserveState: false,
                preserveScroll: false,
            }
        );
    }
};

// Back to categories - Navigate back to main resto page
const backToCategories = () => {
    router.get(
        route("resto.index"),
        {
            tableId: props.tableId,
            orderType: orderStore.selectedOrderType || "dine-in",
        },
        {
            preserveState: false,
            preserveScroll: false,
        }
    );
};

// Handle adding product to cart
const handleAddToCart = (product: any, packaging?: any) => {
    addToCart(product, packaging, {
        tableId: props.tableId,
    });
};

// Handle packaging confirmation with proper options
const handlePackagingConfirm = (packaging: any) => {
    const product = selectedProductForPackaging.value;
    if (product) {
        addToCart(product, packaging, {
            tableId: props.tableId,
        });
    }
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
