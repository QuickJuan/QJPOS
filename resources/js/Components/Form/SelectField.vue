<template>
    <div class="flex flex-col">
        <div
            class="flex justify-between items-end text-gray-700 dark:text-gray-300"
        >
            <InputLabel :for="id">
                {{ label }} <span v-if="required" class="text-red-500">*</span>
            </InputLabel>
        </div>

        <Dropdown
            :modelValue="props.modelValue"
            @update:modelValue="emit('update:modelValue', $event)"
            :options="computedOptions"
            :optionLabel="optionLabel"
            :optionValue="optionValue"
            :defaultValue="defaultValue"
            :placeholder="placeholder || 'Select an option'"
            :filter="searchable"
            @filter="onFilter"
            :showClear="showClear"
            class="w-full text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm"
            panelClass="shadow-lg border border-gray-200 rounded-lg"
        />

        <InputError :message="error" />
    </div>
</template>

<script setup lang="ts">
import Dropdown from "primevue/dropdown";
import InputLabel from "./InputLabel.vue";
import InputError from "./InputError.vue";
import { computed } from "vue";

const props = defineProps<{
    id?: string;
    label: string;
    required?: boolean;
    optionLabel: string;
    optionValue: string;
    defaultValue?: string;
    placeholder?: string;
    options: Array<any>;
    error?: string;
    modelValue: any;
    searchable?: boolean;
    readonly?: boolean;
    showClear?: boolean;
}>();

const computedOptions = computed(() => [
    { [props.optionLabel]: "Select an option", [props.optionValue]: null },
    ...props.options,
]);

const emit = defineEmits(["update:modelValue", "search"]);

const onFilter = (event: any) => {
    const searchQuery = event.value;
    emit("search", searchQuery);
};
</script>
