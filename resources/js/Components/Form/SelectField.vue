<template>
    <div class="flex flex-col">
        <div
            v-if="!hideLabel"
            class="flex justify-between items-end text-neutral-700 dark:text-neutral-300"
        >
            <InputLabel :for="id">
                {{ label }}
                <span v-if="required" class="text-error-500">*</span>
            </InputLabel>
        </div>

        <Dropdown
            :modelValue="props.modelValue"
            @update:modelValue="emit('update:modelValue', $event)"
            @change="emit('change', $event)"
            :options="computedOptions"
            :optionLabel="optionLabel"
            :optionValue="optionValue"
            :defaultValue="defaultValue"
            :disabled="disabled"
            :placeholder="placeholder || 'Select an option'"
            :filter="searchable"
            @filter="onFilter"
            :showClear="showClear"
            class="w-full text-neutral-900 bg-white border border-neutral-300 rounded-lg shadow-sm"
            panelClass="shadow-lg border border-neutral-200 rounded-lg"
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
    error?: string;
    optionLabel: string;
    optionValue: string;
    defaultValue?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    showClear?: boolean;
    searchable?: boolean;
    modelValue: any;
    options: Array<any>;
    hideLabel?: boolean;
}>();

const computedOptions = computed(() => [
    { [props.optionLabel]: "Select an option", [props.optionValue]: null },
    ...props.options,
]);

const emit = defineEmits(["update:modelValue", "change", "search"]);

const onFilter = (event: any) => {
    const searchQuery = event.value;
    emit("search", searchQuery);
};
</script>
