<template>
    <main class="flex-1 p-6">
        <div
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-6 items-stretch"
        >
            <!-- Loading State -->
            <div
                v-if="!props.products?.data"
                class="col-span-full flex flex-col items-center justify-center py-16 text-center"
            >
                <div
                    class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4"
                >
                    <CubeIcon class="w-8 h-8 text-slate-400" />
                </div>

                <h3 class="text-lg font-semibold text-slate-600 mb-2">
                    Loading Products
                </h3>
                <p class="text-slate-500">
                    Please wait while we load the menu...
                </p>
            </div>

            <!-- Empty State -->
            <div
                v-else-if="props.products.data.length === 0"
                class="col-span-full flex flex-col items-center justify-center py-20 text-center"
            >
                <div
                    class="w-20 h-20 bg-gray-300 rounded-full flex items-center justify-center"
                >
                    <ImageIcon class="w-8 h-8 text-gray-600" />
                </div>
                <h3 class="text-xl font-semibold text-slate-600 mb-2">
                    No Products Found
                </h3>
                <p class="text-slate-500 max-w-md">
                    There are no products available in this category at the
                    moment. Please check back later or select a different
                    category.
                </p>
            </div>

            <!-- Product tiles (preview using products) -->
            <div
                v-else
                v-for="product in props.products.data"
                :key="product.id"
                class="relative border border-slate-200 rounded-xl p-5 bg-white hover:shadow-xl hover:border-indigo-300 transition-all duration-200 cursor-pointer flex flex-col group overflow-hidden h-full"
            >
                <!-- Stock Status Badge -->
                <div class="absolute top-3 right-3 z-10">
                    <div
                        v-if="parseFloat(product.total_onhand || '0') > 10"
                        class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium"
                    >
                        In Stock
                    </div>
                    <div
                        v-else-if="parseFloat(product.total_onhand || '0') > 0"
                        class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full font-medium"
                    >
                        Low Stock
                    </div>
                    <div
                        v-else
                        class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-medium"
                    >
                        Out of Stock
                    </div>
                </div>

                <!-- Options Indicator -->
                <div
                    v-if="product.options && product.options.length > 0"
                    class="absolute top-3 left-3 z-10"
                >
                    <div
                        class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full font-medium flex items-center gap-1"
                    >
                        <span
                            class="w-1.5 h-1.5 bg-indigo-500 rounded-full"
                        ></span>
                        With options
                    </div>
                </div>

                <!-- Product Image -->
                <div
                    class="h-40 flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 rounded-lg mb-4 group-hover:from-indigo-50 group-hover:to-blue-50 transition-colors relative"
                >
                    <div
                        class="absolute inset-0 bg-white/20 group-hover:bg-white/10 transition-colors"
                    />
                    <div
                        v-if="product?.productImage"
                        class="w-full h-full flex items-center justify-center overflow-hidden rounded-lg"
                    >
                        <img
                            :src="product?.productImage"
                            :alt="product?.name"
                            class="w-full h-full object-cover rounded-lg"
                        />
                    </div>
                    <div
                        v-else
                        class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center"
                    >
                        <ImageIcon class="w-8 h-8 text-gray-600" />
                    </div>
                </div>

                <!-- Product Info -->
                <div class="flex-1 space-y-2">
                    <div>
                        <h3
                            class="text-base font-bold text-slate-900 mb-1 line-clamp-2 leading-tight"
                        >
                            {{ product.name }}
                        </h3>
                        <p
                            v-if="product.description"
                            class="text-xs text-slate-600 line-clamp-2 leading-relaxed"
                        >
                            {{ product.description }}
                        </p>
                    </div>

                    <!-- Category Badge -->
                    <div
                        v-if="product.category"
                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700"
                    >
                        {{ product.category.name }}
                    </div>
                </div>

                <!-- Price and Actions -->
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <div class="text-xl font-bold text-slate-900">
                                {{
                                    formatMoney(
                                        parseFloat(
                                            product.average_cost || "0"
                                        ).toFixed(2)
                                    )
                                }}
                            </div>
                            <div
                                v-if="
                                    product.options &&
                                    product.options.length > 0
                                "
                                class="text-xs text-slate-500"
                            >
                                + options available
                            </div>
                        </div>
                        <button
                            class="bg-indigo-600 text-white p-3 rounded-xl hover:bg-indigo-700 hover:shadow-lg transition-all duration-200 flex items-center justify-center group-hover:scale-110 disabled:opacity-50 disabled:cursor-not-allowed"
                            @click="handleAddToCart(product)"
                        >
                            <!-- :disabled=" parseFloat(product.total_onhand || '0')
                            <= 0 " -->
                            <PlusIcon class="w-5 h-5" />
                        </button>
                    </div>
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
