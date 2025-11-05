<script setup lang="ts">
import Textarea from "primevue/textarea";
import InputLabel from "./InputLabel.vue";
import InputError from "./InputError.vue";

const props = defineProps<{
    modelValue: string;
    id?: string;
    label?: string;
    error?: string | null | undefined;
    placeholder?: string;
    required?: boolean;
    autofocus?: boolean;
    helper?: string;
    maxlength?: any;
    readonly?: boolean;
    mask?: string;
    pattern?: string;
    max?: string;
    rows?: number | string;
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
        <div class="flex justify-between items-end">
            <InputLabel :for="id">
                {{ label }} <span v-if="required" class="text-gray-500">*</span>
            </InputLabel>
            <p v-if="helper" class="text-sm text-gray-800">{{ helper }}</p>
        </div>
        <Textarea
            class="w-full dark:bg-gray-500 border-gray-400 rounded-lg p-2 dark:text-white"
            :aria-label="label"
            :name="label"
            :placeholder="placeholder"
            :aria-placeholder="placeholder"
            @input="input($event)"
            :class="error ? 'border-red-500' : 'border-gray-400'"
            :id="id"
            :value="modelValue"
            :readonly="readonly"
            :required="required"
            :max="props.max"
            :rows="rows"
        />

        <InputError :message="error" />
    </div>
</template>
