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
                    <!-- Icon circle -->
                    <div
                        class="w-20 h-20 rounded-full flex items-center justify-center text-3xl mb-4 flex-shrink-0"
                        :class="iconBgClass(feature.icon_color)"
                    >
                        {{ feature.icon || "⭐" }}
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

const colorMap = {
    orange: "bg-orange-100 text-orange-600",
    teal: "bg-teal-100 text-teal-600",
    blue: "bg-blue-100 text-blue-600",
    green: "bg-green-100 text-green-600",
    red: "bg-red-100 text-red-600",
    purple: "bg-purple-100 text-purple-600",
    yellow: "bg-yellow-100 text-yellow-600",
    pink: "bg-pink-100 text-pink-600",
    indigo: "bg-indigo-100 text-indigo-600",
    gray: "bg-gray-100 text-gray-600",
};

function iconBgClass(color) {
    return colorMap[color] ?? colorMap["orange"];
}
</script>
