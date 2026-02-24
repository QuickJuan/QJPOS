<template>
    <Dialog
        :visible="visible"
        modal
        :header="label || 'Service Charge'"
        class="w-full max-w-md"
        @update:visible="(val: boolean) => emit('update:visible', val)"
    >
        <div class="space-y-4">
            <p class="text-sm text-secondary-600">
                Set the {{ label || "service charge" }} amount for this order.
            </p>

            <div class="space-y-2">
                <label class="text-sm font-semibold text-secondary-800">
                    {{ label || "Service Charge" }}
                </label>
                <InputNumber
                    v-model="amount"
                    mode="decimal"
                    :min="0"
                    :max="999999"
                    :step="1"
                    input-class="w-full"
                    class="w-full"
                    placeholder="Enter amount"
                    :disabled="!isManual"
                />
                <p
                    v-if="!isManual"
                    class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded px-2 py-1"
                >
                    This location uses automatic service charge. Manual override
                    is disabled.
                </p>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <Button
                    label="Cancel"
                    severity="secondary"
                    outlined
                    size="small"
                    @click="emit('update:visible', false)"
                />
                <Button
                    label="Save"
                    :disabled="!isManual"
                    size="small"
                    @click="handleSave"
                />
            </div>
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { Dialog } from "primevue";
import InputNumber from "primevue/inputnumber";
import Button from "primevue/button";

const props = defineProps<{
    visible: boolean;
    label?: string;
    initialAmount?: number;
    isManual?: boolean;
}>();

const emit = defineEmits<{
    "update:visible": [value: boolean];
    save: [amount: number];
}>();

const amount = ref<number>(props.initialAmount || 0);

watch(
    () => props.initialAmount,
    (val) => {
        amount.value = val ?? 0;
    },
);

watch(
    () => props.visible,
    (visible) => {
        if (visible) {
            amount.value = props.initialAmount ?? 0;
        }
    },
);

const handleSave = () => {
    emit("save", Number(amount.value || 0));
};
</script>
