<template>
    <AuthLayout title="Forgot Password">
        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <TextField
                id="email"
                v-model="form.email"
                label="Email"
                :error="form.errors.email"
            />

            <div class="flex items-start justify-between mt-4">
                <Link
                    :href="route('tenant.login')"
                    class="underline flex gap-2"
                >
                    <BackIcon />
                    <span>Return to Login</span>
                </Link>
                <PrimaryButton
                    type="submit"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Email Password Reset Link
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextField from "@/Components/TextField.vue";
import BackIcon from "@/Components/icons/BackIcon.vue";
import Swal from "sweetalert2";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const props = defineProps<{
    status: string;
}>();

const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    },
});

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"), {
        onSuccess: () => {
            Toast.fire({
                icon: "success",
                title: "Email sent successfully",
            });
        },
    });
};
</script>
