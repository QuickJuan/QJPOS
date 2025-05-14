<template>
    <div
        class="relative min-h-screen flex flex-col items-stretch justify-center overflow-hidden"
    >
        <!-- Navigation Bar -->
        <header
            :class="[
                'fixed top-0 left-0 w-full z-30 flex items-center justify-between px-6 py-4 transition-all duration-300',
                isSticky
                    ? 'bg-white/40 shadow-lg backdrop-blur border-b border-gray-200'
                    : 'bg-gray-600/10 backdrop-blur',
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
                            ? 'text-2xl font-bold text-amber-400 tracking-tight hidden sm:inline'
                            : 'text-2xl font-bold text-amber-400 tracking-tight hidden sm:inline'
                    "
                    >QuickJuan POS</span
                >
            </div>
            <!-- Mobile Burger Icon -->
            <button
                class="md:hidden flex items-center justify-center p-2 rounded focus:outline-none focus:ring-2 focus:ring-amber-400"
                @click="mobileNavOpen = !mobileNavOpen"
                aria-label="Open navigation menu"
            >
                <svg
                    v-if="!mobileNavOpen"
                    class="w-7 h-7 text-amber-400"
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
                    class="w-7 h-7 text-amber-400"
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
                        <a
                            href="#get-started"
                            :class="
                                isSticky
                                    ? 'text-gray-900 hover:text-amber-400'
                                    : 'text-white hover:text-amber-400'
                            "
                            class="transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
                            >Try for FREE</a
                        >
                    </li>
                    <li>
                        <a
                            href="#products"
                            :class="
                                isSticky
                                    ? 'text-gray-900 hover:text-amber-400'
                                    : 'text-white hover:text-amber-400'
                            "
                            class="transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
                            >Products</a
                        >
                    </li>
                    <li>
                        <a
                            href="#contact"
                            :class="
                                isSticky
                                    ? 'text-gray-900 hover:text-amber-400'
                                    : 'text-white hover:text-amber-400'
                            "
                            class="transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
                            >Contact Us</a
                        >
                    </li>
                </ul>
            </nav>
            <!-- Mobile Nav Dropdown -->
            <transition name="fade">
                <nav
                    v-if="mobileNavOpen"
                    class="absolute top-full left-0 w-full bg-white/95 shadow-lg border-b border-gray-200 flex flex-col items-center py-4 md:hidden z-40"
                >
                    <ul
                        class="flex flex-col gap-4 text-lg font-semibold w-full text-center"
                    >
                        <li>
                            <a
                                href="#get-started"
                                class="block py-2 text-gray-900 hover:text-amber-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
                                @click="mobileNavOpen = false"
                                >Try for FREE</a
                            >
                        </li>
                        <li>
                            <a
                                href="#products"
                                class="block py-2 text-gray-900 hover:text-amber-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
                                @click="mobileNavOpen = false"
                                >Products</a
                            >
                        </li>
                        <li>
                            <a
                                href="#contact"
                                class="block py-2 text-gray-900 hover:text-amber-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
                                @click="mobileNavOpen = false"
                                >Contact Us</a
                            >
                        </li>
                    </ul>
                </nav>
            </transition>
        </header>

        <!-- Background Media: Video or Image (full background) -->
        <div
            class="fixed inset-0 z-0 w-full h-full overflow-hidden pointer-events-none"
            aria-label="Restaurant video background"
        >
            <transition name="fade" mode="out-in">
                <video
                    v-if="showVideo && !isMobile"
                    key="video"
                    class="w-full h-full object-cover min-h-screen"
                    autoplay
                    muted
                    loop
                    playsinline
                    preload="auto"
                    :aria-label="videoTitle"
                >
                    <source src="/videos/landingvideo.mov" type="video/mp4" />
                    Your browser does not support the video tag.
                </video>
                <div v-else class="w-full h-screen">
                    <img
                        key="image"
                        :src="posImages[currentImage]"
                        class="w-full h-full object-cover object-top"
                        alt="POS Machine"
                    />
                </div>
            </transition>
            <div class="absolute inset-0 bg-black bg-opacity-60"></div>
            <!-- Swap Button (mobile only) -->
            <button
                v-if="isMobile"
                @click="toggleMedia"
                class="absolute top-4 right-4 z-20 px-4 py-2 rounded-lg bg-amber-400 text-gray-900 font-bold shadow hover:bg-amber-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
                aria-label="Toggle between video and POS images"
            >
                {{ showVideo ? "Show POS Images" : "Show Video" }}
            </button>
            <!-- Image navigation (mobile only) -->
            <div
                v-if="!showVideo && isMobile"
                class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-20"
            >
                <button
                    v-for="(img, idx) in posImages"
                    :key="img"
                    @click="currentImage = idx"
                    :aria-label="'Show image ' + (idx + 1)"
                    :class="[
                        'w-4 h-4 rounded-full border-2 border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400',
                        idx === currentImage
                            ? 'bg-amber-400'
                            : 'bg-white opacity-60',
                    ]"
                ></button>
            </div>
        </div>

        <!-- Content -->
        <div
            class="relative z-10 flex flex-col items-center justify-end md:justify-center text-center px-6 py-8 md:py-0 w-full min-h-screen bg-gradient-to-t md:bg-gradient-to-r from-black/80 via-black/60 to-transparent"
            style="padding-top: 6rem"
            aria-labelledby="landing-title"
        >
            <h1
                id="landing-title"
                class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-white drop-shadow-lg mb-4 font-sans tracking-tight"
            >
                Welcome to <span class="text-amber-400">QuickJuan POS</span>
            </h1>
            <p
                class="text-lg sm:text-xl md:text-2xl text-white mb-8 max-w-2xl font-light drop-shadow"
            >
                The modern, multi-tenant Point of Sale platform for retail,
                restaurants, and fast food.
                <span class="font-semibold text-amber-300"
                    >Manage your business, inventory, and sales with ease.</span
                >
            </p>
            <button
                @click="scrollToGetStarted"
                class="px-8 py-4 rounded-lg bg-amber-400 text-gray-900 font-bold text-xl shadow-lg hover:bg-amber-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 mb-8"
                aria-label="Get Started Free"
            >
                Get Started Free
            </button>
        </div>
        <!-- Products Section -->
        <section
            id="products"
            class="relative z-20 w-full bg-white py-16 px-0 flex flex-col items-center justify-center text-center overflow-hidden"
        >
            <h2
                class="text-3xl md:text-4xl font-bold text-gray-900 mb-10 z-10 relative"
            >
                Our Products
            </h2>
            <div
                class="relative w-full max-w-6xl mx-auto flex flex-col md:flex-row gap-6 md:gap-8 h-auto md:h-[420px] lg:h-[320px] min-h-[320px]"
            >
                <!-- Slanted background divisions -->
                <div
                    class="absolute inset-0 hidden md:flex w-full h-full pointer-events-none select-none"
                    aria-hidden="true"
                >
                    <div
                        class="w-1/3 h-full transform -skew-x-12 origin-left shadow-lg"
                    ></div>
                    <div
                        class="w-1/3 h-full transform -skew-x-12 origin-center shadow-lg"
                    ></div>
                    <div
                        class="w-1/3 h-full transform -skew-x-12 origin-right shadow-lg"
                    ></div>
                </div>
                <!-- Product 1: F&B -->
                <div
                    class="relative flex-1 flex flex-col items-center justify-center z-10 px-2 md:px-6 gap-2"
                >
                    <div class="w-full flex flex-col items-center">
                        <div
                            class="w-full flex items-center justify-center overflow-hidden"
                        >
                            <span class="text-6xl md:text-7xl text-gray-300"
                                >🍽️</span
                            >
                        </div>
                        <span
                            class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-400 text-white font-extrabold text-xl shadow-lg border-4 border-white absolute top-4 right-6 md:right-10"
                        >
                            {{ fnbClients }}+
                        </span>
                        <h4
                            class="font-extrabold text-gray-900 mb-2 text-2xl md:text-3xl mt-2"
                        >
                            POS for F&amp;B
                        </h4>
                        <p class="text-gray-700 text-base font-medium mb-2">
                            For restaurants, cafes, and bars. Fast orders, easy
                            tables, real-time sales.
                        </p>
                    </div>
                </div>
                <!-- Product 2: Drugstore -->
                <div
                    class="relative flex-1 flex flex-col items-center justify-center z-10 px-2 md:px-6 gap-2"
                >
                    <div class="w-full flex flex-col items-center">
                        <div
                            class="w-full flex items-center justify-center overflow-hidden"
                        >
                            <span class="text-6xl md:text-7xl text-gray-300"
                                >💊</span
                            >
                        </div>
                        <span
                            class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-400 text-white font-extrabold text-xl shadow-lg border-4 border-white absolute top-4 right-6 md:right-10"
                        >
                            {{ drugstoreClients }}+
                        </span>
                        <h4
                            class="font-extrabold text-gray-900 mb-2 text-2xl md:text-3xl mt-2"
                        >
                            POS for Drugstore
                        </h4>
                        <p class="text-gray-700 text-base font-medium mb-2">
                            For pharmacies and health stores. Track stocks, fast
                            checkout, compliance ready.
                        </p>
                    </div>
                </div>
                <!-- Product 3: Kiosk/Fast Food -->
                <div
                    class="relative flex-1 flex flex-col items-center justify-center z-10 px-2 md:px-6 gap-2"
                >
                    <div class="w-full flex flex-col items-center">
                        <div
                            class="w-full flex items-center justify-center overflow-hidden"
                        >
                            <span class="text-6xl md:text-7xl text-gray-300"
                                >🍔</span
                            >
                        </div>
                        <span
                            class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-400 text-white font-extrabold text-xl shadow-lg border-4 border-white absolute top-4 right-6 md:right-10"
                        >
                            {{ kioskClients }}+
                        </span>
                        <h4
                            class="font-extrabold text-gray-900 mb-2 text-2xl md:text-3xl mt-2"
                        >
                            POS for Kiosk &amp; Fast Food
                        </h4>
                        <p class="text-gray-700 text-base font-medium mb-2">
                            For kiosks and fast food. Quick orders, combos,
                            kitchen display ready.
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <!-- How QuickJuan POS Helps Your Business Grow Section (now below products) -->
        <section
            class="relative w-full flex flex-col items-center justify-center text-center border border-gray-200 mb-12 mx-auto min-h-screen max-w-none bg-white/90 backdrop-blur shadow-xl px-4"
            style="
                margin-top: 2rem;
                padding-top: 3.5rem;
                padding-bottom: 3.5rem;
            "
        >
            <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-10">
                How QuickJuan POS Helps Your Business Grow
            </h2>
            <div
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-10 w-full max-w-6xl mx-auto"
            >
                <!-- 9 cards go here, unchanged -->
                <div class="flex flex-col items-center">
                    <svg
                        class="w-12 h-12 mb-3 text-amber-400"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 17v-2a4 4 0 014-4h10a4 4 0 014 4v2"
                        />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">
                        Empower Your Team
                    </h4>
                    <p class="text-gray-600 text-base">
                        Serve customers faster and boost loyalty with the right
                        tools.
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <svg
                        class="w-12 h-12 mb-3 text-amber-400"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <rect x="3" y="11" width="18" height="7" rx="2" />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M16 11V7a4 4 0 00-8 0v4"
                        />
                    </svg>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">
                        Real-Time Insights
                    </h4>
                    <p class="text-gray-600 text-base">
                        Smarter decisions with up-to-the-minute sales and
                        inventory.
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <svg
                        class="w-12 h-12 mb-3 text-amber-400"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 8v4l3 3"
                        />
                        <circle cx="12" cy="12" r="10" />
                    </svg>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">
                        Grow Your Revenue
                    </h4>
                    <p class="text-gray-600 text-base">
                        Unlock new sales and streamline operations for growth.
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <svg
                        class="w-12 h-12 mb-3 text-amber-400"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 17v-2a4 4 0 014-4h2a4 4 0 014 4v2"
                        />
                        <circle cx="15" cy="7" r="4" />
                    </svg>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">
                        Multi-Branch Ready
                    </h4>
                    <p class="text-gray-600 text-base">
                        Manage multiple locations from a single dashboard.
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <svg
                        class="w-12 h-12 mb-3 text-amber-400"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 20v-6m0 0V4m0 10h4m-4 0H8"
                        />
                    </svg>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">
                        Easy Integrations
                    </h4>
                    <p class="text-gray-600 text-base">
                        Connect with accounting, delivery, and more.
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <svg
                        class="w-12 h-12 mb-3 text-amber-400"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8 12h8"
                        />
                    </svg>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">
                        Inventory Control
                    </h4>
                    <p class="text-gray-600 text-base">
                        Track stock in real time and avoid shortages.
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <svg
                        class="w-12 h-12 mb-3 text-amber-400"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <rect x="4" y="4" width="16" height="16" rx="2" />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8 12h8"
                        />
                    </svg>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">
                        Customizable Receipts
                    </h4>
                    <p class="text-gray-600 text-base">
                        Brand your receipts and add promos easily.
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <svg
                        class="w-12 h-12 mb-3 text-amber-400"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">
                        Secure & Reliable
                    </h4>
                    <p class="text-gray-600 text-base">
                        Enterprise-grade security and 99.9% uptime.
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <svg
                        class="w-12 h-12 mb-3 text-amber-400"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 10v4m8-8h-4m-8 0H4"
                        />
                    </svg>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">
                        Easy to Use
                    </h4>
                    <p class="text-gray-600 text-base">
                        Intuitive interface for quick staff training.
                    </p>
                </div>
            </div>
        </section>
        <!-- Testimonials Section -->
        <section class="relative w-full bg-white py-16 px-4 z-20">
            <Testimonials />
        </section>
        <!-- Contact Us Section -->
        <section id="contact" class="relative w-full bg-white py-16 px-4 z-20">
            <ContactUs />
        </section>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import QuickJuanIcon from "@/Components/QuickJuanIcon.vue";
import Testimonials from "@/Components/Testimonials.vue";
import ContactUs from "@/Components/ContactUs.vue";

// Animated client numbers for products
const fnbClients = ref(0);
const drugstoreClients = ref(0);
const kioskClients = ref(0);

function animateClients(target: number, refVar: any, duration = 1200) {
    const start = 0;
    const increment = Math.ceil(target / (duration / 16));
    let current = start;
    const step = () => {
        current += increment;
        if (current >= target) {
            refVar.value = target;
        } else {
            refVar.value = current;
            requestAnimationFrame(step);
        }
    };
    step();
}

const showVideo = ref(true);
const posImages = [
    "/images/resto.png",
    "/images/resto.png",
    "/images/resto.png",
];
const currentImage = ref(0);
const videoId = "goNOzX5vg48";
const videoSrc = computed(
    () =>
        `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&controls=0&loop=1&playlist=${videoId}&modestbranding=1&showinfo=0&rel=0`
);
const videoTitle = "Restaurant Video Background";

const isMobile = ref(false);
const isSticky = ref(false);
const navHovered = ref(false);
const mobileNavOpen = ref(false);

function checkMobile() {
    isMobile.value = window.innerWidth < 768;
}

function handleScroll() {
    isSticky.value = window.scrollY > 40;
}

onMounted(() => {
    animateClients(1200, fnbClients);
    animateClients(900, drugstoreClients);
    animateClients(700, kioskClients);
    checkMobile();
    window.addEventListener("resize", checkMobile);
    window.addEventListener("scroll", handleScroll);
});
onUnmounted(() => {
    window.removeEventListener("resize", checkMobile);
    window.removeEventListener("scroll", handleScroll);
});

function toggleMedia() {
    showVideo.value = !showVideo.value;
}

function scrollToGetStarted() {
    const el = document.getElementById("get-started");
    if (el) {
        el.scrollIntoView({ behavior: "smooth" });
    }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
header {
    left: 0;
    right: 0;
}
</style>
