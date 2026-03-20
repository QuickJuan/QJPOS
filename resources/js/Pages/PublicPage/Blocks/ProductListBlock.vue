<template>
    <section class="px-6 py-16" :style="{ backgroundColor: bgColor }">
        <div class="mx-auto max-w-7xl space-y-8">
            <!-- Header -->
            <div v-if="c.title || c.subtitle" class="space-y-2">
                <p
                    v-if="c.subtitle"
                    class="text-xs uppercase tracking-[0.4em]"
                    :style="{ color: fontColor, opacity: 0.7 }"
                >
                    {{ c.subtitle }}
                </p>
                <h2
                    v-if="c.title"
                    class="text-3xl font-black md:text-4xl"
                    :style="{ color: fontColor }"
                >
                    {{ c.title }}
                </h2>
                <p
                    v-if="c.description"
                    class="text-sm max-w-2xl leading-relaxed"
                    :style="{ color: fontColor, opacity: 0.65 }"
                >
                    {{ c.description }}
                </p>
            </div>

            <!-- Search bar (always shown) -->
            <div class="relative max-w-lg">
                <svg
                    class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"
                    />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search products..."
                    class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-9 pr-4 text-sm text-gray-800 placeholder-gray-400 focus:border-primary-500 focus:outline-none"
                />
            </div>

            <!-- TAGS layout: category pills + group pills above the grid -->
            <template v-if="filterLayout === 'tags'">
                <!-- Category tags -->
                <div
                    v-if="availableCategories.length > 0"
                    class="flex flex-wrap gap-2"
                >
                    <button
                        @click="selectedCategory = ''"
                        class="rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.15em] transition border"
                        :style="
                            selectedCategory === ''
                                ? {
                                      backgroundColor: tagActiveBg,
                                      color: tagActiveText,
                                      borderColor: tagActiveBg,
                                  }
                                : {
                                      backgroundColor: 'transparent',
                                      color: tagColor,
                                      borderColor: tagColor + '40',
                                  }
                        "
                    >
                        All
                    </button>
                    <button
                        v-for="cat in availableCategories"
                        :key="cat"
                        @click="
                            selectedCategory =
                                selectedCategory === cat ? '' : cat
                        "
                        class="rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.15em] transition border"
                        :style="
                            selectedCategory === cat
                                ? {
                                      backgroundColor: tagActiveBg,
                                      color: tagActiveText,
                                      borderColor: tagActiveBg,
                                  }
                                : {
                                      backgroundColor: 'transparent',
                                      color: tagColor,
                                      borderColor: tagColor + '40',
                                  }
                        "
                    >
                        {{ cat }}
                    </button>
                </div>
                <!-- Group tags -->
                <div
                    v-if="availableGroups.length > 0"
                    class="flex flex-wrap gap-2"
                >
                    <button
                        v-for="grp in availableGroups"
                        :key="grp"
                        @click="
                            selectedGroup = selectedGroup === grp ? '' : grp
                        "
                        class="rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] transition border"
                        :style="
                            selectedGroup === grp
                                ? {
                                      backgroundColor: tagActiveBg,
                                      color: tagActiveText,
                                      borderColor: tagActiveBg,
                                  }
                                : {
                                      backgroundColor: 'transparent',
                                      color: tagColor,
                                      borderColor: tagColor + '30',
                                  }
                        "
                    >
                        {{ grp }}
                    </button>
                </div>
                <!-- result count + grid -->
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500"
                        >{{ filteredAll.length }} item{{
                            filteredAll.length !== 1 ? "s" : ""
                        }}</span
                    >
                </div>
                <p
                    v-if="filteredProducts.length === 0"
                    class="py-12 text-center text-slate-400 text-sm"
                >
                    No products found.
                </p>
                <div
                    v-else
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <ProductCard
                        v-for="product in filteredProducts"
                        :key="product.id ?? product.name"
                        :product="product"
                        :show-add-to-cart="showAddToCart"
                        :cart-loading="cartLoading"
                        :card-bg-color="cardBgColor"
                        :card-title-color="cardTitleColor"
                        :card-desc-color="cardDescColor"
                        :card-price-color="cardPriceColor"
                        @add="addToCart"
                    />
                </div>
            </template>

            <!-- SIDEBAR layout: filters on left, grid on right -->
            <div v-else class="flex flex-col gap-8 lg:flex-row lg:items-start">
                <!-- Sidebar -->
                <aside class="lg:w-56 xl:w-64 shrink-0 space-y-6">
                    <!-- Category filter -->
                    <div v-if="availableCategories.length > 0">
                        <p
                            class="mb-2 text-[10px] uppercase tracking-[0.3em] font-semibold"
                            :style="{ color: tagColor, opacity: 0.6 }"
                        >
                            Categories
                        </p>
                        <ul class="space-y-0.5">
                            <li>
                                <button
                                    @click="selectedCategory = ''"
                                    @mouseenter="hoveredCat = '_all_'"
                                    @mouseleave="hoveredCat = ''"
                                    class="w-full text-left rounded-lg px-3 py-2 text-sm font-medium transition"
                                    :style="
                                        selectedCategory === ''
                                            ? {
                                                  backgroundColor:
                                                      tagActiveBg + '28',
                                                  color: tagActiveBg,
                                                  fontWeight: '700',
                                              }
                                            : hoveredCat === '_all_'
                                              ? {
                                                    backgroundColor:
                                                        tagActiveBg + '15',
                                                    color: tagActiveBg,
                                                }
                                              : { color: tagColor }
                                    "
                                >
                                    All Categories
                                </button>
                            </li>
                            <li v-for="cat in visibleCategories" :key="cat">
                                <button
                                    @click="
                                        selectedCategory =
                                            selectedCategory === cat ? '' : cat
                                    "
                                    @mouseenter="hoveredCat = cat"
                                    @mouseleave="hoveredCat = ''"
                                    class="w-full text-left rounded-lg px-3 py-2 text-sm transition"
                                    :style="
                                        selectedCategory === cat
                                            ? {
                                                  backgroundColor:
                                                      tagActiveBg + '28',
                                                  color: tagActiveBg,
                                                  fontWeight: '700',
                                              }
                                            : hoveredCat === cat
                                              ? {
                                                    backgroundColor:
                                                        tagActiveBg + '15',
                                                    color: tagActiveBg,
                                                }
                                              : { color: tagColor }
                                    "
                                >
                                    {{ cat }}
                                </button>
                            </li>
                        </ul>
                        <button
                            v-if="availableCategories.length > sidebarLimit"
                            @click="catShowAll = !catShowAll"
                            class="mt-1 pl-3 text-xs underline-offset-2 underline transition"
                            :style="{ color: tagActiveBg }"
                        >
                            {{
                                catShowAll
                                    ? "Show less"
                                    : `+${availableCategories.length - sidebarLimit} more`
                            }}
                        </button>
                    </div>
                    <!-- Group filter -->
                    <div v-if="availableGroups.length > 0">
                        <p
                            class="mb-2 text-[10px] uppercase tracking-[0.3em] font-semibold"
                            :style="{ color: tagColor, opacity: 0.6 }"
                        >
                            Groups
                        </p>
                        <ul class="space-y-0.5">
                            <li>
                                <button
                                    @click="selectedGroup = ''"
                                    @mouseenter="hoveredGrp = '_all_'"
                                    @mouseleave="hoveredGrp = ''"
                                    class="w-full text-left rounded-lg px-3 py-2 text-sm font-medium transition"
                                    :style="
                                        selectedGroup === ''
                                            ? {
                                                  backgroundColor:
                                                      tagActiveBg + '28',
                                                  color: tagActiveBg,
                                                  fontWeight: '700',
                                              }
                                            : hoveredGrp === '_all_'
                                              ? {
                                                    backgroundColor:
                                                        tagActiveBg + '15',
                                                    color: tagActiveBg,
                                                }
                                              : { color: tagColor }
                                    "
                                >
                                    All Groups
                                </button>
                            </li>
                            <li v-for="grp in visibleGroups" :key="grp">
                                <button
                                    @click="
                                        selectedGroup =
                                            selectedGroup === grp ? '' : grp
                                    "
                                    @mouseenter="hoveredGrp = grp"
                                    @mouseleave="hoveredGrp = ''"
                                    class="w-full text-left rounded-lg px-3 py-2 text-sm transition"
                                    :style="
                                        selectedGroup === grp
                                            ? {
                                                  backgroundColor:
                                                      tagActiveBg + '28',
                                                  color: tagActiveBg,
                                                  fontWeight: '700',
                                              }
                                            : hoveredGrp === grp
                                              ? {
                                                    backgroundColor:
                                                        tagActiveBg + '15',
                                                    color: tagActiveBg,
                                                }
                                              : { color: tagColor }
                                    "
                                >
                                    {{ grp }}
                                </button>
                            </li>
                        </ul>
                        <button
                            v-if="availableGroups.length > sidebarLimit"
                            @click="grpShowAll = !grpShowAll"
                            class="mt-1 pl-3 text-xs underline-offset-2 underline transition"
                            :style="{ color: tagActiveBg }"
                        >
                            {{
                                grpShowAll
                                    ? "Show less"
                                    : `+${availableGroups.length - sidebarLimit} more`
                            }}
                        </button>
                    </div>
                    <span
                        class="block text-xs"
                        :style="{ color: tagColor, opacity: 0.5 }"
                        >{{ filteredAll.length }} item{{
                            filteredAll.length !== 1 ? "s" : ""
                        }}</span
                    >
                </aside>

                <!-- Grid -->
                <div class="flex-1 space-y-6">
                    <p
                        v-if="filteredProducts.length === 0"
                        class="py-12 text-center text-slate-400 text-sm"
                    >
                        No products found.
                    </p>
                    <div
                        v-else
                        class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3"
                    >
                        <ProductCard
                            v-for="product in filteredProducts"
                            :key="product.id ?? product.name"
                            :product="product"
                            :show-add-to-cart="showAddToCart"
                            :cart-loading="cartLoading"
                            :card-bg-color="cardBgColor"
                            :card-title-color="cardTitleColor"
                            :card-desc-color="cardDescColor"
                            :card-price-color="cardPriceColor"
                            @add="addToCart"
                        />
                    </div>
                </div>
            </div>

            <!-- Load more (both layouts) -->
            <div v-if="showLoadMore" class="flex justify-center pt-4">
                <button
                    @click="visibleCount += pageSize"
                    class="rounded-full border border-white/15 bg-white/5 px-8 py-2.5 text-sm font-semibold text-white/80 transition hover:border-white/30 hover:bg-white/10"
                >
                    Load more
                </button>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, reactive, defineComponent, h } from "vue";

// ─── Re-usable product card ────────────────────────────────────────────────
const ProductCard = defineComponent({
    props: {
        product: Object,
        showAddToCart: Boolean,
        cartLoading: Object,
        cardBgColor: { type: String, default: "#0f172a" },
        cardTitleColor: { type: String, default: "#ffffff" },
        cardDescColor: { type: String, default: "#94a3b8" },
        cardPriceColor: { type: String, default: "#fb923c" },
    },
    emits: ["add"],
    setup(props, { emit }) {
        const resolveImg = (p) => {
            const src = p.image_url ?? p.image ?? null;
            if (!src) return null;
            if (src.startsWith("http") || src.startsWith("/")) return src;
            return `/storage/${src}`;
        };
        const fmt = (price) => {
            const n = parseFloat(price ?? 0);
            return isNaN(n) ? "0.00" : n.toFixed(2);
        };
        return () => {
            const p = props.product;
            const img = resolveImg(p);
            return h(
                "div",
                {
                    class: "group flex flex-col overflow-hidden rounded-2xl border border-white/5 hover:border-white/15 transition-all duration-300",
                    style: { backgroundColor: props.cardBgColor },
                },
                [
                    // Image
                    h(
                        "div",
                        { class: "relative h-48 overflow-hidden bg-slate-800" },
                        [
                            img
                                ? h("img", {
                                      src: img,
                                      alt: p.name,
                                      class: "h-full w-full object-cover transition duration-500 group-hover:scale-105",
                                  })
                                : h(
                                      "div",
                                      {
                                          class: "flex h-full items-center justify-center text-slate-600",
                                      },
                                      [
                                          h(
                                              "svg",
                                              {
                                                  class: "w-12 h-12",
                                                  fill: "none",
                                                  stroke: "currentColor",
                                                  viewBox: "0 0 24 24",
                                              },
                                              [
                                                  h("path", {
                                                      "stroke-linecap": "round",
                                                      "stroke-linejoin":
                                                          "round",
                                                      "stroke-width": "1.5",
                                                      d: "M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z",
                                                  }),
                                              ],
                                          ),
                                      ],
                                  ),
                            p.category &&
                                h(
                                    "span",
                                    {
                                        class: "absolute top-3 left-3 rounded-full bg-black/60 px-2 py-0.5 text-[10px] uppercase tracking-[0.3em] text-white/70 backdrop-blur",
                                    },
                                    p.category,
                                ),
                            p.groups?.length &&
                                h(
                                    "div",
                                    {
                                        class: "absolute bottom-3 left-3 flex flex-wrap gap-1",
                                    },
                                    (p.groups ?? []).map((g) =>
                                        h(
                                            "span",
                                            {
                                                key: g,
                                                class: "rounded-full bg-primary-800/80 px-2 py-0.5 text-[9px] uppercase tracking-[0.2em] text-primary-200 backdrop-blur",
                                            },
                                            g,
                                        ),
                                    ),
                                ),
                        ],
                    ),
                    // Info
                    h("div", { class: "flex flex-1 flex-col gap-2 p-5" }, [
                        h(
                            "h3",
                            {
                                class: "text-sm font-black uppercase tracking-[0.1em] leading-snug",
                                style: { color: props.cardTitleColor },
                            },
                            p.name,
                        ),
                        p.description &&
                            h("div", {
                                class: "line-clamp-2 text-xs leading-relaxed",
                                style: { color: props.cardDescColor },
                                innerHTML: p.description,
                            }),
                        h(
                            "div",
                            {
                                class: "mt-auto flex items-center justify-between pt-3",
                            },
                            [
                                h(
                                    "span",
                                    {
                                        class: "text-lg font-bold",
                                        style: { color: props.cardPriceColor },
                                    },
                                    `\u20b1${fmt(p.price)}`,
                                ),
                                props.showAddToCart &&
                                    h(
                                        "button",
                                        {
                                            onClick: () => emit("add", p),
                                            disabled: props.cartLoading?.[p.id],
                                            class: "flex items-center gap-1.5 rounded-full border border-primary-500/50 bg-primary-500/10 px-3 py-1.5 text-xs font-semibold text-primary-300 transition hover:bg-primary-500/20 hover:border-primary-400 disabled:opacity-50",
                                        },
                                        props.cartLoading?.[p.id]
                                            ? "..."
                                            : "+ Add",
                                    ),
                            ],
                        ),
                    ]),
                ],
            );
        };
    },
});

// ─── Main component ────────────────────────────────────────────────────────
const props = defineProps({
    content: { type: Object, default: null },
    settings: { type: Object, default: null },
    products: { type: Array, default: null },
});

const c = computed(() => props.content ?? {});
const s = computed(() => props.settings ?? {});
const allProducts = computed(() => props.products ?? []);

const showAddToCart = computed(() => s.value.show_add_to_cart !== false);
const pageSize = computed(() => parseInt(s.value.page_size ?? 12));
const filterLayout = computed(() => s.value.filter_layout ?? "tags");
const bgColor = computed(() => s.value.bg_color || "#020617");
const fontColor = computed(() => s.value.text_color || "#ffffff");
const cardBgColor = computed(() => s.value.card_bg_color || "#0f172a");
const cardTitleColor = computed(() => s.value.card_title_color || "#ffffff");
const cardDescColor = computed(() => s.value.card_desc_color || "#94a3b8");
const cardPriceColor = computed(() => s.value.card_price_color || "#fb923c");
const tagColor = computed(() => s.value.tag_color || "#94a3b8");
const tagActiveBg = computed(() => s.value.tag_active_bg || "#f97316");
const tagActiveText = computed(() => s.value.tag_active_text || "#ffffff");

const search = ref("");
const selectedCategory = ref("");
const selectedGroup = ref("");
const visibleCount = ref(pageSize.value);

// Sidebar interaction state
const hoveredCat = ref("");
const hoveredGrp = ref("");
const catShowAll = ref(false);
const grpShowAll = ref(false);
const sidebarLimit = 8;

const availableCategories = computed(() =>
    [
        ...new Set(allProducts.value.map((p) => p.category).filter(Boolean)),
    ].sort(),
);

const availableGroups = computed(() =>
    [...new Set(allProducts.value.flatMap((p) => p.groups ?? []))].sort(),
);

const visibleCategories = computed(() =>
    catShowAll.value
        ? availableCategories.value
        : availableCategories.value.slice(0, sidebarLimit),
);

const visibleGroups = computed(() =>
    grpShowAll.value
        ? availableGroups.value
        : availableGroups.value.slice(0, sidebarLimit),
);

const filteredAll = computed(() => {
    let list = allProducts.value;
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(
            (p) =>
                p.name?.toLowerCase().includes(q) ||
                p.description?.toLowerCase().includes(q) ||
                p.category?.toLowerCase().includes(q),
        );
    }
    if (selectedCategory.value)
        list = list.filter((p) => p.category === selectedCategory.value);
    if (selectedGroup.value)
        list = list.filter((p) =>
            (p.groups ?? []).includes(selectedGroup.value),
        );
    return list;
});

const filteredProducts = computed(() =>
    filteredAll.value.slice(0, visibleCount.value),
);
const showLoadMore = computed(
    () => filteredAll.value.length > visibleCount.value,
);

const cartLoading = reactive({});

const addToCart = async (product) => {
    cartLoading[product.id] = true;
    try {
        await fetch("/cart/add", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") ?? "",
            },
            body: JSON.stringify({ product_id: product.id, quantity: 1 }),
        });
    } catch {
        // cart endpoint may not be wired yet
    } finally {
        cartLoading[product.id] = false;
    }
};
</script>
