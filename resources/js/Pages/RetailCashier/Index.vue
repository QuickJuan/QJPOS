<template>
    <CashieringLayout>
        <!-- Main grid: left catalog, right order panel -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 p-4 lg:p-8">
            <!-- Left: Sidebar / Categories + Products -->
            <section
                class="col-span-1 lg:col-span-8 bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200"
            >
                <div
                    class="flex items-center justify-between px-8 py-6 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white"
                >
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-3">
                            <img
                                :src="
                                    props.currentUser?.profile_photo_url ||
                                    '/images/default-avatar.png'
                                "
                                :alt="props.currentUser?.name || 'Cashier'"
                                class="w-10 h-10 rounded-full object-cover border-2 border-slate-200"
                            />
                            <div>
                                <div class="text-lg font-bold text-slate-800">
                                    {{ props.currentUser?.name || "Cashier" }}
                                </div>
                                <div class="text-xs text-slate-600">
                                    Shift #12
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <button
                            class="px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-colors shadow-sm flex items-center gap-2"
                        >
                            <span class="text-slate-500">
                                <ChartBarIcon class="w-4 h-4" />
                            </span>
                            Review Transactions
                        </button>
                        <button
                            class="px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-colors shadow-sm flex items-center gap-2"
                        >
                            <span class="text-slate-500">
                                <CogIcon class="w-4 h-4" />
                            </span>
                            More
                        </button>
                    </div>
                </div>

                <div class="flex">
                    <!-- Category Grid -->
                    <CategoryAside :categories="props.categories" />

                    <!-- Products Grid -->
                    <ProductGrid
                        :products="props.products"
                        @add-to-cart="addToCart"
                    />
                </div>
            </section>

            <!-- Right: Order panel -->
            <OrderPanel
                :orderItems="orderItems"
                :selected-order-item="selectedOrderItem"
            />
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { ChartBarIcon, CogIcon } from "@heroicons/vue/24/outline";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import CashieringSession from "@/Types/CashieringSession";
import Category from "@/Types/Category";
import CategoryAside from "@/Components/RetailCashier/CategoryAside.vue";
import Product from "@/Types/Product";
import ProductGrid from "@/Components/RetailCashier/ProductGrid.vue";
import PageProps from "@/Types/PageProps";
import OrderPanel from "@/Components/RetailCashier/OrderPanel.vue";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";

const props = defineProps<{
    pendingCashiering: CashieringSession;
    categories: Category[];
    products: Product[];
    currentUser: any;
    cartItems: any[];
}>();

const page = usePage<PageProps>();
const toast = useToast();
const showPendingCashieringDialog = ref(false);
const selectedOrderItem = ref<any>(null);

// Use cart items from props instead of local state
const orderItems = computed(() => props.cartItems || []);

// Check for pending cashiering on mount
onMounted(() => {
    const pendingCashiering = props.pendingCashiering;

    if (pendingCashiering != null) {
        showPendingCashieringDialog.value = true;
    }
});

const addToCart = (product: any) => {
    // Check if product has options
    if (product.options && product.options.length > 0) {
        // Navigate to options page
        router.visit(route("retail-cashier.product.options", product.id));
    } else {
        // Add directly to cart
        router.post(
            route("retail-cashier.cart.add"),
            {
                quantity: 1,
                product_id: product.id,
                total_price: parseFloat(product.average_cost || "0"),
                selected_options: [],
            },
            {
                preserveScroll: false,
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: page.props.flash.success,
                        life: 3000,
                    });
                },
                onError: (errors) => {
                    console.error("Failed to add item to cart:", errors);
                },
            }
        );
    }
};
</script>
