<template>
    <AppLayout title="Waiter Home">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-neutral-900">
                    Welcome, {{ $page.props.auth.user.name }}
                </h1>
                <p class="text-neutral-600 mt-2">
                    Branch: {{ activeBranch?.name }}
                </p>
            </div>

            <!-- Action Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- View Tables -->
                <Link
                    :href="route('waiter.tables')"
                    class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 border border-neutral-200 hover:border-primary-300"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div
                                class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4 group-hover:bg-primary-200 transition-colors"
                            >
                                <svg
                                    class="w-6 h-6 text-primary-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                    />
                                </svg>
                            </div>
                            <h3
                                class="text-lg font-semibold text-neutral-900 mb-2"
                            >
                                View Tables
                            </h3>
                            <p class="text-sm text-neutral-600">
                                See all tables and their current status
                            </p>
                        </div>
                        <svg
                            class="w-5 h-5 text-neutral-400 group-hover:text-primary-600 transition-colors"
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
                    </div>
                </Link>

                <!-- Logout -->
                <button
                    @click="logout"
                    class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 border border-neutral-200 hover:border-red-300 text-left"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div
                                class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4 group-hover:bg-red-200 transition-colors"
                            >
                                <svg
                                    class="w-6 h-6 text-red-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                    />
                                </svg>
                            </div>
                            <h3
                                class="text-lg font-semibold text-neutral-900 mb-2"
                            >
                                Logout
                            </h3>
                            <p class="text-sm text-neutral-600">
                                Sign out from your waiter account
                            </p>
                        </div>
                        <svg
                            class="w-5 h-5 text-neutral-400 group-hover:text-red-600 transition-colors"
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
                    </div>
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const page = usePage();

const activeBranch = computed(() => page.props.active_branch);

const logout = () => {
    router.post(route("waiter.logout"));
};
</script>
