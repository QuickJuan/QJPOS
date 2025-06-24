<template>
    <div class="flex flex-col gap-2">
        <div class="flex justify-between items-end text-white dark:text-white">
            <InputLabel :for="id">
                {{ label }} <span v-if="required" class="text-red-500">*</span>
            </InputLabel>
        </div>
        <input
            v-model="proxyModel"
            :type="props.type ?? 'text'"
            :id="props.id"
            :name="props.id"
            class="border border-gray-300 font-bold text-primary rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500"
            :placeholder="props.placeholder"
            :disabled="props.disabled"
        />
        <p v-if="props.error" class="text-red-500 text-sm">
            {{ props.error }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import InputLabel from "./InputLabel.vue";

const props = defineProps<{
    id: string;
    label: string;
    modelValue: string;
    placeholder?: string;
    error?: string | null;
    type?: string;
    required?: boolean;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
}>();

const proxyModel = computed({
    get: () => props.modelValue,
    set: (value) => {
        emit("update:modelValue", value);
    },
});
</script>
