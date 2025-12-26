<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import Sidebar from "@/Layouts/Sidebar.vue";

defineProps({
    title: String,
});

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="flex h-screen bg-gray-100">
            <Sidebar :logout="logout" />

            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Page Heading -->
                <header v-if="$slots.header" class="bg-white shadow">
                    <div
                        class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center"
                    >
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main
                    class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200"
                >
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
