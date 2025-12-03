<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        :header="`Edit ${selectedOrderItem?.name || ''}`"
        :style="{ width: '25rem' }"
    >
        <div class="flex flex-col gap-1 mb-8">
            <span class="font-semibold text-secondary-800">
                Modifiers:
            </span>
            <!-- Modifiers -->
            <div v-if="hasModifiers(selectedOrderItem)">
                <div
                    v-for="(modifierData, index) in selectedOrderItem.meta_data"
                    :key="index"
                    class="text-xs text-gray-700 space-y-1 ml-3"
                >
                    <!-- Modifier Options -->
                    <div
                        v-for="(value, key) in getModifierOptions(modifierData)"
                        :key="key"
                    >
                        <strong class="capitalize">
                            {{ formatModifierKey(String(key)) }}:
                        </strong>
                        {{ formatModifierValue(value) }}
                    </div>

                    <!-- Special Instructions -->
                    <div v-if="getSpecialInstructions(modifierData)">
                        <strong>Instructions:</strong>
                        {{ getSpecialInstructions(modifierData) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-4 mb-4">
            <label for="quantity" class="font-semibold w-24 text-secondary-800">
                Quantity
            </label>
            <InputNumber
                v-model="editableItem.quantity"
                showButtons
                buttonLayout="horizontal"
                style="width: 1rem"
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
        </div>
        <template #footer>
            <div class="flex flex-col gap-2 justify-between w-full">
                <div class="flex gap-2">
                    <Button
                        type="button"
                        label="Add Discount"
                        severity="info"
                        size="small"
                        @click="$emit('addDiscount', selectedOrderItem)"
                    />
                    <Button
                        type="button"
                        label="Clear Discount"
                        severity="warn"
                        size="small"
                        @click="$emit('clearDiscount', selectedOrderItem)"
                    />
                    <Button
                        type="button"
                        label="Add Modifier"
                        severity="success"
                        size="small"
                        @click="$emit('addModifier', selectedOrderItem)"
                    />
                </div>
                <div class="flex flex-end justify-end gap-2 mt-5">
                    <Button
                        type="button"
                        label="Cancel"
                        severity="secondary"
                        size="small"
                        @click="$emit('update:visible', false)"
                    />
                    <Button
                        type="button"
                        label="Save"
                        size="small"
                        @click="saveEdit"
                        class="bg-primary hover:bg-primary-600"
                    />
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { Button, Dialog, InputNumber } from "primevue";

const props = defineProps<{
    visible: boolean;
    selectedOrderItem: any;
}>();

const emit = defineEmits<{
    save: [item: any];
    "update:visible": [value: boolean];
    addDiscount: [item: any];
    clearDiscount: [item: any];
    addModifier: [item: any];
}>();

const editableItem = ref({
    quantity: 1,
});

watch(
    () => props.selectedOrderItem,
    (newItem) => {
        if (newItem) {
            editableItem.value = {
                ...newItem,
                quantity: newItem.quantity || 1,
            };
        }
    },
    { immediate: true }
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

const saveEdit = () => {
    emit("save", editableItem.value);
    emit("update:visible", false);
};
</script>
