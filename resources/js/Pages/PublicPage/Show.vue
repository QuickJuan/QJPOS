<template>
    <Head :title="seo?.meta_title || appName">
        <!-- Canonical URL -->
        <link rel="canonical" :href="seo?.canonical_url || $page.url" />

        <!-- Meta Robots -->
        <meta
            v-if="seo?.meta_robots"
            name="robots"
            :content="seo.meta_robots"
        />

        <!-- Basic SEO -->
        <meta
            v-if="seo?.meta_description"
            name="description"
            :content="seo.meta_description"
        />
        <meta
            v-if="seo?.meta_keywords"
            name="keywords"
            :content="seo.meta_keywords"
        />

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="$page.url" />
        <meta
            property="og:title"
            :content="seo?.og_title || seo?.meta_title || appName"
        />
        <meta
            v-if="seo?.og_description || seo?.meta_description"
            property="og:description"
            :content="seo?.og_description || seo?.meta_description"
        />
        <meta
            v-if="seo?.og_image || page.featured_image"
            property="og:image"
            :content="seo?.og_image || page.featured_image"
        />

        <!-- Twitter Card -->
        <meta
            property="twitter:card"
            :content="seo?.twitter_card || 'summary_large_image'"
        />
        <meta
            property="twitter:title"
            :content="
                seo?.twitter_title ||
                seo?.og_title ||
                seo?.meta_title ||
                appName
            "
        />
        <meta
            v-if="
                seo?.twitter_description ||
                seo?.og_description ||
                seo?.meta_description
            "
            property="twitter:description"
            :content="
                seo?.twitter_description ||
                seo?.og_description ||
                seo?.meta_description
            "
        />
        <meta
            v-if="seo?.twitter_image || seo?.og_image || page.featured_image"
            property="twitter:image"
            :content="
                seo?.twitter_image || seo?.og_image || page.featured_image
            "
        />

        <!-- JSON-LD Structured Data injected via onMounted -->
    </Head>

    <PublicPageLayout
        :navigation="navigation"
        :appName="appName"
        :companyLogo="companyLogo"
    >
        <div
            class="min-h-screen"
            :class="page.is_landing_page ? 'bg-transparent' : 'bg-gray-50'"
        >
            <!-- Visually-hidden H1 for SEO when hide_title is enabled -->
            <h1 v-if="!page.is_landing_page && page.hide_title" class="sr-only">
                {{ seo?.meta_title || page.title }}
            </h1>

            <!-- Page Header - Only show if title exists and hide_title is false -->
            <div
                v-if="
                    !page.is_landing_page &&
                    page.title &&
                    !page.hide_title &&
                    featuredImageUrl
                "
                class="relative h-64 bg-gray-900"
            >
                <img
                    :src="featuredImageUrl"
                    :alt="page.title"
                    class="w-full h-full object-cover opacity-70"
                />
                <div
                    class="absolute inset-0 flex items-center"
                    :class="titleJustifyClass"
                >
                    <h1
                        class="text-4xl font-bold text-white"
                        :class="titleAlignClass"
                    >
                        {{ page.title }}
                    </h1>
                </div>
            </div>
            <div
                v-else-if="
                    !page.is_landing_page && page.title && !page.hide_title
                "
                class="bg-white border-b"
            >
                <div class="max-w-7xl mx-auto py-8 px-4">
                    <h1
                        class="text-4xl font-bold text-gray-900"
                        :class="titleAlignClass"
                    >
                        {{ page.title }}
                    </h1>
                </div>
            </div>

            <!-- Page Blocks -->
            <div :class="page.is_landing_page ? '' : 'max-w-7xl mx-auto py-8'">
                <component
                    v-for="block in page.blocks"
                    :key="block.id"
                    :is="getBlockComponent(block.type)"
                    :content="block.content"
                    :settings="block.settings"
                    :products="block.products ?? null"
                    :careers="block.careers ?? []"
                    :articles="block.articles ?? []"
                />
            </div>
        </div>
    </PublicPageLayout>
</template>

<script setup>
import { defineProps, defineAsyncComponent, onMounted, onUnmounted } from "vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import PublicPageLayout from "@/Layouts/PublicPageLayout.vue";

// Import block components
const BannerBlock = defineAsyncComponent(
    () => import("./Blocks/BannerBlock.vue"),
);
const TextBlock = defineAsyncComponent(() => import("./Blocks/TextBlock.vue"));
const GalleryBlock = defineAsyncComponent(
    () => import("./Blocks/GalleryBlock.vue"),
);
const ProductsBlock = defineAsyncComponent(
    () => import("./Blocks/ProductsBlock.vue"),
);
const ReviewsBlock = defineAsyncComponent(
    () => import("./Blocks/ReviewsBlock.vue"),
);
const TestimonialBlock = defineAsyncComponent(
    () => import("./Blocks/TestimonialBlock.vue"),
);
const CtaBlock = defineAsyncComponent(() => import("./Blocks/CtaBlock.vue"));
const FaqBlock = defineAsyncComponent(() => import("./Blocks/FaqBlock.vue"));
const StatsBlock = defineAsyncComponent(
    () => import("./Blocks/StatsBlock.vue"),
);
const FeaturesBlock = defineAsyncComponent(
    () => import("./Blocks/FeaturesBlock.vue"),
);
const NewsletterBlock = defineAsyncComponent(
    () => import("./Blocks/NewsletterBlock.vue"),
);
const ContactFormBlock = defineAsyncComponent(
    () => import("./Blocks/ContactFormBlock.vue"),
);
const ProductListBlock = defineAsyncComponent(
    () => import("./Blocks/ProductListBlock.vue"),
);
const CareersBlock = defineAsyncComponent(
    () => import("./Blocks/CareersBlock.vue"),
);
const BlogBlock = defineAsyncComponent(() => import("./Blocks/BlogBlock.vue"));

const props = defineProps({
    page: Object,
    seo: Object,
    navigation: Array,
    appName: String,
    companyLogo: String,
});

// Inject JSON-LD directly into document.head via DOM API.
// Cannot use v-html inside Inertia's <Head> because the Head component calls
// setAttribute() for all VNode props, leaving the <script> element empty.
let jsonLdScript = null;
onMounted(() => {
    if (props.seo?.schema_json) {
        jsonLdScript = document.createElement("script");
        jsonLdScript.type = "application/ld+json";
        jsonLdScript.textContent =
            typeof props.seo.schema_json === "string"
                ? props.seo.schema_json
                : JSON.stringify(props.seo.schema_json);
        document.head.appendChild(jsonLdScript);
    }
});
onUnmounted(() => {
    jsonLdScript?.remove();
    jsonLdScript = null;
});

const resolveAsset = (path) => {
    if (!path) return null;
    if (path.startsWith("http://") || path.startsWith("https://")) return path;
    if (path.startsWith("/")) return path;
    return `/storage/${path}`;
};

const featuredImageUrl = computed(() =>
    resolveAsset(props.page?.featured_image),
);

const titleAlignment = computed(
    () => props.page?.content_json?.title_alignment || "center",
);

const titleAlignClass = computed(
    () =>
        ({
            left: "text-left",
            center: "text-center",
            right: "text-right",
        })[titleAlignment.value] || "text-center",
);

const titleJustifyClass = computed(
    () =>
        ({
            left: "justify-start",
            center: "justify-center",
            right: "justify-end",
        })[titleAlignment.value] || "justify-center",
);

const blockComponents = {
    banner: BannerBlock,
    text: TextBlock,
    gallery: GalleryBlock,
    products: ProductsBlock,
    reviews: ReviewsBlock,
    testimonial: TestimonialBlock,
    cta: CtaBlock,
    faq: FaqBlock,
    stats: StatsBlock,
    features: FeaturesBlock,
    newsletter: NewsletterBlock,
    "contact-form": ContactFormBlock,
    contact_form: ContactFormBlock,
    contactform: ContactFormBlock,
    contact: ContactFormBlock,
    "product-list": ProductListBlock,
    product_list: ProductListBlock,
    productlist: ProductListBlock,
    careers: CareersBlock,
    articles: BlogBlock,
};

const getBlockComponent = (type) => {
    return blockComponents[type] || TextBlock;
};
</script>
