<template>
    <!-- Category Thumbnails Row -->
    <div class="w-full max-w-full">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Categories</h2>
        <div class="flex gap-4 overflow-x-auto scrollbar-hide pb-2 max-w-full">
            <!-- Category Thumbnails -->
            <div
                v-for="category in categories"
                :key="category.id"
                @click="$emit('categorySelected', category.id)"
                :class="[
                    'bg-white rounded-2xl  cursor-pointer hover:shadow-lg transition-all duration-200 border group flex-shrink-0 w-40 min-w-40',
                    selectedCategoryId === category.id
                        ? 'border-blue-300 bg-blue-50'
                        : 'border-gray-100 hover:border-gray-200',
                ]"
            >
                <!-- Category Image -->
                <div class="relative">
                    <div
                        class="aspect-square rounded-t-xl overflow-hidden bg-gray-50"
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
                                :is="getCategoryIconComponent(category.name)"
                                class="w-8 h-8 text-gray-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Category Info -->
                <div class="text-center">
                    <h3
                        :class="[
                            'font-semibold text-sm leading-tight line-clamp-2',
                            selectedCategoryId === category.id
                                ? 'text-blue-700'
                                : 'text-gray-900',
                        ]"
                    >
                        {{ category.name }}
                    </h3>
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
