<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header Navigation -->
        <header class="bg-white shadow-sm sticky top-0 z-50">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo/Brand -->
                    <div class="flex items-center">
                        <Link href="/" class="flex items-center space-x-3">
                            <img
                                v-if="companyLogo"
                                :src="companyLogo"
                                :alt="appName"
                                class="h-10 w-auto"
                            />
                            <span
                                class="text-2xl font-bold text-orange-600"
                                :class="{ 'sr-only': companyLogo }"
                            >
                                {{ appName }}
                            </span>
                        </Link>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center space-x-8">
                        <template v-for="item in navigation" :key="item.id">
                            <!-- Dropdown Menu -->
                            <div
                                v-if="item.children && item.children.length > 0"
                                class="relative group"
                            >
                                <button
                                    class="flex items-center text-gray-700 hover:text-orange-600 transition-colors"
                                >
                                    {{ item.label }}
                                    <svg
                                        class="ml-1 w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                </button>
                                <div
                                    class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200"
                                >
                                    <Link
                                        v-for="child in item.children"
                                        :key="child.id"
                                        :href="child.url"
                                        :target="child.target"
                                        class="block px-4 py-2 text-gray-700 hover:bg-orange-50 hover:text-orange-600"
                                    >
                                        {{ child.label }}
                                    </Link>
                                </div>
                            </div>

                            <!-- Regular Link -->
                            <Link
                                v-else
                                :href="item.url"
                                :target="item.target"
                                class="text-gray-700 hover:text-orange-600 transition-colors"
                            >
                                {{ item.label }}
                            </Link>
                        </template>

                        <Link
                            :href="route('guest.cart')"
                            class="relative inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-700 transition hover:border-orange-300 hover:bg-orange-100"
                        >
                            Cart
                            <span
                                v-if="guestCartCount > 0"
                                class="ml-2 inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-orange-600 px-1.5 text-xs font-bold text-white"
                            >
                                {{ guestCartCount }}
                            </span>
                        </Link>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="md:hidden flex items-center">
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="text-gray-700 hover:text-orange-600"
                        >
                            <svg
                                class="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    v-if="!mobileMenuOpen"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    v-else
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div v-show="mobileMenuOpen" class="md:hidden py-4 border-t">
                    <div
                        v-for="item in navigation"
                        :key="item.id"
                        class="space-y-2"
                    >
                        <Link
                            :href="item.url"
                            :target="item.target"
                            class="block px-4 py-2 text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded"
                        >
                            {{ item.label }}
                        </Link>
                        <Link
                            v-for="child in item.children"
                            :key="child.id"
                            :href="child.url"
                            :target="child.target"
                            class="block px-8 py-2 text-sm text-gray-600 hover:bg-orange-50 hover:text-orange-600 rounded"
                        >
                            {{ child.label }}
                        </Link>
                    </div>
                    <Link
                        :href="route('guest.cart')"
                        class="mt-3 block rounded-xl bg-orange-50 px-4 py-3 font-semibold text-orange-700"
                    >
                        Cart
                        <span v-if="guestCartCount > 0">({{ guestCartCount }})</span>
                    </Link>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-xl font-bold mb-4">{{ appName }}</h3>
                        <p class="text-gray-400">
                            Building amazing experiences
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Quick Links</h4>
                        <ul class="space-y-2">
                            <li
                                v-for="item in navigation.slice(0, 5)"
                                :key="item.id"
                            >
                                <Link
                                    :href="item.url"
                                    class="text-gray-400 hover:text-white transition-colors"
                                >
                                    {{ item.label }}
                                </Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Contact</h4>
                        <p class="text-gray-400">Email: info@example.com</p>
                        <p class="text-gray-400">Phone: (123) 456-7890</p>
                    </div>
                </div>
                <div
                    class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400"
                >
                    <p>
                        &copy; {{ new Date().getFullYear() }} {{ appName }}. All
                        rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import { useGuestCartStore } from "@/stores/guestCartStore";

defineProps({
    navigation: {
        type: Array,
        default: () => [],
    },
    appName: {
        type: String,
        default: "QuickJuan POS",
    },
    companyLogo: {
        type: String,
        default: null,
    },
});

const mobileMenuOpen = ref(false);
const guestCartStore = useGuestCartStore();
const guestCartCount = computed(() => guestCartStore.totalItems);
</script>
