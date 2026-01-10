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
                    <template v-if="isAuthenticated">
                        <div class="flex items-center gap-2">
                            <div class="relative" ref="landingUserMenuRef">
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/80 transition hover:bg-white/15"
                                    @click="toggleLandingUserMenu"
                                >
                                    <UserIcon class="h-4 w-4" />
                                    <span class="hidden sm:inline">
                                        {{ authUserName || "Account" }}
                                    </span>
                                    <svg
                                        class="h-3 w-3 text-white/80"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 9l6 6 6-6"
                                        />
                                    </svg>
                                </button>

                                <transition
                                    enter-active-class="transition duration-150 ease-out"
                                    enter-from-class="opacity-0 scale-95"
                                    enter-to-class="opacity-100 scale-100"
                                    leave-active-class="transition duration-100 ease-in"
                                    leave-from-class="opacity-100 scale-100"
                                    leave-to-class="opacity-0 scale-95"
                                >
                                    <div
                                        v-if="isLandingUserMenuOpen"
                                        class="absolute right-0 mt-3 w-64 origin-top-right rounded-2xl bg-white/95 text-slate-900 shadow-2xl ring-1 ring-black/5 backdrop-blur"
                                    >
                                        <div
                                            class="border-b border-slate-100 px-4 py-3"
                                        >
                                            <p class="text-sm font-semibold">
                                                {{ authUserName }}
                                            </p>
                                            <p
                                                class="text-xs text-slate-500 truncate"
                                            >
                                                {{
                                                    authUserEmail || "Signed in"
                                                }}
                                            </p>
                                        </div>
                                        <div
                                            class="py-2 text-sm font-semibold text-slate-700"
                                        >
                                            <Link
                                                :href="route('profile.show')"
                                                class="flex items-center gap-2 px-4 py-2 transition hover:bg-slate-100"
                                                @click="closeLandingUserMenu"
                                            >
                                                Profile
                                            </Link>
                                            <button
                                                type="button"
                                                class="flex w-full items-center gap-2 px-4 py-2 text-left transition hover:bg-slate-100"
                                                @click="handleLandingLogout"
                                            >
                                                Logout
                                            </button>
                                        </div>
                                    </div>
                                </transition>
                            </div>

                            <Link
                                :href="route('home')"
                                class="rounded-full bg-primary-500/90 px-5 py-2 text-sm font-semibold text-white transition hover:bg-primary-400"
                            >
                                Go to app
                            </Link>
                        </div>
                    </template>
                    <Link
                        v-else
                        :href="route('login')"
                        class="rounded-full bg-white/10 px-5 py-2 text-sm font-semibold text-white transition hover:bg-white/20"
                    >
                        Login
                    </Link>
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
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
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
const authUserEmail = computed(() => {
    const email =
        authUser.value && typeof authUser.value === "object"
            ? (authUser.value as { email?: string }).email
            : undefined;
    return email ?? null;
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

const landingUserMenuRef = ref<HTMLElement | null>(null);
const isLandingUserMenuOpen = ref(false);

const toggleLandingUserMenu = () => {
    isLandingUserMenuOpen.value = !isLandingUserMenuOpen.value;
};

const closeLandingUserMenu = () => {
    isLandingUserMenuOpen.value = false;
};

const handleLandingLogout = () => {
    closeLandingUserMenu();
    router.post(
        route("logout"),
        {},
        {
            preserveState: false,
            preserveScroll: false,
            onSuccess: () => {
                window.location.href = route("login");
            },
        }
    );
};

const handleLandingOutsideClick = (event: MouseEvent) => {
    if (!isLandingUserMenuOpen.value) {
        return;
    }

    const target = event.target as Node | null;
    if (
        landingUserMenuRef.value &&
        target &&
        !landingUserMenuRef.value.contains(target)
    ) {
        closeLandingUserMenu();
    }
};

const handleLandingKeydown = (event: KeyboardEvent) => {
    if (event.key === "Escape") {
        closeLandingUserMenu();
    }
};

onMounted(() => {
    document.addEventListener("click", handleLandingOutsideClick);
    document.addEventListener("keydown", handleLandingKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleLandingOutsideClick);
    document.removeEventListener("keydown", handleLandingKeydown);
});
</script>
