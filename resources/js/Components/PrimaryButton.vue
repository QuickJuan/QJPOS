<template>
    <button
        :type="props.type ?? 'button'"
        :disabled="props.disabled"
        class="w-full bg-primary hover:bg-primary-light text-white font-semibold py-3 px-6 rounded-lg shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]"
    >
        <slot />
    </button>
</template>

<script setup lang="ts">
const props = defineProps<{
    type?: "button" | "submit" | "reset";
    disabled?: boolean;
}>();
</script>
