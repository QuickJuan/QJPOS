<template>
    <TenantLandingLayout>
        <div class="min-h-screen bg-slate-950 text-white">
            <section class="relative overflow-hidden px-6 py-20 md:py-28">
                <div
                    v-if="heroImage"
                    class="pointer-events-none absolute inset-0"
                    :style="{
                        backgroundImage: `url(${heroImage})`,
                        backgroundSize: 'cover',
                        backgroundPosition: 'center',
                    }"
                    aria-hidden="true"
                ></div>
                <div
                    class="pointer-events-none absolute inset-0 opacity-30 mix-blend-screen"
                    aria-hidden="true"
                >
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-primary-500/40 via-purple-500/30 to-rose-500/20 blur-3xl"
                    ></div>
                </div>

                <div class="relative z-10 mx-auto max-w-6xl">
                    <div
                        class="space-y-10 rounded-3xl bg-black/40 p-8 shadow-2xl shadow-black/10 backdrop-blur-xl border border-slate/30 sm:p-12"
                    >
                        <p
                            class="text-sm font-semibold uppercase tracking-[0.4em] text-primary-300"
                        >
                            Experience a better way to dine with
                            {{ brandName }}.
                        </p>
                        <div class="space-y-5">
                            <h1
                                class="text-4xl font-black leading-tight text-white md:text-6xl"
                            >
                                Enjoy a smoother, more delightful dining
                                experience,
                                <span class="text-primary-300">
                                    from your first order to your final bill.
                                </span>
                            </h1>
                            <div class="space-y-3 text-slate-200">
                                <p class="text-lg">
                                    {{ heroTeaser }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <a
                                href="#menu"
                                class="inline-flex items-center justify-center rounded-full border border-white/40 bg-white/10 px-6 py-3 text-base font-semibold text-white transition hover:border-white hover:bg-white/20"
                            >
                                Explore our menu
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section
                id="menu"
                aria-labelledby="menu-heading"
                class="px-6 py-16"
            >
                <div class="mx-auto max-w-6xl space-y-6">
                    <div class="flex flex-col gap-2">
                        <p
                            class="text-xs uppercase tracking-[0.4em] text-primary-300"
                        >
                            store menu
                        </p>
                        <h2
                            id="menu-heading"
                            class="text-3xl font-black text-white md:text-4xl"
                        >
                            Let guests preview what makes {{ brandName }} shine.
                        </h2>
                        <p class="max-w-3xl text-base text-slate-300">
                            Feature your best sellers, seasonal drops, and
                            counter exclusives with high-contrast visuals so
                            first-time visitors can decide in seconds.
                        </p>
                    </div>

                    <div
                        class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                    >
                        <div class="space-y-3">
                            <div
                                class="flex flex-wrap items-center gap-3 text-xs uppercase tracking-[0.3em] text-slate-500"
                            >
                                <span>Filter by</span>
                                <div
                                    class="inline-flex rounded-full border border-white/10 bg-slate-900/70 p-1"
                                >
                                    <button
                                        type="button"
                                        class="rounded-full px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.3em] transition"
                                        :class="
                                            filterMode === 'category'
                                                ? 'bg-white text-slate-900'
                                                : 'text-white/70 hover:text-white'
                                        "
                                        @click="setFilterMode('category')"
                                    >
                                        Categories
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-full px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.3em] transition"
                                        :class="
                                            filterMode === 'group'
                                                ? 'bg-white text-slate-900'
                                                : 'text-white/70 hover:text-white'
                                        "
                                        @click="setFilterMode('group')"
                                    >
                                        Groups
                                    </button>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    @click="setActiveFilter(null)"
                                    :class="[
                                        'rounded-full px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] transition',
                                        !activeFilterSlug
                                            ? 'border border-primary-300 bg-white text-slate-900'
                                            : 'border border-white/20 text-white hover:border-white',
                                    ]"
                                >
                                    All
                                </button>
                                <button
                                    v-for="entry in currentFilterEntries"
                                    :key="entry.slug ?? entry.id"
                                    type="button"
                                    @click="setActiveFilter(entry.slug ?? null)"
                                    :class="[
                                        'rounded-full px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] transition',
                                        activeFilterSlug === entry.slug
                                            ? 'border border-primary-300 bg-primary-500/10 text-primary-200'
                                            : 'border border-white/20 text-white hover:border-white',
                                    ]"
                                >
                                    {{ entry.name }}
                                </button>
                            </div>
                        </div>
                        <label class="w-full md:w-auto">
                            <span class="sr-only">Search menu</span>
                            <input
                                v-model="searchTerm"
                                type="search"
                                placeholder="Search by dish, drink, or note"
                                class="w-full rounded-2xl border border-white/20 bg-slate-900/50 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/50 md:w-80"
                            />
                        </label>
                    </div>

                    <p
                        class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500"
                    >
                        Showing
                        <span class="text-white">{{
                            displayProducts.length
                        }}</span>
                        {{ activeFilterLabel }}
                        <span v-if="searchTerm">
                            matching "{{ searchTerm }}"</span
                        >
                    </p>

                    <div
                        v-if="displayProducts.length === 0"
                        class="rounded-3xl border border-white/10 bg-slate-900/60 p-6 text-sm text-slate-300"
                    >
                        No menu items match that search. Add dishes to the
                        selected category so visitors see those specialties.
                    </div>

                    <div
                        v-else
                        class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <article
                            v-for="product in displayProducts"
                            :key="product.id ?? product.name"
                            class="flex flex-col rounded-3xl border border-white/5 bg-slate-900/60 p-6 shadow-xl shadow-indigo-500/10"
                        >
                            <figure
                                class="overflow-hidden rounded-2xl bg-slate-950/40"
                            >
                                <img
                                    :src="
                                        product.featured_image_url ??
                                        product.image ??
                                        sampleFallbackImage
                                    "
                                    :alt="product.name"
                                    class="h-44 w-full object-cover transition duration-300 hover:scale-105"
                                    loading="lazy"
                                />
                            </figure>
                            <div
                                class="mt-4 flex items-start justify-between gap-3"
                            >
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-white"
                                    >
                                        {{ product.name }}
                                    </h3>
                                    <p
                                        v-if="product.category"
                                        class="text-xs uppercase tracking-[0.3em] text-slate-400"
                                    >
                                        {{ product.category }}
                                    </p>
                                </div>
                                <span
                                    class="text-sm font-semibold text-primary-300"
                                >
                                    {{ formatPrice(product.price) }}
                                </span>
                            </div>
                            <p class="mt-3 text-sm text-slate-300 flex-1">
                                {{
                                    product.description ??
                                    "Description coming soon."
                                }}
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section
                id="branches"
                aria-labelledby="branches-heading"
                class="px-6 pb-20"
            >
                <div class="mx-auto max-w-6xl space-y-8">
                    <div class="space-y-3">
                        <p
                            class="text-xs uppercase tracking-[0.4em] text-primary-300"
                        >
                            locations
                        </p>
                        <h2 class="text-3xl font-black text-white md:text-4xl">
                            {{ brandName }} keeps
                            <span class="text-primary-300">{{
                                branchCountLabel
                            }}</span>
                            in sync.
                        </h2>
                        <p class="max-w-3xl text-base text-slate-300">
                            Track every branch with contact details so operators
                            and guests know exactly where the kitchen and
                            service teams are stationed.
                        </p>
                    </div>

                    <div
                        v-if="branchesList.length === 0"
                        class="rounded-3xl border border-white/10 bg-slate-900/60 p-6 text-sm text-slate-300"
                    >
                        No branches configured yet. Add locations through the
                        tenant admin so this page can highlight each storefront.
                    </div>
                    <div
                        v-else
                        class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <article
                            v-for="branch in branchesList"
                            :key="branch.id"
                            class="flex flex-col rounded-3xl border border-white/5 bg-slate-900/60 p-6 shadow-xl shadow-slate-900/40"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-3"
                            >
                                <div>
                                    <h3
                                        class="text-xl font-semibold text-white"
                                    >
                                        {{ branch.name }}
                                    </h3>
                                    <p
                                        class="text-xs uppercase tracking-[0.3em] text-slate-400"
                                    >
                                        {{
                                            branch.branch_code ??
                                            "Branch code pending"
                                        }}
                                    </p>
                                </div>
                                <span
                                    :class="[
                                        'rounded-full px-3 py-1 text-xs font-semibold tracking-[0.3em]',
                                        branch.is_active
                                            ? 'bg-emerald-500/20 text-emerald-200'
                                            : 'bg-slate-600/40 text-slate-300',
                                    ]"
                                >
                                    {{
                                        branch.is_active ? "Active" : "Inactive"
                                    }}
                                </span>
                            </div>
                            <p class="mt-4 text-sm text-slate-300">
                                {{ branch.address ?? "Address coming soon." }}
                            </p>
                            <div
                                class="mt-4 flex flex-col gap-2 text-sm text-slate-300"
                            >
                                <div v-if="branch.phone">
                                    <span class="font-semibold text-white"
                                        >Phone:</span
                                    >
                                    {{ branch.phone }}
                                </div>
                                <div v-if="branch.email">
                                    <span class="font-semibold text-white"
                                        >Email:</span
                                    >
                                    {{ branch.email }}
                                </div>
                                <div v-if="branch.contact_person">
                                    <span class="font-semibold text-white"
                                        >Contact:</span
                                    >
                                    {{ branch.contact_person }}
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </div>
    </TenantLandingLayout>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import TenantLandingLayout from "@/Layouts/TenantLandingLayout.vue";

type Product = {
    id?: number;
    name: string;
    price?: number | string;
    description?: string;
    category?: string;
    featured_image_url?: string;
    image?: string;
};

type Branch = {
    id: number;
    name: string;
    branch_code?: string;
    address?: string;
    phone?: string;
    email?: string;
    contact_person?: string;
    is_active?: boolean;
};

type MenuCollection = {
    id: number;
    name: string;
    slug?: string;
    featured_image_url?: string;
    products?: Product[];
};

type Category = MenuCollection;
type Group = MenuCollection;
type FilterMode = "category" | "group";

const props = defineProps<{
    tenant: {
        name?: string;
        brand_name?: string;
    };
    branches?: Branch[];
    categories?: Category[];
    groups?: Group[];
    selectedCategorySlug?: string | null;
    selectedGroupSlug?: string | null;
    filterMode?: FilterMode;
    searchQuery?: string;
}>();

const page = usePage();

const sampleProducts: Product[] = [
    {
        name: "Manila Sunrise Latte",
        price: 195,
        description:
            "Bright citrus-toned espresso folded with toasted coconut milk and a dash of muscovado sugar.",
        category: "Specialty coffee",
        image: "https://images.unsplash.com/photo-1466978913421-dad2ebd01d17?auto=format&fit=crop&w=900&q=80",
    },
    {
        name: "Basil Citrus Poke Bowl",
        price: 375,
        description:
            "Sustainable tuna, heirloom tomatoes, citrus ponzu, and basil oil over charcoal rice.",
        category: "Chef special",
        image: "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=900&q=80",
    },
    {
        name: "Volcano Night Ribs",
        price: 460,
        description:
            "Smoked baby back ribs glazed in lemongrass chili honey and served with charred pineapple.",
        category: "Entrée",
        image: "https://images.unsplash.com/photo-1473093226795-af9932fe5856?auto=format&fit=crop&w=900&q=80",
    },
    {
        name: "Lumina Cloud Dessert",
        price: 265,
        description:
            "Coconut panna cotta layered with passionfruit gel and a toasted macadamia crumble.",
        category: "Dessert",
        image: "https://images.unsplash.com/photo-1499636136210-6f4ee915583e?auto=format&fit=crop&w=900&q=80",
    },
];

const categoriesList = computed<Category[]>(() => props.categories ?? []);
const groupsList = computed<Group[]>(() => props.groups ?? []);
const filterMode = ref<FilterMode>(props.filterMode ?? "category");
const activeCategorySlug = ref<string | null>(
    props.selectedCategorySlug ?? null
);
const activeGroupSlug = ref<string | null>(props.selectedGroupSlug ?? null);
const searchTerm = ref(props.searchQuery ?? "");

const menuCollectionsHaveProducts = computed(() => {
    const categoryHasProducts = categoriesList.value.some(
        (category) => (category.products?.length ?? 0) > 0
    );
    const groupHasProducts = groupsList.value.some(
        (group) => (group.products?.length ?? 0) > 0
    );
    return categoryHasProducts || groupHasProducts;
});

const currentFilterEntries = computed<MenuCollection[]>(() =>
    filterMode.value === "group" ? groupsList.value : categoriesList.value
);

const activeFilterSlug = computed<string | null>(() =>
    filterMode.value === "group"
        ? activeGroupSlug.value
        : activeCategorySlug.value
);

const hasEntriesInCurrentMode = computed(() =>
    currentFilterEntries.value.some(
        (entry) => (entry.products?.length ?? 0) > 0
    )
);

const gatherProducts = (
    collection: Array<Category | Group>,
    slug: string | null
): Product[] => {
    if (!collection.length) {
        return [];
    }

    if (slug) {
        const selected = collection.find((entry) => entry.slug === slug);
        return selected?.products ?? [];
    }

    return collection.flatMap((entry) => entry.products ?? []);
};

const filteredProducts = computed<Product[]>(() => {
    let products = gatherProducts(
        currentFilterEntries.value,
        activeFilterSlug.value
    );

    if (!products.length && !menuCollectionsHaveProducts.value) {
        products = sampleProducts;
    }

    const query = searchTerm.value.trim().toLowerCase();
    if (query) {
        products = products.filter((product) => {
            const name = product.name?.toLowerCase() ?? "";
            const description = product.description?.toLowerCase() ?? "";
            return name.includes(query) || description.includes(query);
        });
    }

    return products;
});

const activeFilterLabel = computed(() => {
    if (activeFilterSlug.value) {
        return (
            currentFilterEntries.value.find(
                (entry) => entry.slug === activeFilterSlug.value
            )?.name ?? (filterMode.value === "group" ? "group" : "category")
        );
    }

    if (hasEntriesInCurrentMode.value) {
        return filterMode.value === "group" ? "all groups" : "all categories";
    }

    if (menuCollectionsHaveProducts.value) {
        return filterMode.value === "group" ? "categories" : "groups";
    }

    return "sample menu";
});

const brandName = computed(
    () => props.tenant?.brand_name ?? props.tenant?.name ?? "your brand"
);

const heroImage = computed(() => page.props.company_info?.hero_image ?? null);

const branchesList = computed<Branch[]>(() => props.branches ?? []);
const branchCountLabel = computed(() => {
    const count = branchesList.value.length;
    const descriptor = count === 1 ? "location" : "locations";
    return `${count} ${descriptor}`;
});
const heroTeaser =
    "Discover our most loved dishes, seasonal favorites, and exclusive specials through vibrant visuals—so choosing what to order feels effortless.";

const setFilterMode = (mode: FilterMode) => {
    filterMode.value = mode;
};

const setActiveFilter = (slug: string | null) => {
    if (filterMode.value === "group") {
        activeGroupSlug.value = slug;
        return;
    }

    activeCategorySlug.value = slug;
};

const formatPrice = (value?: number | string) => {
    const amount = Number(value ?? 0);
    if (Number.isNaN(amount)) {
        return "₱0";
    }
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        maximumFractionDigits: 0,
    }).format(amount);
};

const sampleFallbackImage =
    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=900&q=80";

const displayProducts = computed(() => filteredProducts.value);
</script>

<style scoped>
section {
    font-family: "Space Grotesk", "Inter", system-ui, -apple-system, sans-serif;
}

@media (min-width: 640px) {
    h1,
    h2,
    h3 {
        letter-spacing: -0.02em;
    }
}
</style>
