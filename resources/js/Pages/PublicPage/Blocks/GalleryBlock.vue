<template>
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <h2 v-if="content.title" class="text-3xl font-bold text-gray-900 mb-6">
            {{ content.title }}
        </h2>

        <!-- Grid / Masonry layout -->
        <div v-if="layout !== 'carousel'" :class="gridClasses">
            <div
                v-for="(image, index) in images"
                :key="index"
                class="relative group overflow-hidden rounded-lg"
            >
                <img
                    :src="resolveUrl(image.url)"
                    :alt="image.alt || `Gallery image ${index + 1}`"
                    class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-105"
                />
                <div
                    v-if="image.caption"
                    class="absolute bottom-0 left-0 right-0 bg-black/60 text-white p-3 text-sm"
                >
                    {{ image.caption }}
                </div>
            </div>
        </div>

        <!-- Carousel layout -->
        <div v-else class="relative overflow-hidden rounded-lg select-none">
            <!-- Slides -->
            <div
                class="flex transition-transform duration-500 ease-in-out"
                :style="{ transform: `translateX(-${current * 100}%)` }"
            >
                <div
                    v-for="(image, index) in images"
                    :key="index"
                    class="relative min-w-full"
                >
                    <img
                        :src="resolveUrl(image.url)"
                        :alt="image.alt || `Gallery image ${index + 1}`"
                        class="w-full h-80 object-cover"
                    />
                    <div
                        v-if="image.caption"
                        class="absolute bottom-0 left-0 right-0 bg-black/60 text-white p-3 text-sm text-center"
                    >
                        {{ image.caption }}
                    </div>
                </div>
            </div>

            <!-- Prev / Next buttons -->
            <button
                v-if="images.length > 1"
                @click="prev"
                class="absolute left-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white rounded-full w-9 h-9 flex items-center justify-center transition"
                aria-label="Previous"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
            </button>
            <button
                v-if="images.length > 1"
                @click="next"
                class="absolute right-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white rounded-full w-9 h-9 flex items-center justify-center transition"
                aria-label="Next"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>
            </button>

            <!-- Dots -->
            <div
                v-if="images.length > 1"
                class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5"
            >
                <button
                    v-for="(_, i) in images"
                    :key="i"
                    @click="current = i"
                    class="w-2 h-2 rounded-full transition"
                    :class="i === current ? 'bg-white' : 'bg-white/40'"
                    :aria-label="`Go to slide ${i + 1}`"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    content: Object,
    settings: Object,
});

const images = computed(() => props.content?.images ?? []);
const layout = computed(() => props.content?.layout || "grid");
const current = ref(0);

const resolveUrl = (path) => {
    if (!path) return "";
    if (
        path.startsWith("http://") ||
        path.startsWith("https://") ||
        path.startsWith("/")
    )
        return path;
    return `/storage/${path}`;
};

const gridClasses = computed(() => {
    const columns = props.content?.columns || 3;
    const colMap = {
        2: "md:grid-cols-2",
        3: "md:grid-cols-3",
        4: "md:grid-cols-4",
    };
    const col = colMap[String(columns)] || "md:grid-cols-3";
    return `grid grid-cols-1 ${col} gap-4`;
});

const prev = () => {
    current.value =
        (current.value - 1 + images.value.length) % images.value.length;
};
const next = () => {
    current.value = (current.value + 1) % images.value.length;
};
</script>
