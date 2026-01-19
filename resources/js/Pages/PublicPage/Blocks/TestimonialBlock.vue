<template>
    <div class="bg-gray-100 rounded-lg p-8 mb-8 text-center">
        <blockquote class="text-2xl font-medium text-gray-900 mb-4">
            "{{ content.quote }}"
        </blockquote>
        <cite class="text-gray-600">— {{ content.author }}</cite>
        <p v-if="content.position" class="text-sm text-gray-500">
            {{ content.position }}
        </p>
    </div>
</template>

<script setup>
defineProps({
    content: Object,
    settings: Object,
});
</script>
