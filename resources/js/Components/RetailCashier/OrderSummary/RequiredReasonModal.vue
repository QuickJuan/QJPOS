<template>
    <Dialog
        :visible="props.visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        header="Enter the reason for voiding this order"
        :style="{ width: '25rem' }"
    >
        <div class="flex flex-col gap-4 mb-4">
            <Textarea v-model="reason" label="Reason" required />
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
import Textarea from "@/Components/Form/Textarea.vue";
import { Button, Dialog } from "primevue";
import { ref } from "vue";

const props = defineProps<{
    visible: boolean;
    orderItem: any;
}>();

const reason = ref(null);

const emit = defineEmits<{
    submit: [item: any];
    "update:visible": [value: boolean];
}>();

const saveEdit = () => {
    emit("submit", {
        orderItem: props.orderItem,
        reason: reason.value,
    });
    emit("update:visible", false);
};
</script>
