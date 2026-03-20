<template>
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <div
            v-if="content?.title || content?.subtitle"
            class="mb-8 text-center"
        >
            <h2
                v-if="content?.title"
                class="text-3xl font-bold text-gray-900 mb-2"
            >
                {{ content.title }}
            </h2>
            <p v-if="content?.subtitle" class="text-lg text-gray-500">
                {{ content.subtitle }}
            </p>
        </div>

        <div
            v-if="careers && careers.length"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            <div
                v-for="career in careers"
                :key="career.id"
                class="border border-gray-200 rounded-xl p-6 flex flex-col hover:shadow-md transition-shadow"
            >
                <div class="flex flex-wrap gap-2 mb-3">
                    <span
                        v-if="career.department"
                        class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded-full"
                    >
                        {{ career.department }}
                    </span>
                    <span
                        v-if="career.employment_type"
                        class="inline-block bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full capitalize"
                    >
                        {{ career.employment_type.replace("_", " ") }}
                    </span>
                </div>

                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                    {{ career.title }}
                </h3>

                <div
                    v-if="career.location"
                    class="flex items-center gap-1 text-sm text-gray-500 mb-3"
                >
                    <svg
                        class="w-4 h-4 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>
                    <span>{{ career.location }}</span>
                </div>

                <p
                    v-if="career.summary"
                    class="text-sm text-gray-600 mb-4 flex-1 line-clamp-3"
                >
                    {{ career.summary }}
                </p>

                <a
                    :href="`/careers/${career.slug}`"
                    class="mt-auto inline-block text-center bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors"
                >
                    View Details
                </a>
            </div>
        </div>

        <div v-else class="text-center text-gray-400 py-12">
            <svg
                class="w-12 h-12 mx-auto mb-3 opacity-40"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                />
            </svg>
            <p>No open positions at this time.</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    content: Object,
    settings: Object,
    careers: {
        type: Array,
        default: () => [],
    },
});
</script>
