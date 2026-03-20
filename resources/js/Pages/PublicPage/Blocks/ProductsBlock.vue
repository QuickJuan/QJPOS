<template>
    <section class="px-6 py-16 bg-slate-950">
        <div class="mx-auto max-w-6xl space-y-8">
            <!-- Header -->
            <div v-if="content.title || content.description" class="space-y-2">
                <p
                    v-if="content.description"
                    class="text-xs uppercase tracking-[0.4em] text-primary-300"
                >
                    {{ content.description }}
                </p>
                <h2
                    v-if="content.title"
                    class="text-3xl font-black text-white md:text-4xl"
                >
                    {{ content.title }}
                </h2>
            </div>

            <!-- Empty state -->
            <p
                v-if="displayProducts.length === 0"
                class="text-slate-400 text-sm"
            >
                No products to display.
            </p>

            <!-- Product grid -->
            <div
                v-else
                :class="[
                    'gap-6',
                    layout === 'list'
                        ? 'flex flex-col'
                        : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
                ]"
            >
                <div
                    v-for="product in displayProducts"
                    :key="product.id ?? product.name"
                    class="group relative flex flex-col overflow-hidden rounded-2xl bg-slate-900 border border-white/5 hover:border-white/15 transition-all duration-300"
                >
                    <!-- Image -->
                    <div class="relative h-48 overflow-hidden bg-slate-800">
                        <img
                            v-if="resolveImage(product)"
                            :src="resolveImage(product)"
                            :alt="product.name"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        />
                        <div
                            v-else
                            class="flex h-full items-center justify-center text-slate-600"
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
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <span
                            v-if="product.category"
                            class="absolute top-3 left-3 rounded-full bg-black/60 px-2 py-0.5 text-[10px] uppercase tracking-[0.3em] text-white/70 backdrop-blur"
                            >{{ product.category }}</span
                        >
                    </div>

                    <!-- Info -->
                    <div class="flex flex-1 flex-col gap-2 p-5">
                        <h3
                            class="text-sm font-black uppercase tracking-[0.15em] text-white"
                        >
                            {{ product.name }}
                        </h3>
                        <p
                            v-if="product.description"
                            class="line-clamp-2 text-xs text-slate-400"
                        >
                            {{ product.description }}
                        </p>
                        <div
                            class="mt-auto flex items-center justify-between pt-3"
                        >
                            <span class="text-lg font-bold text-primary-400">
                                ₱{{ formatPrice(product.price) }}
                            </span>
                            <a
                                v-if="product.button_url"
                                :href="product.button_url"
                                class="rounded-full border border-white/20 bg-white/5 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-white/80 transition hover:border-white/40 hover:bg-white/10"
                                >{{ product.button_text || "View" }}</a
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    content: { type: Object, default: () => ({}) },
    settings: { type: Object, default: () => ({}) },
    // Injected by PublicPageController for DB-driven blocks
    products: { type: Array, default: null },
});

// DB-injected products take priority; fall back to content.products (legacy/static)
const displayProducts = computed(() =>
    props.products !== null ? props.products : (props.content?.products ?? []),
);

const layout = computed(() => props.content?.layout ?? "grid");

const resolveImage = (product) => {
    const src = product.image_url ?? product.image ?? null;
    if (!src) return null;
    if (
        src.startsWith("http://") ||
        src.startsWith("https://") ||
        src.startsWith("/")
    )
        return src;
    return `/storage/${src}`;
};

const formatPrice = (price) => {
    const num = parseFloat(price ?? 0);
    return isNaN(num) ? "0.00" : num.toFixed(2);
};
</script>
