<template>
    <PublicPageLayout
        :navigation="navigation"
        :appName="appName"
        :companyLogo="companyLogo"
    >
        <Head :title="career.title" />

        <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6">
            <!-- Breadcrumb -->
            <nav class="mb-6 text-sm text-gray-500 flex items-center gap-2">
                <a href="/" class="hover:text-gray-700">Home</a>
                <span>/</span>
                <span class="text-gray-700">Careers</span>
                <span>/</span>
                <span class="text-gray-900 font-medium">{{
                    career.title
                }}</span>
            </nav>

            <!-- Job Header -->
            <div class="bg-white rounded-xl shadow-sm p-8 mb-6">
                <div class="flex flex-wrap gap-2 mb-3">
                    <span
                        v-if="career.department"
                        class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded-full"
                    >
                        {{ career.department }}
                    </span>
                    <span
                        v-if="career.employment_type"
                        class="inline-block bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full capitalize"
                    >
                        {{ career.employment_type.replace("_", " ") }}
                    </span>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-3">
                    {{ career.title }}
                </h1>

                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <div v-if="career.location" class="flex items-center gap-1">
                        <svg
                            class="w-4 h-4 flex-shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        {{ career.location }}
                    </div>
                    <div
                        v-if="career.salary_range"
                        class="flex items-center gap-1"
                    >
                        <svg
                            class="w-4 h-4 flex-shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        {{ career.salary_range }}
                    </div>
                </div>

                <p
                    v-if="career.summary"
                    class="text-gray-600 mt-4 text-base leading-relaxed"
                >
                    {{ career.summary }}
                </p>
            </div>

            <!-- Description Sections -->
            <div class="bg-white rounded-xl shadow-sm p-8 mb-6 space-y-8">
                <div v-if="career.description">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">
                        About the Role
                    </h2>
                    <div
                        class="prose prose-sm max-w-none text-gray-700"
                        v-html="career.description"
                    ></div>
                </div>

                <div v-if="career.responsibilities">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">
                        Responsibilities
                    </h2>
                    <div
                        class="prose prose-sm max-w-none text-gray-700"
                        v-html="career.responsibilities"
                    ></div>
                </div>

                <div v-if="career.requirements">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">
                        Requirements
                    </h2>
                    <div
                        class="prose prose-sm max-w-none text-gray-700"
                        v-html="career.requirements"
                    ></div>
                </div>
            </div>

            <!-- Application Form -->
            <div class="bg-white rounded-xl shadow-sm p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    Apply for this Position
                </h2>

                <!-- Success message -->
                <div
                    v-if="$page.props.flash?.success"
                    class="mb-6 flex items-start gap-3 bg-green-50 border border-green-200 rounded-lg p-4"
                >
                    <svg
                        class="w-5 h-5 text-green-500 shrink-0 mt-0.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <p class="text-green-800 font-medium">
                        {{ $page.props.flash.success }}
                    </p>
                </div>

                <!-- Already applied / error message -->
                <div
                    v-if="$page.props.flash?.error"
                    class="mb-6 flex items-start gap-3 bg-amber-50 border border-amber-300 rounded-lg p-4"
                >
                    <svg
                        class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                        />
                    </svg>
                    <p class="text-amber-800 font-medium">
                        {{ $page.props.flash.error }}
                    </p>
                </div>

                <form
                    v-if="!$page.props.flash?.success"
                    @submit.prevent="submit"
                    class="space-y-5"
                    enctype="multipart/form-data"
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.first_name"
                                type="text"
                                required
                                placeholder="Juan"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                                :class="{
                                    'border-red-400': form.errors.first_name,
                                }"
                            />
                            <p
                                v-if="form.errors.first_name"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.first_name }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.last_name"
                                type="text"
                                required
                                placeholder="Dela Cruz"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                                :class="{
                                    'border-red-400': form.errors.last_name,
                                }"
                            />
                            <p
                                v-if="form.errors.last_name"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.last_name }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="juan@example.com"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                                :class="{ 'border-red-400': form.errors.email }"
                            />
                            <p
                                v-if="form.errors.email"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Phone <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                required
                                placeholder="+63 912 345 6789"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                                :class="{ 'border-red-400': form.errors.phone }"
                            />
                            <p
                                v-if="form.errors.phone"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Resume / CV <span class="text-red-500">*</span>
                        </label>
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".pdf,.doc,.docx"
                            required
                            @change="form.resume = $event.target.files[0]"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-900 file:text-white hover:file:bg-gray-700 cursor-pointer"
                            :class="{ 'border-red-400': form.errors.resume }"
                        />
                        <p class="text-xs text-gray-400 mt-1">
                            PDF, DOC, or DOCX — max 5MB
                        </p>
                        <p
                            v-if="form.errors.resume"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ form.errors.resume }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Cover Letter</label
                        >
                        <textarea
                            v-model="form.cover_letter"
                            rows="5"
                            placeholder="Tell us why you're a great fit for this role..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-y"
                            :class="{
                                'border-red-400': form.errors.cover_letter,
                            }"
                        ></textarea>
                        <p
                            v-if="form.errors.cover_letter"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ form.errors.cover_letter }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full sm:w-auto bg-gray-900 text-white font-semibold px-8 py-3 rounded-lg hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Submitting…</span>
                        <span v-else>Submit Application</span>
                    </button>
                </form>
            </div>
        </div>
    </PublicPageLayout>
</template>

<script setup>
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import PublicPageLayout from "@/Layouts/PublicPageLayout.vue";

const props = defineProps({
    career: Object,
    navigation: Array,
    appName: String,
    companyLogo: String,
});

const fileInput = ref(null);

const form = useForm({
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    resume: null,
    cover_letter: "",
});

const submit = () => {
    form.post(`/careers/${props.career.id}/apply`, {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            if (fileInput.value) fileInput.value.value = "";
        },
    });
};
</script>
