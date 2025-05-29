<template>
    <div
        class="relative flex flex-col bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-6 w-full transition-colors duration-200 border border-gray-200 dark:border-gray-700"
        role="region"
        aria-label="Product details"
    >
        <!-- Product Image -->
        <div class="flex justify-center mb-4">
            <img
                :src="product.img"
                :alt="product.name"
                class="w-28 h-28 object-cover rounded-xl border-2 border-gray-200 dark:border-gray-700 shadow-sm bg-gray-100 dark:bg-gray-800"
            />
        </div>
        <!-- Product Name & Price -->
        <div class="text-center mb-2">
            <h2
                class="text-2xl font-extrabold text-gray-900 dark:text-white leading-tight"
                tabindex="0"
            >
                {{ product.name }}
            </h2>
            <div
                class="text-lg text-green-700 dark:text-green-400 font-semibold mt-1"
                tabindex="0"
            >
                ₱{{ product.price }}
            </div>
            <div
                class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                tabindex="0"
            >
                Base price: ₱{{ product.price }}
            </div>
        </div>
        <!-- Sizes -->
        <div
            v-if="product.sizes && product.sizes.length"
            class="flex flex-wrap justify-center gap-3 mt-4"
            role="radiogroup"
            aria-label="Select size"
        >
            <button
                v-for="size in product.sizes"
                :key="size.value"
                @click="selectedSize = size.value"
                :class="[
                    'w-12 h-12 flex items-center justify-center rounded-full border-2 font-bold text-base focus:outline-none focus:ring-2 focus:ring-indigo-400 transition',
                    selectedSize === size.value
                        ? 'bg-indigo-600 text-white border-indigo-700 shadow'
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-indigo-50 dark:hover:bg-indigo-900',
                ]"
                :aria-checked="selectedSize === size.value"
                role="radio"
                :aria-label="size.label"
                tabindex="0"
            >
                {{ size.label }}
            </button>
        </div>
        <!-- Modifiers -->
        <div
            v-for="modifier in product.modifiers"
            :key="modifier.name"
            class="mt-5"
            role="radiogroup"
            :aria-label="modifier.name"
        >
            <div class="font-semibold text-gray-700 dark:text-gray-200 mb-2">
                {{ modifier.name }}
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="option in modifier.options"
                    :key="option"
                    @click="selectModifier(modifier.name, option)"
                    :class="[
                        'px-4 py-2 rounded-full border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-400 transition',
                        selectedModifiers[modifier.name] === option
                            ? 'bg-indigo-600 text-white border-indigo-700 shadow'
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-indigo-50 dark:hover:bg-indigo-900',
                    ]"
                    :aria-checked="selectedModifiers[modifier.name] === option"
                    role="radio"
                    :aria-label="option"
                    tabindex="0"
                >
                    {{ option }}
                </button>
            </div>
        </div>
        <!-- Product Options (with additional cost) -->
        <div
            v-if="product.options && product.options.length"
            class="mt-5"
            role="group"
            aria-label="Product options"
        >
            <div class="font-semibold text-gray-700 dark:text-gray-200 mb-2">
                Options
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="option in product.options"
                    :key="option.name"
                    @click="toggleOption(option)"
                    :class="[
                        'px-4 py-2 rounded-full border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-400 transition',
                        selectedOptions.some((o) => o.name === option.name)
                            ? 'bg-green-600 text-white border-green-700 shadow'
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-green-50 dark:hover:bg-green-900',
                    ]"
                    :aria-pressed="
                        selectedOptions.some((o) => o.name === option.name)
                    "
                    role="checkbox"
                    :aria-label="
                        option.name +
                        (option.price ? ' (₱' + option.price + ')' : '')
                    "
                    tabindex="0"
                >
                    {{ option.name
                    }}<span v-if="option.price"> +₱{{ option.price }}</span>
                </button>
            </div>
        </div>
        <!-- Qty and Add to Cart -->
        <div class="flex items-center mt-8 gap-3">
            <label
                class="text-gray-700 dark:text-gray-200 font-medium"
                for="qty"
                >Qty</label
            >
            <button
                type="button"
                class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-lg font-bold focus:outline-none focus:ring-2 focus:ring-indigo-400"
                @click="qty = Math.max(1, qty - 1)"
                aria-label="Decrease quantity"
            >
                -
            </button>
            <input
                id="qty"
                type="number"
                min="1"
                v-model.number="qty"
                class="w-16 border rounded px-2 py-1 text-center focus:ring-2 focus:ring-indigo-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600"
                aria-label="Quantity"
            />
            <button
                type="button"
                class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-lg font-bold focus:outline-none focus:ring-2 focus:ring-indigo-400"
                @click="qty++"
                aria-label="Increase quantity"
            >
                +
            </button>
            <button
                class="ml-auto px-6 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-bold shadow transition focus:outline-none focus:ring-2 focus:ring-green-400"
                @click="addToCart"
                :aria-label="
                    (editMode ? 'Update' : 'Add') +
                    ' ' +
                    product.name +
                    ' to cart'
                "
            >
                {{ editMode ? "Update Cart" : mainButtonLabel }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, defineProps, defineEmits } from "vue";

const props = defineProps({
    product: Object,
    initialQty: { type: Number, default: 1 },
    initialSize: { type: String, default: "" },
    initialOptions: { type: Array, default: () => [] },
    initialModifiers: { type: Object, default: () => ({}) },
    initialPrice: { type: Number, default: 0 },
    editMode: Boolean,
    mainButtonLabel: { type: String, default: "Add to Cart" },
});

const emit = defineEmits(["add-to-cart"]);

const qty = ref(props.initialQty || 1);
const selectedSize = ref(props.initialSize || props.product?.sizes?.[0] || "");
const selectedOptions = ref<any[]>(
    props.initialOptions ? [...props.initialOptions] : []
);
const selectedModifiers = ref(
    props.initialModifiers ? { ...props.initialModifiers } : {}
);

watch(
    () => props.initialOptions,
    (val) => {
        if (val) selectedOptions.value = [...val];
    },
    { immediate: true }
);
watch(
    () => props.initialModifiers,
    (val) => {
        if (val) selectedModifiers.value = { ...val };
    },
    { immediate: true }
);

function selectModifier(modifierName: string, option: string) {
    selectedModifiers.value[modifierName] = option;
}

function toggleOption(option: any) {
    const idx = selectedOptions.value.findIndex(
        (o: any) => o.name === option.name
    );
    if (idx === -1) {
        selectedOptions.value.push(option);
    } else {
        selectedOptions.value.splice(idx, 1);
    }
}

function resetForm() {
    qty.value = 1;
    selectedSize.value = props.product?.sizes?.[0]?.value || "";
    selectedOptions.value = [];
    selectedModifiers.value = {};
}

function addToCart() {
    // Calculate total price with options
    let basePrice = parseFloat(props.product.price) || 0;
    let optionsTotal = selectedOptions.value.reduce(
        (sum: number, opt: any) => sum + (parseFloat(opt.price) || 0),
        0
    );
    let unitPrice = basePrice; // Only base price for price property
    let totalPrice = (basePrice + optionsTotal) * qty.value;
    emit("add-to-cart", {
        product: props.product,
        size: selectedSize.value,
        modifiers: { ...selectedModifiers.value },
        options: [...selectedOptions.value],
        qty: qty.value,
        price: unitPrice, // base price only
        total: totalPrice, // base + options * qty
    });
    resetForm();
}
</script>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type="number"] {
    -moz-appearance: textfield;
}
</style>
