<template>
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <h2 v-if="content.title" class="text-3xl font-bold text-gray-900 mb-4">
            {{ content.title }}
        </h2>
        <div class="prose max-w-none" v-html="content.text"></div>
    </div>
</template>

<script setup>
defineProps({
    content: Object,
    settings: Object,
});
</script>
