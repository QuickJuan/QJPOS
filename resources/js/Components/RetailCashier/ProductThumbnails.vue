<template>
    <h2 class="text-xl font-bold text-gray-800 mb-4">{{ categoryName }}</h2>
    <div
        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4"
    >
        <div
            v-for="product in products"
            :key="product.id"
            @click="$emit('addToCart', product)"
            class="bg-white rounded-lg cursor-pointer hover:shadow-lg transition-all duration-200 border border-gray-100 hover:border-gray-200 group flex flex-col h-full"
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
                        <ImageIcon class="w-8 h-8 text-gray-400" />
                    </div>
                </div>

                <!-- Stock Status Badge -->
                <div
                    v-if="parseFloat(product.total_onhand || '0') <= 0"
                    class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-lg font-medium"
                >
                    Out of Stock
                </div>
                <div
                    v-else-if="parseFloat(product.total_onhand || '0') <= 10"
                    class="absolute top-2 right-2 bg-orange-500 text-white text-xs px-2 py-1 rounded-lg font-medium"
                >
                    Low Stock
                </div>
            </div>

            <!-- Product Info - Flexible content area -->
            <div class="flex flex-col flex-1 p-3">
                <!-- Product Name -->
                <h3
                    class="text-gray-900 text-sm leading-tight line-clamp-2 mb-2"
                >
                    {{ product.name }}
                </h3>

                <!-- Spacer to push price/button to bottom -->
                <div class="flex-1"></div>

                <!-- Price and Pick Button - Always at bottom -->
                <div class="flex flex-row justify-between items-center mt-auto">
                    <p class="text-lg font-bold text-gray-900">
                        {{
                            formatMoney(
                                parseFloat(product.average_cost || "0").toFixed(
                                    2
                                )
                            )
                        }}
                    </p>
                    <button
                        class="text-xs rounded-xl bg-blue-300 px-2 py-1 hover:bg-blue-400 transition-colors"
                    >
                        Pick
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ArrowLeftIcon, CubeIcon } from "@heroicons/vue/24/outline";
import Product from "@/Types/Product";
import ImageIcon from "../icons/ImageIcon.vue";
import { formatMoney } from "@/Utils/FormatMoney";

defineProps<{
    products: Product[];
    categoryName?: string;
}>();

defineEmits<{
    backToCategories: [];
    addToCart: [product: Product];
}>();
</script>
