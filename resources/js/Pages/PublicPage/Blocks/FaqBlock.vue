<template>
    <section class="px-6 py-16">
        <div class="mx-auto max-w-7xl space-y-8">
            <h2
                v-if="content.title"
                class="text-3xl font-black md:text-4xl text-gray-900"
            >
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
                        class="p-4 pt-0 text-gray-600 prose max-w-none"
                        v-html="item.answer"
                    />
                </div>
            </div>
        </div>
    </section>
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
