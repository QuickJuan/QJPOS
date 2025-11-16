<template>
    <div
        class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-8 px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-2xl w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-3">
                    Welcome to StorePOS
                </h1>
                <p class="text-lg text-gray-600">
                    Let's get your business set up with our powerful POS system
                </p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8 sm:p-10">
                <!-- Form -->
                <form @submit.prevent="submitForm" class="space-y-8">
                    <!-- Error Message -->
                    <div
                        v-if="errors.general"
                        class="bg-red-50 border-2 border-red-200 rounded-lg p-4"
                        role="alert"
                    >
                        <p class="text-red-800 font-semibold">
                            ⚠️ {{ errors.general }}
                        </p>
                    </div>

                    <!-- Section 1: Business Information -->
                    <fieldset class="space-y-6">
                        <legend
                            class="text-2xl font-bold text-gray-900 pb-4 border-b-2 border-indigo-200"
                        >
                            Business Information
                        </legend>

                        <!-- Business Name -->
                        <div>
                            <label
                                for="business_name"
                                class="block text-base font-semibold text-gray-800 mb-2"
                            >
                                Business Name
                                <span
                                    class="text-red-600 ml-1"
                                    aria-label="required"
                                    >*</span
                                >
                            </label>
                            <input
                                id="business_name"
                                v-model="form.business_name"
                                type="text"
                                placeholder="e.g., Juan's Coffee Shop"
                                class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                @blur="validateField('business_name')"
                                :aria-invalid="!!errors.business_name"
                                :aria-describedby="
                                    errors.business_name
                                        ? 'business_name_error'
                                        : null
                                "
                                required
                            />
                            <p
                                v-if="errors.business_name"
                                id="business_name_error"
                                class="text-red-600 text-sm mt-2 font-medium"
                            >
                                {{ errors.business_name[0] }}
                            </p>
                        </div>

                        <!-- Business Address -->
                        <div>
                            <label
                                for="business_address"
                                class="block text-base font-semibold text-gray-800 mb-2"
                            >
                                Business Address
                                <span
                                    class="text-red-600 ml-1"
                                    aria-label="required"
                                    >*</span
                                >
                            </label>
                            <textarea
                                id="business_address"
                                v-model="form.business_address"
                                placeholder="Street address, building, or location"
                                class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition resize-none"
                                rows="3"
                                @blur="validateField('business_address')"
                                :aria-invalid="!!errors.business_address"
                                :aria-describedby="
                                    errors.business_address
                                        ? 'business_address_error'
                                        : null
                                "
                                required
                            ></textarea>
                            <p
                                v-if="errors.business_address"
                                id="business_address_error"
                                class="text-red-600 text-sm mt-2 font-medium"
                            >
                                {{ errors.business_address[0] }}
                            </p>
                        </div>

                        <!-- Business Permit (Optional) -->
                        <div>
                            <label
                                for="business_permit_number"
                                class="block text-base font-semibold text-gray-800 mb-2"
                            >
                                Business Permit Number
                                <span class="text-gray-500 text-sm font-normal"
                                    >(Optional)</span
                                >
                            </label>
                            <input
                                id="business_permit_number"
                                v-model="form.business_permit_number"
                                type="text"
                                placeholder="e.g., BIR123456789"
                                class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                @blur="validateField('business_permit_number')"
                                :aria-invalid="!!errors.business_permit_number"
                                :aria-describedby="
                                    errors.business_permit_number
                                        ? 'business_permit_number_error'
                                        : null
                                "
                            />
                            <p
                                v-if="errors.business_permit_number"
                                id="business_permit_number_error"
                                class="text-red-600 text-sm mt-2 font-medium"
                            >
                                {{ errors.business_permit_number[0] }}
                            </p>
                        </div>
                    </fieldset>

                    <!-- Section 2: Owner Information -->
                    <fieldset class="space-y-6">
                        <legend
                            class="text-2xl font-bold text-gray-900 pb-4 border-b-2 border-indigo-200"
                        >
                            Owner Information
                        </legend>

                        <!-- Owner Name -->
                        <div>
                            <label
                                for="owner_name"
                                class="block text-base font-semibold text-gray-800 mb-2"
                            >
                                Your Full Name
                                <span
                                    class="text-red-600 ml-1"
                                    aria-label="required"
                                    >*</span
                                >
                            </label>
                            <input
                                id="owner_name"
                                v-model="form.owner_name"
                                type="text"
                                placeholder="e.g., Juan Dela Cruz"
                                class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                @blur="validateField('owner_name')"
                                :aria-invalid="!!errors.owner_name"
                                :aria-describedby="
                                    errors.owner_name
                                        ? 'owner_name_error'
                                        : null
                                "
                                required
                            />
                            <p
                                v-if="errors.owner_name"
                                id="owner_name_error"
                                class="text-red-600 text-sm mt-2 font-medium"
                            >
                                {{ errors.owner_name[0] }}
                            </p>
                        </div>

                        <!-- Owner Email -->
                        <div>
                            <label
                                for="owner_email"
                                class="block text-base font-semibold text-gray-800 mb-2"
                            >
                                Your Email Address
                                <span
                                    class="text-red-600 ml-1"
                                    aria-label="required"
                                    >*</span
                                >
                            </label>
                            <p class="text-gray-600 text-sm mb-2">
                                We'll send updates about your application here
                            </p>
                            <input
                                id="owner_email"
                                v-model="form.owner_email"
                                type="email"
                                placeholder="juan@example.com"
                                class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                @blur="validateField('owner_email')"
                                :aria-invalid="!!errors.owner_email"
                                :aria-describedby="
                                    errors.owner_email
                                        ? 'owner_email_error'
                                        : null
                                "
                                required
                            />
                            <p
                                v-if="errors.owner_email"
                                id="owner_email_error"
                                class="text-red-600 text-sm mt-2 font-medium"
                            >
                                {{ errors.owner_email[0] }}
                            </p>
                        </div>

                        <!-- Owner Phone -->
                        <div>
                            <label
                                for="owner_phone"
                                class="block text-base font-semibold text-gray-800 mb-2"
                            >
                                Your Phone Number
                                <span
                                    class="text-red-600 ml-1"
                                    aria-label="required"
                                    >*</span
                                >
                            </label>
                            <input
                                id="owner_phone"
                                v-model="form.owner_phone"
                                type="tel"
                                placeholder="+63 9XX XXX XXXX"
                                class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                @blur="validateField('owner_phone')"
                                :aria-invalid="!!errors.owner_phone"
                                :aria-describedby="
                                    errors.owner_phone
                                        ? 'owner_phone_error'
                                        : null
                                "
                                required
                            />
                            <p
                                v-if="errors.owner_phone"
                                id="owner_phone_error"
                                class="text-red-600 text-sm mt-2 font-medium"
                            >
                                {{ errors.owner_phone[0] }}
                            </p>
                        </div>
                    </fieldset>

                    <!-- Section 3: Business Logo -->
                    <fieldset class="space-y-4">
                        <legend
                            class="text-2xl font-bold text-gray-900 pb-4 border-b-2 border-indigo-200"
                        >
                            Business Logo
                        </legend>

                        <p class="text-gray-600 text-base">
                            <span class="text-gray-500 text-sm"
                                >(Optional)</span
                            >
                            Upload a logo to help us recognize your brand. JPG,
                            PNG, or GIF files up to 5MB.
                        </p>

                        <!-- Logo Upload Area -->
                        <div
                            class="relative border-4 border-dashed border-indigo-300 rounded-xl p-8 text-center hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer bg-indigo-50/50"
                            @click="triggerFileInput"
                            @dragover.prevent="isDragging = true"
                            @dragleave="isDragging = false"
                            @drop.prevent="handleDrop"
                            :class="{
                                'border-indigo-500 bg-indigo-100': isDragging,
                            }"
                        >
                            <input
                                ref="logoInput"
                                type="file"
                                class="hidden"
                                accept="image/*"
                                @change="handleLogoChange"
                                :aria-label="
                                    logoPreview
                                        ? 'Change business logo'
                                        : 'Upload business logo'
                                "
                            />

                            <!-- Logo Preview -->
                            <div v-if="logoPreview" class="mb-4">
                                <img
                                    :src="logoPreview"
                                    alt="Logo preview"
                                    class="h-32 w-auto mx-auto rounded-lg shadow-md"
                                />
                            </div>

                            <!-- Upload Icon & Text -->
                            <div v-if="!logoPreview" class="text-indigo-600">
                                <svg
                                    class="w-16 h-16 mx-auto mb-4 text-indigo-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                                <p class="text-lg font-semibold text-gray-800">
                                    Click to upload or drag and drop
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    PNG, JPG, GIF up to 5MB
                                </p>
                            </div>

                            <!-- Change Logo Button -->
                            <button
                                v-if="logoPreview"
                                type="button"
                                class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition"
                            >
                                Change Logo
                            </button>
                        </div>

                        <p
                            v-if="errors.logo"
                            id="logo_error"
                            class="text-red-600 text-sm font-medium"
                        >
                            {{ errors.logo[0] }}
                        </p>
                    </fieldset>

                    <!-- Privacy Notice -->
                    <div
                        class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 text-sm text-gray-700"
                    >
                        <p class="font-semibold text-blue-900 mb-2">
                            🔒 Your Information is Safe
                        </p>
                        <p>
                            We protect your information with industry-standard
                            security. By submitting this form, you agree to our
                            <a
                                href="/terms-of-service"
                                class="text-blue-600 underline font-semibold hover:text-blue-800"
                                >Terms of Service</a
                            >
                            and
                            <a
                                href="/privacy-policy"
                                class="text-blue-600 underline font-semibold hover:text-blue-800"
                                >Privacy Policy</a
                            >.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-bold py-4 rounded-lg text-lg transition duration-200 flex items-center justify-center gap-2"
                    >
                        <span v-if="!isSubmitting">Submit Application</span>
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
                            Submitting...
                        </span>
                    </button>

                    <!-- Already a customer link -->
                    <p class="text-center text-gray-600">
                        Already have a StorePos account?
                        <a
                            href="/login"
                            class="text-indigo-600 font-semibold hover:text-indigo-700 underline"
                            >Log in here</a
                        >
                    </p>
                </form>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-600 text-sm">
                <p>
                    Questions? Contact us at
                    <a
                        href="mailto:support@storepos.online"
                        class="text-indigo-600 font-semibold hover:underline"
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

const logoInput = ref<HTMLInputElement | null>(null);
const logoPreview = ref<string | null>(null);
const isDragging = ref(false);
const isSubmitting = ref(false);
const errors = reactive<Record<string, string[]>>({});

const form = useForm({
    business_name: "",
    business_address: "",
    owner_name: "",
    owner_email: "",
    owner_phone: "",
    business_permit_number: "",
    logo: null as File | null,
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

    // Return true if no errors
    return Object.keys(errors).length === 0;
};

const submitForm = async () => {
    // Don't submit if already submitting
    if (isSubmitting.value) return;

    // Validate all fields first
    if (!validateAllFields()) {
        return;
    }

    isSubmitting.value = true;

    try {
        // Submit using Inertia
        form.post("/tenant/register", {
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
