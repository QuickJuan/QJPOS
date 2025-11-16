<template>
    <CashieringLayout :current-user="props.currentUser">
        <!-- Main responsive grid: categories, products, and order panel -->
        <div class="flex flex-col lg:flex-row h-full min-w-0">
            <!-- Categories - Top horizontal on mobile, left sidebar on desktop -->
            <aside
                class="bg-gray-50 flex-shrink-0 overflow-hidden w-full lg:w-28 xl:w-64 lg:h-full"
            >
                <CategoryThumbnails
                    :categories="activeCategories"
                    :selected-category-id="selectedCategoryId"
                    @categorySelected="handleCategorySelection"
                />
            </aside>

            <!-- Center: Products Area -->
            <section class="flex-1 bg-gray-50 overflow-hidden min-h-0">
                <!-- Products - Scrollable area -->
                <div class="h-full p-3 sm:p-4 lg:p-6 overflow-y-auto">
                    <ProductThumbnails
                        v-if="selectedCategoryId"
                        :products="filteredProducts"
                        :category-name="selectedCategoryName"
                        @backToCategories="backToCategories"
                        @addToCart="addToCart"
                    />
                    <div v-else class="flex items-center justify-center h-full">
                        <p
                            class="text-gray-500 text-base sm:text-lg text-center px-4"
                        >
                            Select a category to view products
                        </p>
                    </div>
                </div>
            </section>

            <!-- Right: Order Summary - Full width on mobile, fixed width on desktop -->
            <section
                class="w-full lg:w-[400px] xl:w-[450px] 2xl:w-[500px] flex-shrink-0"
            >
                <OrderSummary
                    :orderItems="orderItems"
                    :selected-order-item="selectedOrderItem"
                    :available-discounts="props.availableDiscounts"
                    :available-modifiers="props.availableModifiers"
                    :table-id="tableId"
                    :location-type="locationType"
                    :cart="cart"
                    :current-table="props.currentTable"
                    :sub-total="subTotal"
                    :total="total"
                    :less-tax-total="lessTaxTotal"
                    :less-discount-total="lessDiscountTotal"
                    :tax-rate="taxRate"
                    :bill-footer="billFooter"
                    :receipt-footer="receiptFooter"
                    @selected-order-type="updateOrderType"
                    @show-receipt="handleShowReceipt"
                />
            </section>
        </div>

        <PackagingSelectionModal
            v-model="showPackagingModal"
            :product="selectedProductForPackaging"
            @confirm="handlePackagingConfirm"
            @cancel="handlePackagingCancel"
        />
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import Category from "@/Types/Category";
import CategoryThumbnails from "@/Components/RetailCashier/CategoryThumbnails.vue";
import ProductThumbnails from "@/Components/RetailCashier/ProductThumbnails.vue";
import PageProps from "@/Types/PageProps";
import OrderSummary from "@/Components/RetailCashier/OrderSummary.vue";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import PackagingSelectionModal from "@/Components/RetailCashier/PackagingSelectionModal.vue";
import { useCashierCache } from "@/composables/useCashierCache";

const props = defineProps<{
    categories: { data: Category[] } | Category[];
    currentUser: any;
    cart: any;
    cartItems: any[];
    availableDiscounts: any[];
    availableModifiers: any[];
    currentTable: any;
    pendingCashiering: any;
    subTotal: number;
    total: number;
    lessTaxTotal: number;
    lessDiscountTotal: number;
    taxRate: number;
    billFooter: any;
    receiptFooter: any;
}>();

const page = usePage<PageProps>();
const toast = useToast();
const tableId = ref(null);
const locationType = ref(null);
const selectedOrderItem = ref<any>(null);
const selectedCategoryId = ref<number | null>(null);
const selectedOrderType = ref<any>("dine-in");
const showPackagingModal = ref(false);
const selectedProductForPackaging = ref<any>(null);
const receiptData = ref({
    receiptNumber: "",
    date: "",
    subtotal: 0,
    taxAmount: 0,
    discountAmount: 0,
    totalAmount: 0,
    paymentInfo: null as any,
});

// Initialize client-side cache
const {
    categories: cachedCategories,
    discounts: cachedDiscounts,
    loadCategories,
    loadDiscounts,
} = useCashierCache();

const orderItems = computed(() => props.cartItems || []);

const activeCategories = computed(() => {
    const getCategoriesData = (): Category[] => {
        if (Array.isArray(props.categories)) {
            return props.categories;
        } else if (
            props.categories &&
            typeof props.categories === "object" &&
            "data" in props.categories &&
            Array.isArray(props.categories.data)
        ) {
            return props.categories.data;
        }
        return [];
    };

    const categoriesData = getCategoriesData();

    if (cachedCategories.value.length > 0) {
        return cachedCategories.value;
    }

    const filtered = categoriesData.filter(
        (category) => category && category.id && category.name
    );

    return filtered;
});

// Get all products from all categories
const allProducts = computed(() => {
    return activeCategories.value.flatMap(
        (category) => category.products || []
    );
});

// Filter products based on selected category
const filteredProducts = computed(() => {
    if (selectedCategoryId.value === null) {
        return allProducts.value;
    }
    const selectedCategory = activeCategories.value.find(
        (cat) => cat.id === selectedCategoryId.value
    );
    return selectedCategory?.products || [];
});

// Get selected category name
const selectedCategoryName = computed(() => {
    if (selectedCategoryId.value === null) {
        return null;
    }
    const category = activeCategories.value.find(
        (cat) => cat.id === selectedCategoryId.value
    );
    return category?.name || "Unknown Category";
});

// Handle category selection
const handleCategorySelection = (categoryId: number | null) => {
    selectedCategoryId.value = categoryId;
};

// Back to categories
const backToCategories = () => {
    selectedCategoryId.value = null;
};

// Update order type
const updateOrderType = (type: string) => {
    selectedOrderType.value = type;
};

// Handle show receipt modal
const handleShowReceipt = (data: any) => {
    // Prepare receipt data
    const subtotal = orderItems.value.reduce(
        (sum, item) => sum + (item.price || 0),
        0
    );
    const discountAmount = orderItems.value.reduce(
        (sum, item) => sum + (item.discount || 0),
        0
    );
    const discountedSubtotal = subtotal - discountAmount;
    const taxAmount = discountedSubtotal * 0.12; // 12% VAT

    receiptData.value = {
        receiptNumber: `RCP-${Date.now()}`,
        date: new Date().toISOString(),
        subtotal: subtotal,
        taxAmount: taxAmount,
        discountAmount: discountAmount,
        totalAmount: data.total_amount,
        paymentInfo: {
            amount_paid: data.amount_paid,
            change: data.amount_paid - data.total_amount,
            method: "Cash",
        },
    };
};

// Check for pending cashiering on mount
onMounted(() => {
    const getCategoriesData = (): Category[] => {
        if (Array.isArray(props.categories)) {
            return props.categories;
        } else if (
            props.categories &&
            typeof props.categories === "object" &&
            "data" in props.categories &&
            Array.isArray(props.categories.data)
        ) {
            return props.categories.data;
        }
        return [];
    };

    const categoriesData = getCategoriesData();

    // First, try to load from localStorage
    loadCategories();

    // If we have fresh server data and no cached data, or server data is newer
    if (categoriesData && categoriesData.length > 0) {
        loadCategories(categoriesData);
    } else if (cachedCategories.value.length === 0) {
        console.warn("No categories available from server or cache");
    }

    // Load discounts (keeping existing logic)
    if (props.availableDiscounts && props.availableDiscounts.length > 0) {
        loadDiscounts(props.availableDiscounts);
    }

    nextTick(() => {
        if (
            selectedCategoryId.value === null &&
            activeCategories.value.length > 0
        ) {
            selectedCategoryId.value = activeCategories.value[0].id;
        }
    });

    // Get the tableId in URL if available
    const params = new URLSearchParams(window.location.search);
    const urlTableId = params.get("tableId");
    const urlLocationType = params.get("locationType");
    tableId.value = urlTableId;
    locationType.value = urlLocationType;
    selectedOrderType.value = urlLocationType;

    // If no tableId in URL and we have a pending cashiering session, redirect to tables page
    // if (!urlTableId && props.pendingCashiering) {
    //     nextTick(() => {
    //         router.visit(route("retail-cashier.tables"));
    //     });
    // }
});

const addToCart = (product: any, packaging?: any) => {
    if (packaging) {
        // Packaging already selected, proceed
        if (product.options && product.options.length > 0) {
            // Has options, redirect to options page with packaging
            router.visit(
                route("retail-cashier.product.options", {
                    product: product.id,
                    orderType: selectedOrderType.value,
                    tableId: tableId.value,
                    packagingId: packaging.id,
                })
            );
        } else {
            // No options, add to cart with packaging
            router.post(
                route("retail-cashier.cart.add"),
                {
                    quantity: 1,
                    product_id: product.id,
                    product_packaging_id: packaging.id,
                    table_id: tableId.value,
                    total_price: parseFloat(packaging.price || 0),
                    order_type: selectedOrderType.value,
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
    } else {
        // No packaging provided, check if product has packagings
        if (
            product.product_packagings &&
            product.multiple_packaging && // Check if the multiple packaging is true
            product.product_packagings.length > 0
        ) {
            if (product.product_packagings.length === 1) {
                // Auto-select the single packaging
                const packaging = product.product_packagings[0];
                if (product.options && product.options.length > 0) {
                    // Has options, redirect to options page with packaging
                    router.visit(
                        route("retail-cashier.product.options", {
                            product: product.id,
                            orderType: selectedOrderType.value,
                            tableId: tableId.value,
                            packagingId: packaging.id,
                        })
                    );
                } else {
                    // No options, add to cart with packaging
                    router.post(
                        route("retail-cashier.cart.add"),
                        {
                            quantity: 1,
                            product_id: product.id,
                            product_packaging_id: packaging.id,
                            table_id: tableId.value,
                            total_price: parseFloat(packaging.price || 0),
                            order_type: selectedOrderType.value,
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
                                console.error(
                                    "Failed to add item to cart:",
                                    errors
                                );
                            },
                        }
                    );
                }
            } else {
                // Multiple packagings, show modal
                selectedProductForPackaging.value = product;
                showPackagingModal.value = true;
            }
        } else {
            // No packaging, check options
            if (product.options && product.options.length > 0) {
                // Redirect to options page
                router.visit(
                    route("retail-cashier.product.options", {
                        product: product.id,
                        orderType: selectedOrderType.value,
                        tableId: tableId.value,
                    })
                );
            } else {
                // No options, add to cart without packaging
                router.post(
                    route("retail-cashier.cart.add"),
                    {
                        quantity: 1,
                        product_id: product.id,
                        product_packaging_id: null,
                        table_id: tableId.value,
                        total_price: parseFloat(product.average_cost || "0"),
                        order_type: selectedOrderType.value,
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
                            console.error(
                                "Failed to add item to cart:",
                                errors
                            );
                        },
                    }
                );
            }
        }
    }
};

const handlePackagingConfirm = (packaging: any) => {
    const product = selectedProductForPackaging.value;
    if (product) {
        addToCart(product, packaging);
    }
    showPackagingModal.value = false;
    selectedProductForPackaging.value = null;
};

const handlePackagingCancel = () => {
    showPackagingModal.value = false;
    selectedProductForPackaging.value = null;
};
</script>
