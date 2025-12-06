<template>
    <GuestLayout>
        <div class="flex flex-col bg-white">
            <main class="flex-1">
                <!-- Hero -->
                <section class="py-20 md:py-32 bg-gradient-to-br from-primary/5 via-white to-secondary/5">
                    <div class="container mx-auto px-6 md:px-12 lg:px-24">
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center"
                        >
                            <!-- Left: copy -->
                            <div class="space-y-6">
                                <h1
                                    class="text-3xl md:text-5xl font-extrabold text-gray-900 leading-tight"
                                >
                                    The restaurant and retail
                                    <span class="text-primary-400">
                                        point of sale
                                    </span>
                                    at our service.
                                </h1>

                                <p class="text-gray-600 max-w-xl">
                                    The modern, multi-tenant Point of Sale
                                    platform for retail, restaurants, and fast
                                    food. Manage your business, inventory, and
                                    sales with ease.
                                </p>

                                <div class="flex flex-wrap gap-4 mt-4">
                                    <Link
                                        :href="route('tenant-registration')"
                                        class="inline-flex items-center px-5 py-3 bg-primary-400 hover:bg-primary-700 text-white rounded-lg shadow"
                                    >
                                        Start Free Trial
                                    </Link>
                                    <a
                                        href="#features"
                                        class="inline-flex items-center px-5 py-3 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50"
                                    >
                                        See Features
                                    </a>
                                </div>

                                <div
                                    class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6"
                                >
                                    <div class="bg-white rounded-lg shadow p-4 text-center">
                                        <div
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            1,200+
                                        </div>
                                        <div class="text-gray-600">FNB clients</div>
                                    </div>
                                    <div class="bg-white rounded-lg shadow p-4 text-center">
                                        <div
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            900+
                                        </div>
                                        <div class="text-gray-600">Drugstores</div>
                                    </div>
                                    <div class="bg-white rounded-lg shadow p-4 text-center">
                                        <div
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            700+
                                        </div>
                                        <div class="text-gray-600">Kiosks</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: mockup -->
                            <div
                                class="relative flex justify-center md:justify-end"
                            >
                                <div
                                    class="w-[300px] sm:w-[420px] md:w-[520px] lg:w-[600px] transform -rotate-6 rounded-3xl overflow-hidden"
                                >
                                    <img
                                        src="/images/quickjuan-pos.png"
                                        alt="POS on iPad"
                                        class="w-full h-auto block"
                                    />
                                </div>

                                <img
                                    src="/images/pos-reader.svg"
                                    alt="Card reader"
                                    class="absolute bottom-4 left-6 w-20 md:w-24 transform rotate-6 shadow-lg"
                                />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Floating Cards Section -->
                <WhatOurClientsSay />
                <QuickJuanCapabilities />
                <HowQuickJuanHelps />
                <WhyChooseQuickJuan />
                <ContactUs />
            </main>

            <Footer />
        </div>
    </GuestLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import WhatOurClientsSay from "@/Components/Landing/WhatOurClientsSay.vue";
import QuickJuanCapabilities from "@/Components/Landing/QuickJuanCapabilities.vue";
import HowQuickJuanHelps from "@/Components/Landing/HowQuickJuanHelps.vue";
import WhyChooseQuickJuan from "@/Components/Landing/WhyChooseQuickJuan.vue";
import ContactUs from "@/Components/Landing/ContactUs.vue";
import Footer from "@/Components/Footer.vue";

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

const isMobile = ref(false);
const isSticky = ref(false);

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

@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

.float-card {
    perspective: 1000px;
}
</style>
