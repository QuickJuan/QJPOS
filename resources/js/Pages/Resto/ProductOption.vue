<template>
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto">
            <!-- Top: Header (keeps existing Header component) -->
            <Header />

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Back Button -->
                <div class="mb-6">
                    <button
                        @click="goBack"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors"
                    >
                        <ChevronLeftIcon class="w-4 h-4 mr-2" />
                        Back to Products
                    </button>
                </div>

                <!-- Product Header -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
                >
                    <div class="flex items-center space-x-4">
                        <!-- Product Image -->
                        <div
                            class="w-32 h-32 flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg mb-4"
                        >
                            <div
                                v-if="props.product?.data.productImage"
                                class="w-full h-full flex items-center justify-center overflow-hidden rounded-lg"
                            >
                                <img
                                    :src="props.product?.data.productImage"
                                    :alt="props.product?.data.name"
                                    class="w-full h-full object-cover rounded-lg"
                                />
                            </div>
                            <div
                                v-else
                                class="h-20 w-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg flex items-center justify-center"
                            >
                                <ImageIcon class="w-8 h-8 text-gray-600" />
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-gray-900">
                                {{ props.product.data?.name }}
                            </h1>
                            <p
                                class="text-gray-600"
                                v-html="props.product.data?.description"
                            ></p>
                            <div class="mt-2">
                                <span
                                    class="text-lg font-semibold text-gray-900"
                                >
                                    ₱{{
                                        parseFloat(
                                            props.product.data?.average_cost ||
                                                "0"
                                        ).toFixed(2)
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Options Selection -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
                >
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        Customize Your Order
                    </h2>

                    <Tabs class="w-full" :value="selectedTab" scrollable>
                        <TabList>
                            <Tab
                                v-for="option in props.product.data?.options"
                                :key="option.id"
                                :value="option.id"
                                as="div"
                                class="flex items-center gap-2"
                            >
                                <Avatar
                                    :image="option.optionImage"
                                    shape="circle"
                                />
                                <span class="font-bold whitespace-nowrap">
                                    {{ option.option_name }}
                                </span>
                            </Tab>
                        </TabList>
                        <TabPanels>
                            <TabPanel
                                v-for="option in props.product.data?.options"
                                :key="option.id"
                                :header="option.name"
                                :value="option.id"
                            >
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4"
                                >
                                    <div
                                        v-for="item in option.optionItems"
                                        :key="item.id"
                                        :class="[
                                            'relative border-2 rounded-xl p-6 cursor-pointer transition-all duration-200 hover:shadow-lg',
                                            selectedOptions[option.id]?.id ===
                                            item.id
                                                ? 'border-indigo-500 bg-indigo-50 shadow-md'
                                                : 'border-gray-200 hover:border-indigo-300',
                                        ]"
                                        @click="
                                            selectedOptions[option.id] = item
                                        "
                                    >
                                        <!-- Selection Indicator -->
                                        <div
                                            v-if="
                                                selectedOptions[option.id]
                                                    ?.id === item.id
                                            "
                                            class="absolute top-3 right-3 w-6 h-6 bg-indigo-500 rounded-full flex items-center justify-center"
                                        >
                                            <CheckIcon
                                                class="w-4 h-4 text-white"
                                            />
                                        </div>

                                        <!-- Option Image -->
                                        <div
                                            class="h-32 flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg mb-4"
                                        >
                                            <div
                                                v-if="
                                                    item?.product?.media
                                                        ?.length > 0
                                                "
                                                class="w-full h-full flex items-center justify-center overflow-hidden rounded-lg"
                                            >
                                                <img
                                                    :src="
                                                        item?.product
                                                            ?.media?.[0]
                                                            ?.original_url
                                                    "
                                                    :alt="
                                                        item?.product
                                                            ?.media?.[0]?.name
                                                    "
                                                    class="w-full h-full object-cover rounded-lg"
                                                />
                                            </div>

                                            <div
                                                v-else
                                                class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center"
                                            >
                                                <ImageIcon
                                                    class="w-8 h-8 text-gray-600"
                                                />
                                            </div>
                                        </div>

                                        <!-- Option Details -->
                                        <div class="text-center">
                                            <h5
                                                class="font-semibold text-gray-900 mb-2 text-lg"
                                            >
                                                {{ item?.product?.name }}
                                            </h5>
                                            <div
                                                v-if="
                                                    item.price &&
                                                    parseFloat(item.price) > 0
                                                "
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
                                            >
                                                +{{
                                                    formatMoney(
                                                        parseFloat(
                                                            item.price
                                                        ).toFixed(2)
                                                    )
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </TabPanel>
                        </TabPanels>
                    </Tabs>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-4 mt-8">
                        <Button
                            @click="goBack"
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors"
                        >
                            Cancel
                        </Button>
                        <button
                            @click="addToCart"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors"
                        >
                            Add to Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { route } from "ziggy-js";
import { router, usePage } from "@inertiajs/vue3";
import {
    Avatar,
    Tab,
    TabList,
    TabPanel,
    TabPanels,
    Tabs,
    useToast,
} from "primevue";
import Header from "./Partials/Header.vue";
import ChevronLeftIcon from "@/Components/icons/ChevronLeftIcon.vue";
import Product from "@/Types/Product";
import CheckIcon from "@/Components/icons/CheckIcon.vue";
import ImageIcon from "@/Components/icons/ImageIcon.vue";
import { formatMoney } from "@/Utils/FormatMoney";
import PageProps from "@/Types/PageProps";

const props = defineProps<{
    product: Product;
}>();

const selectedOptions = ref<Record<string, any>>({});
const params = new URLSearchParams(window.location.search);
const productPackagingId = ref(null);
const tableId = ref(null);
const selectedTab = ref(null);

productPackagingId.value = params.get("packagingId");
tableId.value = params.get("tableId");

const toast = useToast();
const page = usePage<PageProps>();
const goBack = () => {
    router.visit(route("resto.index", { tableId: tableId.value }));
};

const calculateTotal = () => {
    if (!props.product.data) return 0;

    let total = parseFloat(props.product.data.average_cost || "0");

    Object.values(selectedOptions.value).forEach((optionItem: any) => {
        if (optionItem && optionItem.price) {
            total += parseFloat(optionItem.price);
        }
    });

    return total;
};

const addToCart = () => {
    // Get table ID and order type from URL params
    const params = new URLSearchParams(window.location.search);
    const tableId = params.get("tableId");
    const orderType = params.get("orderType") || "dine-in";

    router.post(
        route("resto.cart.add"),
        {
            product_id: props.product?.data?.id,
            product_packaging_id: productPackagingId.value,
            selected_options: selectedOptions.value,
            quantity: 1,
            total_price: calculateTotal(),
            table_id: tableId,
            order_type: orderType,
            withParent: true,
        },
        {
            onSuccess: () => {
                // Navigate back to index
                router.visit(route("resto.index", { tableId: tableId }));
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: page.props.flash.success,
                    life: 3000,
                });
            },
            onError: (errors) => {
                console.log(errors);
                alert(
                    "Failed to add item to cart: " +
                        (errors.message || "Unknown error")
                );
            },
        }
    );
};

watch(
    () => props.product.data?.options,
    (options) => {
        if (options && options.length > 0) {
            selectedTab.value = options[0].id;
        }
    },
    { immediate: true }
);
</script>
