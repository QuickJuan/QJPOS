<script setup lang="ts">
import { InputText } from "primevue";
import InputLabel from "./InputLabel.vue";
import InputError from "./InputError.vue";

const props = defineProps<{
    modelValue: string | number;
    id?: string;
    label?: string;
    error?: string | null | undefined;
    placeholder?: string;
    type?: any;
    required?: boolean;
    autofocus?: boolean;
    helper?: string;
    maxlength?: any;
    readonly?: boolean;
    disabled?: boolean;
    mask?: string;
    pattern?: string;
    max?: string;
}>();

const input = (event: any) => {
    if (props.maxlength && event.target.value.length > +props.maxlength) {
        event.target.value = event.target.value?.slice(0, +props.maxlength);
        return;
    }

    emit("update:modelValue", event.target.value);
};

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="flex flex-col">
        <div
            class="flex justify-between items-end text-neutral-700 dark:text-neutral-300"
        >
            <InputLabel :for="id">
                {{ label }}
                <span v-if="label && required" class="text-neutral-500">*</span>
            </InputLabel>
        </div>
        <InputText
            class="w-full dark:bg-white placeholder:text-sm placeholder:md:text-base"
            :aria-label="label"
            :name="label"
            :placeholder="placeholder"
            :aria-placeholder="placeholder"
            @input="input($event)"
            :class="error ? 'border-error-500' : 'border-neutral-300'"
            :type="type ?? 'text'"
            :id="id"
            :value="modelValue"
            :readonly="readonly"
            :disabled="disabled"
            :required="required"
            :max="props.max"
        />

        <p v-if="helper" class="text-xs text-neutral-500">{{ helper }}</p>

        <InputError :message="error" />
    </div>
</template>
