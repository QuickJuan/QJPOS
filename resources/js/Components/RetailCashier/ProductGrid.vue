<template>
    <main class="flex-1 p-6">
        <!-- Loading State -->
        <div
            v-if="!props.products || props.products.length === 0"
            class="flex flex-col items-center justify-center py-16 text-center"
        >
            <div
                class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4"
            >
                <CubeIcon class="w-8 h-8 text-slate-400" />
            </div>
            <h3 class="text-lg font-semibold text-slate-600 mb-2">
                No Products Found
            </h3>
            <p class="text-slate-500">
                There are no products available in this category.
            </p>
        </div>

        <!-- Products Grid -->
        <div
            v-else
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-4"
        >
            <div
                v-for="product in props.products"
                :key="product.id"
                @click="handleAddToCart(product)"
                class="bg-white rounded-2xl p-4 cursor-pointer hover:shadow-lg transition-all duration-200 border border-gray-100 hover:border-gray-200 group"
            >
                <!-- Product Image -->
                <div class="relative mb-3">
                    <div
                        class="aspect-square rounded-xl overflow-hidden bg-gray-50"
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
                        v-else-if="
                            parseFloat(product.total_onhand || '0') <= 10
                        "
                        class="absolute top-2 right-2 bg-orange-500 text-white text-xs px-2 py-1 rounded-lg font-medium"
                    >
                        Low Stock
                    </div>
                </div>

                <!-- Product Info -->
                <div class="space-y-1">
                    <h3
                        class="font-semibold text-gray-900 text-sm leading-tight line-clamp-2"
                    >
                        {{ product.name }}
                    </h3>
                    <p class="text-lg font-bold text-gray-900">
                        {{
                            formatMoney(
                                parseFloat(product.average_cost || "0").toFixed(
                                    2
                                )
                            )
                        }}
                    </p>
                </div>

                <!-- Options Indicator -->
                <div
                    v-if="product.options && product.options.length > 0"
                    class="mt-2"
                >
                    <span
                        class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-md"
                    >
                        + options
                    </span>
                </div>
            </div>
        </div>
    </main>
</template>

<script setup lang="ts">
import Product from "@/Types/Product";
import CubeIcon from "../icons/CubeIcon.vue";
import PlusIcon from "../icons/CashierIcons/PlusIcon.vue";
import { formatMoney } from "@/Utils/FormatMoney";
import ImageIcon from "../icons/ImageIcon.vue";

const props = defineProps<{
    products: Product[];
}>();

const emit = defineEmits(["addToCart"]);

const handleAddToCart = (product: any) => {
    emit("addToCart", product);
};
</script>
