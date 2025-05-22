<template>
    <button
        :type="props.type ?? 'button'"
        class="bg-primary text-white font-medium py-2 px-4 rounded shadow hover:bg-primary-light focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed"
    >
        <slot />
    </button>
</template>

<script setup lang="ts">

const props = defineProps<{
    type?: 'button' | 'submit' | 'reset';
    disabled?: boolean;
}>()
</script>
