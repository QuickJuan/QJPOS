<template>
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <h2 v-if="content.title" class="text-3xl font-bold text-gray-900 mb-6">
            {{ content.title }}
        </h2>
        <div class="space-y-4">
            <div
                v-for="(item, index) in content.items"
                :key="index"
                class="border rounded-lg"
            >
                <button
                    @click="toggleItem(index)"
                    class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50"
                >
                    <span class="font-semibold text-gray-900">{{
                        item.question
                    }}</span>
                    <span class="text-gray-500">{{
                        openItems.includes(index) ? "−" : "+"
                    }}</span>
                </button>
                <div
                    v-show="openItems.includes(index)"
                    class="p-4 pt-0 text-gray-600"
                >
                    {{ item.answer }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

defineProps({
    content: Object,
    settings: Object,
});

const openItems = ref([]);

const toggleItem = (index) => {
    const idx = openItems.value.indexOf(index);
    if (idx > -1) {
        openItems.value.splice(idx, 1);
    } else {
        openItems.value.push(index);
    }
};
</script>
