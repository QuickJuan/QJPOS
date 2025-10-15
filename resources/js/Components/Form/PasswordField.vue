<template>
    <div class="flex flex-col gap-2">
        <div
            class="flex justify-between items-end text-gray-700 dark:text-gray-300"
        >
            <InputLabel :for="id">
                {{ label }} <span v-if="required" class="text-red-500">*</span>
            </InputLabel>
        </div>
        <div class="relative">
            <input
                v-model="proxyModel"
                :type="showPassword ? 'text' : 'password'"
                :id="props.id"
                :name="props.id"
                class="w-full pr-12 pl-4 py-3 border border-gray-300 bg-white font-medium text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200 placeholder-gray-400"
                :placeholder="props.placeholder"
                :disabled="props.disabled"
                autocomplete="current-password"
                :aria-describedby="
                    props.error ? `${props.id}-error` : undefined
                "
            />
            <button
                type="button"
                @click="togglePassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none focus:text-primary transition-colors duration-200"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
            >
                <svg
                    v-if="!showPassword"
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                </svg>
                <svg
                    v-else
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"
                    />
                </svg>
            </button>
        </div>
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
import { computed, ref } from "vue";
import InputLabel from "./InputLabel.vue";

const props = defineProps<{
    id: string;
    label: string;
    modelValue: string;
    placeholder?: string;
    error?: string | null;
    required?: boolean;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
}>();

const showPassword = ref(false);

const proxyModel = computed({
    get: () => props.modelValue,
    set: (value) => {
        emit("update:modelValue", value);
    },
});

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};
</script>
