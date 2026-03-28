<template>
    <section class="px-6 py-16">
        <div class="mx-auto max-w-7xl space-y-8">
            <!-- Section header -->
            <div v-if="content?.title || content?.subtitle" class="space-y-2">
                <h2
                    v-if="content?.title"
                    class="text-3xl font-black md:text-4xl text-gray-900"
                >
                    {{ content.title }}
                </h2>
                <p
                    v-if="content?.subtitle"
                    class="text-sm text-gray-500 max-w-2xl leading-relaxed"
                >
                    {{ content.subtitle }}
                </p>
            </div>

            <!-- Top-level accordion items -->
            <div class="space-y-2">
                <AccordionItem
                    v-for="(item, idx) in content?.items ?? []"
                    :key="idx"
                    :item="item"
                    :icon-style="settings?.icon_style ?? 'chevron'"
                    :default-open="settings?.open_first && idx === 0"
                />
            </div>
        </div>
    </section>
</template>

<script setup>
import AccordionItem from "./AccordionItem.vue";

defineProps({
    content: Object,
    settings: Object,
});
</script>
