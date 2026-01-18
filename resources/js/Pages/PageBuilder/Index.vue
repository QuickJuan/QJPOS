<template>
    <PageBuilderLayout>
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Pages</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Create and manage your static pages
                    </p>
                </div>
                <Link
                    :href="route('tenant.page-builder.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    <PlusIcon class="h-5 w-5" />
                    Create Page
                </Link>
            </div>

            <!-- Pages Table -->
            <div class="rounded-lg border border-gray-200 bg-white shadow">
                <div v-if="pages.data.length === 0" class="p-12 text-center">
                    <DocumentIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        No pages
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Get started by creating a new page.
                    </p>
                    <Link
                        :href="route('tenant.page-builder.create')"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        <PlusIcon class="h-5 w-5" />
                        Create Page
                    </Link>
                </div>

                <table v-else class="w-full">
                    <thead class="border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                            >
                                Title
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                            >
                                Slug
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                            >
                                Published
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                            >
                                Views
                            </th>
                            <th
                                class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr
                            v-for="page in pages.data"
                            :key="page.id"
                            class="hover:bg-gray-50"
                        >
                            <td
                                class="px-6 py-4 text-sm font-medium text-gray-900"
                            >
                                {{ page.title }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ page.slug }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span
                                    :class="
                                        page.status === 'published'
                                            ? 'bg-green-100 text-green-800'
                                            : page.status === 'draft'
                                              ? 'bg-yellow-100 text-yellow-800'
                                              : 'bg-gray-100 text-gray-800'
                                    "
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold capitalize"
                                >
                                    {{ page.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{
                                    page.published_at
                                        ? new Date(
                                              page.published_at,
                                          ).toLocaleDateString()
                                        : "-"
                                }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ page.view_count }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'tenant.page-builder.edit',
                                                page.id,
                                            )
                                        "
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        Edit
                                    </Link>
                                    <a
                                        v-if="page.status === 'published'"
                                        :href="route('pages.show', page.slug)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-green-600 hover:text-green-900"
                                    >
                                        View
                                    </a>
                                    <button
                                        @click="deletePage(page.id)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="pages.links.length > 3"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-gray-600">
                    Showing {{ pages.from }} to {{ pages.to }} of
                    {{ pages.total }} pages
                </p>
                <div class="flex gap-1">
                    <Link
                        v-for="link in pages.links"
                        :key="link.url"
                        :href="link.url"
                        :class="
                            link.active
                                ? 'bg-blue-600 text-white'
                                : link.url
                                  ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                  : 'bg-gray-50 text-gray-400'
                        "
                        class="px-3 py-2 text-sm rounded"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </PageBuilderLayout>
</template>

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import PageBuilderLayout from "@/Layouts/PageBuilderLayout.vue";
import { PlusIcon, DocumentIcon } from "@heroicons/vue/24/outline";

interface Props {
    pages: any;
}

defineProps<Props>();

function deletePage(pageId: number) {
    if (confirm("Are you sure you want to delete this page?")) {
        // TODO: Implement delete functionality
    }
}
</script>
