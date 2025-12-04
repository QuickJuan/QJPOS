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
                                    {{ getPrice(props.product.data) }}
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
                                <div class="flex flex-col">
                                    <span class="font-bold whitespace-nowrap">
                                        {{ option.option_name }}
                                    </span>
                                </div>
                            </Tab>
                        </TabList>
                        <TabPanels>
                            <TabPanel
                                v-for="option in props.product.data?.options"
                                :key="option.id"
                                :header="option.name"
                                :value="option.id"
                            >
                                <p class="text-base font-medium text-gray-600">
                                    Select exactly
                                    {{ option?.max_quantity }} item(s)
                                </p>
                                <div
                                    v-if="
                                        option.max_quantity > 0 &&
                                        getTotalSelected(option.id) <
                                            option.max_quantity
                                    "
                                    class="mb-4 p-3 bg-red-100 border border-red-300 rounded-lg text-red-700 text-center"
                                >
                                    Please select exactly
                                    {{ option.max_quantity }} items for this
                                    option.
                                </div>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4"
                                >
                                    <div
                                        v-for="item in option.optionItems"
                                        :key="item.id"
                                        :class="[
                                            'relative border-2 rounded-xl p-6 transition-all duration-200 hover:shadow-lg flex flex-col h-full',
                                            option.max_quantity > 0 &&
                                            getTotalSelected(option.id) <
                                                option.max_quantity
                                                ? 'border-red-500'
                                                : 'border-gray-200 hover:border-indigo-300',
                                        ]"
                                    >
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
                                                <span
                                                    v-if="
                                                        item.product_packaging
                                                    "
                                                >
                                                    ({{
                                                        item.product_packaging
                                                            ?.name
                                                    }})
                                                </span>
                                            </h5>
                                            <div
                                                v-if="
                                                    item.price &&
                                                    parseFloat(item.price) > 0
                                                "
                                                class="inline-flex items-center px-3 py-1 mb-5 rounded-full text-sm font-medium bg-green-100 text-green-800"
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
                                        <div
                                            class="flex justify-center mt-auto"
                                        >
                                            <InputNumber
                                                :modelValue="
                                                    selectedOptions[
                                                        option.id
                                                    ]?.[item.id] || 0
                                                "
                                                @update:modelValue="
                                                    updateQuantity(
                                                        option,
                                                        item.id,
                                                        $event
                                                    )
                                                "
                                                showButtons
                                                buttonLayout="horizontal"
                                                :min="0"
                                                :max="
                                                    option.max_quantity > 0 &&
                                                    getTotalSelected(
                                                        option.id
                                                    ) >= option.max_quantity
                                                        ? selectedOptions[
                                                              option.id
                                                          ]?.[item.id] || 0
                                                        : 99
                                                "
                                                inputClass="text-center w-20"
                                                buttonClass="p-button-outlined p-button-sm"
                                            >
                                                <template #incrementicon>
                                                    <span class="pi pi-plus" />
                                                </template>
                                                <template #decrementicon>
                                                    <span class="pi pi-minus" />
                                                </template>
                                            </InputNumber>
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
import { ref, watch } from "vue";
import { route } from "ziggy-js";
import { router, usePage } from "@inertiajs/vue3";
import {
    Avatar,
    InputNumber,
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

const selectedOptions = ref<Record<string, Record<string, number>>>({});
const params = new URLSearchParams(window.location.search);
const productPackagingId = ref(null);
const tableId = ref(null);
const selectedTab = ref(null);

productPackagingId.value = params.get("packagingId");
tableId.value = params.get("tableId");

const updateQuantity = (option: any, itemId: string, value: number) => {
    const optionId = option.id;
    if (!selectedOptions.value[optionId]) {
        selectedOptions.value[optionId] = {};
    }
    const currentQty = selectedOptions.value[optionId][itemId] || 0;
    const currentTotal = getTotalSelected(optionId);
    if (
        value > currentQty &&
        option.max_quantity > 0 &&
        currentTotal >= option.max_quantity
    ) {
        return; // prevent increase when at max
    }
    selectedOptions.value[optionId][itemId] = value;
};

const getTotalSelected = (optionId: string) => {
    const optionSelections = selectedOptions.value[optionId] || {};
    return Object.values(optionSelections).reduce(
        (sum: number, qty: number) => sum + qty,
        0
    );
};

const toast = useToast();
const page = usePage<PageProps>();
const goBack = () => {
    router.visit(route("resto.index", { tableId: tableId.value }));
};

const calculateTotal = () => {
    if (!props.product.data) return 0;

    let total = parseFloat(props.product.data.average_cost || "0");

    props.product.data.options.forEach((option) => {
        option.optionItems.forEach((item) => {
            const qty = selectedOptions.value[option.id]?.[item.id] || 0;
            if (qty > 0 && item.price) {
                total += qty * parseFloat(item.price);
            }
        });
    });

    return total;
};

const addToCart = () => {
    // Get table ID and order type from URL params
    const params = new URLSearchParams(window.location.search);
    const tableId = params.get("tableId");
    const orderType = params.get("orderType") || "dine-in";

    // Filter selected options to only include items with quantity > 0 and include full option data
    const filteredOptions = [];
    Object.keys(selectedOptions.value).forEach((optionId) => {
        const option = props.product.data.options.find((o) => o.id == optionId);

        if (option) {
            const items = [];
            Object.keys(selectedOptions.value[optionId]).forEach((itemId) => {
                const qty = selectedOptions.value[optionId][itemId];
                if (qty > 0) {
                    const item = option.optionItems.find((i) => i.id == itemId);
                    items.push({
                        ...item,
                        quantity: qty,
                    });
                }
            });

            if (items.length > 0) {
                filteredOptions.push({
                    id: optionId,
                    option_name: option.option_name,
                    max_quantity: option.max_quantity,
                    product_id: option.product_id,
                    product_packaging_id: option.product_packaging_id,
                    price: option.price,
                    items: items,
                });
            }
        }
    });

    router.post(
        route("resto.cart.add"),
        {
            product_id: props.product?.data?.id,
            product_packaging_id: productPackagingId.value,
            selected_options: filteredOptions,
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
                getLowestPriceForProductPackagings(product.product_packagings)
            );
        } else {
            formatMoney(parseFloat(product.price || "0").toFixed(2));
        }
    } else {
        price = formatMoney(product.price);
    }

    return price;
};

watch(
    () => props.product.data?.options,
    (options) => {
        if (options && options.length > 0) {
            selectedTab.value = options[0].id;
            options.forEach((option) => {
                selectedOptions.value[option.id] = {};

                // Find the options that has is_default set to true
                const defaultProduct = option.products.find(
                    (p) => p.pivot.is_default
                );

                if (defaultProduct) {
                    const defaultItem = option.optionItems.find(
                        (item) => item.option_id == defaultProduct.pivot.option_id
                    );
                    
                    console.log("option", option);
                    console.log("defaultProduct", defaultProduct);
                    console.log("optionItems", option.optionItems);
                    console.log("defaultItem", defaultItem);

                    if (defaultItem) {
                        selectedOptions.value[option.id][defaultItem.id] =
                            defaultItem.quantity;
                    }
                } else if (option.optionItems.length > 0) {
                    selectedOptions.value[option.id][option.optionItems[0].id] =
                        option.max_quantity;
                }
            });
        }
    },
    { immediate: true }
);
</script>
