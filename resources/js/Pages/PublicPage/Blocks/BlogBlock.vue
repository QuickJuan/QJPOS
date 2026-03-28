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
            v-if="articles && articles.length"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            <article
                v-for="article in articles"
                :key="article.id"
                class="border border-gray-200 rounded-xl overflow-hidden flex flex-col hover:shadow-md transition-shadow"
            >
                <a
                    :href="`/blogs/${article.slug}`"
                    class="block aspect-[16/9] bg-gray-100 overflow-hidden"
                >
                    <img
                        v-if="article.featured_image"
                        :src="article.featured_image"
                        :alt="article.title"
                        class="w-full h-full object-cover transition-transform hover:scale-105"
                    />
                    <div
                        v-else
                        class="w-full h-full flex items-center justify-center text-gray-300"
                    >
                        <svg
                            class="w-12 h-12"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
                            />
                        </svg>
                    </div>
                </a>

                <div class="p-6 flex flex-col flex-1">
                    <p
                        v-if="article.published_at"
                        class="text-xs text-gray-400 uppercase tracking-wide mb-2"
                    >
                        {{ formatDate(article.published_at) }}
                    </p>

                    <h3
                        class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2"
                    >
                        <a
                            :href="`/blogs/${article.slug}`"
                            class="hover:text-gray-600 transition-colors"
                        >
                            {{ article.title }}
                        </a>
                    </h3>

                    <p
                        v-if="article.excerpt"
                        class="text-sm text-gray-600 mb-4 flex-1 line-clamp-3"
                    >
                        {{ article.excerpt }}
                    </p>

                    <a
                        :href="`/blogs/${article.slug}`"
                        class="mt-auto inline-flex items-center gap-1 text-sm font-medium text-gray-900 hover:text-gray-600 transition-colors"
                    >
                        Read more
                        <svg
                            class="w-4 h-4"
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
                    </a>
                </div>
            </article>
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
                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
                />
            </svg>
            <p>No articles published yet.</p>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    content: Object,
    settings: Object,
    articles: {
        type: Array,
        default: () => [],
    },
});

const formatDate = (value) => {
    if (!value) return "";
    return new Date(value).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>
