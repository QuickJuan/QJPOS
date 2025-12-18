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

                    <!-- User Info and Logout -->
                    <div class="flex items-center space-x-4">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium text-gray-900">
                                {{ user.name }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ user.email }}
                            </p>
                        </div>
                        <button
                            @click="logout"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center space-x-2"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                ></path>
                            </svg>
                            <span>Logout</span>
                        </button>
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
    </div>
</template>

<script setup lang="ts">
import { Head, router, usePage } from "@inertiajs/vue3";
import { ConfirmPopup, Toast, useToast } from "primevue";
import { computed } from "vue";
import { route } from "ziggy-js";

const page = usePage();
const toast = useToast();
const activeBranch = computed(() => page.props.active_branch);
const user = computed(() => page.props.auth.user);

const logout = () => {
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
