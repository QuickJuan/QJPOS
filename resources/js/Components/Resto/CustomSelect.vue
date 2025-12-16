<template>
    <div class="relative">
        <select
            :value="modelValue"
            @change="handleChange"
            :class="[
                'w-full appearance-none px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm bg-white',
                'focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none',
                'transition-colors duration-200',
                'hover:border-gray-400',
                customClass,
            ]"
        >
            <slot></slot>
        </select>

        <!-- Custom Dropdown Arrow -->
        <div
            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
        >
            <ChevronDownIcon class="w-5 h-5 text-gray-500" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ChevronDownIcon } from "@heroicons/vue/24/outline";

interface Props {
    modelValue: string | number;
    customClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    customClass: "",
});

const emit = defineEmits<{
    "update:modelValue": [value: string | number];
    change: [value: string | number];
}>();

const handleChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    const value = target.value;
    emit("update:modelValue", value);
    emit("change", value);
};
</script>

<style scoped>
/* Ensure proper spacing for dropdown arrow */
select {
    background-image: none;
}
</style>
