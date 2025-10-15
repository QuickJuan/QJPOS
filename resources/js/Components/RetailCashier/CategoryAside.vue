<template>
    <!-- Categories column -->
    <aside
        class="w-56 border-r border-slate-200 p-6 hidden md:block bg-slate-50"
    >
        <h3
            class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4"
        >
            Categories
        </h3>
        <div class="space-y-2">
            <button
                class="w-full text-left px-4 py-3 rounded-lg hover:bg-white hover:shadow-sm transition-all text-slate-700 font-medium flex items-center gap-3"
            >
                <span class="text-slate-500">
                    <CubeIcon class="w-4 h-4" />
                </span>
                All
            </button>
            <template v-for="category in props.categories" :key="category.id">
                <button
                    class="w-full text-left px-4 py-3 rounded-lg hover:bg-white hover:shadow-sm transition-all text-slate-700 font-medium flex items-center gap-3"
                >
                    <span class="text-slate-500">
                        <BeverageIcon
                            v-if="getCategoryIcon(category.name) === 'beverage'"
                        />
                        <FoodIcon
                            v-else-if="
                                getCategoryIcon(category.name) === 'food'
                            "
                        />
                        <DessertIcon
                            v-else-if="
                                getCategoryIcon(category.name) === 'dessert'
                            "
                        />
                        <ShoppingBagIcon v-else />
                    </span>
                    {{ category.name }}
                </button>
            </template>
        </div>
    </aside>
</template>

<script setup lang="ts">
import Category from "@/Types/Category";
import BeverageIcon from "../icons/CashierIcons/BeverageIcon.vue";
import FoodIcon from "../icons/CashierIcons/FoodIcon.vue";
import DessertIcon from "../icons/CashierIcons/DessertIcon.vue";
import ShoppingBagIcon from "../icons/CashierIcons/ShoppingBagIcon.vue";

const props = defineProps<{
    categories: Category[];
}>();

const getCategoryIcon = (categoryName: string) => {
    const name = categoryName.toLowerCase();
    if (name.includes("beverage") || name.includes("drink")) {
        return "beverage";
    } else if (name.includes("food") || name.includes("meal")) {
        return "food";
    } else if (name.includes("dessert") || name.includes("sweet")) {
        return "dessert";
    } else {
        return "shopping";
    }
};
</script>
