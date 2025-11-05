<template>
    <AuthLayout title="Login">
        <div class="space-y-8">
            <!-- Header -->
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                    Welcome Back
                </h2>
                <p class="text-gray-600">Sign in to your QJPOS account</p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="login" class="space-y-6">
                <!-- Branch Selection -->
                <div class="space-y-1">
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
                <div class="space-y-1">
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
                <div class="space-y-1">
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
                <div class="text-center pt-4">
                    <Link
                        class="text-sm text-primary hover:text-primary-light underline font-medium transition-colors duration-200"
                        :href="route('password.request')"
                    >
                        Forgot your password?
                    </Link>
                </div>
            </form>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500 border-t pt-6">
                <p>&copy; {{ currentYear }} QJPOS. All rights reserved.</p>
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
