<template>
    <PageBuilderLayout>
        <div class="flex h-screen flex-col bg-gray-50">
            <!-- Header -->
            <div class="border-b border-gray-200 bg-white px-6 py-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ pageBuilderStore.currentPage?.title }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ pageBuilderStore.currentPage?.slug }}
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            v-if="pageBuilderStore.hasUnsavedChanges"
                            @click="undo"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                        >
                            <ArrowUturnLeftIcon class="h-4 w-4" />
                            Undo
                        </button>
                        <button
                            v-if="pageBuilderStore.hasUnsavedChanges"
                            @click="redo"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                        >
                            <ArrowUturnRightIcon class="h-4 w-4" />
                            Redo
                        </button>
                        <div
                            v-if="pageBuilderStore.hasUnsavedChanges"
                            class="flex items-center gap-2 text-sm text-orange-600"
                        >
                            <span
                                class="inline-block h-2 w-2 rounded-full bg-orange-600"
                            />
                            Unsaved changes
                        </div>
                        <Link
                            :href="route('tenant.page-builder.index')"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                        >
                            Back
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 bg-white">
                <div class="flex px-6">
                    <button
                        @click="activeTab = 'editor'"
                        :class="[
                            'border-b-2 px-4 py-3 text-sm font-medium',
                            activeTab === 'editor'
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-gray-600 hover:text-gray-900',
                        ]"
                    >
                        Editor
                    </button>
                    <button
                        @click="activeTab = 'seo'"
                        :class="[
                            'border-b-2 px-4 py-3 text-sm font-medium',
                            activeTab === 'seo'
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-gray-600 hover:text-gray-900',
                        ]"
                    >
                        SEO
                    </button>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex flex-1 overflow-hidden">
                <!-- Editor Tab -->
                <div
                    v-show="activeTab === 'editor'"
                    class="flex-1 overflow-auto"
                >
                    <PageBuilderEditor
                        @block-added="saveBlocks"
                        @block-deleted="saveBlocks"
                        @block-reordered="saveBlocks"
                    />
                </div>

                <!-- SEO Tab -->
                <div
                    v-show="activeTab === 'seo'"
                    class="w-full max-w-2xl overflow-auto border-l border-gray-200 bg-white"
                >
                    <SEOPanel :page-id="pageId" @seo-updated="saveSEO" />
                </div>
            </div>
        </div>
    </PageBuilderLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { usePageBuilderStore } from "@/stores/pageBuilderStore";
import PageBuilderLayout from "@/Layouts/PageBuilderLayout.vue";
import PageBuilderEditor from "@/Components/PageBuilder/PageBuilderEditor.vue";
import SEOPanel from "@/Components/PageBuilder/SEOPanel.vue";
import {
    ArrowUturnLeftIcon,
    ArrowUturnRightIcon,
} from "@heroicons/vue/20/solid";
import { useToast } from "@/Services/ToastService";

const pageId = ref(route().params.id);
const activeTab = ref("editor");
const pageBuilderStore = usePageBuilderStore();
const { showToast } = useToast();

onMounted(async () => {
    await pageBuilderStore.loadPage(pageId.value);
});

async function saveBlocks() {
    try {
        await Promise.all(
            pageBuilderStore.blocksSorted.map((block) =>
                pageBuilderStore.updateBlock(pageId.value, block.id, {
                    content: block.content,
                    settings: block.settings,
                }),
            ),
        );
        showToast("Blocks saved", "success");
    } catch (error) {
        showToast("Failed to save blocks", "error");
    }
}

async function saveSEO(seoData: any) {
    try {
        await pageBuilderStore.savePageSEO(pageId.value, seoData);
        showToast("SEO data saved", "success");
    } catch (error) {
        showToast("Failed to save SEO", "error");
    }
}

function undo() {
    pageBuilderStore.undo();
}

function redo() {
    pageBuilderStore.redo();
}
</script>
