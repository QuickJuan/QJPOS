<template>
    <div
        :class="[
            'rounded-lg border transition-all duration-200',
            nested
                ? 'border-gray-200 bg-gray-50'
                : 'border-gray-200 bg-white shadow-sm',
            open
                ? nested
                    ? 'border-gray-300'
                    : 'border-gray-300 shadow-md'
                : '',
        ]"
    >
        <!-- Header button -->
        <button
            type="button"
            @click="open = !open"
            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-lg"
        >
            <span
                :class="[
                    'font-semibold text-gray-900',
                    nested ? 'text-sm' : 'text-base',
                ]"
            >
                {{ item?.title }}
            </span>

            <!-- Chevron icon -->
            <svg
                v-if="iconStyle === 'chevron'"
                class="flex-shrink-0 w-5 h-5 text-gray-400 transition-transform duration-200"
                :class="open ? 'rotate-180' : ''"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19 9l-7 7-7-7"
                />
            </svg>

            <!-- Plus / minus icon -->
            <span
                v-else
                class="flex-shrink-0 flex items-center justify-center w-6 h-6 text-gray-400 text-xl leading-none select-none font-light"
            >
                {{ open ? "−" : "+" }}
            </span>
        </button>

        <!-- Body -->
        <div v-show="open" class="px-5 pb-5 space-y-4">
            <!-- Content blocks -->
            <template
                v-for="(block, bi) in item?.content_items ?? []"
                :key="bi"
            >
                <!-- Rich text -->
                <div
                    v-if="block.type === 'text' || !block.type"
                    class="prose prose-sm max-w-none text-gray-700"
                    v-html="block.text"
                />

                <!-- Image -->
                <figure
                    v-else-if="block.type === 'image' && block.image"
                    class="space-y-2"
                >
                    <img
                        :src="resolveImage(block.image)"
                        :alt="block.image_alt || ''"
                        class="rounded-lg w-full object-cover max-h-96"
                    />
                    <figcaption
                        v-if="block.image_caption"
                        class="text-xs text-center text-gray-500 italic"
                    >
                        {{ block.image_caption }}
                    </figcaption>
                </figure>
            </template>

            <!-- Child accordions (recursive) -->
            <div
                v-if="item?.children?.length"
                class="space-y-2 mt-4 pl-3 border-l-2 border-indigo-100"
            >
                <AccordionItem
                    v-for="(child, ci) in item.children"
                    :key="ci"
                    :item="child"
                    :icon-style="iconStyle"
                    :default-open="false"
                    :nested="true"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

const props = defineProps({
    item: Object,
    iconStyle: { type: String, default: "chevron" },
    defaultOpen: { type: Boolean, default: false },
    nested: { type: Boolean, default: false },
});

const open = ref(props.defaultOpen);

function resolveImage(path) {
    if (!path) return null;
    if (path.startsWith("http") || path.startsWith("/")) return path;
    return `/storage/${path}`;
}
</script>
