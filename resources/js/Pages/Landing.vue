<template>
    <div class="min-h-screen flex flex-col">
        <!-- Navigation Bar -->
        <header
            :class="[
                'fixed top-0 left-0 w-full z-30 flex items-center justify-between px-6 py-4 transition-all duration-300',
                isSticky
                    ? 'bg-primary/80 shadow-lg backdrop-blur '
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
                            ? 'text-2xl font-bold text-white tracking-tight hidden sm:inline'
                            : 'text-2xl font-bold text-primary tracking-tight hidden sm:inline'
                    "
                    >QuickJuan POS</span
                >
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
                        <a
                            href="#get-started"
                            :class="
                                isSticky
                                    ? 'text-white hover:text-primary'
                                    : 'text-primary hover:text-third'
                            "
                            class="transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            >Try for FREE</a
                        >
                    </li>
                    <li>
                        <a
                            href="#products"
                            :class="
                                isSticky
                                    ? 'text-white hover:text-primary'
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
                                    ? 'text-white hover:text-primary'
                                    : 'text-primary hover:text-third'
                            "
                            class="transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            >Contact Us</a
                        >
                    </li>
                </ul>
            </nav>
            <!-- Mobile Nav Dropdown -->
            <transition name="fade">
                <nav
                    v-if="mobileNavOpen"
                    class="absolute top-full left-0 w-full bg-white/95 shadow-lg border-b border-third flex flex-col items-center py-4 md:hidden z-40"
                >
                    <ul
                        class="flex flex-col gap-4 text-lg font-semibold w-full text-center"
                    >
                        <li>
                            <a
                                href="#get-started"
                                class="block py-2 text-secondary hover:text-primary transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                @click="mobileNavOpen = false"
                                >Try for FREE</a
                            >
                        </li>
                        <li>
                            <a
                                href="#products"
                                class="block py-2 text-secondary hover:text-primary transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                @click="mobileNavOpen = false"
                                >Products</a
                            >
                        </li>
                        <li>
                            <a
                                href="#contact"
                                class="block py-2 text-secondary hover:text-primary transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                @click="mobileNavOpen = false"
                                >Contact Us</a
                            >
                        </li>
                    </ul>
                </nav>
            </transition>
        </header>

        <main class="flex-grow flex flex-col">
            <!-- Content -->
            <div
                class="relative z-10 flex flex-col items-center justify-end md:justify-center text-center px-6 py-8 md:py-0 w-full min-h-screen bg-gradient-to-t md:bg-gradient-to-r from-fourth/90 via-fourth/60 to-transparent"
                style="padding-top: 6rem"
                aria-labelledby="landing-title"
            >
                <!-- Video/Image Background: now only for hero/landing section -->
                <div
                    class="absolute inset-0 w-full h-full pointer-events-none z-0"
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
                            <source
                                src="/videos/landingvideo.mov"
                                type="video/mp4"
                            />
                            Your browser does not support the video tag.
                        </video>
                        <div v-else class="w-full h-full">
                            <img
                                key="image"
                                :src="posImages[currentImage]"
                                class="w-full h-full object-cover object-top"
                                alt="POS Machine"
                            />
                        </div>
                    </transition>
                    <div class="absolute inset-0 bg-fourth bg-opacity-60"></div>
                    <!-- Swap Button (mobile only) -->
                    <button
                        v-if="isMobile"
                        @click="toggleMedia"
                        class="absolute top-4 right-4 z-20 px-4 py-2 rounded-lg bg-primary text-white font-bold shadow hover:bg-third hover:text-secondary transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
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
                                'w-4 h-4 rounded-full border-2 border-primary focus:outline-none focus:ring-2 focus:ring-primary',
                                idx === currentImage
                                    ? 'bg-primary'
                                    : 'bg-fourth opacity-60',
                            ]"
                        ></button>
                    </div>
                </div>
                <!-- Hero Content -->
                <div
                    class="relative z-10 flex flex-col items-center justify-center w-full h-full"
                >
                    <h1
                        id="landing-title"
                        class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-primary drop-shadow-lg mb-2 font-sans tracking-tight"
                    >
                        Welcome to
                        <span class="text-primary">QuickJuan POS</span>
                    </h1>
                    <p
                        class="text-lg sm:text-xl md:text-2xl text-secondary mb-4 max-w-2xl font-light drop-shadow"
                    >
                        The modern, multi-tenant Point of Sale platform for
                        retail, restaurants, and fast food.
                        <span class="font-semibold text-third"
                            >Manage your business, inventory, and sales with
                            ease.</span
                        >
                    </p>
                    <button
                        @click="scrollToGetStarted"
                        class="px-8 py-4 rounded-lg bg-primary text-white font-bold text-xl shadow-lg hover:bg-third hover:text-secondary transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 mb-4"
                        aria-label="Get Started Free"
                    >
                        Get Started Free
                    </button>
                    <!-- Products Section (inline, inside hero/landing area) -->
                    <ProductsSection />
                </div>
            </div>
            <!-- How QuickJuan POS Helps Your Business Grow Section (now as component) -->
            <HowQuickJuanHelps />
            <!-- Why Choose QuickJuan Section -->
            <WhyChooseQuickJuan />
            <!-- Testimonials Section -->
            <section class="relative w-full bg-fourth px-4 z-20">
                <Testimonials />
            </section>
            <!-- Contact Us Section -->
            <section id="contact" class="relative w-full bg-fourth px-4 z-20">
                <ContactUs />
            </section>

            <!-- Footer Section -->
            <footer
                class="w-full bg-primary text-white pt-10 pb-6 border-t border-third mt-0"
            >
                <div class="max-w-6xl mx-auto px-4 flex flex-col items-center">
                    <!-- Office Hours -->
                    <div class="text-center mb-6">
                        <div class="text-lg font-semibold text-fourth">
                            Office Hours
                        </div>
                        <div class="text-base text-secondary">
                            Monday to Saturday, 8:00 AM – 7:00 PM
                        </div>
                    </div>
                    <!-- Footer Menu -->
                    <nav aria-label="Footer menu" class="mb-4">
                        <ul
                            class="flex flex-wrap justify-center gap-6 text-base font-medium"
                        >
                            <li>
                                <a
                                    href="/terms-and-conditions"
                                    class="hover:text-third transition-colors"
                                    >Terms and Conditions</a
                                >
                            </li>
                            <li>
                                <a
                                    href="/privacy-policy"
                                    class="hover:text-third transition-colors"
                                    >Privacy Policy</a
                                >
                            </li>
                            <li>
                                <a
                                    href="/customer-support"
                                    class="hover:text-third transition-colors"
                                    >Customer Support</a
                                >
                            </li>
                            <li>
                                <a
                                    href="/faq"
                                    class="hover:text-third transition-colors"
                                    >FAQ</a
                                >
                            </li>
                            <li>
                                <a
                                    href="/reviews"
                                    class="hover:text-third transition-colors"
                                    >Reviews</a
                                >
                            </li>
                        </ul>
                    </nav>
                    <div class="text-sm text-secondary text-center">
                        &copy; {{ new Date().getFullYear() }} QuickJuan POS. All
                        rights reserved.
                    </div>
                </div>
            </footer>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import QuickJuanIcon from "@/Components/QuickJuanIcon.vue";
import Testimonials from "@/Components/Testimonials.vue";
import ContactUs from "@/Components/ContactUs.vue";
import WhyChooseQuickJuan from "@/Components/WhyChooseQuickJuan.vue";
import HowQuickJuanHelps from "@/Components/HowQuickJuanHelps.vue";
import ProductsSection from "./Home/ProductsSection.vue";

// Animated client numbers for products
const fnbClients = ref(0);
const drugstoreClients = ref(0);
const kioskClients = ref(0);

function animateClients(target: number, refVar: any, duration = 1200) {
    const start = 0;
    let current = start;
    const increment = Math.ceil(target / (duration / 16));
    function step() {
        current += increment;
        if (current >= target) {
            refVar.value = target;
        } else {
            refVar.value = current;
            requestAnimationFrame(step);
        }
    }
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
