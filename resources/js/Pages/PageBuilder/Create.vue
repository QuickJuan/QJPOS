<template>
    <PageBuilderLayout>
        <div class="max-w-2xl space-y-6 p-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Create New Page
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Start by giving your page a title
                </p>
            </div>

            <form
                @submit.prevent="createPage"
                class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow"
            >
                <!-- Title Field -->
                <div>
                    <label
                        for="title"
                        class="block text-sm font-medium text-gray-900"
                        >Page Title</label
                    >
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        placeholder="e.g., About Us, Contact, Services"
                        class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-2 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <p v-if="errors.title" class="mt-1 text-sm text-red-600">
                        {{ errors.title }}
                    </p>
                </div>

                <!-- Slug Field -->
                <div>
                    <label
                        for="slug"
                        class="block text-sm font-medium text-gray-900"
                        >Page Slug</label
                    >
                    <div class="mt-2 flex gap-2">
                        <input
                            id="slug"
                            v-model="form.slug"
                            type="text"
                            placeholder="auto-generated-from-title"
                            class="block flex-1 rounded-lg border border-gray-300 px-4 py-2 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                        <button
                            type="button"
                            @click="generateSlug"
                            class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                        >
                            Generate
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        URL: /pages/{{ form.slug || "slug" }}
                    </p>
                    <p v-if="errors.slug" class="mt-1 text-sm text-red-600">
                        {{ errors.slug }}
                    </p>
                </div>

                <!-- Description Field -->
                <div>
                    <label
                        for="description"
                        class="block text-sm font-medium text-gray-900"
                        >Description</label
                    >
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        placeholder="Brief description of the page (optional)"
                        class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-2 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <p
                        v-if="errors.description"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.description }}
                    </p>
                </div>

                <!-- Featured Image Field -->
                <div>
                    <label
                        for="featured_image"
                        class="block text-sm font-medium text-gray-900"
                        >Featured Image URL</label
                    >
                    <input
                        id="featured_image"
                        v-model="form.featured_image"
                        type="url"
                        placeholder="https://example.com/image.jpg"
                        class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-2 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <p
                        v-if="errors.featured_image"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.featured_image }}
                    </p>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 pt-4">
                    <button
                        type="submit"
                        :disabled="processing"
                        class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ processing ? "Creating..." : "Create Page" }}
                    </button>
                    <Link
                        :href="route('tenant.page-builder.index')"
                        class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-center text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </PageBuilderLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Link, router } from "@inertiajs/vue3";
import PageBuilderLayout from "@/Layouts/PageBuilderLayout.vue";

const form = useForm({
    title: "",
    slug: "",
    description: "",
    featured_image: "",
});

const processing = ref(false);
const errors = reactive<Record<string, string>>({});

function generateSlug() {
    if (!form.title) return;
    form.slug = form.title
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, "")
        .replace(/[\s_]+/g, "-")
        .replace(/^-+|-+$/g, "");
}

function createPage() {
    processing.value = true;
    form.post(route("tenant.page-builder.store"), {
        onError: (pageErrors) => {
            Object.assign(errors, pageErrors);
            processing.value = false;
        },
    });
}
</script>
