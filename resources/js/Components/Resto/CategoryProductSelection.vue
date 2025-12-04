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
            v-if="selectedCategoryId === null"
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
                                :is="getCategoryIconComponent(category.name)"
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
import { computed } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import Category from "@/Types/Category";
import ProductThumbnails from "@/Components/Resto/ProductThumbnails.vue";
import PackagingSelectionModal from "@/Components/Resto/PackagingSelectionModal.vue";
import BeverageIcon from "@/Components/icons/CashierIcons/BeverageIcon.vue";
import FoodIcon from "@/Components/icons/CashierIcons/FoodIcon.vue";
import DessertIcon from "@/Components/icons/CashierIcons/DessertIcon.vue";
import ShoppingBagIcon from "@/Components/icons/CashierIcons/ShoppingBagIcon.vue";
import { useProduct } from "@/composables/useProduct";

const props = defineProps<{
    categories: { data: Category[] } | Category[];
    selectedCategorySlug?: string | null;
    cachedCategories: Category[];
    tableId: string | null;
    locationType: string | null;
    selectedOrderType?: string;
}>();

// Initialize product composable
const {
    addToCart,
    showPackagingModal,
    selectedProductForPackaging,
    handlePackagingConfirm: baseHandlePackagingConfirm,
    handlePackagingCancel,
} = useProduct();

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

    if (props.cachedCategories.length > 0) {
        return props.cachedCategories;
    }

    const filtered = categoriesData.filter(
        (category) => category && category.id && category.name
    );

    return filtered;
});

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
        route("resto.category", {
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
    router.visit(route("resto.index", queryParams));
};

// Handle adding product to cart
const handleAddToCart = (product: any, packaging?: any) => {
    addToCart(product, packaging, {
        tableId: props.tableId,
        selectedOrderType: props.selectedOrderType || "dine-in",
    });
};

// Handle packaging confirmation with proper options
const handlePackagingConfirm = (packaging: any) => {
    const product = selectedProductForPackaging.value;
    if (product) {
        addToCart(product, packaging, {
            tableId: props.tableId,
            selectedOrderType: props.selectedOrderType || "dine-in",
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
