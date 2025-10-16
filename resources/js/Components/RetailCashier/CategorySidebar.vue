<template>
    <!-- Compact Categories Sidebar -->
    <aside
        class="w-24 h-screen bg-white border-r border-slate-200 flex flex-col py-4 sticky top-0"
    >
        <div class="px-2 mb-4">
            <h3
                class="text-xs font-semibold text-slate-600 text-center uppercase tracking-wide"
            >
                Categories
            </h3>
        </div>

        <div class="flex-1 space-y-3 px-2 overflow-y-auto">
            <!-- All Categories Button -->
            <button
                @click="selectCategory(null)"
                :class="[
                    'w-full flex flex-col items-center p-2 rounded-lg transition-all duration-200',
                    selectedCategoryId === null
                        ? 'bg-blue-50 border border-blue-200 shadow-sm'
                        : 'hover:bg-slate-50 border border-transparent',
                ]"
                class="group"
            >
                <div
                    :class="[
                        'w-12 h-12 rounded-lg flex items-center justify-center mb-2',
                        selectedCategoryId === null
                            ? 'bg-blue-100 text-blue-600'
                            : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200',
                    ]"
                >
                    <CubeIcon class="w-6 h-6" />
                </div>
                <span
                    :class="[
                        'text-xs font-medium text-center leading-tight',
                        selectedCategoryId === null
                            ? 'text-blue-700'
                            : 'text-slate-600',
                    ]"
                >
                    All
                </span>
            </button>

            <!-- Category Buttons -->
            <template v-for="category in props.categories" :key="category.id">
                <button
                    @click="selectCategory(category.id)"
                    :class="[
                        'w-full flex flex-col items-center p-2 rounded-lg transition-all duration-200',
                        selectedCategoryId === category.id
                            ? 'bg-blue-50 border border-blue-200 shadow-sm'
                            : 'hover:bg-slate-50 border border-transparent',
                    ]"
                    class="group"
                >
                    <!-- Category Image or Icon -->
                    <div
                        :class="[
                            'w-12 h-12 rounded-lg flex items-center justify-center mb-2 overflow-hidden border',
                            selectedCategoryId === category.id
                                ? 'bg-blue-100 border-blue-200'
                                : 'bg-slate-100 border-slate-200 group-hover:bg-slate-200',
                        ]"
                    >
                        <img
                            v-if="category.featured_image_url"
                            :src="category.featured_image_url"
                            :alt="category.name"
                            class="w-full h-full object-cover rounded-lg"
                        />
                        <component
                            v-else
                            :is="getCategoryIconComponent(category.name)"
                            :class="[
                                'w-6 h-6',
                                selectedCategoryId === category.id
                                    ? 'text-blue-600'
                                    : 'text-slate-500',
                            ]"
                        />
                    </div>

                    <!-- Category Name -->
                    <span
                        :class="[
                            'text-xs font-medium text-center leading-tight',
                            selectedCategoryId === category.id
                                ? 'text-blue-700'
                                : 'text-slate-600',
                        ]"
                    >
                        {{ truncateName(category.name) }}
                    </span>
                </button>
            </template>
        </div>
    </aside>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { CubeIcon } from "@heroicons/vue/24/outline";
import Category from "@/Types/Category";
import BeverageIcon from "../icons/CashierIcons/BeverageIcon.vue";
import FoodIcon from "../icons/CashierIcons/FoodIcon.vue";
import DessertIcon from "../icons/CashierIcons/DessertIcon.vue";
import ShoppingBagIcon from "../icons/CashierIcons/ShoppingBagIcon.vue";

const props = defineProps<{
    categories: Category[];
}>();

const emit = defineEmits<{
    categorySelected: [categoryId: number | null];
}>();

const selectedCategoryId = ref<number | null>(null);

const selectCategory = (categoryId: number | null) => {
    selectedCategoryId.value = categoryId;
    emit("categorySelected", categoryId);
};

const getCategoryIconComponent = (categoryName: string) => {
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

const truncateName = (name: string) => {
    if (name.length <= 10) return name;
    return name.substring(0, 9) + "...";
};
</script>
