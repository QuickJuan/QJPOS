<template>
    <Dialog
        v-model:visible="showModal"
        modal
        :header="`Select Packaging for ${product?.name || 'Product'}`"
        :style="{ width: '500px' }"
        :closable="true"
        @hide="handleCancel"
    >
        <div class="space-y-4">
            <div
                v-if="product?.product_packagings?.length > 0"
                class="grid gap-3"
            >
                <div
                    v-for="packaging in product.product_packagings"
                    :key="packaging.id"
                    :class="[
                        'border-2 rounded-lg p-4 cursor-pointer transition-all duration-200 hover:shadow-md',
                        selectedPackaging?.id === packaging.id
                            ? 'border-primary bg-primary-50'
                            : 'border-gray-200 hover:border-primary-300',
                    ]"
                    @click="selectPackaging(packaging)"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">
                                {{ packaging.name }}
                            </h4>
                            <p class="text-sm text-gray-600">
                                Quantity: {{ packaging.qty }} {{ packaging.unit_measure }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-primary">
                                {{ formatMoney(packaging.price) }}
                            </p>
                        </div>
                    </div>
                    <div
                        v-if="selectedPackaging?.id === packaging.id"
                        class="mt-2"
                    >
                        <div class="flex items-center text-primary">
                            <CheckIcon class="w-5 h-5 mr-1" />
                            <span class="text-sm font-medium">Selected</span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-8">
                <p class="text-gray-500">No packaging options available</p>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end space-x-3 mt-5">
                <Button
                    label="Cancel"
                    class="p-button-secondary"
                    @click="handleCancel"
                />
                <Button
                    label="Add to Cart"
                    class="p-button-primary"
                    :disabled="!selectedPackaging"
                    @click="handleConfirm"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import CheckIcon from "@/Components/icons/CheckIcon.vue";
import { formatMoney } from "@/Utils/FormatMoney";

interface ProductPackaging {
    id: number;
    name: string;
    unit_measure: string;
    price: number;
    qty: number;
    cost: number;
    featured_image_url?: string;
}

interface Product {
    id: number;
    name: string;
    product_packagings?: ProductPackaging[];
}

const props = defineProps<{
    modelValue: boolean;
    product: Product | null;
}>();

const emit = defineEmits<{
    "update:modelValue": [value: boolean];
    confirm: [packaging: ProductPackaging];
    cancel: [];
}>();

const showModal = ref(false);
const selectedPackaging = ref<ProductPackaging | null>(null);

watch(
    () => props.modelValue,
    (newValue) => {
        showModal.value = newValue;
        if (newValue && props.product?.product_packagings?.length === 1) {
            // Auto-select if only one packaging option
            selectedPackaging.value = props.product.product_packagings[0];
        } else {
            selectedPackaging.value = null;
        }
    }
);

watch(showModal, (newValue) => {
    emit("update:modelValue", newValue);
});

const selectPackaging = (packaging: ProductPackaging) => {
    selectedPackaging.value = packaging;
};

const handleConfirm = () => {
    if (selectedPackaging.value) {
        emit("confirm", selectedPackaging.value);
        showModal.value = false;
    }
};

const handleCancel = () => {
    emit("cancel");
    showModal.value = false;
};
</script>
