<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        :header="dialogTitle"
        :style="{ width: '30rem' }"
        class="bg-white"
    >
        <div v-if="selectedItem" class="space-y-6">
            <section
                class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 shadow-inner"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p
                            class="text-[11px] font-semibold uppercase tracking-wide text-secondary-500"
                        >
                            Current item
                        </p>
                        <h2 class="text-xl font-semibold text-secondary-900">
                            {{ itemName }}
                        </h2>
                        <p class="text-sm text-secondary-500">
                            {{ selectedItem.quantity }} ×
                            {{ formatMoney(unitPrice) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p
                            class="text-[11px] uppercase tracking-wide text-secondary-500"
                        >
                            Line total
                        </p>
                        <p class="text-2xl font-bold text-secondary-900">
                            {{ formatMoney(lineTotal) }}
                        </p>
                        <p
                            v-if="hasAppliedDiscount"
                            class="text-xs font-semibold text-success-600"
                        >
                            -{{ formatMoney(selectedDiscountAmount) }}
                            <span v-if="discountLabel">
                                ({{ discountLabel }})
                            </span>
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
                    <span
                        v-if="selectedItem.order_type"
                        :class="[
                            'px-2 py-0.5 rounded-full',
                            getOrderTypeBadgeClass(selectedItem.order_type),
                        ]"
                    >
                        {{ getOrderTypeLabel(selectedItem.order_type) }}
                    </span>
                    <span
                        v-if="packagingLabel"
                        class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700"
                    >
                        {{ packagingLabel }}
                    </span>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-100 p-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p
                            class="text-xs uppercase tracking-wide text-secondary-500"
                        >
                            Quantity
                        </p>
                        <p class="text-sm text-secondary-600">
                            Update the number of servings for this item.
                        </p>
                    </div>
                    <span class="text-xs font-semibold text-secondary-500">
                        {{ editableItem.quantity }} in cart
                    </span>
                </div>
                <InputNumber
                    v-model="editableItem.quantity"
                    showButtons
                    buttonLayout="horizontal"
                    class="w-full"
                    :inputClass="'w-full text-center text-lg font-semibold'"
                    :min="0"
                    :max="99"
                >
                    <template #incrementicon>
                        <span class="pi pi-plus" />
                    </template>
                    <template #decrementicon>
                        <span class="pi pi-minus" />
                    </template>
                </InputNumber>
            </section>

            <section
                v-if="hasModifiers(selectedItem)"
                class="rounded-2xl border border-slate-100 p-4"
            >
                <div class="flex items-center justify-between mb-3">
                    <p
                        class="text-xs uppercase tracking-wide text-secondary-500"
                    >
                        Modifiers
                    </p>
                    <span class="text-xs text-secondary-400">
                        {{ modifierCount }} applied
                    </span>
                </div>
                <div class="space-y-3">
                    <div
                        v-for="(modifierData, index) in selectedItem.meta_data"
                        :key="index"
                        class="rounded-xl border border-slate-100 bg-slate-50/80 p-3 text-sm"
                    >
                        <div
                            v-for="(value, key) in getModifierOptions(
                                modifierData,
                            )"
                            :key="key"
                            class="flex items-start justify-between gap-4"
                        >
                            <span class="text-secondary-500">
                                {{ formatModifierKey(String(key)) }}
                            </span>
                            <span
                                class="font-medium text-secondary-900 text-right"
                            >
                                {{ formatModifierValue(value) }}
                            </span>
                        </div>
                        <p
                            v-if="getSpecialInstructions(modifierData)"
                            class="mt-2 text-xs text-secondary-500"
                        >
                            Instructions:
                            <span class="font-medium text-secondary-900">
                                {{ getSpecialInstructions(modifierData) }}
                            </span>
                        </p>
                    </div>
                </div>
            </section>

            <section
                v-if="hasChildOptions"
                class="rounded-2xl border border-slate-100 p-4"
            >
                <div class="flex items-center justify-between mb-3">
                    <p
                        class="text-xs uppercase tracking-wide text-secondary-500"
                    >
                        Option items
                    </p>
                    <span class="text-xs text-secondary-400">
                        {{ childItems.length }} linked
                    </span>
                </div>
                <div class="space-y-2">
                    <div
                        v-for="option in childItems"
                        :key="option.id"
                        class="flex items-center justify-between rounded-xl border border-slate-100 bg-white px-3 py-2 text-sm"
                    >
                        <div>
                            <p class="font-semibold text-secondary-900">
                                {{ getChildName(option) }}
                            </p>
                            <p class="text-xs text-secondary-500">
                                {{ option.quantity }} ×
                                {{ formatMoney(getChildUnitPrice(option)) }}
                            </p>
                        </div>
                        <p class="text-sm font-semibold text-secondary-900">
                            <template v-if="getChildAmount(option) > 0">
                                +{{ formatMoney(getChildAmount(option)) }}
                            </template>
                            <template v-else>Included</template>
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-100 p-4">
                <p
                    class="text-xs uppercase tracking-wide text-secondary-500 mb-3"
                >
                    Discounts & tax
                </p>
                <div class="space-y-2 text-sm">
                    <div v-if="hasAppliedDiscount" class="flex justify-between">
                        <span>
                            Less Discount
                            <span v-if="discountLabel"
                                >({{ discountLabel }})</span
                            >
                        </span>
                        <span class="font-semibold text-success-600">
                            -{{ formatMoney(selectedDiscountAmount) }}
                        </span>
                    </div>
                    <p v-else class="text-secondary-500">
                        No discounts applied to this item yet.
                    </p>
                    <div
                        v-if="selectedItem.less_tax"
                        class="flex justify-between"
                    >
                        <span>Less Tax</span>
                        <span class="font-semibold text-secondary-700">
                            -{{ formatMoney(selectedItem.less_tax) }}
                        </span>
                    </div>
                </div>
            </section>
        </div>
        <div v-else class="py-12 text-center text-secondary-500">
            Select an item to edit.
        </div>

        <template #footer>
            <div class="flex flex-col gap-3 w-full pt-6 border-t">
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
                    <Button
                        v-if="selectedItem && !hasAppliedDiscount"
                        type="button"
                        label="Add Discount"
                        severity="info"
                        size="small"
                        @click="$emit('addDiscount', selectedItem)"
                    />
                    <Button
                        v-if="selectedItem && hasAppliedDiscount"
                        type="button"
                        label="Clear Discount"
                        severity="warn"
                        size="small"
                        @click="$emit('clearDiscount', selectedItem)"
                    />
                    <Button
                        v-if="showAddOnButton"
                        type="button"
                        label="Add Add-on"
                        severity="success"
                        size="small"
                        @click="$emit('addAddOn', selectedItem)"
                    />
                    <Button
                        type="button"
                        label="Add Modifier"
                        severity="success"
                        size="small"
                        :disabled="!selectedItem"
                        @click="$emit('addModifier', selectedItem)"
                    />
                    <Button
                        type="button"
                        label="Save"
                        size="small"
                        :disabled="!selectedItem"
                        @click="saveEdit"
                        class="bg-primary hover:bg-primary-600"
                    />
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { Button, Dialog, InputNumber } from "primevue";
import { formatMoney } from "@/Utils/FormatMoney";
import axios from "axios";
import { route } from "ziggy-js";

const props = defineProps<{
    visible: boolean;
    selectedOrderItem: any;
}>();

const emit = defineEmits<{
    save: [item: any];
    "update:visible": [value: boolean];
    addDiscount: [item: any];
    clearDiscount: [item: any];
    addAddOn: [item: any];
    addModifier: [item: any];
}>();

const selectedItem = computed(() => props.selectedOrderItem);

const addOnAvailabilityCache: Record<number, boolean> = {};
const hasAddOnsForSelectedProduct = ref(false);

const getSelectedProductId = (item: any): number | null => {
    const raw = item?.product_id ?? item?.product?.id ?? null;
    const asNumber = Number(raw);
    return Number.isFinite(asNumber) && asNumber > 0 ? asNumber : null;
};

const checkHasAddOns = async (item: any) => {
    hasAddOnsForSelectedProduct.value = false;

    const productId = getSelectedProductId(item);
    if (!productId) {
        return;
    }

    if (
        Object.prototype.hasOwnProperty.call(addOnAvailabilityCache, productId)
    ) {
        hasAddOnsForSelectedProduct.value = !!addOnAvailabilityCache[productId];
        return;
    }

    try {
        const response = await axios.get(
            route("resto.product.add-ons", { product: productId }),
        );
        const addOns = response?.data?.data ?? response?.data ?? [];
        const available = Array.isArray(addOns) && addOns.length > 0;
        addOnAvailabilityCache[productId] = available;
        hasAddOnsForSelectedProduct.value = available;
    } catch {
        addOnAvailabilityCache[productId] = false;
        hasAddOnsForSelectedProduct.value = false;
    }
};

const showAddOnButton = computed(() => {
    return !!selectedItem.value && hasAddOnsForSelectedProduct.value;
});

const editableItem = ref({
    quantity: 1,
});

const dialogTitle = computed(() => {
    const item = selectedItem.value;
    if (!item) {
        return "Edit Item";
    }

    const label = item.description || item.name || "Item";
    return `Edit ${label}`;
});

const itemName = computed(() => {
    const item = selectedItem.value;
    if (!item) {
        return "Item";
    }

    return item.description || item.name || "Item";
});

const unitPrice = computed(() => {
    const item = selectedItem.value;
    if (!item) {
        return 0;
    }

    const directPrice = Number(item.price ?? item.unit_price ?? 0);
    if (directPrice > 0) {
        return directPrice;
    }

    const subTotal = Number(item.sub_total ?? 0);
    const quantity = Number(item.quantity || 1);
    if (quantity <= 0) {
        return subTotal;
    }

    return subTotal / quantity;
});

const lineTotal = computed(() => {
    const item = selectedItem.value;
    if (!item) {
        return 0;
    }

    return Number(item.amount ?? item.sub_total ?? 0);
});

const selectedDiscountAmount = computed(() => {
    const item = selectedItem.value;
    return item ? Number(item.discount_amount || 0) : 0;
});

const hasAppliedDiscount = computed(() => selectedDiscountAmount.value > 0);

const discountLabel = computed(() => {
    const item = selectedItem.value;
    if (!item) {
        return "";
    }

    return (
        item.discount_name ||
        item.discount?.discount_name ||
        item.discount_code ||
        ""
    );
});

const packagingLabel = computed(() => {
    const item = selectedItem.value;
    if (!item) {
        return "";
    }

    if (item.product_packaging) {
        const packaging = item.product_packaging;
        const unit =
            typeof packaging.unit_measure === "string"
                ? packaging.unit_measure
                : "";
        return `${packaging.name} (${packaging.qty}${unit ?? ""})`;
    }

    if (
        item.product &&
        item.product.unit_measure &&
        !item.product.multiple_packaging
    ) {
        return item.product.unit_measure;
    }

    return "";
});

const childItems = computed(() => selectedItem.value?.children ?? []);
const hasChildOptions = computed(() => childItems.value.length > 0);

const modifierCount = computed(() => {
    const item = selectedItem.value;
    if (!item?.meta_data || !Array.isArray(item.meta_data)) {
        return 0;
    }

    return item.meta_data.length;
});

watch(
    () => props.selectedOrderItem,
    (newItem) => {
        if (newItem) {
            editableItem.value = {
                ...newItem,
                quantity: newItem.quantity || 1,
            };
        } else {
            editableItem.value = { quantity: 1 };
        }

        if (props.visible) {
            checkHasAddOns(newItem);
        } else {
            hasAddOnsForSelectedProduct.value = false;
        }
    },
    { immediate: true },
);

watch(
    () => props.visible,
    (isVisible) => {
        if (isVisible) {
            checkHasAddOns(props.selectedOrderItem);
        } else {
            hasAddOnsForSelectedProduct.value = false;
        }
    },
);

const hasModifiers = (item: any) => {
    return (
        item.meta_data &&
        Array.isArray(item.meta_data) &&
        item.meta_data.length > 0
    );
};

// Helper methods for improved modifiers display
const getSpecialInstructions = (modifierData: any) => {
    return modifierData?.modifier?.specialInstructions || "";
};

const hasModifierOptions = (modifierData: any) => {
    const modifier = modifierData?.modifier;
    return modifier && Object.keys(modifier).length > 1;
};

const getModifierOptions = (modifierData: any) => {
    const modifier = modifierData?.modifier || {};
    const options: any = {};

    Object.entries(modifier).forEach(([key, value]) => {
        if (key !== "specialInstructions") {
            options[key] = value;
        }
    });

    return options;
};

const formatModifierKey = (key: string | number) => {
    return String(key).replace(/([A-Z])/g, " $1");
};

const formatModifierValue = (value: any) => {
    if (Array.isArray(value)) {
        return value.map((item) => item.name || item).join(", ");
    }
    return String(value);
};

const getChildName = (option: any) => {
    return (
        option?.description || option?.name || option?.product?.name || "Option"
    );
};

const getChildUnitPrice = (option: any) => {
    return Number(option?.price ?? option?.unit_price ?? option?.amount ?? 0);
};

const getChildAmount = (option: any) => {
    return Number(option?.amount ?? option?.sub_total ?? option?.price ?? 0);
};

const getOrderTypeBadgeClass = (orderType: string) => {
    switch (orderType) {
        case "dine-in":
            return "bg-blue-100 text-blue-800";
        case "takeout":
            return "bg-green-100 text-green-800";
        case "delivery":
            return "bg-orange-100 text-orange-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

const getOrderTypeLabel = (orderType: string) => {
    switch (orderType) {
        case "dine-in":
            return "Dine-in";
        case "takeout":
            return "Takeout";
        case "delivery":
            return "Delivery";
        default:
            return orderType;
    }
};

const saveEdit = () => {
    if (!selectedItem.value) {
        return;
    }
    console.log("Saving edited item:", editableItem.value);
    emit("save", editableItem.value);
    emit("update:visible", false);
};
</script>
