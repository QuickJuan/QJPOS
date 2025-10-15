<template>
    <header class="h-16 bg-white shadow flex items-center px-8 justify-between">
        <div class="text-xl font-semibold text-gray-800">Cashier</div>
        <div class="flex items-center gap-4">
            <span class="text-gray-600">Shift #12</span>
            <button
                type="button"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                @click="logout"
            >
                Logout
            </button>
        </div>
    </header>
</template>

<script setup lang="ts">
import { route } from "ziggy-js";
import { router } from "@inertiajs/vue3";

const logout = () => {
    router.post(route("logout"));
};
</script>
