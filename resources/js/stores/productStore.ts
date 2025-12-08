import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export interface Product {
    id: number;
    name: string;
    description?: string;
    price: number;
    vat_type?: string;
    vat_rate?: number;
    vat_inclusive?: boolean;
    [key: string]: any;
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    featured_image_url?: string;
    products?: Product[];
    [key: string]: any;
}

export const useProductStore = defineStore('product', () => {
    // State
    const categories = ref<Category[]>([]);
    const selectedCategory = ref<Category | null>(null);
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    // Getters
    const getCategories = computed(() => categories.value);
    const getSelectedCategory = computed(() => selectedCategory.value);
    const getCategoryBySlug = (slug: string) => {
        return categories.value.find((cat) => cat.slug === slug);
    };
    const getProductsByCategoryId = (categoryId: number) => {
        const category = categories.value.find((cat) => cat.id === categoryId);
        return category?.products || [];
    };
    const getProductById = (productId: number) => {
        for (const category of categories.value) {
            const product = category.products?.find((p) => p.id === productId);
            if (product) return product;
        }
        return null;
    };

    // Actions
    function loadCategories(data: Category[]) {
        categories.value = data;
        error.value = null;
    }

    function selectCategory(category: Category) {
        selectedCategory.value = category;
    }

    function selectCategoryBySlug(slug: string) {
        const category = getCategoryBySlug(slug);
        if (category) {
            selectedCategory.value = category;
            return category;
        }
        return null;
    }

    function setLoading(value: boolean) {
        isLoading.value = value;
    }

    function setError(message: string | null) {
        error.value = message;
    }

    function getSelectedCategoryProducts() {
        return selectedCategory.value?.products || [];
    }

    function isCategoryLoaded() {
        return categories.value.length > 0;
    }

    function clearAll() {
        categories.value = [];
        selectedCategory.value = null;
        error.value = null;
    }

    return {
        // State
        categories,
        selectedCategory,
        isLoading,
        error,

        // Getters
        getCategories,
        getSelectedCategory,
        getCategoryBySlug,
        getProductsByCategoryId,
        getProductById,

        // Actions
        loadCategories,
        selectCategory,
        selectCategoryBySlug,
        setLoading,
        setError,
        getSelectedCategoryProducts,
        isCategoryLoaded,
        clearAll,
    };
});
