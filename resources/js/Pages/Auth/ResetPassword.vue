<template>
    <AuthLayout title="Reset Password">
        <form @submit.prevent="submit" class="w-full max-w-lg flex flex-col gap-4">
            <TextField id="email" v-model="form.email" label="Email" :error="form.errors.email" />
            <TextField type="password" id="password" v-model="form.password" label="Password" :error="form.errors.password" />
            <TextField type="password" id="password_confirmation" v-model="form.password_confirmation" label="Confirm Password" />
            <div class="flex items-start justify-between mt-4">
                <Link :href="route('login')" class="underline flex gap-2">
                    <BackIcon />
                    <span>Return to Login</span>
                </Link>
                <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Reset Password
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>

<script setup lang="ts">
import {Link, useForm} from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextField from "@/Components/TextField.vue";
import BackIcon from "@/Components/icons/BackIcon.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const props = defineProps<{
    email: string,
    token: string,
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {

    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
