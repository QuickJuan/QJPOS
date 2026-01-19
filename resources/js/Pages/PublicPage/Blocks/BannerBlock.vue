<template>
    <div class="relative bg-gray-900 mb-8 rounded-lg overflow-hidden">
        <img
            v-if="content.image"
            :src="`/storage/${content.image}`"
            :alt="content.title"
            class="w-full h-96 object-cover"
            :style="{ opacity: 1 - (content.overlay_opacity || 0) }"
        />
        <div
            class="absolute inset-0 flex items-center justify-center text-center p-8"
        >
            <div class="max-w-3xl">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
                    {{ content.title }}
                </h2>
                <p v-if="content.subtitle" class="text-xl text-white mb-6">
                    {{ content.subtitle }}
                </p>
                <a
                    v-if="content.button_text && content.button_url"
                    :href="content.button_url"
                    :class="buttonClasses"
                    class="inline-block px-8 py-3 rounded-lg font-semibold transition-colors"
                >
                    {{ content.button_text }}
                </a>
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

const buttonClasses = computed(() => {
    const style = props.content.button_style || "primary";
    return {
        primary: "bg-orange-600 text-white hover:bg-orange-700",
        secondary: "bg-gray-600 text-white hover:bg-gray-700",
        outline:
            "border-2 border-white text-white hover:bg-white hover:text-gray-900",
    }[style];
});
</script>
