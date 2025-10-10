<template>
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto">
            <!-- Top: Header (keeps existing Header component) -->
            <Header />

            <!-- Main grid: left catalog, right order panel -->
            <div
                class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 p-4 lg:p-8"
            >
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
                                    <div
                                        class="text-lg font-bold text-slate-800"
                                    >
                                        {{
                                            props.currentUser?.name || "Cashier"
                                        }}
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
                        <!-- Categories column -->
                        <aside
                            class="w-56 border-r border-slate-200 p-6 hidden md:block bg-slate-50"
                        >
                            <h3
                                class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4"
                            >
                                Categoriess
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
                                <template
                                    v-for="category in props.categories"
                                    :key="category.id"
                                >
                                    <button
                                        class="w-full text-left px-4 py-3 rounded-lg hover:bg-white hover:shadow-sm transition-all text-slate-700 font-medium flex items-center gap-3"
                                    >
                                        <span class="text-slate-500">
                                            <BeverageIcon
                                                v-if="
                                                    getCategoryIcon(
                                                        category.name
                                                    ) === 'beverage'
                                                "
                                            />
                                            <FoodIcon
                                                v-else-if="
                                                    getCategoryIcon(
                                                        category.name
                                                    ) === 'food'
                                                "
                                            />
                                            <DessertIcon
                                                v-else-if="
                                                    getCategoryIcon(
                                                        category.name
                                                    ) === 'dessert'
                                                "
                                            />
                                            <ShoppingBagIcon v-else />
                                        </span>
                                        {{ category.name }}
                                    </button>
                                </template>
                            </div>
                        </aside>

                        <!-- Products grid -->
                        <main class="flex-1 p-6">
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-6 items-start"
                            >
                                <!-- Loading State -->
                                <div
                                    v-if="!props.products?.data"
                                    class="col-span-full flex flex-col items-center justify-center py-16 text-center"
                                >
                                    <div
                                        class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4"
                                    >
                                        <CubeIcon
                                            class="w-8 h-8 text-slate-400"
                                        />
                                    </div>
                                    <h3
                                        class="text-lg font-semibold text-slate-600 mb-2"
                                    >
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
                                        class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-6"
                                    >
                                        <CubeIcon
                                            class="w-10 h-10 text-slate-400"
                                        />
                                    </div>
                                    <h3
                                        class="text-xl font-semibold text-slate-600 mb-2"
                                    >
                                        No Products Found
                                    </h3>
                                    <p class="text-slate-500 max-w-md">
                                        There are no products available in this
                                        category at the moment. Please check
                                        back later or select a different
                                        category.
                                    </p>
                                </div>

                                <!-- Product tiles (preview using products) -->
                                <div
                                    v-else
                                    v-for="product in props.products.data"
                                    :key="product.id"
                                    class="relative border border-slate-200 rounded-xl p-5 bg-white hover:shadow-xl hover:border-indigo-300 transition-all duration-200 cursor-pointer flex flex-col group overflow-hidden"
                                >
                                    <!-- Stock Status Badge -->
                                    <div class="absolute top-3 right-3 z-10">
                                        <div
                                            v-if="
                                                parseFloat(
                                                    product.total_onhand || '0'
                                                ) > 10
                                            "
                                            class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium"
                                        >
                                            In Stock
                                        </div>
                                        <div
                                            v-else-if="
                                                parseFloat(
                                                    product.total_onhand || '0'
                                                ) > 0
                                            "
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
                                        v-if="
                                            product.options &&
                                            product.options.length > 0
                                        "
                                        class="absolute top-3 left-3 z-10"
                                    >
                                        <div
                                            class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full font-medium flex items-center gap-1"
                                        >
                                            <span
                                                class="w-1.5 h-1.5 bg-indigo-500 rounded-full"
                                            ></span>
                                            Customizable
                                        </div>
                                    </div>

                                    <!-- Product Image -->
                                    <div
                                        class="h-40 flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 rounded-lg mb-4 group-hover:from-indigo-50 group-hover:to-blue-50 transition-colors relative"
                                    >
                                        <div
                                            class="absolute inset-0 bg-white/20 group-hover:bg-white/10 transition-colors"
                                        ></div>
                                        <div
                                            class="w-20 h-20 bg-slate-300 rounded-full flex items-center justify-center shadow-sm"
                                        >
                                            <CubeIcon
                                                class="w-8 h-8 text-slate-600"
                                            />
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
                                    <div
                                        class="mt-4 pt-3 border-t border-slate-100"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <div class="flex flex-col">
                                                <div
                                                    class="text-xl font-bold text-slate-900"
                                                >
                                                    ${{
                                                        parseFloat(
                                                            product.average_cost ||
                                                                "0"
                                                        ).toFixed(2)
                                                    }}
                                                </div>
                                                <div
                                                    v-if="
                                                        product.options &&
                                                        product.options.length >
                                                            0
                                                    "
                                                    class="text-xs text-slate-500"
                                                >
                                                    + options available
                                                </div>
                                            </div>
                                            <button
                                                class="bg-indigo-600 text-white p-3 rounded-xl hover:bg-indigo-700 hover:shadow-lg transition-all duration-200 flex items-center justify-center group-hover:scale-110 disabled:opacity-50 disabled:cursor-not-allowed"
                                                @click="addToCart(product)"
                                            >
                                                <!-- :disabled="
                                                    parseFloat(
                                                        product.total_onhand ||
                                                            '0'
                                                    ) <= 0
                                                " -->
                                                <PlusIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </main>
                    </div>
                </section>

                <!-- Right: Order panel -->
                <aside
                    class="col-span-1 lg:col-span-4 bg-white rounded-xl shadow-lg p-6 flex flex-col lg:sticky lg:top-6 border border-slate-200"
                >
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <div
                                class="text-xl font-bold text-slate-800 flex items-center gap-3"
                            >
                                <div
                                    class="w-3 h-3 bg-green-500 rounded-full animate-pulse"
                                />
                                Order Summary
                            </div>
                            <div class="text-sm text-slate-600 mt-1">
                                Table 1 • 2 guests
                            </div>
                        </div>
                        <div
                            class="text-sm text-slate-500 font-medium bg-slate-100 px-3 py-1 rounded-full"
                        >
                            #Shift:12
                        </div>
                    </div>

                    <div
                        class="flex-1 overflow-auto border border-slate-200 rounded-lg p-4 mb-6 max-h-[50vh] bg-slate-50"
                    >
                        <div v-if="tab === 'lines'">
                            <OrderItemList
                                :order-items="orderItems"
                                @edit="handleEdit"
                                @delete="handleDelete"
                                @selection-change="handleSelectionChange"
                            />
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center h-40 text-slate-400 font-medium"
                        >
                            No payments view
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-6 mt-4 space-y-4">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-slate-600 font-medium">
                                Subtotal
                            </div>
                            <div class="font-semibold text-slate-800">
                                ${{ subtotal.toFixed(2) }}
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-slate-600 font-medium">
                                Tax (6.25%)
                            </div>
                            <div class="font-semibold text-slate-800">
                                ${{ tax.toFixed(2) }}
                            </div>
                        </div>
                        <div
                            class="flex justify-between items-center text-xl font-bold text-slate-900 border-t border-slate-200 pt-4"
                        >
                            <div>Total</div>
                            <div>${{ total.toFixed(2) }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-6">
                            <button
                                class="py-3 px-4 bg-slate-100 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-200 hover:shadow-md transition-all"
                            >
                                Place to table
                            </button>
                            <button
                                class="py-3 px-4 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 hover:shadow-md transition-all"
                            >
                                Proceed to checkout
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
            <!-- (Footer component removed here to avoid duplicate summary area) -->
        </div>

        <!-- Edit Dialog -->
        <Dialog
            v-model:visible="visible"
            modal
            :header="`Edit ${selectedOrderItem?.name || ''}`"
            :style="{ width: '25rem' }"
        >
            <div class="flex flex-col gap-4 mb-4">
                <label for="username" class="font-semibold w-24"
                    >Quantity</label
                >
                <InputNumber
                    v-model="selectedOrderItem.quantity"
                    showButtons
                    buttonLayout="horizontal"
                    style="width: 1rem"
                    :min="0"
                    :max="99"
                >
                    <template #incrementbuttonicon>
                        <span class="pi pi-plus" />
                    </template>
                    <template #decrementbuttonicon>
                        <span class="pi pi-minus" />
                    </template>
                </InputNumber>
            </div>
            <div class="flex justify-end gap-2">
                <Button
                    type="button"
                    label="Cancel"
                    severity="secondary"
                    @click="visible = false"
                />
                <Button type="button" label="Save" @click="visible = false" />
            </div>
        </Dialog>

        <!-- For Pending Cashiering -->
        <Dialog
            v-model:visible="showPendingCashieringDialog"
            modal
            header="Edit Profile"
            :style="{ width: '25rem' }"
        >
            <span class="text-surface-500 dark:text-surface-400 block mb-8">
                You have a pending cashiering session. Would you like to
                continue it, or start a new session and close the pending one?
            </span>

            <div class="flex justify-end gap-2">
                <Button
                    type="button"
                    label="No, close it"
                    severity="secondary"
                    @click="visible = false"
                />
                <Button
                    type="button"
                    label="Yes, continue"
                    @click="visible = false"
                />
            </div>
        </Dialog>

        <!-- Product Options Dialog -->
        <Dialog
            v-model:visible="showProductOptionsDialog"
            modal
            :header="`Customize ${selectedProduct?.name || ''}`"
            :style="{ width: '32rem' }"
            class="bg-white"
        >
            <div class="bg-white p-2">
                <div v-if="selectedProduct?.options" class="space-y-6">
                    <div
                        v-for="option in selectedProduct.options"
                        :key="option.id"
                        class="bg-slate-50 rounded-lg p-4 border border-slate-200"
                    >
                        <h4 class="font-semibold text-slate-800 mb-4 text-base">
                            {{ option.name }}
                        </h4>
                        <div class="space-y-3">
                            <label
                                v-for="item in option.option_items"
                                :key="item.id"
                                class="flex items-center justify-between p-3 bg-white rounded-lg border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 transition-all cursor-pointer"
                            >
                                <div class="flex items-center space-x-3">
                                    <input
                                        type="radio"
                                        :name="`option-${option.id}`"
                                        :value="item"
                                        v-model="selectedOptions[option.id]"
                                        class="text-indigo-600 focus:ring-indigo-500 w-4 h-4"
                                    />
                                    <span class="text-slate-700 font-medium">{{
                                        item.name
                                    }}</span>
                                </div>
                                <span
                                    v-if="
                                        item.price && parseFloat(item.price) > 0
                                    "
                                    class="text-green-600 font-semibold bg-green-100 px-2 py-1 rounded-md text-sm"
                                >
                                    +${{ parseFloat(item.price).toFixed(2) }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-6 bg-slate-50 rounded-lg p-4 border border-slate-200"
                >
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium"
                            >Base Price:</span
                        >
                        <span class="text-slate-800"
                            >${{
                                parseFloat(
                                    selectedProduct?.average_cost || "0"
                                ).toFixed(2)
                            }}</span
                        >
                    </div>
                    <div
                        v-if="Object.keys(selectedOptions).length > 0"
                        class="mt-2 space-y-1"
                    >
                        <div
                            v-for="(optionItem, optionId) in selectedOptions"
                            :key="optionId"
                            class="flex justify-between items-center text-sm"
                        >
                            <span class="text-slate-600">{{
                                optionItem?.name
                            }}</span>
                            <span class="text-green-600"
                                >+${{
                                    parseFloat(
                                        optionItem?.price || "0"
                                    ).toFixed(2)
                                }}</span
                            >
                        </div>
                    </div>
                    <div
                        class="flex justify-between items-center pt-3 mt-3 border-t border-slate-300"
                    >
                        <span class="text-lg font-bold text-slate-800"
                            >Total:</span
                        >
                        <span class="text-xl font-bold text-indigo-600"
                            >${{ calculateOptionTotal().toFixed(2) }}</span
                        >
                    </div>
                </div>
            </div>

            <template #footer>
                <div
                    class="flex justify-end gap-3 bg-slate-50 px-6 py-4 border-t border-slate-200"
                >
                    <Button
                        type="button"
                        label="Cancel"
                        severity="secondary"
                        @click="showProductOptionsDialog = false"
                        class="px-6 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:border-slate-400 rounded-lg font-medium"
                    />
                    <Button
                        type="button"
                        label="Add to Order"
                        @click="confirmAddToCart"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium shadow-sm"
                    />
                </div>
            </template>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { Button, Dialog, InputNumber } from "primevue";
import { ChartBarIcon, CogIcon, CubeIcon } from "@heroicons/vue/24/outline";
import BeverageIcon from "@/Components/icons/CashierIcons/BeverageIcon.vue";
import FoodIcon from "@/Components/icons/CashierIcons/FoodIcon.vue";
import DessertIcon from "@/Components/icons/CashierIcons/DessertIcon.vue";
import ShoppingBagIcon from "@/Components/icons/CashierIcons/ShoppingBagIcon.vue";
import PlusIcon from "@/Components/icons/CashierIcons/PlusIcon.vue";
import OrderItemList from "@/Components/OrderItemList.vue";
import Header from "./Partials/Header.vue";
import CashieringSession from "@/Types/CashieringSession";
import Category from "@/Types/Category";
import Product from "@/Types/Product";

const props = defineProps<{
    pendingCashiering: CashieringSession;
    categories: Category[];
    products: any; // Laravel ResourceCollection
    currentUser: any;
}>();

const visible = ref(false);
const showPendingCashieringDialog = ref(false);
const showProductOptionsDialog = ref(false);
const selectedOrderItem = ref<any>(null);
const selectedProduct = ref<any>(null);
const selectedOptions = ref<any>({});
const tab = ref<"lines" | "payments">("lines");

// Separate cart/order items from catalog products
const cartItems = ref<any[]>([]);

const orderItems = computed(() => cartItems.value);

const subtotal = computed(() =>
    orderItems.value.reduce(
        (sum, item) =>
            sum +
            parseFloat(item.price || item.average_cost || "0") * item.quantity,
        0
    )
);
const tax = computed(() => subtotal.value * 0.0625); // Example 6.25% tax
const total = computed(() => subtotal.value + tax.value);

const handleEdit = (orderItem: any) => {
    visible.value = true;
    selectedOrderItem.value = orderItem;
};

const handleDelete = (orderItem: any) => {
    // Remove item from cart
    cartItems.value = cartItems.value.filter((i: any) => i.id !== orderItem.id);
};

const handleSelectionChange = (selectedItems: any[]) => {
    // Update checked status for all items
    cartItems.value = cartItems.value.map((item) => ({
        ...item,
        checked: selectedItems.some((selected) => selected.id === item.id),
    }));
};

const addToCart = (product: any) => {
    // Check if product has options
    if (product.options && product.options.length > 0) {
        // Show options dialog
        selectedProduct.value = product;
        selectedOptions.value = {};
        showProductOptionsDialog.value = true;
    } else {
        // Add directly to cart
        addProductToCart(product);
    }
};

const addProductToCart = (product: any, selectedOptionsData: any = {}) => {
    const existingItem = cartItems.value.find((item) => item.id === product.id);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        // Calculate total price including options
        let totalPrice = parseFloat(product.average_cost || "0");
        const selectedOptionItems: any[] = [];

        // Add option prices
        Object.values(selectedOptionsData).forEach((optionItem: any) => {
            if (optionItem && optionItem.price) {
                totalPrice += parseFloat(optionItem.price);
                selectedOptionItems.push(optionItem);
            }
        });

        cartItems.value.push({
            ...product,
            quantity: 1,
            price: totalPrice,
            selectedOptions: selectedOptionItems,
            checked: false, // For OrderItemList selection
        });
    }
};

const confirmAddToCart = () => {
    if (selectedProduct.value) {
        addProductToCart(selectedProduct.value, selectedOptions.value);
        showProductOptionsDialog.value = false;
        selectedProduct.value = null;
        selectedOptions.value = {};
    }
};

const calculateOptionTotal = () => {
    if (!selectedProduct.value) return 0;

    let total = parseFloat(selectedProduct.value.average_cost || "0");

    Object.values(selectedOptions.value).forEach((optionItem: any) => {
        if (optionItem && optionItem.price) {
            total += parseFloat(optionItem.price);
        }
    });

    return total;
};

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

onMounted(() => {
    const pendingCashiering = props.pendingCashiering;

    if (pendingCashiering != null) {
        showPendingCashieringDialog.value = true;
    }
});
</script>
