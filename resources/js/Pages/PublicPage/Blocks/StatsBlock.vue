<template>
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <h2
            v-if="content.title"
            class="text-3xl font-bold text-gray-900 mb-6 text-center"
        >
            {{ content.title }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div
                v-for="stat in content.stats"
                :key="stat.label"
                class="text-center"
            >
                <div class="text-5xl font-bold text-orange-600 mb-2">
                    {{ stat.value }}
                </div>
                <div class="text-gray-600">{{ stat.label }}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    content: Object,
    settings: Object,
});
</script>
