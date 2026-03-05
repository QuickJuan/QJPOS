<template>
    <CashieringLayout :current-user="page.props.currentUser">
        <div class="min-h-screen bg-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                <button
                    @click="goBack"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900 transition mb-4 lg:mb-6"
                >
                    <ChevronLeftIcon class="w-4 h-4" />
                    Back to Menu
                </button>

                <div
                    class="grid lg:grid-cols-[1fr_380px] xl:grid-cols-[1fr_420px] gap-6 lg:gap-8 lg:h-[calc(100vh-140px)] items-start"
                >
                    <section class="order-2 lg:order-1 lg:h-full">
                        <div
                            class="lg:h-full lg:overflow-y-auto lg:pr-4 custom-scrollbar"
                        >
                            <div
                                class="bg-white rounded-2xl lg:rounded-3xl shadow-xl p-5 lg:p-6 xl:p-8 space-y-6 lg:space-y-8 mb-6 lg:mb-0"
                            >
                                <div class="flex flex-col gap-2">
                                    <span
                                        class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500"
                                    >
                                        Customize
                                    </span>
                                    <div
                                        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3"
                                    >
                                        <h2
                                            class="text-2xl font-bold text-slate-900"
                                        >
                                            Build your bundle
                                        </h2>
                                        <p
                                            class="text-sm text-slate-500"
                                            v-if="hasCustomizableOptions"
                                        >
                                            {{ selectionHint }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="hasCustomizableOptions"
                                    class="space-y-5 lg:space-y-6"
                                >
                                    <div
                                        v-for="(
                                            option, index
                                        ) in customizableOptions"
                                        :key="option.id"
                                        class="flex gap-4 lg:gap-6 border border-slate-200 rounded-2xl lg:rounded-3xl p-4 lg:p-5 xl:p-6 bg-slate-50"
                                    >
                                        <div
                                            class="flex flex-col items-center flex-shrink-0"
                                        >
                                            <div
                                                class="w-10 h-10 lg:w-11 lg:h-11 rounded-full bg-sky-600 text-white text-sm lg:text-base font-semibold flex items-center justify-center"
                                            >
                                                {{ index + 1 }}
                                            </div>
                                            <div
                                                v-if="
                                                    index !==
                                                    customizableOptions.length -
                                                        1
                                                "
                                                class="w-px flex-1 bg-slate-200 mt-2 lg:mt-3 hidden lg:block"
                                            ></div>
                                        </div>
                                        <div
                                            class="flex-1 space-y-4 lg:space-y-5 min-w-0"
                                        >
                                            <div
                                                class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between"
                                            >
                                                <div>
                                                    <p
                                                        class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400"
                                                    >
                                                        Step {{ index + 1 }}
                                                    </p>
                                                    <h3
                                                        class="text-xl font-semibold text-slate-900"
                                                    >
                                                        {{ option.option_name }}
                                                    </h3>
                                                    <p
                                                        class="text-sm text-slate-500"
                                                    >
                                                        {{
                                                            getMaxQuantity(
                                                                option
                                                            )
                                                                ? `Pick ${getMaxQuantity(
                                                                      option
                                                                  )} item${
                                                                      getMaxQuantity(
                                                                          option
                                                                      ) > 1
                                                                          ? "s"
                                                                          : ""
                                                                  }`
                                                                : "Choose as many as you like"
                                                        }}
                                                    </p>
                                                </div>
                                                <div
                                                    class="text-sm font-semibold text-slate-600"
                                                >
                                                    <template
                                                        v-if="
                                                            getMaxQuantity(
                                                                option
                                                            )
                                                        "
                                                    >
                                                        {{
                                                            getTotalSelected(
                                                                option.id
                                                            )
                                                        }}
                                                        /
                                                        {{
                                                            getMaxQuantity(
                                                                option
                                                            )
                                                        }}
                                                        selected
                                                    </template>
                                                    <template v-else>
                                                        {{
                                                            getTotalSelected(
                                                                option.id
                                                            )
                                                        }}
                                                        selected
                                                    </template>
                                                </div>
                                            </div>

                                            <div
                                                v-if="
                                                    option.optionItems?.length
                                                "
                                                class="space-y-3"
                                            >
                                                <div
                                                    v-for="item in option.optionItems"
                                                    :key="item.id"
                                                    :class="[
                                                        'relative rounded-2xl border p-4 bg-white flex items-center gap-4 transition hover:shadow-md',
                                                        getSelectionValue(
                                                            option.id,
                                                            item.id
                                                        ) > 0
                                                            ? 'border-sky-500 shadow-sky-50 bg-sky-50/30'
                                                            : 'border-slate-200',
                                                    ]"
                                                >
                                                    <!-- Product Image -->
                                                    <div
                                                        class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 flex-shrink-0 flex items-center justify-center overflow-hidden"
                                                    >
                                                        <img
                                                            v-if="
                                                                item?.product
                                                                    ?.media
                                                                    ?.length
                                                            "
                                                            :src="
                                                                item.product
                                                                    .media[0]
                                                                    ?.original_url
                                                            "
                                                            :alt="
                                                                item.product
                                                                    .media[0]
                                                                    ?.name
                                                            "
                                                            class="w-full h-full object-cover"
                                                        />
                                                        <div
                                                            v-else
                                                            class="text-slate-500"
                                                        >
                                                            <ImageIcon
                                                                class="w-6 h-6"
                                                            />
                                                        </div>
                                                    </div>

                                                    <!-- Product Info -->
                                                    <div class="flex-1 min-w-0">
                                                        <p
                                                            class="font-semibold text-slate-900 text-sm sm:text-base truncate"
                                                        >
                                                            {{
                                                                item?.product
                                                                    ?.name ||
                                                                "Option item"
                                                            }}
                                                        </p>
                                                        <p
                                                            class="text-xs sm:text-sm text-slate-500 mt-0.5"
                                                        >
                                                            {{
                                                                item
                                                                    ?.product_packaging
                                                                    ?.name ||
                                                                "Single serve"
                                                            }}
                                                        </p>
                                                        <div
                                                            class="mt-1 text-xs sm:text-sm font-semibold"
                                                            :class="[
                                                                Number(
                                                                    item.price
                                                                ) > 0
                                                                    ? 'text-emerald-600'
                                                                    : 'text-slate-500',
                                                            ]"
                                                        >
                                                            {{
                                                                Number(
                                                                    item.price
                                                                ) > 0
                                                                    ? "+" +
                                                                      formatPrice(
                                                                          Number(
                                                                              item.price
                                                                          )
                                                                      )
                                                                    : "Included"
                                                            }}
                                                        </div>
                                                    </div>

                                                    <!-- Quantity Controls -->
                                                    <div
                                                        class="flex items-center gap-2 flex-shrink-0"
                                                    >
                                                        <button
                                                            type="button"
                                                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border border-slate-300 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center justify-center"
                                                            @click="
                                                                adjustQuantity(
                                                                    option,
                                                                    item.id,
                                                                    -1
                                                                )
                                                            "
                                                            :disabled="
                                                                getSelectionValue(
                                                                    option.id,
                                                                    item.id
                                                                ) === 0
                                                            "
                                                        >
                                                            <span
                                                                aria-hidden="true"
                                                                class="text-lg leading-none"
                                                                >−</span
                                                            >
                                                            <span
                                                                class="sr-only"
                                                                >Decrease
                                                                quantity</span
                                                            >
                                                        </button>
                                                        <span
                                                            class="w-8 text-center font-semibold text-slate-900 text-sm sm:text-base"
                                                        >
                                                            {{
                                                                getSelectionValue(
                                                                    option.id,
                                                                    item.id
                                                                )
                                                            }}
                                                        </span>
                                                        <button
                                                            <button
                                                            type="button"
                                                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border border-sky-500 text-sky-600 hover:bg-sky-100 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center justify-center"
                                                            @click="
                                                                adjustQuantity(
                                                                    option,
                                                                    item.id,
                                                                    1
                                                                )
                                                            "
                                                            :disabled="
                                                                isIncrementDisabled(
                                                                    option,
                                                                    item.id
                                                                )
                                                            "
                                                        >
                                                            <span
                                                                aria-hidden="true"
                                                                class="text-lg leading-none"
                                                                >+</span
                                                            >
                                                            <span
                                                                class="sr-only"
                                                                >Increase
                                                                quantity</span
                                                            >
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                v-else
                                                class="border border-dashed border-slate-200 rounded-2xl p-6 text-sm text-slate-500 bg-white"
                                            >
                                                No option items available for
                                                this group yet.
                                            </div>

                                            <div
                                                v-if="
                                                    getMaxQuantity(option) &&
                                                    getPendingSelections(option)
                                                "
                                                class="text-sm text-rose-500"
                                            >
                                                Choose
                                                {{
                                                    getPendingSelections(option)
                                                }}
                                                more item(s) to complete this
                                                option.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="border border-dashed border-slate-300 rounded-2xl p-8 text-center text-slate-500 bg-slate-50"
                                >
                                    All required components are already
                                    included. You can add this bundle right
                                    away.
                                </div>

                                <div
                                    class="border-t border-slate-200 pt-5 lg:pt-6 space-y-3 lg:space-y-4"
                                >
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500"
                                            >Base Price</span
                                        >
                                        <span
                                            class="font-semibold text-slate-900"
                                        >
                                            {{ formatPrice(basePriceValue) }}
                                        </span>
                                    </div>
                                    <div
                                        class="flex justify-between text-base lg:text-lg font-bold text-slate-900"
                                    >
                                        <span>Total</span>
                                        <span>{{
                                            formatPrice(totalAmount)
                                        }}</span>
                                    </div>
                                    <div
                                        class="grid gap-3 sm:flex sm:gap-3 lg:gap-4"
                                    >
                                        <button
                                            type="button"
                                            @click="goBack"
                                            class="px-4 py-3 border border-slate-200 rounded-xl sm:rounded-2xl text-slate-600 font-semibold hover:bg-slate-50 transition sm:flex-1"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="button"
                                            :disabled="
                                                hasCustomizableOptions &&
                                                !selectionsAreComplete()
                                            "
                                            @click="addToCart"
                                            class="px-4 py-3 rounded-xl sm:rounded-2xl font-semibold text-white shadow-lg transition sm:flex-1"
                                            :class="[
                                                hasCustomizableOptions &&
                                                !selectionsAreComplete()
                                                    ? 'bg-slate-300 cursor-not-allowed'
                                                    : 'bg-sky-600 hover:bg-sky-700',
                                            ]"
                                        >
                                            Add to Order
                                        </button>
                                    </div>
                                    <p
                                        v-if="
                                            hasCustomizableOptions &&
                                            !selectionsAreComplete()
                                        "
                                        class="text-xs text-rose-500"
                                    >
                                        Complete each option's required quantity
                                        to continue.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <aside
                        class="order-1 lg:order-2 lg:h-full lg:overflow-y-auto custom-scrollbar"
                    >
                        <div class="space-y-4 lg:space-y-5 pb-6 lg:pb-0">
                            <div
                                class="bg-white rounded-2xl lg:rounded-3xl shadow-xl p-4 sm:p-5 lg:p-7 space-y-4 lg:space-y-6"
                            >
                                <div class="flex gap-3 sm:gap-4">
                                    <div
                                        class="w-20 h-20 sm:w-24 sm:h-24 lg:w-28 lg:h-28 rounded-xl lg:rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex-shrink-0 flex items-center justify-center overflow-hidden"
                                    >
                                        <img
                                            v-if="coverImage"
                                            :src="coverImage"
                                            :alt="productData?.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <div v-else class="text-slate-500">
                                            <ImageIcon class="w-9 h-9" />
                                        </div>
                                    </div>
                                    <div
                                        class="flex-1 min-w-0 space-y-1 sm:space-y-2"
                                    >
                                        <p
                                            class="text-[10px] sm:text-xs uppercase tracking-[0.2em] sm:tracking-[0.25em] text-slate-400"
                                        >
                                            Bundle
                                        </p>
                                        <h1
                                            class="text-lg sm:text-xl lg:text-2xl font-bold text-slate-900 line-clamp-2"
                                        >
                                            {{ productData?.name }}
                                        </h1>
                                        <p
                                            v-if="productDescription"
                                            class="text-xs sm:text-sm text-slate-600 leading-relaxed line-clamp-2 hidden sm:block"
                                            v-html="productDescription"
                                        ></p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                    <div>
                                        <p
                                            class="text-[10px] sm:text-xs uppercase text-slate-500"
                                        >
                                            Base Price
                                        </p>
                                        <p
                                            class="text-base sm:text-lg lg:text-xl font-bold text-slate-900"
                                        >
                                            {{ formatPrice(basePriceValue) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-[10px] sm:text-xs uppercase text-slate-500"
                                        >
                                            Current Total
                                        </p>
                                        <p
                                            class="text-base sm:text-lg lg:text-xl font-semibold text-emerald-600"
                                        >
                                            {{ formatPrice(totalAmount) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-white rounded-2xl lg:rounded-3xl shadow-xl p-4 sm:p-5 lg:p-7 space-y-4 lg:space-y-6"
                            >
                                <div>
                                    <p
                                        class="text-xs uppercase tracking-[0.3em] text-slate-500"
                                    >
                                        Included
                                    </p>
                                    <div
                                        v-if="defaultOptionSummary.length"
                                        class="mt-3 space-y-3"
                                    >
                                        <div
                                            v-for="group in defaultOptionSummary"
                                            :key="group.id"
                                            class="border border-slate-200 rounded-2xl p-4"
                                        >
                                            <p
                                                class="text-sm font-semibold text-slate-900"
                                            >
                                                {{ group.name }}
                                            </p>
                                            <ul
                                                class="mt-2 space-y-1 text-sm text-slate-600"
                                            >
                                                <li
                                                    v-for="item in group.items"
                                                    :key="item.id"
                                                    class="flex justify-between"
                                                >
                                                    <span>
                                                        {{ item.quantity }} ×
                                                        {{ item.name }}
                                                    </span>
                                                    <span
                                                        v-if="
                                                            Number(item.price) >
                                                            0
                                                        "
                                                        class="text-emerald-600"
                                                    >
                                                        +{{
                                                            formatPrice(
                                                                item.price
                                                            )
                                                        }}
                                                    </span>
                                                    <span
                                                        v-else
                                                        class="text-slate-400"
                                                    >
                                                        Included
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm text-slate-500 mt-2"
                                    >
                                        No automatic inclusions for this bundle.
                                    </p>
                                </div>

                                <div>
                                    <p
                                        class="text-xs uppercase tracking-[0.3em] text-slate-500"
                                    >
                                        Your Selections
                                    </p>
                                    <div
                                        v-if="hasCustomizableSelections"
                                        class="mt-3 space-y-3"
                                    >
                                        <div
                                            v-for="group in customizableSelectionSummary"
                                            :key="group.id"
                                            class="border border-slate-200 rounded-2xl p-4"
                                        >
                                            <p
                                                class="text-sm font-semibold text-slate-900"
                                            >
                                                {{ group.name }}
                                            </p>
                                            <ul
                                                class="mt-2 space-y-1 text-sm text-slate-600"
                                            >
                                                <li
                                                    v-for="item in group.items"
                                                    :key="item.id"
                                                    class="flex justify-between"
                                                >
                                                    <span>
                                                        {{ item.quantity }} ×
                                                        {{ item.name }}
                                                    </span>
                                                    <span
                                                        v-if="
                                                            Number(item.price) >
                                                            0
                                                        "
                                                        class="text-emerald-600"
                                                    >
                                                        +{{
                                                            formatPrice(
                                                                item.price *
                                                                    item.quantity
                                                            )
                                                        }}
                                                    </span>
                                                    <span
                                                        v-else
                                                        class="text-slate-400"
                                                    >
                                                        Included
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm text-slate-500 mt-2"
                                    >
                                        Your custom picks will appear here once
                                        selected.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>

        <OpenPriceModal
            v-model="showOpenPriceModal"
            :product="pendingOpenPrice?.product || productData"
            :default-price="pendingOpenPrice?.defaultPrice ?? totalAmount"
            @confirm="handleOpenPriceConfirm"
            @update:modelValue="(value: boolean) => {
                if (!value) handleOpenPriceCancel();
            }"
        />
    </CashieringLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { route } from "ziggy-js";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "primevue";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import ChevronLeftIcon from "@/Components/icons/ChevronLeftIcon.vue";
import ImageIcon from "@/Components/icons/ImageIcon.vue";
import OpenPriceModal from "@/Components/Resto/OpenPriceModal.vue";
import Product from "@/Types/Product";
import PageProps from "@/Types/PageProps";
import { formatMoney } from "@/Utils/FormatMoney";
import { useProduct } from "@/composables/useProduct";

type ProductResponse = {
    data: Product & {
        options?: any[];
        product_packagings?: any[];
        featured_image_url?: string;
        product_images_urls?: string[];
    };
};

const props = defineProps<{ product: ProductResponse }>();
const toast = useToast();
const page = usePage<PageProps>();
const {
    submitCartPayload,
    showOpenPriceModal,
    pendingOpenPrice,
    handleOpenPriceConfirm,
    handleOpenPriceCancel,
} = useProduct();

const productData = computed(() => props.product?.data || {});
const coverImage = computed(
    () =>
        productData.value?.featured_image_url ||
        productData.value?.product_images_urls?.[0] ||
        null
);
const productDescription = computed(() => productData.value?.description || "");

const formatPrice = (value: number | string | null | undefined) => {
    const numericValue = Number(value);
    if (Number.isNaN(numericValue)) {
        return formatMoney("0.00");
    }

    return formatMoney(numericValue === 0 ? "0.00" : numericValue);
};

const basePriceValue = computed(() => {
    const averageCost = Number(productData.value?.average_cost);
    if (!Number.isNaN(averageCost) && averageCost > 0) {
        return averageCost;
    }
    const fallback = Number(productData.value?.price);
    return Number.isNaN(fallback) ? 0 : fallback;
});

const getLowestPriceForProductPackagings = (packagings: any[] = []) => {
    const prices = packagings
        .map((packaging) => Number(packaging.price))
        .filter((price) => !Number.isNaN(price));
    return prices.length ? Math.min(...prices) : 0;
};

const customizableOptions = computed(
    () =>
        productData.value?.options?.filter(
            (option: any) => !option.is_default
        ) || []
);

const defaultOptionSummary = computed(() => {
    return (
        productData.value?.options
            ?.filter((option: any) => option.is_default)
            .map((option: any) => ({
                id: option.id,
                name: option.option_name,
                items:
                    option.optionItems
                        ?.filter((item: any) => (item.quantity ?? 0) > 0)
                        .map((item: any) => ({
                            id: item.id,
                            name:
                                item.product?.name ||
                                item.product_packaging?.name ||
                                "Included item",
                            quantity: item.quantity ?? 0,
                            price: Number(item.price || 0),
                            product_id: item.product_id,
                            product_packaging_id: item.product_packaging_id,
                        })) || [],
            }))
            .filter((group: any) => group.items.length > 0) || []
    );
});

const defaultOptionPayloads = computed(() => {
    return (
        productData.value?.options
            ?.filter((option: any) => option.is_default)
            .map((option: any) => {
                const items =
                    option.optionItems
                        ?.filter((item: any) => (item.quantity ?? 0) > 0)
                        .map((item: any) => ({
                            id: item.id,
                            product_id: item.product_id,
                            product_packaging_id: item.product_packaging_id,
                            price: Number(item.price || 0),
                            quantity: item.quantity ?? 0,
                        })) || [];

                return {
                    id: option.id,
                    option_name: option.option_name,
                    max_quantity: option.max_quantity,
                    product_id: option.product_id,
                    is_default: true,
                    items,
                };
            })
            .filter((option: any) => option.items.length > 0) || []
    );
});

const hasCustomizableOptions = computed(
    () => customizableOptions.value.length > 0
);

const selectedOptions = ref<Record<string, Record<string, number>>>({});

watch(
    () => customizableOptions.value,
    (options) => {
        const nextSelections: Record<string, Record<string, number>> = {};
        const previousSelections = selectedOptions.value;

        options.forEach((option: any) => {
            const key = String(option.id);
            nextSelections[key] = { ...(previousSelections[key] || {}) };
        });

        selectedOptions.value = nextSelections;
    },
    { immediate: true }
);

const getMaxQuantity = (option: any) => {
    const max = Number(option?.max_quantity);
    return Number.isNaN(max) || max <= 0 ? 0 : max;
};

const getSelectionValue = (
    optionId: string | number,
    itemId: string | number
) => {
    return selectedOptions.value[String(optionId)]?.[String(itemId)] || 0;
};

const getTotalSelected = (optionId: string | number) => {
    const selections = selectedOptions.value[String(optionId)] || {};
    return Object.values(selections).reduce(
        (sum, qty) => sum + Number(qty || 0),
        0
    );
};

const getPendingSelections = (option: any) => {
    const max = getMaxQuantity(option);
    if (!max) {
        return 0;
    }

    return Math.max(0, max - getTotalSelected(option.id));
};

const updateQuantity = (
    option: any,
    itemId: string | number,
    nextValue: number | null
) => {
    const optionKey = String(option.id);
    const itemKey = String(itemId);
    const sanitizedValue = Math.max(0, Number(nextValue ?? 0));
    const max = getMaxQuantity(option);

    const optionSelections = { ...(selectedOptions.value[optionKey] || {}) };
    const totalWithoutCurrent = Object.entries(optionSelections)
        .filter(([key]) => key !== itemKey)
        .reduce((sum, [, qty]) => sum + Number(qty || 0), 0);

    let resolvedValue = sanitizedValue;

    if (max && totalWithoutCurrent + sanitizedValue > max) {
        resolvedValue = Math.max(0, max - totalWithoutCurrent);
    }

    if (resolvedValue === 0) {
        delete optionSelections[itemKey];
    } else {
        optionSelections[itemKey] = resolvedValue;
    }

    selectedOptions.value = {
        ...selectedOptions.value,
        [optionKey]: optionSelections,
    };
};

const getMaxAllocatableForItem = (option: any, itemId: string | number) => {
    const max = getMaxQuantity(option);
    if (!max) {
        return Number.POSITIVE_INFINITY;
    }

    const optionKey = String(option.id);
    const itemKey = String(itemId);
    const optionSelections = selectedOptions.value[optionKey] || {};

    const totalWithoutCurrent = Object.entries(optionSelections)
        .filter(([key]) => key !== itemKey)
        .reduce((sum, [, qty]) => sum + Number(qty || 0), 0);

    const currentValue = Number(optionSelections[itemKey] || 0);
    const remainingSlots = Math.max(0, max - totalWithoutCurrent);

    return Math.max(currentValue, remainingSlots);
};

const isIncrementDisabled = (option: any, itemId: string | number) => {
    const maxForItem = getMaxAllocatableForItem(option, itemId);
    if (!Number.isFinite(maxForItem)) {
        return false;
    }

    const currentValue = getSelectionValue(option.id, itemId);
    return currentValue >= maxForItem;
};

const adjustQuantity = (option: any, itemId: string | number, step: number) => {
    if (step > 0 && isIncrementDisabled(option, itemId)) {
        return;
    }

    const currentValue = getSelectionValue(option.id, itemId);
    const nextValue = Math.max(0, currentValue + step);
    updateQuantity(option, itemId, nextValue);
};

const customizableSelectionSummary = computed(() => {
    return customizableOptions.value
        .map((option: any) => {
            const optionItems = option.optionItems || [];
            const selections = Object.entries(
                selectedOptions.value[String(option.id)] || {}
            )
                .filter(([, qty]) => Number(qty) > 0)
                .map(([itemId, qty]) => {
                    const item = optionItems.find(
                        (optionItem: any) => String(optionItem.id) === itemId
                    );

                    if (!item) {
                        return null;
                    }

                    return {
                        id: item.id,
                        name:
                            item.product?.name ||
                            item.product_packaging?.name ||
                            "Selected item",
                        quantity: Number(qty),
                        price: Number(item.price || 0),
                    };
                })
                .filter(Boolean) as Array<{
                id: number;
                name: string;
                quantity: number;
                price: number;
            }>;

            return {
                id: option.id,
                name: option.option_name,
                items: selections,
            };
        })
        .filter((group: any) => group.items.length > 0);
});

const hasCustomizableSelections = computed(
    () => customizableSelectionSummary.value.length > 0
);

const selectionsAreComplete = () => {
    return customizableOptions.value.every((option: any) => {
        const max = getMaxQuantity(option);
        if (!max) {
            return true;
        }

        return getTotalSelected(option.id) === max;
    });
};

const selectionHint = computed(() => {
    if (!hasCustomizableOptions.value) {
        return "Everything in this bundle is already set.";
    }

    return selectionsAreComplete()
        ? "Selections complete — ready to add."
        : "Finish the required picks for each step.";
});

const buildSelectedOptionPayload = (option: any) => {
    const optionSelections = selectedOptions.value[String(option.id)] || {};
    const optionItems = option.optionItems || [];

    const items = Object.entries(optionSelections)
        .filter(([, qty]) => Number(qty) > 0)
        .map(([itemId, qty]) => {
            const item = optionItems.find(
                (optionItem: any) => String(optionItem.id) === itemId
            );

            if (!item) {
                return null;
            }

            return {
                id: item.id,
                product_id: item.product_id,
                product_packaging_id: item.product_packaging_id,
                price: Number(item.price || 0),
                quantity: Number(qty),
                receipt_name:
                    item.product?.receipt_alias ||
                    item.product?.name ||
                    item.product_packaging?.name ||
                    item.product_packaging?.unit_measure ||
                    item.name ||
                    null,
            };
        })
        .filter(Boolean);

    if (!items.length) {
        return null;
    }

    return {
        id: option.id,
        option_name: option.option_name,
        max_quantity: option.max_quantity,
        product_id: option.product_id,
        is_default: false,
        items,
    };
};

const calculateTotal = () => {
    let total = basePriceValue.value || 0;

    defaultOptionPayloads.value.forEach((option) => {
        option.items.forEach((item: any) => {
            total += Number(item.price || 0) * Number(item.quantity || 0);
        });
    });

    customizableOptions.value.forEach((option: any) => {
        const optionSelections = selectedOptions.value[String(option.id)] || {};
        option.optionItems?.forEach((item: any) => {
            const qty = Number(optionSelections[String(item.id)] || 0);
            if (qty > 0) {
                total += qty * Number(item.price || 0);
            }
        });
    });

    return total;
};

const totalAmount = computed(() => calculateTotal());

const searchParams =
    typeof window !== "undefined"
        ? new URLSearchParams(window.location.search)
        : new URLSearchParams();

const productPackagingId = ref<string | null>(searchParams.get("packagingId"));
const tableId = ref<string | null>(searchParams.get("tableId"));
const orderType = ref<string>(searchParams.get("orderType") || "dine-in");

const goBack = () => {
    router.visit(route("resto.index", { tableId: tableId.value }));
};

const addToCart = () => {
    if (hasCustomizableOptions.value && !selectionsAreComplete()) {
        toast.add({
            severity: "warn",
            summary: "Incomplete",
            detail: "Please finish the required selections before adding.",
            life: 3000,
        });
        return;
    }

    const customizablePayloads = customizableOptions.value
        .map((option: any) => buildSelectedOptionPayload(option))
        .filter((option: any) => option !== null);

    const selectedOptionPayloads = [
        ...defaultOptionPayloads.value,
        ...customizablePayloads,
    ];

    const hasChildItems = selectedOptionPayloads.some(
        (option: any) => option.items && option.items.length > 0
    );

    submitCartPayload(
        {
            product_id: productData.value?.id,
            product_packaging_id: productPackagingId.value,
            selected_options: selectedOptionPayloads,
            quantity: 1,
            product_type: productData.value?.product_type,
            total_price: totalAmount.value,
            table_id: tableId.value,
            order_type: orderType.value,
            withParent: hasChildItems,
        },
        productData.value,
        totalAmount.value,
        {
            onSuccess: () => {
                router.visit(route("resto.index", { tableId: tableId.value }));
                toast.add({
                    severity: "success",
                    summary: "Added",
                    detail: page.props.flash?.success || "Item added to order.",
                    life: 2500,
                });
            },
            onError: (errors: any) => {
                console.error(errors);
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errors?.message || "Unable to add item to cart.",
                    life: 3000,
                });
            },
        }
    );
};
</script>

<style scoped>
/* Custom scrollbar styling */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Firefox scrollbar */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
</style>
