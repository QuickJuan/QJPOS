<template>
    <!-- Navigation Bar -->
    <header
        :class="[
            'fixed top-0 left-0 w-full z-30 flex items-center justify-between px-6 py-4 transition-all duration-300',
            isSticky
                ? 'bg-white/95 shadow-md backdrop-blur-sm border-b border-gray-200'
                : 'bg-transparent shadow-none',
        ]"
    >
        <div class="flex items-center gap-2">
            <QuickJuanIcon
                class="w-10 h-10 md:w-12 md:h-12"
                aria-label="QuickJuan POS Logo"
            />
            <span
                :class="
                    isSticky
                        ? 'text-2xl font-bold text-gray-900 tracking-tight hidden sm:inline'
                        : 'text-2xl font-bold text-primary tracking-tight hidden sm:inline'
                "
            >
                QuickJuan POS
            </span>
        </div>
        <!-- Mobile Burger Icon -->
        <button
            class="md:hidden flex items-center justify-center p-2 rounded focus:outline-none focus:ring-2 focus:ring-primary"
            @click="mobileNavOpen = !mobileNavOpen"
            aria-label="Open navigation menu"
        >
            <svg
                v-if="!mobileNavOpen"
                class="w-7 h-7 text-primary"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M4 6h16M4 12h16M4 18h16"
                />
            </svg>
            <svg
                v-else
                class="w-7 h-7 text-primary"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
        <!-- Desktop Nav -->
        <nav class="hidden md:block">
            <ul class="flex gap-6 text-lg font-semibold">
                <li>
                    <Link
                        href="/tenant/register"
                        :class="
                            isSticky
                                ? 'text-gray-900 hover:text-primary bg-primary text-white px-4 py-2 rounded-lg font-semibold'
                                : 'text-primary hover:text-third'
                        "
                        class="bg-blue-100 p-2 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >Start for FREE</Link
                    >
                </li>
                <li>
                    <a
                        href="#products"
                        :class="
                            isSticky
                                ? 'text-gray-700 hover:text-primary'
                                : 'text-primary hover:text-third'
                        "
                        class="transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >Products</a
                    >
                </li>
                <li>
                    <a
                        href="#contact"
                        :class="
                            isSticky
                                ? 'text-gray-700 hover:text-primary'
                                : 'text-primary hover:text-third'
                        "
                        class="transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >Contact Us</a
                    >
                </li>
                <!-- <li>
                    <Link
                        :href="
                            $page.props.auth.user
                                ? route('home')
                                : route('login')
                        "
                        :class="
                            isSticky
                                ? 'text-gray-700 hover:text-primary'
                                : 'text-primary hover:text-third'
                        "
                        class="transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        {{
                            $page.props.auth.user
                                ? `Hi, ${$page.props.auth.user.name}`
                                : "Sign in"
                        }}
                    </Link>
                </li> -->
            </ul>
        </nav>
        <!-- Mobile Nav Dropdown -->
        <transition name="fade">
            <nav
                v-if="mobileNavOpen"
                class="absolute top-full left-0 w-full bg-white shadow-lg border-b border-gray-200 flex flex-col items-center py-4 md:hidden z-40"
            >
                <ul
                    class="flex flex-col gap-4 text-lg font-semibold w-full text-center"
                >
                    <li>
                        <Link
                            :href="route('tenant-registration')"
                            class="block py-2 text-secondary hover:text-primary transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            @click="mobileNavOpen = false"
                        >
                            Try for FREE
                        </Link>
                    </li>
                    <li>
                        <a
                            href="#products"
                            class="block py-2 text-secondary hover:text-primary transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            @click="mobileNavOpen = false"
                        >
                            Products
                        </a>
                    </li>
                    <li>
                        <a
                            href="#contact"
                            class="block py-2 text-secondary hover:text-primary transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            @click="mobileNavOpen = false"
                        >
                            Contact Us
                        </a>
                    </li>
                    <!-- <li>
                        <Link
                            :href="
                                $page.props.auth.user
                                    ? route('home')
                                    : route('login')
                            "
                            class="block py-2 text-secondary hover:text-primary transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            @click="mobileNavOpen = false"
                        >
                            {{
                                $page.props.auth.user
                                    ? `Hi, ${$page.props.auth.user.name}`
                                    : "Sign in"
                            }}
                        </Link>
                    </li> -->
                </ul>
            </nav>
        </transition>
    </header>
</template>
<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import QuickJuanIcon from "./QuickJuanIcon.vue";
import { ref, onMounted, onUnmounted } from "vue";
import { route } from "ziggy-js";

const isSticky = ref(false);
const mobileNavOpen = ref(false);

function handleScroll() {
    isSticky.value = window.scrollY > 40;
}

onMounted(() => {
    window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
});
</script>
