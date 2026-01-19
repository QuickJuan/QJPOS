<template>
    <Head :title="`Welcome to ${appName}`" />

    <PublicPageLayout :navigation="[]" :appName="appName">
        <div
            class="min-h-screen bg-gradient-to-b from-blue-50 to-white flex items-center justify-center"
        >
            <div class="max-w-2xl mx-auto px-4 text-center">
                <div class="mb-8">
                    <div
                        class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-blue-100 mb-6"
                    >
                        <svg
                            class="w-12 h-12 text-blue-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                            />
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">
                        Welcome to {{ appName }}
                    </h1>
                    <p class="text-xl text-gray-600 mb-8">
                        No landing page has been created yet.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                        Get Started
                    </h2>
                    <p class="text-gray-600 mb-6">
                        To create a landing page, go to the admin panel and
                        create a new page with the type "Landing Page".
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a
                            :href="route('filament.tenant.pages.dashboard')"
                            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors"
                        >
                            Go to Admin Panel
                        </a>
                        <a
                            :href="route('login')"
                            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                        >
                            Login
                        </a>
                    </div>
                </div>

                <div class="text-sm text-gray-500">
                    <p>Landing pages are built using the Page Builder</p>
                    <p class="mt-1">
                        Create beautiful, custom landing pages with
                        drag-and-drop blocks
                    </p>
                </div>
            </div>
        </div>
    </PublicPageLayout>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import PublicPageLayout from "@/Layouts/PublicPageLayout.vue";

defineProps({
    appName: String,
});
</script>
