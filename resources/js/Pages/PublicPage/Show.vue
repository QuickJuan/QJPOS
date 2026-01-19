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

        <!-- JSON-LD Structured Data -->
        <component
            v-if="seo?.schema_json"
            :is="'script'"
            type="application/ld+json"
            v-html="
                typeof seo.schema_json === 'string'
                    ? seo.schema_json
                    : JSON.stringify(seo.schema_json)
            "
        ></component>
    </Head>

    <PublicPageLayout
        :navigation="navigation"
        :appName="appName"
        :companyLogo="companyLogo"
    >
        <div class="min-h-screen bg-gray-50">
            <!-- Page Header - Only show if title exists and hide_title is false -->
            <div
                v-if="page.title && !page.hide_title && page.featured_image"
                class="relative h-64 bg-gray-900"
            >
                <img
                    :src="page.featured_image"
                    :alt="page.title"
                    class="w-full h-full object-cover opacity-70"
                />
                <div class="absolute inset-0 flex items-center justify-center">
                    <h1 class="text-4xl font-bold text-white">
                        {{ page.title }}
                    </h1>
                </div>
            </div>
            <div
                v-else-if="page.title && !page.hide_title"
                class="bg-white border-b"
            >
                <div class="max-w-7xl mx-auto py-8 px-4">
                    <h1 class="text-4xl font-bold text-gray-900">
                        {{ page.title }}
                    </h1>
                </div>
            </div>

            <!-- Page Blocks -->
            <div class="max-w-7xl mx-auto py-8">
                <component
                    v-for="block in page.blocks"
                    :key="block.id"
                    :is="getBlockComponent(block.type)"
                    :content="block.content"
                    :settings="block.settings"
                />
            </div>
        </div>
    </PublicPageLayout>
</template>

<script setup>
import { defineProps, defineAsyncComponent } from "vue";
import { Head } from "@inertiajs/vue3";
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
const NewsletterBlock = defineAsyncComponent(
    () => import("./Blocks/NewsletterBlock.vue"),
);
const ContactFormBlock = defineAsyncComponent(
    () => import("./Blocks/ContactFormBlock.vue"),
);

const props = defineProps({
    page: Object,
    seo: Object,
    navigation: Array,
    appName: String,
    companyLogo: String,
});

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
    newsletter: NewsletterBlock,
    "contact-form": ContactFormBlock,
};

const getBlockComponent = (type) => {
    return blockComponents[type] || TextBlock;
};
</script>
