<template>
    <div class="p-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900">SEO Settings</h2>
            <p class="mt-1 text-sm text-gray-500">
                Optimize your page for search engines.
            </p>
        </div>

        <form @submit.prevent="saveSEO" class="space-y-6">
            <!-- Meta Title -->
            <div>
                <label
                    for="meta_title"
                    class="block text-sm font-medium text-gray-700"
                >
                    Meta Title
                </label>
                <input
                    id="meta_title"
                    v-model="form.meta_title"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Enter meta title"
                />
                <p class="mt-1 text-xs text-gray-500">
                    Recommended: 50-60 characters
                </p>
            </div>

            <!-- Meta Description -->
            <div>
                <label
                    for="meta_description"
                    class="block text-sm font-medium text-gray-700"
                >
                    Meta Description
                </label>
                <textarea
                    id="meta_description"
                    v-model="form.meta_description"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Enter meta description"
                />
                <p class="mt-1 text-xs text-gray-500">
                    Recommended: 150-160 characters
                </p>
            </div>

            <!-- Meta Keywords -->
            <div>
                <label
                    for="meta_keywords"
                    class="block text-sm font-medium text-gray-700"
                >
                    Meta Keywords
                </label>
                <input
                    id="meta_keywords"
                    v-model="form.meta_keywords"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="keyword1, keyword2, keyword3"
                />
                <p class="mt-1 text-xs text-gray-500">
                    Separate keywords with commas
                </p>
            </div>

            <!-- OG Image -->
            <div>
                <label
                    for="og_image"
                    class="block text-sm font-medium text-gray-700"
                >
                    OG Image URL
                </label>
                <input
                    id="og_image"
                    v-model="form.og_image"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="https://example.com/image.jpg"
                />
                <p class="mt-1 text-xs text-gray-500">
                    Image for social media sharing
                </p>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    Save SEO Settings
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { router } from "@inertiajs/vue3";

interface Props {
    pageId: string | number;
}

const props = defineProps<Props>();
const emit = defineEmits(["seo-updated"]);

const form = ref({
    meta_title: "",
    meta_description: "",
    meta_keywords: "",
    og_image: "",
});

onMounted(() => {
    // TODO: Load existing SEO data
    loadSEOData();
});

function loadSEOData() {
    // TODO: Fetch SEO data from the API
    // For now, this is a placeholder
}

function saveSEO() {
    emit("seo-updated", form.value);
}
</script>
