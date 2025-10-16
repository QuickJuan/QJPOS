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
        <div class="flex justify-end gap-2">
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                @click="$emit('update:visible', false)"
            />
            <Button
                type="button"
                label="Save"
                @click="saveEdit"
                class="bg-primary hover:bg-primary-600"
            />
        </div>
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
