<template>
    <Head :title="props.title ?? 'Tenant Portal'" />

    <div class="relative min-h-screen text-white">
        <header
            class="sticky top-0 z-40 border-b border-white/5 bg-slate-950/95 px-4 backdrop-blur sm:px-6"
        >
            <div
                class="mx-auto flex max-w-6xl items-center justify-between py-4"
            >
                <div class="flex items-center gap-3">
                    <span class="text-xl font-black tracking-tight">
                        {{ brandTitle }}
                    </span>
                    <span
                        class="text-xs uppercase tracking-[0.4em] text-slate-400"
                    >
                        Portal</span
                    >
                </div>
                <nav
                    class="hidden items-center gap-6 text-sm font-semibold lg:flex"
                >
                    <a
                        v-for="link in navLinks"
                        :key="link.label"
                        :href="link.href"
                        class="uppercase tracking-[0.2em] text-white/70 transition hover:text-white"
                    >
                        {{ link.label }}
                    </a>
                </nav>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span
                            v-if="isAuthenticated && authUserName"
                            class="px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-white/80 hover:text-white flex items-center"
                        >
                            <UserIcon class="inline-block h-4 w-4 mr-1" />
                            {{ authUserName }}
                        </span>
                        <Link
                            v-else
                            :href="route('login')"
                            class="rounded-full bg-white/10 px-5 py-2 text-sm font-semibold text-white transition hover:bg-white/20"
                        >
                            Login
                        </Link>
                        <Link
                            v-if="isAuthenticated"
                            :href="route('home')"
                            class="rounded-full bg-primary-500/90 px-5 py-2 text-sm font-semibold text-white transition hover:bg-primary-400"
                        >
                            Go to app
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <main class="pt-0">
            <slot />
        </main>
    </div>

    <Toast />
</template>

<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Toast } from "primevue";
import UserIcon from "@/Components/icons/UserIcon.vue";

type ActiveBranch = {
    id?: number;
    name?: string;
    branch_code?: string;
};

type PageProps = {
    auth?: {
        user?: Record<string, unknown> | null;
    } | null;
    branches?: Array<{ id: number; name: string }>;
    active_branch?: ActiveBranch | null;
    tenant?: {
        name?: string;
        brand_name?: string;
    } | null;
};

const props = defineProps<{
    title?: string;
}>();

const page = usePage<PageProps>();

const authUser = computed(() => page.props.auth?.user ?? null);
const isAuthenticated = computed(() => Boolean(authUser.value));
const activeBranch = computed<ActiveBranch | null>(
    () => page.props.active_branch ?? null
);
const authUserName = computed(() => {
    const name =
        authUser.value && typeof authUser.value === "object"
            ? (authUser.value as { name?: string }).name
            : undefined;
    return name ?? null;
});
const totalBranches = computed(() => page.props.branches?.length ?? 0);
const hasMultipleBranches = computed(() => totalBranches.value > 1);
const brandTitle = computed(
    () =>
        activeBranch.value?.name ??
        page.props.tenant?.brand_name ??
        page.props.tenant?.name ??
        "QuickJuan"
);
const brandLabel = computed(() =>
    activeBranch.value?.name ? "Current branch" : "Tenant"
);
const brandCTA = computed(() => activeBranch.value?.name ?? brandTitle.value);

const navLinks = computed(() => {
    const links = [
        { label: "Menu", href: "#menu", visible: true },
        {
            label: "Branches",
            href: "#branches",
            visible: hasMultipleBranches.value,
        },
        { label: "Blogs", href: "#blogs", visible: true },
        { label: "Careers", href: "#careers", visible: true },
        { label: "Contact us", href: "#contact", visible: true },
    ];

    return links.filter((link) => link.visible);
});
</script>
