<template>
    <Head :title="props.title" />

    <GuestNav />
    <div class="w-full mx-auto pt-20 overflow-x-hidden">
        <slot />
    </div>

    <Toast />

    <!-- <Footer /> -->
</template>
<script setup lang="ts">
// import Footer from "@/Components/Footer.vue";
import GuestNav from "@/Components/GuestNav.vue";
import { Head } from "@inertiajs/vue3";
import { Toast } from "primevue";

const props = defineProps<{
    title?: string;
}>();
</script>
