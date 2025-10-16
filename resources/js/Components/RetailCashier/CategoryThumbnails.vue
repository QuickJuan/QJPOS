<template>
    <!-- Category Thumbnails Vertical Sidebar -->
    <div class="h-full flex flex-col bg-gray-100">
        <h2 class="text-xl font-bold text-secondary-800 p-4 pb-2">
            Categories
        </h2>
        <div class="flex-1 overflow-y-auto scrollbar-hide p-4 pt-2 space-y-3">
            <!-- Category Thumbnails -->
            <div
                v-for="category in categories"
                :key="category.id"
                @click="$emit('categorySelected', category.id)"
                :class="[
                    'rounded-lg cursor-pointer hover:shadow-lg transition-all duration-200 border group w-full',
                    selectedCategoryId === category.id
                        ? 'border-primary-600 bg-primary text-white shadow-md'
                        : 'bg-white border-gray-200 hover:border-primary-300 hover:bg-gray-100',
                ]"
            >
                <!-- Category Content - Horizontal layout for sidebar -->
                <div class="flex items-center p-3 space-x-3">
                    <!-- Category Image -->
                    <div class="relative flex-shrink-0">
                        <div
                            class="w-12 h-12 rounded-lg overflow-hidden bg-gray-50"
                        >
                            <img
                                v-if="category.featured_image_url"
                                :src="category.featured_image_url"
                                :alt="category.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200"
                            >
                                <component
                                    :is="
                                        getCategoryIconComponent(category.name)
                                    "
                                    :class="[
                                        'w-6 h-6',
                                        selectedCategoryId === category.id
                                            ? 'text-white'
                                            : 'text-secondary-500',
                                    ]"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Category Info -->
                    <div class="flex-1 min-w-0">
                        <h3
                            :class="[
                                'font-semibold text-sm leading-tight line-clamp-2',
                                selectedCategoryId === category.id
                                    ? 'text-white'
                                    : 'text-secondary-800',
                            ]"
                        >
                            {{ category.name }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { CubeIcon } from "@heroicons/vue/24/outline";
import Category from "@/Types/Category";
import BeverageIcon from "../icons/CashierIcons/BeverageIcon.vue";
import FoodIcon from "../icons/CashierIcons/FoodIcon.vue";
import DessertIcon from "../icons/CashierIcons/DessertIcon.vue";
import ShoppingBagIcon from "../icons/CashierIcons/ShoppingBagIcon.vue";

defineProps<{
    categories: Category[];
    selectedCategoryId: number | null;
}>();

defineEmits<{
    categorySelected: [categoryId: number | null];
}>();

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

<style scoped>
/* Custom scrollbar styles for horizontal scroll */
.scrollbar-hide {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}

.scrollbar-hide::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}
</style>
