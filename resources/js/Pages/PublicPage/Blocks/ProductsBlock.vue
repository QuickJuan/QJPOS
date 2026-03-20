<template>
    <section class="px-6 py-16" :style="{ backgroundColor: bgColor }">
        <div class="mx-auto max-w-6xl space-y-8">
            <!-- Header -->
            <div v-if="content.title || content.description" class="space-y-2">
                <p
                    v-if="content.description"
                    class="text-xs uppercase tracking-[0.4em] text-primary-300"
                    :style="{ color: fontColor }"
                >
                    {{ content.description }}
                </p>
                <h2
                    v-if="content.title"
                    class="text-3xl font-black md:text-4xl"
                    :style="{ color: fontColor }"
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

            <!-- GRID layout -->
            <div
                v-else-if="layout === 'grid'"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
            >
                <ProductCard
                    v-for="product in displayProducts"
                    :key="product.id ?? product.name"
                    :product="product"
                />
            </div>

            <!-- CAROUSEL layout -->
            <div v-else-if="layout === 'carousel'" class="relative">
                <!-- Slides track -->
                <div class="overflow-hidden rounded-2xl">
                    <div
                        class="flex transition-transform duration-500 ease-in-out"
                        :style="{
                            transform: `translateX(-${carouselIndex * 100}%)`,
                        }"
                    >
                        <div
                            v-for="product in displayProducts"
                            :key="product.id ?? product.name"
                            class="min-w-full"
                        >
                            <!-- Full-bleed card -->
                            <div
                                class="relative flex flex-col md:flex-row overflow-hidden rounded-2xl bg-slate-900 border border-white/5 min-h-[360px]"
                            >
                                <!-- Image side -->
                                <div
                                    class="relative md:w-1/2 h-64 md:h-auto overflow-hidden bg-slate-800 flex-shrink-0"
                                >
                                    <img
                                        v-if="resolveImage(product)"
                                        :src="resolveImage(product)"
                                        :alt="product.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full items-center justify-center text-slate-600"
                                    >
                                        <svg
                                            class="w-16 h-16"
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
                                        class="absolute top-4 left-4 rounded-full bg-black/60 px-3 py-1 text-[10px] uppercase tracking-[0.3em] text-white/70 backdrop-blur"
                                        >{{ product.category }}</span
                                    >
                                </div>
                                <!-- Content side -->
                                <div
                                    class="flex flex-1 flex-col justify-center gap-4 p-8"
                                >
                                    <h3
                                        class="text-2xl font-black uppercase tracking-[0.15em] text-white"
                                    >
                                        {{ product.name }}
                                    </h3>
                                    <div
                                        v-if="product.description"
                                        class="text-sm text-slate-400 leading-relaxed line-clamp-4 prose-invert"
                                        v-html="product.description"
                                    />
                                    <span
                                        class="text-2xl font-bold text-primary-400"
                                    >
                                        ₱{{ formatPrice(product.price) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prev / Next arrows -->
                <button
                    v-if="displayProducts.length > 1"
                    @click="prevSlide"
                    class="absolute left-3 top-1/2 -translate-y-1/2 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-black/50 text-white backdrop-blur transition hover:bg-black/80"
                    aria-label="Previous"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </button>
                <button
                    v-if="displayProducts.length > 1"
                    @click="nextSlide"
                    class="absolute right-3 top-1/2 -translate-y-1/2 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-black/50 text-white backdrop-blur transition hover:bg-black/80"
                    aria-label="Next"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </button>

                <!-- Dots -->
                <div
                    v-if="displayProducts.length > 1"
                    class="mt-5 flex justify-center gap-2"
                >
                    <button
                        v-for="(_, i) in displayProducts"
                        :key="i"
                        @click="carouselIndex = i"
                        :class="[
                            'h-2 rounded-full transition-all duration-300',
                            i === carouselIndex
                                ? 'w-6 bg-primary-400'
                                : 'w-2 bg-white/25',
                        ]"
                        :aria-label="`Go to slide ${i + 1}`"
                    />
                </div>
            </div>

            <!-- LIST layout — alternating image left/right -->
            <div v-else class="flex flex-col gap-12">
                <div
                    v-for="(product, index) in displayProducts"
                    :key="product.id ?? product.name"
                    :class="[
                        'flex flex-col md:flex-row overflow-hidden rounded-2xl bg-slate-900 border border-white/5 min-h-[260px]',
                        index % 2 !== 0 ? 'md:flex-row-reverse' : '',
                    ]"
                >
                    <!-- Image -->
                    <div
                        class="relative md:w-1/2 h-64 md:h-auto overflow-hidden bg-slate-800 flex-shrink-0"
                    >
                        <img
                            v-if="resolveImage(product)"
                            :src="resolveImage(product)"
                            :alt="product.name"
                            class="h-full w-full object-cover transition duration-500 hover:scale-105"
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
                    <!-- Content -->
                    <div class="flex flex-1 flex-col justify-center gap-3 p-8">
                        <h3
                            class="text-xl font-black uppercase tracking-[0.15em] text-white"
                        >
                            {{ product.name }}
                        </h3>
                        <div
                            v-if="product.description"
                            class="text-sm text-slate-400 leading-relaxed line-clamp-5"
                            v-html="product.description"
                        />
                        <span class="text-xl font-bold text-primary-400 mt-2">
                            ₱{{ formatPrice(product.price) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from "vue";

// Re-usable card for grid layout
const ProductCard = {
    props: { product: Object },
    setup(props) {
        const resolveImage = (p) => {
            const src = p.image_url ?? p.image ?? null;
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
        return { resolveImage, formatPrice };
    },
    template: `
        <div class="group relative flex flex-col overflow-hidden rounded-2xl bg-slate-900 border border-white/5 hover:border-white/15 transition-all duration-300">
            <div class="relative h-48 overflow-hidden bg-slate-800">
                <img v-if="resolveImage(product)" :src="resolveImage(product)" :alt="product.name" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                <div v-else class="flex h-full items-center justify-center text-slate-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <span v-if="product.category" class="absolute top-3 left-3 rounded-full bg-black/60 px-2 py-0.5 text-[10px] uppercase tracking-[0.3em] text-white/70 backdrop-blur">{{ product.category }}</span>
            </div>
            <div class="flex flex-1 flex-col gap-2 p-5">
                <h3 class="text-sm font-black uppercase tracking-[0.15em] text-white">{{ product.name }}</h3>
                <div v-if="product.description" class="line-clamp-2 text-xs text-slate-400" v-html="product.description" />
                <span class="mt-auto pt-3 text-lg font-bold text-primary-400">₱{{ formatPrice(product.price) }}</span>
            </div>
        </div>
    `,
};

const props = defineProps({
    content: { type: Object, default: () => ({}) },
    settings: { type: Object, default: () => ({}) },
    products: { type: Array, default: null },
});

const bgColor = computed(() => props.settings?.bg_color || "#020617");
const fontColor = computed(() => props.settings?.text_color || "#ffffff");

const displayProducts = computed(() =>
    props.products !== null ? props.products : (props.content?.products ?? []),
);

const layout = computed(() => props.content?.layout ?? "grid");

// Carousel state
const carouselIndex = ref(0);
let autoplayTimer = null;

const nextSlide = () => {
    carouselIndex.value =
        (carouselIndex.value + 1) % displayProducts.value.length;
};
const prevSlide = () => {
    carouselIndex.value =
        (carouselIndex.value - 1 + displayProducts.value.length) %
        displayProducts.value.length;
};

onMounted(() => {
    if (layout.value === "carousel") {
        autoplayTimer = setInterval(nextSlide, 5000);
    }
});
onBeforeUnmount(() => clearInterval(autoplayTimer));

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
