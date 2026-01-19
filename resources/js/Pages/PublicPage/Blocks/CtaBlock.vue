<template>
    <div class="bg-orange-600 text-white rounded-lg p-12 mb-8 text-center">
        <h2 class="text-4xl font-bold mb-4">{{ content.title }}</h2>
        <p v-if="content.description" class="text-xl mb-6">
            {{ content.description }}
        </p>
        <a
            v-if="content.button_text && content.button_url"
            :href="content.button_url"
            class="inline-block bg-white text-orange-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors"
        >
            {{ content.button_text }}
        </a>
    </div>
</template>

<script setup>
defineProps({
    content: Object,
    settings: Object,
});
</script>
