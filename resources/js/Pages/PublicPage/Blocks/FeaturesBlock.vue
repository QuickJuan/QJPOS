<template>
    <div class="py-12 px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Section heading -->
            <div
                v-if="content.title || content.subtitle"
                class="text-center mb-12"
            >
                <h2
                    v-if="content.title"
                    class="text-3xl font-bold text-gray-900 mb-3"
                >
                    {{ content.title }}
                </h2>
                <p
                    v-if="content.subtitle"
                    class="text-gray-500 text-lg max-w-2xl mx-auto"
                >
                    {{ content.subtitle }}
                </p>
            </div>

            <!-- Features grid -->
            <div
                class="grid grid-cols-1 sm:grid-cols-2 gap-8"
                :class="gridColsClass"
            >
                <div
                    v-for="(feature, index) in content.features"
                    :key="index"
                    class="flex flex-col items-center text-center"
                >
                    <!-- Feature image -->
                    <div
                        class="w-24 h-24 rounded-full overflow-hidden mb-4 flex-shrink-0 bg-gray-100"
                    >
                        <img
                            v-if="feature.image"
                            :src="`/storage/${feature.image}`"
                            :alt="feature.title"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center text-gray-300"
                        >
                            <svg
                                class="w-10 h-10"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                    </div>

                    <!-- Title -->
                    <h3 class="text-lg font-bold text-gray-900 mb-2">
                        {{ feature.title }}
                    </h3>

                    <!-- Description -->
                    <p
                        v-if="feature.description"
                        class="text-gray-500 text-sm leading-relaxed"
                    >
                        {{ feature.description }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    content: {
        type: Object,
        default: () => ({ title: "", subtitle: "", features: [] }),
    },
    settings: {
        type: Object,
        default: () => ({ columns: "3" }),
    },
});

const gridColsClass = computed(() => {
    const cols = props.settings?.columns ?? "3";
    return (
        {
            2: "md:grid-cols-2",
            3: "md:grid-cols-3",
            4: "md:grid-cols-4",
        }[cols] ?? "md:grid-cols-3"
    );
});
</script>
