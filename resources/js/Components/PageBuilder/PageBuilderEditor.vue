<template>
    <div class="p-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Page Blocks</h2>
            <p class="mt-1 text-sm text-gray-500">
                Add and arrange blocks to build your page content.
            </p>
        </div>

        <!-- Add Block Button -->
        <div class="mb-6">
            <button
                @click="showAddBlockModal = true"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                <PlusIcon class="h-5 w-5" />
                Add Block
            </button>
        </div>

        <!-- Blocks List -->
        <div v-if="blocks.length > 0" class="space-y-4">
            <div
                v-for="block in blocks"
                :key="block.id"
                class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="font-medium text-gray-900">
                            {{ block.type }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Order: {{ block.order }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="editBlock(block)"
                            class="text-blue-600 hover:text-blue-700"
                        >
                            Edit
                        </button>
                        <button
                            @click="deleteBlock(block.id)"
                            class="text-red-600 hover:text-red-700"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else
            class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center"
        >
            <p class="text-gray-500">
                No blocks yet. Click "Add Block" to get started.
            </p>
        </div>

        <!-- Add Block Modal (Placeholder) -->
        <div
            v-if="showAddBlockModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold">Add Block</h3>
                <p class="mb-4 text-sm text-gray-600">
                    Block creation functionality coming soon.
                </p>
                <div class="flex justify-end">
                    <button
                        @click="showAddBlockModal = false"
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { usePageBuilderStore } from "@/stores/pageBuilderStore";
import { PlusIcon } from "@heroicons/vue/20/solid";

const emit = defineEmits(["block-added", "block-deleted", "block-reordered"]);

const pageBuilderStore = usePageBuilderStore();
const showAddBlockModal = ref(false);

const blocks = computed(() => pageBuilderStore.blocksSorted || []);

function editBlock(block: any) {
    // TODO: Implement edit functionality
    console.log("Edit block:", block);
}

function deleteBlock(blockId: string) {
    // TODO: Implement delete functionality
    emit("block-deleted", blockId);
}
</script>
