<template>
    <AuthLayout title="Login">
        <form @submit.prevent="login" class="flex flex-col gap-6">
            <h2 class="font-medium text-3xl">Login</h2>
            <div class="flex flex-col gap-4">
                <SelectField
                    id="branch"
                    v-model="form.branch"
                    label="Branch"
                    :options="props.branches"
                    optionLabel="name"
                    optionValue="id"
                    required
                    :error="form.errors.branch"
                />

                <TextField
                    id="email"
                    v-model="form.email"
                    label="Email"
                    :error="form.errors.email"
                    required
                />

                <TextField
                    type="password"
                    id="password"
                    v-model="form.password"
                    label="Password"
                    required
                />
            </div>
            <PrimaryButton type="submit" :disabled="form.processing">
                Login
            </PrimaryButton>
            <Link
                class="underline text-secondary"
                :href="route('password.request')"
            >
                Forgot Password?
            </Link>
        </form>
    </AuthLayout>
</template>

<script setup>
import TextField from "@/Components/TextField.vue";
import { useForm, Link, router, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import SelectField from "@/Components/SelectField.vue";
import { ref, watch } from "vue";
import { useToast } from "primevue";

const page = usePage();
const toast = useToast();
const props = defineProps({
    branches: Object,
});

const form = useForm({
    email: null,
    password: null,
    branch: null,
});

const login = () => {
    form.post(route("login"), {
        onSuccess: () => {
            toast.add({
                severity: "success",
                summary: "Success",
                detail: page.props.flash.success,
                life: 3000,
            });
            form.reset();
        },
        onError: () => {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: page.props.flash.error,
                life: 3000,
            });
        },
    });
};
</script>
