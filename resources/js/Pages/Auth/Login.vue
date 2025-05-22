<template>
    <AuthLayout title="Login">
        <form @submit.prevent="login" class=" flex flex-col gap-6">
            <h2 class="font-medium text-3xl">Login</h2>
            <div class="flex flex-col gap-4">
                <TextField id="email" v-model="form.email" label="Email" :error="form.errors.email" />
                <TextField type="password" id="password" v-model="form.password" label="Password" />
            </div>
            <PrimaryButton type="submit" :disabled="form.processing">
                Login
            </PrimaryButton>
            <Link class="underline text-secondary" :href="route('password.request')">
                Forgot Password?
            </Link>
        </form>
    </AuthLayout>
</template>

<script setup>

import TextField from "@/Components/TextField.vue";
import {useForm, Link} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const form = useForm({
    email: null,
    password: null,
})

const login = () => {
    form.post(route('login'), {
        onSuccess: () => {
            form.reset();
        },
    });
}
</script>

