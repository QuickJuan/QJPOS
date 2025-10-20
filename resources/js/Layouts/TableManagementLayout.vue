<template>
    <Head :title="props.title" />
    <div class="h-screen bg-gray-50 flex flex-col">
        <!-- Navigation Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-8">
                        <h1 class="text-xl font-semibold text-gray-900">
                            Table Management
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-600">
                            {{ page.props.auth?.user.name }}
                        </div>
                        <Link
                            :href="route('table-rooms.list')"
                            class="text-gray-600 hover:text-gray-900"
                        >
                            <i class="pi pi-home"></i>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-auto">
            <slot />
        </main>
    </div>

    <Toast />
    <ConfirmPopup />
</template>

<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import { ConfirmPopup, Toast } from "primevue";
import { route } from "ziggy-js";

const page = usePage();
const props = defineProps<{
    title?: string;
}>();
</script>
