<template>
    <!-- Header with back button -->
    <div class="sticky top-0 bg-gray-50 z-10 p-3 sm:p-4 lg:p-6 pb-3">
        <div class="flex items-center gap-3 mb-4">
            <!-- Back button -->
            <button
                @click="handleBackToCategories"
                class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-primary-500 transition-all duration-200 shadow-sm hover:shadow-md"
            >
                <svg
                    class="w-5 h-5 mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
                Back to Categories
            </button>

            <h2
                class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 flex-1"
            >
                {{ categoryName }}
            </h2>
        </div>
    </div>

    <!-- Responsive product grid -->
    <div class="px-3 sm:px-4 lg:px-6 pb-6">
        <div
            class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 gap-3 sm:gap-4 lg:gap-6"
        >
            <div
                v-for="product in products"
                :key="product.id"
                class="bg-white rounded-lg cursor-pointer hover:shadow-xl transition-all duration-200 border-2 border-gray-200 hover:border-primary-500 group flex flex-col h-full"
                @click="$emit('addToCart', product)"
            >
                <!-- Product Image -->
                <div class="relative">
                    <div
                        class="rounded-t-lg bg-gray-50 h-36 flex items-center justify-center overflow-hidden"
                    >
                        <img
                            v-if="product?.featured_image_url"
                            :src="product?.featured_image_url"
                            :alt="product?.name"
                            class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-200"
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

                    <div
                        class="absolute top-1 sm:top-2 right-1 sm:right-2 bg-primary text-white text-xs px-1 sm:px-2 py-0.5 sm:py-1 rounded font-medium"
                    >
                        {{ getPrice(product) || "0.00" }}
                        {{
                            product.multiple_packaging &&
                            product.product_packagings.length > 1
                                ? "+"
                                : ""
                        }}
                    </div>
                </div>

                <!-- Responsive Product Info -->
                <div class="flex items-center p-2 lg:p-4">
                    <!-- Responsive Product Name -->
                    <h3
                        class="text-secondary-800 text-xs sm:text-sm leading-tight line-clamp-2"
                    >
                        {{ product.name }}
                    </h3>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Product from "@/Types/Product";
import ImageIcon from "../icons/ImageIcon.vue";
import { formatMoney } from "@/Utils/FormatMoney";
import { useProductStore } from "@/stores/productStore";

const props = defineProps<{
    products: Product[];
    categoryName?: string;
}>();

const emit = defineEmits<{
    backToCategories: [];
    addToCart: [product: Product];
}>();

const productStore = useProductStore();

const handleBackToCategories = () => {
    productStore.selectCategory(null as any);
    emit("backToCategories");
};

const getLowestPriceForProductPackagings = (data: any) => {
    const prices = data
        .filter((item: any) => item.price !== undefined)
        .map((item: any) => item.price);

    const lowestPrice = prices.length ? Math.min(...prices) : null;

    return lowestPrice;
};

const getPrice = (product: any) => {
    let price = 0;
    if (product.multiple_packaging) {
        if (product.product_packagings.length > 0) {
            price = formatMoney(
                getLowestPriceForProductPackagings(product.product_packagings),
            );
        } else {
            formatMoney(parseFloat(product.price || "0").toFixed(2));
        }
    } else {
        price = formatMoney(product.price);
    }

    return price;
};
</script>
