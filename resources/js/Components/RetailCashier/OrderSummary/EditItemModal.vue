<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        :header="`Edit ${selectedOrderItem?.name || ''}`"
        :style="{ width: '25rem' }"
    >
        <div class="flex flex-col gap-4 mb-4">
            <label for="quantity" class="font-semibold w-24 text-secondary-800"
                >Quantity</label
            >
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

const saveEdit = () => {
    emit("save", editableItem.value);
    emit("update:visible", false);
};
</script>

