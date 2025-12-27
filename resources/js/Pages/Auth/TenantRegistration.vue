<template>
    <div
        class="min-h-screen bg-gradient-to-br from-primary-50 via-secondary-50 to-tertiary-50 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
    >
        <!-- Top Decoration -->
        <div
            class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-bl from-primary-200 to-transparent rounded-full blur-3xl opacity-20 -z-10"
        ></div>
        <div
            class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-secondary-200 to-transparent rounded-full blur-3xl opacity-20 -z-10"
        ></div>

        <div class="max-w-3xl w-full">
            <!-- Header -->
            <div class="text-center mb-10">
                <!-- Logo/Icon -->
                <div class="flex justify-center mb-6">
                    <div
                        class="w-20 h-20 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl shadow-lg flex items-center justify-center"
                    >
                        <svg
                            class="w-10 h-10 text-white"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                            />
                        </svg>
                    </div>
                </div>

                <h1
                    class="text-4xl sm:text-5xl font-black bg-gradient-to-r from-primary-700 via-secondary-700 to-tertiary-700 bg-clip-text text-transparent mb-2"
                >
                    {{ appName }}
                </h1>
                <p class="text-lg text-neutral-600 font-medium">
                    Start Your Business Journey Today
                </p>
                <p class="text-neutral-500 mt-2">
                    Join thousands of successful merchants using our POS system
                </p>
            </div>

            <!-- Form Card -->
            <div
                class="bg-white rounded-3xl shadow-2xl p-8 sm:p-10 backdrop-blur-sm border border-neutral-100"
            >
                <!-- Error Message -->
                <Transition name="fade">
                    <div
                        v-if="errors.general"
                        class="mb-8 bg-gradient-to-r from-error-50 to-error-100 border-2 border-error-300 rounded-xl p-4"
                        role="alert"
                    >
                        <div class="flex items-start gap-3">
                            <svg
                                class="w-6 h-6 text-error-600 flex-shrink-0 mt-0.5"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"
                                />
                            </svg>
                            <div>
                                <p class="text-error-800 font-bold">Error</p>
                                <p class="text-error-700 text-sm mt-1">
                                    {{ errors.general }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Form -->
                <form @submit.prevent="submitForm" class="space-y-8">
                    <!-- Section 1: Business Information -->
                    <fieldset class="space-y-6">
                        <div
                            class="flex items-center gap-3 pb-4 border-b-2 border-primary-100"
                        >
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-bold"
                            >
                                1
                            </div>
                            <legend class="text-2xl font-bold text-neutral-900">
                                Business Details
                            </legend>
                        </div>

                        <!-- Business Name -->
                        <div>
                            <label
                                for="business_name"
                                class="block text-sm font-bold text-neutral-800 mb-3"
                            >
                                Business Name
                                <span class="text-error-600">*</span>
                            </label>
                            <input
                                id="business_name"
                                v-model="form.business_name"
                                type="text"
                                placeholder="e.g., Juan's Coffee Shop"
                                class="w-full px-4 py-3 text-base border-2 border-neutral-200 rounded-xl focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-100 transition bg-neutral-50 hover:bg-white"
                                @blur="validateField('business_name')"
                                :aria-invalid="!!errors.business_name"
                                required
                            />
                            <p
                                v-if="errors.business_name"
                                class="text-error-600 text-sm mt-2 font-medium flex items-center gap-1"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M18.101 12.93a1 1 0 00-1.414-1.414L10 16.586l-6.687-6.687a1 1 0 00-1.414 1.414l8.1 8.1a1 1 0 001.414 0l8.1-8.1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                {{ errors.business_name[0] }}
                            </p>
                        </div>

                        <!-- Business Address -->
                        <div>
                            <label
                                for="business_address"
                                class="block text-sm font-bold text-neutral-800 mb-3"
                            >
                                Business Address
                                <span class="text-error-600">*</span>
                            </label>
                            <textarea
                                id="business_address"
                                v-model="form.business_address"
                                placeholder="Street address, building, or location"
                                class="w-full px-4 py-3 text-base border-2 border-neutral-200 rounded-xl focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-100 transition resize-none bg-neutral-50 hover:bg-white"
                                rows="3"
                                @blur="validateField('business_address')"
                                :aria-invalid="!!errors.business_address"
                                required
                            ></textarea>
                            <p
                                v-if="errors.business_address"
                                class="text-error-600 text-sm mt-2 font-medium flex items-center gap-1"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M18.101 12.93a1 1 0 00-1.414-1.414L10 16.586l-6.687-6.687a1 1 0 00-1.414 1.414l8.1 8.1a1 1 0 001.414 0l8.1-8.1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                {{ errors.business_address[0] }}
                            </p>
                        </div>

                        <!-- Business Permit (Optional) -->
                        <div>
                            <label
                                for="business_permit_number"
                                class="block text-sm font-bold text-neutral-800 mb-3"
                            >
                                Business Permit Number
                                <span
                                    class="text-neutral-500 text-xs font-normal"
                                    >(Optional)</span
                                >
                            </label>
                            <input
                                id="business_permit_number"
                                v-model="form.business_permit_number"
                                type="text"
                                placeholder="e.g., BIR123456789"
                                class="w-full px-4 py-3 text-base border-2 border-neutral-200 rounded-xl focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-100 transition bg-neutral-50 hover:bg-white"
                                @blur="validateField('business_permit_number')"
                                :aria-invalid="!!errors.business_permit_number"
                            />
                            <p
                                v-if="errors.business_permit_number"
                                class="text-error-600 text-sm mt-2 font-medium"
                            >
                                {{ errors.business_permit_number[0] }}
                            </p>
                        </div>
                    </fieldset>

                    <!-- Section 2: Owner Information -->
                    <fieldset class="space-y-6">
                        <div
                            class="flex items-center gap-3 pb-4 border-b-2 border-secondary-100"
                        >
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-br from-secondary-500 to-secondary-600 flex items-center justify-center text-white font-bold"
                            >
                                2
                            </div>
                            <legend class="text-2xl font-bold text-neutral-900">
                                Your Information
                            </legend>
                        </div>

                        <!-- Owner Name -->
                        <div>
                            <label
                                for="owner_name"
                                class="block text-sm font-bold text-neutral-800 mb-3"
                            >
                                Full Name <span class="text-error-600">*</span>
                            </label>
                            <input
                                id="owner_name"
                                v-model="form.owner_name"
                                type="text"
                                placeholder="e.g., Juan Dela Cruz"
                                class="w-full px-4 py-3 text-base border-2 border-neutral-200 rounded-xl focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-100 transition bg-neutral-50 hover:bg-white"
                                @blur="validateField('owner_name')"
                                :aria-invalid="!!errors.owner_name"
                                required
                            />
                            <p
                                v-if="errors.owner_name"
                                class="text-error-600 text-sm mt-2 font-medium"
                            >
                                {{ errors.owner_name[0] }}
                            </p>
                        </div>

                        <!-- Owner Email -->
                        <div>
                            <label
                                for="owner_email"
                                class="block text-sm font-bold text-neutral-800 mb-3"
                            >
                                Email Address
                                <span class="text-error-600">*</span>
                            </label>
                            <p class="text-neutral-600 text-sm mb-2">
                                We'll send your account details here
                            </p>
                            <input
                                id="owner_email"
                                v-model="form.owner_email"
                                type="email"
                                placeholder="juan@example.com"
                                class="w-full px-4 py-3 text-base border-2 border-neutral-200 rounded-xl focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-100 transition bg-neutral-50 hover:bg-white"
                                @blur="validateField('owner_email')"
                                :aria-invalid="!!errors.owner_email"
                                required
                            />
                            <p
                                v-if="errors.owner_email"
                                class="text-error-600 text-sm mt-2 font-medium"
                            >
                                {{ errors.owner_email[0] }}
                            </p>
                        </div>

                        <!-- Owner Phone -->
                        <div>
                            <label
                                for="owner_phone"
                                class="block text-sm font-bold text-neutral-800 mb-3"
                            >
                                Phone Number
                                <span class="text-error-600">*</span>
                            </label>
                            <input
                                id="owner_phone"
                                v-model="form.owner_phone"
                                type="tel"
                                placeholder="+63 9XX XXX XXXX"
                                class="w-full px-4 py-3 text-base border-2 border-neutral-200 rounded-xl focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-100 transition bg-neutral-50 hover:bg-white"
                                @blur="validateField('owner_phone')"
                                :aria-invalid="!!errors.owner_phone"
                                required
                            />
                            <p
                                v-if="errors.owner_phone"
                                class="text-error-600 text-sm mt-2 font-medium"
                            >
                                {{ errors.owner_phone[0] }}
                            </p>
                        </div>
                    </fieldset>

                    <!-- Section 3: Business Logo -->
                    <fieldset class="space-y-4">
                        <div
                            class="flex items-center gap-3 pb-4 border-b-2 border-tertiary-100"
                        >
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-br from-tertiary-500 to-tertiary-600 flex items-center justify-center text-white font-bold"
                            >
                                3
                            </div>
                            <legend class="text-2xl font-bold text-neutral-900">
                                Business Logo
                            </legend>
                        </div>

                        <p class="text-neutral-600 text-sm">
                            <span class="text-neutral-500 text-xs"
                                >(Optional)</span
                            >
                            Upload a logo to help us recognize your brand. JPG,
                            PNG, or GIF files up to 5MB.
                        </p>

                        <!-- Logo Upload Area -->
                        <div
                            class="relative border-4 border-dashed border-primary-300 rounded-2xl p-8 text-center hover:border-primary-500 hover:bg-primary-50 transition cursor-pointer bg-primary-50/30"
                            @click="triggerFileInput"
                            @dragover.prevent="isDragging = true"
                            @dragleave="isDragging = false"
                            @drop.prevent="handleDrop"
                            :class="{
                                'border-primary-500 bg-primary-100 scale-105':
                                    isDragging,
                            }"
                        >
                            <input
                                ref="logoInput"
                                type="file"
                                class="hidden"
                                accept="image/*"
                                @change="handleLogoChange"
                            />

                            <!-- Logo Preview -->
                            <div v-if="logoPreview" class="mb-4">
                                <img
                                    :src="logoPreview"
                                    alt="Logo preview"
                                    class="h-32 w-auto mx-auto rounded-xl shadow-lg"
                                />
                            </div>

                            <!-- Upload Icon & Text -->
                            <div v-if="!logoPreview" class="text-primary-600">
                                <svg
                                    class="w-16 h-16 mx-auto mb-4 text-primary-400 animate-bounce"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M12 4v16m8-8H4M12 4l-4 4m0 0l4-4l4 4"
                                    />
                                </svg>
                                <p class="text-lg font-bold text-neutral-800">
                                    Click to upload or drag and drop
                                </p>
                                <p class="text-sm text-neutral-500 mt-1">
                                    PNG, JPG, GIF up to 5MB
                                </p>
                            </div>

                            <!-- Change Logo Button -->
                            <button
                                v-if="logoPreview"
                                type="button"
                                class="mt-4 px-6 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg font-bold hover:shadow-lg transition transform hover:-translate-y-0.5"
                            >
                                Change Logo
                            </button>
                        </div>

                        <p
                            v-if="errors.logo"
                            class="text-error-600 text-sm font-medium"
                        >
                            {{ errors.logo[0] }}
                        </p>
                    </fieldset>

                    <!-- Acceptance Checkboxes -->
                    <div class="space-y-4">
                        <!-- Terms and Conditions -->
                        <div
                            class="flex items-start gap-3 p-4 rounded-xl border-2 border-neutral-200 hover:border-primary-300 hover:bg-primary-50/30 transition cursor-pointer"
                        >
                            <input
                                type="checkbox"
                                id="accept_terms"
                                v-model="acceptTerms"
                                class="mt-1.5 w-5 h-5 rounded-lg text-primary-600 focus:ring-2 focus:ring-primary-500 cursor-pointer flex-shrink-0"
                            />
                            <label
                                for="accept_terms"
                                class="cursor-pointer flex-1"
                            >
                                <p class="font-bold text-neutral-900">
                                    I agree to the Terms of Service
                                </p>
                                <p class="text-neutral-600 text-sm">
                                    I understand and accept the
                                    <a
                                        href="/terms-of-service"
                                        class="text-primary-600 font-bold hover:underline"
                                        target="_blank"
                                        >Terms of Service</a
                                    >
                                </p>
                            </label>
                        </div>
                        <p
                            v-if="errors.acceptTerms"
                            class="text-error-600 text-sm font-medium ml-8"
                        >
                            {{ errors.acceptTerms[0] }}
                        </p>

                        <!-- Privacy Policy -->
                        <div
                            class="flex items-start gap-3 p-4 rounded-xl border-2 border-neutral-200 hover:border-secondary-300 hover:bg-secondary-50/30 transition cursor-pointer"
                        >
                            <input
                                type="checkbox"
                                id="accept_privacy"
                                v-model="acceptPrivacy"
                                class="mt-1.5 w-5 h-5 rounded-lg text-secondary-600 focus:ring-2 focus:ring-secondary-500 cursor-pointer flex-shrink-0"
                            />
                            <label
                                for="accept_privacy"
                                class="cursor-pointer flex-1"
                            >
                                <p class="font-bold text-neutral-900">
                                    I agree to the Privacy Policy
                                </p>
                                <p class="text-neutral-600 text-sm">
                                    I have read and accept the
                                    <a
                                        href="/privacy-policy"
                                        class="text-secondary-600 font-bold hover:underline"
                                        target="_blank"
                                        >Privacy Policy</a
                                    >
                                    regarding how my information will be
                                    processed
                                </p>
                            </label>
                        </div>
                        <p
                            v-if="errors.acceptPrivacy"
                            class="text-error-600 text-sm font-medium ml-8"
                        >
                            {{ errors.acceptPrivacy[0] }}
                        </p>

                        <!-- Promotional Communications -->
                        <div
                            class="flex items-start gap-3 p-4 rounded-xl border-2 border-tertiary-200 hover:border-tertiary-300 hover:bg-tertiary-50/30 transition cursor-pointer"
                        >
                            <input
                                type="checkbox"
                                id="accept_promotions"
                                v-model="acceptPromotions"
                                class="mt-1.5 w-5 h-5 rounded-lg text-tertiary-600 focus:ring-2 focus:ring-tertiary-500 cursor-pointer flex-shrink-0"
                            />
                            <label
                                for="accept_promotions"
                                class="cursor-pointer flex-1"
                            >
                                <p class="font-bold text-neutral-900">
                                    I opt-in to receive promotional
                                    communications
                                </p>
                                <p class="text-neutral-600 text-sm">
                                    I consent to receive news, special offers,
                                    and promotions from {{ appName }} and our
                                    trusted partner businesses. I understand my
                                    email may be shared with carefully selected
                                    partners to deliver relevant promotions and
                                    offers. I can unsubscribe anytime.
                                </p>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 disabled:from-neutral-400 disabled:to-neutral-400 text-white font-bold py-4 rounded-xl text-lg transition duration-200 transform hover:shadow-xl hover:-translate-y-1 disabled:hover:shadow-none disabled:hover:translate-y-0 flex items-center justify-center gap-2"
                    >
                        <span v-if="!isSubmitting">Create My Account</span>
                        <span v-else class="flex items-center gap-2">
                            <svg
                                class="animate-spin h-5 w-5"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Creating Account...
                        </span>
                    </button>

                    <!-- Already a customer link -->
                    <p class="text-center text-neutral-700">
                        Already have a {{ appName }} account?
                        <a
                            href="/login"
                            class="text-primary-600 font-bold hover:text-primary-700 underline"
                            >Log in here</a
                        >
                    </p>
                </form>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-neutral-600 text-sm">
                <p>
                    Questions? Contact us at
                    <a
                        href="mailto:support@storepos.online"
                        class="text-primary-600 font-bold hover:underline"
                        >support@storepos.online</a
                    >
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from "vue";
import { useForm } from "@inertiajs/vue3";

const appName = ref(import.meta.env.VITE_APP_NAME || "QuickJuan POS");

const logoInput = ref<HTMLInputElement | null>(null);
const logoPreview = ref<string | null>(null);
const isDragging = ref(false);
const isSubmitting = ref(false);
const errors = reactive<Record<string, string[]>>({});

const acceptTerms = ref(false);
const acceptPrivacy = ref(false);
const acceptPromotions = ref(false);

const form = useForm({
    business_name: "",
    business_address: "",
    owner_name: "",
    owner_email: "",
    owner_phone: "",
    business_permit_number: "",
    logo: null as File | null,
    accept_terms: false,
    accept_privacy: false,
    accept_promotions: false,
});

const triggerFileInput = () => {
    logoInput.value?.click();
};

const handleLogoChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (file) {
        processLogoFile(file);
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    const file = event.dataTransfer?.files[0];
    if (file && file.type.startsWith("image/")) {
        processLogoFile(file);
    }
};

const processLogoFile = (file: File) => {
    form.logo = file;

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        logoPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);

    errors.logo = [];
};

const validateField = (field: string) => {
    // Real-time validation feedback
    if (field === "business_name" && !form.business_name.trim()) {
        errors[field] = ["Business name is required"];
    } else if (field === "business_address" && !form.business_address.trim()) {
        errors[field] = ["Business address is required"];
    } else if (field === "owner_name" && !form.owner_name.trim()) {
        errors[field] = ["Your name is required"];
    } else if (
        field === "owner_email" &&
        (!form.owner_email.trim() || !form.owner_email.includes("@"))
    ) {
        errors[field] = ["Valid email is required"];
    } else if (field === "owner_phone" && !form.owner_phone.trim()) {
        errors[field] = ["Phone number is required"];
    } else {
        delete errors[field];
    }
};

const validateAllFields = (): boolean => {
    // Validate all required fields
    validateField("business_name");
    validateField("business_address");
    validateField("owner_name");
    validateField("owner_email");
    validateField("owner_phone");

    // Validate checkboxes
    if (!acceptTerms.value) {
        errors.acceptTerms = ["You must accept the Terms of Service"];
    } else {
        delete errors.acceptTerms;
    }

    if (!acceptPrivacy.value) {
        errors.acceptPrivacy = ["You must accept the Privacy Policy"];
    } else {
        delete errors.acceptPrivacy;
    }

    // Return true if no errors
    return Object.keys(errors).length === 0;
};

const submitForm = async () => {
    // Don't submit if already submitting
    if (isSubmitting.value) return;

    // Validate all fields first
    if (!validateAllFields()) {
        errors.general = [
            "Please fill in all required fields and accept the terms.",
        ];
        return;
    }

    isSubmitting.value = true;

    // Update form with checkbox values before submission
    form.accept_terms = acceptTerms.value;
    form.accept_privacy = acceptPrivacy.value;
    form.accept_promotions = acceptPromotions.value;

    try {
        // Submit using Inertia with multipart/form-data for file upload
        form.post("/tenant/register", {
            forceFormData: true,
            onSuccess: () => {
                // Success - redirect handled by server
                isSubmitting.value = false;
            },
            onError: (pageErrors) => {
                // Merge server errors with client-side errors
                Object.entries(pageErrors).forEach(([key, value]) => {
                    errors[key] = Array.isArray(value) ? value : [value];
                });
                isSubmitting.value = false;
            },
        });
    } catch (error) {
        console.error("Form submission error:", error);
        errors.general = ["An unexpected error occurred. Please try again."];
        isSubmitting.value = false;
    }
};
</script>
