<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message" class="mt-1">
        <p class="text-sm font-medium text-red-600">
            {{ message }}
        </p>
    </div>
</template>
