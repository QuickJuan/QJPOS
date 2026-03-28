<template>
    <PublicPageLayout
        :navigation="navigation"
        :app-name="appName"
        :company-logo="companyLogo"
    >
        <Head title="Access Restricted" />

        <section class="px-6 py-24">
            <div class="mx-auto max-w-2xl text-center space-y-8">
                <!-- Lock icon -->
                <div class="flex justify-center">
                    <span
                        class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-orange-50"
                    >
                        <svg
                            class="w-10 h-10 text-orange-500"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"
                            />
                        </svg>
                    </span>
                </div>

                <!-- Heading -->
                <div class="space-y-3">
                    <p
                        class="text-sm font-semibold uppercase tracking-widest text-orange-500"
                    >
                        Access Restricted
                    </p>
                    <h1 class="text-4xl font-black text-gray-900">
                        You are not allowed to view this page
                    </h1>
                    <p class="text-lg text-gray-500 leading-relaxed">
                        This content is only available to authorized users. If
                        you believe you should have access, please contact the
                        system administrator.
                    </p>
                </div>

                <!-- Actions -->
                <div
                    class="flex flex-col sm:flex-row items-center justify-center gap-4"
                >
                    <Link
                        href="/"
                        class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-6 py-3 font-semibold text-white hover:bg-orange-700 transition-colors"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        Go to Home
                    </Link>
                </div>
            </div>
        </section>
    </PublicPageLayout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PublicPageLayout from "@/Layouts/PublicPageLayout.vue";

defineProps({
    navigation: { type: Array, default: () => [] },
    appName: { type: String, default: "" },
    companyLogo: { type: String, default: null },
});
</script>
