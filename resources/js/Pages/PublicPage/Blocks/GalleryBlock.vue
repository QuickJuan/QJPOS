<template>
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <h2 v-if="content.title" class="text-3xl font-bold text-gray-900 mb-6">
            {{ content.title }}
        </h2>
        <div :class="gridClasses">
            <div
                v-for="(image, index) in content.images"
                :key="index"
                class="relative group overflow-hidden rounded-lg"
            >
                <img
                    :src="`/storage/${image.path}`"
                    :alt="image.alt || `Gallery image ${index + 1}`"
                    class="w-full h-64 object-cover transition-transform group-hover:scale-110"
                />
                <div
                    v-if="image.caption"
                    class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-70 text-white p-3 text-sm"
                >
                    {{ image.caption }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    content: Object,
    settings: Object,
});

const gridClasses = computed(() => {
    const columns = props.content.columns || 3;
    return `grid grid-cols-1 md:grid-cols-${columns} gap-4`;
});
</script>
