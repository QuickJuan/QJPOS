<template>
    <Dialog
        :visible="visible"
        modal
        :style="{ width: 'min(46rem, 94vw)' }"
        :closable="false"
        :showHeader="false"
        @update:visible="handleClose"
    >
        <div class="overflow-x-hidden">
            <div
                class="mb-4 rounded-xl border border-gray-200 bg-white px-6 py-4"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold tracking-tight">
                            Add Add-on
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Applies to
                            <span class="font-semibold">{{
                                selectedItemCount
                            }}</span>
                            {{ selectedItemCount === 1 ? "item" : "items" }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-700 transition hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-200"
                        aria-label="Close"
                        @click="handleClose"
                    >
                        <span class="text-xl leading-none">×</span>
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <div
                        v-if="!selectedItemCount"
                        class="text-sm text-gray-500"
                    >
                        Select at least one item first.
                    </div>

                    <div
                        v-else-if="!isSameProduct"
                        class="text-sm text-gray-500"
                    >
                        Please select items from the same product to add an
                        add-on.
                    </div>

                    <div v-else>
                        <div
                            class="mb-3 flex items-center justify-between gap-3"
                        >
                            <p class="text-sm font-semibold text-gray-900">
                                Choose an add-on
                            </p>
                            <span v-if="loading" class="text-xs text-gray-500"
                                >Loading...</span
                            >
                        </div>

                        <div
                            v-if="error"
                            class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
                        >
                            {{ error }}
                        </div>

                        <div
                            v-else-if="!loading && !addOns.length"
                            class="text-sm text-gray-500"
                        >
                            No add-ons available for this product.
                        </div>

                        <div
                            v-else
                            class="grid grid-cols-2 gap-2 sm:grid-cols-3"
                        >
                            <div
                                v-for="addOn in addOns"
                                :key="addOn.id"
                                class="min-w-0"
                            >
                                <input
                                    :id="`addon-${addOn.id}`"
                                    type="radio"
                                    name="selected-addon"
                                    :value="addOn.id"
                                    v-model="selectedAddOnId"
                                    class="peer sr-only"
                                />
                                <label
                                    :for="`addon-${addOn.id}`"
                                    class="flex w-full cursor-pointer select-none items-center justify-between gap-2 rounded-lg border px-3 py-2 text-sm shadow-sm transition duration-150 hover:-translate-y-px hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-200 active:translate-y-0 active:shadow"
                                    :class="
                                        selectedAddOnId === addOn.id
                                            ? 'border-primary-500 bg-primary-50 text-primary-800'
                                            : 'border-gray-300 bg-white text-gray-800 hover:border-primary-300 hover:bg-primary-50'
                                    "
                                >
                                    <span
                                        class="min-w-0 truncate font-semibold"
                                    >
                                        {{ addOn.name }}
                                    </span>
                                    <div
                                        class="flex shrink-0 items-center gap-2"
                                    >
                                        <span
                                            class="text-xs font-semibold text-gray-600"
                                        >
                                            +{{ addOn.add_on_price_formatted }}
                                        </span>
                                        <span
                                            v-if="selectedAddOnId === addOn.id"
                                            class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-800"
                                            aria-hidden="true"
                                        >
                                            ✓
                                        </span>
                                    </div>
                                </label>
                                <p
                                    v-if="addOn.packaging_label"
                                    class="mt-1 truncate text-xs text-gray-500"
                                >
                                    {{ addOn.packaging_label }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end space-x-3">
                <Button
                    label="Cancel"
                    class="p-button-text"
                    @click="handleClose"
                />
                <Button
                    label="Add Add-on"
                    class="p-button-primary"
                    :disabled="!canSubmit"
                    @click="handleAdd"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import { route } from "ziggy-js";
import { httpGet } from "@/Utils/axiosHelper";
import { formatMoney } from "@/Utils/FormatMoney";

const props = defineProps<{
    visible: boolean;
    selectedItems: any[];
}>();

const emit = defineEmits<{
    add: [payload: any];
    "update:visible": [value: boolean];
}>();

const visible = computed(() => props.visible);
const selectedItemCount = computed(() => props.selectedItems?.length || 0);

const firstProductId = computed(
    () => props.selectedItems?.[0]?.product_id ?? null,
);
const isSameProduct = computed(() => {
    if (!selectedItemCount.value) return true;
    return props.selectedItems.every(
        (item) => item.product_id === firstProductId.value,
    );
});

type AddOnOption = {
    id: number;
    name: string;
    add_on_price: string | number;
    add_on_price_formatted: string;
    packaging_label?: string | null;
};

const addOns = ref<AddOnOption[]>([]);
const selectedAddOnId = ref<number | null>(null);
const loading = ref(false);
const error = ref<string | null>(null);

const canSubmit = computed(() => {
    return (
        selectedItemCount.value > 0 &&
        isSameProduct.value &&
        !!selectedAddOnId.value
    );
});

const loadAddOns = async () => {
    addOns.value = [];
    selectedAddOnId.value = null;
    error.value = null;

    if (!firstProductId.value || !isSameProduct.value) return;

    loading.value = true;
    const response = await httpGet<any>(
        route("resto.product.add-ons", { product: firstProductId.value }),
    );
    loading.value = false;

    if (!response.success || !response.data) {
        error.value = response.error || "Failed to load add-ons.";
        return;
    }

    const raw = Array.isArray(response.data)
        ? response.data
        : response.data.data;
    addOns.value = (raw || []).map((item: any) => ({
        id: Number(item.id),
        name: String(item.name ?? ""),
        add_on_price: item.add_on_price,
        add_on_price_formatted: formatMoney(item.add_on_price ?? 0),
        packaging_label: item.packaging_label ?? null,
    }));
};

watch(
    () => props.visible,
    (newValue) => {
        if (newValue) {
            loadAddOns();
        }
    },
);

watch(
    () => firstProductId.value,
    () => {
        if (props.visible) {
            loadAddOns();
        }
    },
);

const handleAdd = () => {
    const selected = addOns.value.find((a) => a.id === selectedAddOnId.value);
    emit("add", {
        selectedCartItems: props.selectedItems,
        addOn: selected,
    });
    emit("update:visible", false);
};

const handleClose = () => {
    emit("update:visible", false);
};
</script>
