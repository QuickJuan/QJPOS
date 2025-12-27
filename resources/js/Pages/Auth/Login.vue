<template>
    <AuthLayout title="Login">
        <div class="space-y-8">
            <!-- Header -->
            <div class="text-center space-y-2">
                <div class="flex justify-center mb-4">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl shadow-lg flex items-center justify-center"
                    >
                        <svg
                            class="w-8 h-8 text-white"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                d="M12 2L2 7V10C2 16 6 20.5 12 22C18 20.5 22 16 22 10V7L12 2ZM12 4.19L19 7.3V10C19 15.85 15.74 19.94 12 20.96C8.26 19.94 5 15.85 5 10V7.3L12 4.19ZM12 8C10.34 8 9 9.34 9 11S10.34 14 12 14S15 12.66 15 11S13.66 8 12 8ZM12 10C12.55 10 13 10.45 13 11S12.55 12 12 12S11 11.55 11 11S11.45 10 12 10Z"
                            />
                        </svg>
                    </div>
                </div>
                <h2
                    class="text-4xl font-bold bg-gradient-to-r from-neutral-900 to-neutral-700 bg-clip-text text-transparent"
                >
                    Welcome Back
                </h2>
                <p class="text-neutral-600 text-lg">
                    Sign in to your {{ companyName }} account
                </p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="login" class="space-y-5">
                <!-- Branch Selection -->
                <div class="space-y-2">
                    <SelectField
                        id="branch"
                        v-model="form.branch"
                        label="Select Branch"
                        :options="props.branches"
                        optionLabel="name"
                        optionValue="id"
                        :disabled="props.branches.length <= 1 ? true : false"
                        placeholder="Choose your branch location"
                        required
                        searchable
                        :error="form.errors.branch"
                    />
                </div>

                <!-- Email Field -->
                <div class="space-y-2">
                    <TextField
                        id="email"
                        v-model="form.email"
                        label="Email Address"
                        type="email"
                        placeholder="Enter your email address"
                        :error="form.errors.email"
                        required
                    />
                </div>

                <!-- Password Field -->
                <div class="space-y-2">
                    <PasswordField
                        id="password"
                        v-model="form.password"
                        label="Password"
                        placeholder="Enter your password"
                        required
                        :error="form.errors.password"
                    />
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <PrimaryButton
                        type="submit"
                        :disabled="
                            form.processing ||
                            // !form.branch ||
                            !form.email ||
                            !form.password
                        "
                        class="w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 shadow-lg"
                    >
                        <span
                            v-if="form.processing"
                            class="flex items-center justify-center"
                        >
                            <svg
                                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
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
                            Signing in...
                        </span>
                        <span v-else>Sign In</span>
                    </PrimaryButton>
                </div>

                <!-- Forgot Password Link -->
                <div class="text-center pt-2">
                    <a
                        :href="forgotPasswordUrl"
                        target="_blank"
                        class="text-sm text-primary-600 hover:text-primary-700 underline font-medium transition-colors duration-200"
                    >
                        Forgot your password?
                    </a>
                </div>
            </form>

            <!-- Footer -->
            <div
                class="text-center text-xs text-neutral-500 border-t border-neutral-200 pt-6"
            >
                <p>
                    &copy; {{ currentYear }} {{ companyName }}. All rights
                    reserved.
                </p>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup>
import TextField from "@/Components/Form/TextField.vue";
import PasswordField from "@/Components/Form/PasswordField.vue";
import { useForm, Link, router, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import SelectField from "@/Components/Form/SelectField.vue";
import { ref, watch, computed } from "vue";
import { useToast } from "primevue";

const page = usePage();
const toast = useToast();
const props = defineProps({
    branches: Object,
});

const currentYear = computed(() => new Date().getFullYear());

const companyName = computed(() => {
    return page.props.company_info?.company_name || "QJPOS";
});

// Get the central domain for Filament password reset
const centralDomain = computed(() => {
    // Get the central domain from config or use default
    const domain = import.meta.env.VITE_CENTRAL_DOMAIN || "storepos.online";
    return domain;
});

const forgotPasswordUrl = computed(() => {
    // Construct the Filament password reset URL
    const protocol = window.location.protocol;
    return `${protocol}//${centralDomain.value}/central/password/reset`;
});

const form = useForm({
    email: "",
    password: "",
    branch: props.branches.length <= 1 ? props.branches?.[0]?.id : [],
});

const login = () => {
    form.post(route("login"), {
        onSuccess: () => {
            if (page.props.flash.success) {
                toast.add({
                    severity: "success",
                    summary: "Welcome!",
                    detail: page.props.flash.success,
                    life: 3000,
                });
            }
            form.reset();
        },
        onError: () => {
            toast.add({
                severity: "error",
                summary: "Login Failed",
                detail:
                    page.props.flash.error ||
                    "Please check your credentials and try again.",
                life: 4000,
            });
        },
    });
};
</script>
