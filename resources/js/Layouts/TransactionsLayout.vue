<template>
    <div class="h-screen flex flex-col">
        <header class="bg-white border-b border-gray-200 shadow-lg">
            <div class="px-4 md:px-6 lg:px-8 py-3 md:py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button
                            @click="goBack"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-white"
                        >
                            <ArrowLeftIcon class="w-5 h-5" />
                            <span class="font-semibold"
                                >Back to Cashiering</span
                            >
                        </button>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="hidden md:block text-right">
                            <p class="text-xs text-gray-500">Branch</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ branchName }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                v-if="userAvatarUrl"
                                class="h-9 w-9 rounded-full overflow-hidden"
                            >
                                <img
                                    :src="userAvatarUrl"
                                    alt="User"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <div
                                v-else
                                class="h-9 w-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-semibold"
                            >
                                {{ userInitials }}
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">User</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ userName }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <slot name="header" />
                </div>
            </div>
        </header>
        <div class="flex flex-grow h-full flex-col">
            <slot />
        </div>
    </div>
</template>

<script setup lang="ts">
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { ArrowLeftIcon } from "@heroicons/vue/24/outline";
import { computed } from "vue";

const goBack = () => {
    window.history.back();
};

const page = usePage();
const userName = computed(() => (page.props as any)?.auth?.user?.name || "");
const userAvatarUrl = computed(
    () => (page.props as any)?.auth?.user?.profile_photo_url || ""
);
const userInitials = computed(() => {
    const n = userName.value || "U";
    const parts = n.split(" ");
    const first = parts[0]?.[0] || "U";
    const last = parts.length > 1 ? parts[parts.length - 1]?.[0] : "";
    return (first + (last || "")).toUpperCase();
});
const branchName = computed(
    () => (page.props as any)?.active_branch?.name || ""
);
</script>
