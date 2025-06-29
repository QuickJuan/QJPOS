<template>
    <div class="flex flex-col gap-2">
        <div
            class="flex justify-between items-end text-gray-700 dark:text-gray-300"
        >
            <InputLabel :for="id">
                {{ label }} <span v-if="required" class="text-red-500">*</span>
            </InputLabel>
        </div>
        <input
            v-model="proxyModel"
            :type="props.type ?? 'text'"
            :id="props.id"
            :name="props.id"
            class="w-full px-4 py-3 border border-gray-300 bg-white font-medium text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200 placeholder-gray-400"
            :placeholder="props.placeholder"
            :disabled="props.disabled"
            :aria-describedby="props.error ? `${props.id}-error` : undefined"
        />
        <p
            v-if="props.error"
            :id="`${props.id}-error`"
            class="text-red-500 text-sm font-medium"
            role="alert"
        >
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
