<template>
    <Head :title="props.title" />
    <main>
        <div class="grid md:grid-cols-2 lg:grid-cols-5 h-screen">
            <div
                class="hidden md:block md:col-span-1 lg:col-span-3 py-40 md:px-20 lg:px-40"
                style="
                    background-image: url('/images/auth-image.jpg');
                    background-size: cover;
                    background-position: center;
                "
            >
                <h1 class="text-7xl font-medium text-white">QJPOS</h1>
            </div>
            <div
                class="md:col-span-1 lg:col-span-2 px-20 py-40 flex flex-col gap-6"
            >
                <slot></slot>
            </div>
        </div>
    </main>

    <Toast />
</template>

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { Toast } from "primevue";

const props = defineProps<{
    title?: string;
}>();
</script>
