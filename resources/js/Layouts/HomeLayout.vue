<template>
    <Head title="Home - QJPOS" />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo and Navigation -->
                    <div class="flex items-center space-x-8">
                        <div class="flex items-center space-x-3">
                            <div class="bg-primary p-2 rounded-lg">
                                <svg
                                    class="w-8 h-8 text-white"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M12 2L2 7V10C2 16 6 20.5 12 22C18 20.5 22 16 22 10V7L12 2ZM12 4.19L19 7.3V10C19 15.85 15.74 19.94 12 20.96C8.26 19.94 5 15.85 5 10V7.3L12 4.19Z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900">
                                    QJ<span class="text-primary">POS</span>
                                </h1>
                                <!-- <p class="text-xs text-gray-500">
                                    {{
                                        activeBranch?.name ||
                                        "No Branch Selected"
                                    }}
                                </p> -->
                            </div>
                        </div>

                        <!-- Navigation Links -->
                        <nav class="hidden md:flex space-x-6">
                            <span
                                class="text-primary font-semibold px-3 py-2 rounded-md text-sm"
                            >
                                Home
                            </span>
                            <!-- <Link
                                :href="route('dashboard')"
                                class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors"
                            >
                                Dashboard
                            </Link> -->
                        </nav>
                    </div>

                    <!-- User Info and Menu -->
                    <div class="flex items-center space-x-4">
                        <div class="relative" ref="homeUserMenuRef">
                            <button
                                type="button"
                                class="group flex items-center space-x-3 rounded-xl border border-gray-200 bg-white px-4 py-2 text-left shadow-sm transition hover:border-gray-300 hover:shadow"
                                @click="toggleHomeUserMenu"
                            >
                                <div class="hidden sm:flex flex-col">
                                    <span
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        {{ user?.name || "User" }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ user?.email || "Signed in" }}
                                    </span>
                                </div>
                                <div
                                    class="sm:hidden text-sm font-semibold text-gray-900"
                                >
                                    {{ initials }}
                                </div>
                                <svg
                                    class="h-4 w-4 text-gray-500 transition group-hover:text-gray-700"
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
                                enter-from-class="opacity-0 translate-y-1"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition duration-100 ease-in"
                                leave-from-class="opacity-100"
                                leave-to-class="opacity-0 translate-y-1"
                            >
                                <div
                                    v-if="isHomeUserMenuOpen"
                                    class="absolute right-0 mt-2 w-48 origin-top-right rounded-xl bg-white py-2 shadow-lg ring-1 ring-black/5"
                                >
                                    <Link
                                        :href="route('profile.show')"
                                        class="block px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-100"
                                        @click="closeHomeUserMenu"
                                    >
                                        Profile
                                    </Link>
                                    <button
                                        type="button"
                                        class="block w-full px-4 py-2 text-left text-sm text-red-600 transition hover:bg-red-50"
                                        @click="logout"
                                    >
                                        Logout
                                    </button>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>

        <!-- Toast Notifications -->
        <Toast />
        <ConfirmPopup />

        <!-- PWA Install Banner -->
        <PWAInstallBanner />
    </div>
</template>

<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { ConfirmPopup, Toast, useToast } from "primevue";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { route } from "ziggy-js";
import PWAInstallBanner from "@/Components/PWAInstallBanner.vue";

const page = usePage();
const toast = useToast();
const activeBranch = computed(() => page.props.active_branch);
const user = computed(() => page.props.auth.user);
const initials = computed(() => {
    const name = user.value?.name || "User";
    return name
        .split(" ")
        .map((segment) => segment.charAt(0))
        .join("")
        .slice(0, 2)
        .toUpperCase();
});

const homeUserMenuRef = ref<HTMLElement | null>(null);
const isHomeUserMenuOpen = ref(false);

const toggleHomeUserMenu = () => {
    isHomeUserMenuOpen.value = !isHomeUserMenuOpen.value;
};

const closeHomeUserMenu = () => {
    isHomeUserMenuOpen.value = false;
};

const handleHomeMenuOutsideClick = (event: MouseEvent) => {
    if (!isHomeUserMenuOpen.value) {
        return;
    }

    const target = event.target as Node | null;
    if (
        homeUserMenuRef.value &&
        target &&
        !homeUserMenuRef.value.contains(target)
    ) {
        closeHomeUserMenu();
    }
};

const handleHomeMenuKeydown = (event: KeyboardEvent) => {
    if (event.key === "Escape") {
        closeHomeUserMenu();
    }
};

onMounted(() => {
    document.addEventListener("click", handleHomeMenuOutsideClick);
    document.addEventListener("keydown", handleHomeMenuKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleHomeMenuOutsideClick);
    document.removeEventListener("keydown", handleHomeMenuKeydown);
});

const logout = () => {
    closeHomeUserMenu();
    router.post(
        route("logout"),
        {},
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Logged out",
                    detail: "You have been successfully logged out.",
                    life: 3000,
                });
            },
            onError: () => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "There was an error logging out. Please try again.",
                    life: 4000,
                });
            },
        }
    );
};
</script>
