<template>
    <!-- Responsive header -->
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800">
            {{ categoryName }}
        </h2>
        <!-- Back button for mobile -->
        <button
            @click="$emit('backToCategories')"
            class="lg:hidden inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
        >
            ← Back
        </button>
    </div>

    <!-- Responsive product grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3 lg:gap-4">
        <div
            v-for="product in products"
            :key="product.id"
            class="bg-white rounded-lg cursor-pointer hover:shadow-lg transition-all duration-200 border border-gray-200 hover:border-primary-300 group flex flex-col h-full"
            @click="$emit('addToCart', product)"
        >
            <!-- Product Image -->
            <div class="relative">
                <div
                    class="aspect-square rounded-t-lg overflow-hidden bg-gray-50"
                >
                    <img
                        v-if="product?.featured_image_url"
                        :src="product?.featured_image_url"
                        :alt="product?.name"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                    />
                    <div
                        v-else
                        class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200"
                    >
                        <ImageIcon
                            class="w-4 h-4 sm:w-6 sm:h-6 lg:w-8 lg:h-8 text-gray-400"
                        />
                    </div>
                </div>

                <!-- Responsive Stock Status Badge -->
                <div
                    v-if="parseFloat(product.total_onhand || '0') <= 0"
                    class="absolute top-1 sm:top-2 right-1 sm:right-2 bg-error text-white text-xs px-1 sm:px-2 py-0.5 sm:py-1 rounded font-medium"
                >
                    <span class="hidden sm:inline">Out of Stock</span>
                    <span class="sm:hidden">Out</span>
                </div>
                <div
                    v-else-if="parseFloat(product.total_onhand || '0') <= 10"
                    class="absolute top-1 sm:top-2 right-1 sm:right-2 bg-warning text-white text-xs px-1 sm:px-2 py-0.5 sm:py-1 rounded font-medium"
                >
                    <span class="hidden sm:inline">Low Stock</span>
                    <span class="sm:hidden">Low</span>
                </div>
            </div>

            <!-- Responsive Product Info -->
            <div class="flex flex-col flex-1 p-2 sm:p-3">
                <!-- Responsive Product Name -->
                <h3
                    class="text-secondary-800 text-xs sm:text-sm lg:text-base leading-tight line-clamp-2 mb-1 sm:mb-2 font-semibold min-h-[2.5rem] sm:min-h-[3rem]"
                >
                    {{ product.name }}
                </h3>

                <!-- Spacer to push price/button to bottom -->
                <div class="flex-1"></div>

                <!-- Responsive Price and Pick Button -->
                <div
                    class="flex flex-row justify-between items-center mt-auto gap-1 sm:gap-2"
                >
                    <!-- Responsive Price -->
                    <p
                        class="text-sm sm:text-base lg:text-lg font-bold text-secondary-800 truncate"
                    >
                        {{
                            formatMoney(
                                parseFloat(product.average_cost || "0").toFixed(2)
                            )
                        }}
                    </p>
                    <!-- Responsive Pick Button -->
                    <button
                        class="text-xs sm:text-sm rounded-lg sm:rounded-xl bg-primary text-white px-2 sm:px-3 py-1 sm:py-1.5 hover:bg-primary-600 transition-colors flex-shrink-0 min-h-[32px] sm:min-h-[36px] touch-manipulation"
                    >
                        <span class="hidden sm:inline">Pick</span>
                        <span class="sm:hidden">+</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</template>

<script setup lang="ts">
import Product from "@/Types/Product";
import ImageIcon from "../icons/ImageIcon.vue";
import { formatMoney } from "@/Utils/FormatMoney";


const props = defineProps<{
    products: Product[];
    categoryName?: string;
}>();

const emit = defineEmits<{
    backToCategories: [];
    addToCart: [product: Product];
}>();

</script>
