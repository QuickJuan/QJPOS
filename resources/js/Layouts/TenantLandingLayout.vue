<template>
    <Head :title="props.title ?? 'Tenant Portal'" />

    <div class="relative min-h-screen text-white">
        <header
            class="sticky top-0 z-40 border-b border-white/5 bg-slate-950/95 px-4 backdrop-blur sm:px-6"
        >
            <div
                class="mx-auto flex max-w-6xl items-center justify-between py-4"
            >
                <div class="flex items-center gap-3">
                    <span class="text-xl font-black tracking-tight"
                        >QuickJuan</span
                    >
                    <span
                        class="text-xs uppercase tracking-[0.4em] text-slate-400"
                        >Tenant Portal</span
                    >
                </div>
                <nav
                    class="hidden items-center gap-6 text-sm font-semibold lg:flex"
                >
                    <a href="#menu" class="transition hover:text-white/80"
                        >Menu</a
                    >
                    <a href="#features" class="transition hover:text-white/80"
                        >Features</a
                    >
                    <a href="#stats" class="transition hover:text-white/80"
                        >Stats</a
                    >
                    <a href="#cta" class="transition hover:text-white/80"
                        >Ready</a
                    >
                </nav>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('tenant-registration')"
                        class="hidden rounded-full border border-white/30 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white/90 transition hover:border-white sm:inline-flex"
                    >
                        Add branch
                    </Link>
                    <Link
                        :href="route('login')"
                        class="rounded-full bg-white/10 px-5 py-2 text-sm font-semibold text-white transition hover:bg-white/20"
                    >
                        Login
                    </Link>
                </div>
            </div>
        </header>

        <main class="pt-0">
            <slot />
        </main>
    </div>

    <Toast />
</template>

<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Toast } from "primevue";

const props = defineProps<{
    title?: string;
}>();
</script>
