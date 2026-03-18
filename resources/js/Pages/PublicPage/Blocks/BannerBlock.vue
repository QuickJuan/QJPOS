<template>
    <div
        class="relative mb-8 rounded-lg overflow-hidden h-96"
        :style="{ backgroundColor: backgroundColor }"
    >
        <img
            v-if="imageSrc"
            :src="imageSrc"
            :alt="content.title"
            class="w-full h-full object-cover"
        />

        <div
            class="absolute inset-0"
            :style="{ backgroundColor: overlayColor }"
            aria-hidden="true"
        ></div>

        <div
            class="absolute inset-0 flex items-center p-8"
            :class="alignmentClass"
        >
            <div
                class="max-w-3xl"
                :class="textAlignClass"
                :style="{ color: textColor }"
            >
                <h2
                    class="text-4xl md:text-5xl font-bold mb-4"
                    :style="{ color: textColor }"
                >
                    {{ content.title }}
                </h2>
                <p
                    v-if="content.subtitle"
                    class="text-xl mb-6"
                    :style="{ color: textColor }"
                >
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

const resolveAsset = (path) => {
    if (!path) return null;
    if (path.startsWith("http://") || path.startsWith("https://")) return path;
    if (path.startsWith("/")) return path;
    return `/storage/${path}`;
};

const imageSrc = computed(() => resolveAsset(props.content?.image));

const backgroundColor = computed(
    () => props.content?.background_color || "#0f172a",
);

const overlayOpacity = computed(() => {
    const raw = props.content?.overlay_opacity;
    const parsed = parseFloat(raw);
    return Number.isFinite(parsed) ? Math.min(Math.max(parsed, 0), 1) : 0;
});

const overlayColor = computed(() => {
    if (!imageSrc.value) return backgroundColor.value;
    return `rgba(0, 0, 0, ${overlayOpacity.value})`;
});

const textColor = computed(() => props.content?.text_color || "#ffffff");

const alignment = computed(() => props.content?.alignment || "center");

const alignmentClass = computed(() => {
    return (
        {
            left: "justify-start",
            center: "justify-center",
            right: "justify-end",
        }[alignment.value] || "justify-center"
    );
});

const textAlignClass = computed(() => {
    return (
        {
            left: "text-left",
            center: "text-center",
            right: "text-right",
        }[alignment.value] || "text-center"
    );
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
