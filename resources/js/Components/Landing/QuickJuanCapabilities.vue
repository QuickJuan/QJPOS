<template>
    <div class="min-h-[70vh] md:min-h-[90vh] w-full flex flex-row bg-gradient-to-r from-fourth/20 to-fourth/10">
        <!-- Product Slider -->
        <div
            class="w-full flex flex-col items-center justify-center relative overflow-hidden"
        >
            <!-- Capabilities -->
            <div
                class="w-full max-w-6xl flex flex-col justify-center px-8 md:px-12 py-12 md:py-16 gap-10 transition-all duration-500"
                @mouseenter="pauseAutoSlide"
                @mouseleave="resumeAutoSlide"
                @keydown.left="prev"
                @keydown.right="next"
                tabindex="0"
            >
                <h1 class="text-3xl md:text-5xl font-bold text-gray-900 mb-6 text-center">
                    {{ products[current].name }} Capabilities
                </h1>
                <!-- Slider Indicators -->
                <div class="flex gap-3 justify-center mb-8">
                    <button
                        v-for="(prod, idx) in products"
                        :key="idx"
                        @click="goTo(idx)"
                        :aria-label="'Go to ' + prod.name"
                        :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary/50',
                            current === idx
                                ? 'bg-primary border-primary text-white shadow-md'
                                : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50',
                        ]"
                    >
                        <span class="text-sm font-medium">{{ idx + 1 }}</span>
                    </button>
                </div>
                <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <li
                        v-for="(item, idx) in products[current].capabilities"
                        :key="idx"
                        class="flex items-start gap-4 bg-white rounded-xl p-6 border border-gray-200 hover:border-primary/30 hover:shadow-lg transition-all duration-300"
                    >
                        <span
                            class="flex items-center justify-center w-14 h-14 rounded-full border-2 border-primary/30 text-primary"
                        >
                            <component :is="item.icon" class="w-8 h-8" />
                        </span>
                        <div class="flex-1">
                            <div class="font-semibold text-xl text-gray-900 mb-2">
                                {{ item.title }}
                            </div>
                            <div class="text-gray-600 text-base leading-relaxed">
                                {{ item.desc }}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <!-- Decorative Background Elements -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-20 left-10 w-32 h-32 bg-primary/5 rounded-full blur-xl"></div>
                <div class="absolute bottom-20 right-10 w-40 h-40 bg-secondary/5 rounded-full blur-xl"></div>
                <div class="absolute top-1/2 left-1/4 w-24 h-24 bg-primary/10 rounded-full blur-lg"></div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import {
    CreditCardIcon,
    CubeIcon,
    ChartBarIcon,
    UsersIcon,
    ClockIcon,
    BellIcon,
    DocumentTextIcon,
    GlobeAltIcon,
    LinkIcon,
    DevicePhoneMobileIcon,
    Battery100Icon,
    DocumentIcon,
    LockClosedIcon,
    CpuChipIcon,
} from "@heroicons/vue/24/outline";


const products = [
    {
        name: "QuickJuan POS Terminal",
        image: "/images/resto.png",
        capabilities: [
            {
                icon: CreditCardIcon,
                title: "Flexible Payments",
                desc: "Accept cash, card, QR, and e-wallets with ease.",
            },
            {
                icon: CubeIcon,
                title: "Inventory Management",
                desc: "Track stock in real time and get low-inventory alerts.",
            },
            {
                icon: ChartBarIcon,
                title: "Sales Analytics",
                desc: "Visualize sales trends and make data-driven decisions.",
            },
            {
                icon: UsersIcon,
                title: "Customer Database",
                desc: "Build loyalty with customer profiles and purchase history.",
            },
            {
                icon: ClockIcon,
                title: "Fast Checkout",
                desc: "Speedy transactions keep lines moving and customers happy.",
            },
            {
                icon: BellIcon,
                title: "Smart Notifications",
                desc: "Get notified for important events and low stock.",
            },
            {
                icon: DocumentTextIcon,
                title: "Digital Receipts",
                desc: "Send receipts via SMS or email instantly.",
            },
            {
                icon: GlobeAltIcon,
                title: "Cloud Access",
                desc: "Manage your business from anywhere, anytime.",
            },
            {
                icon: LinkIcon,
                title: "Integrations",
                desc: "Connect with accounting, delivery, and more.",
            },
        ],
    },
    {
        name: "QuickJuan Mobile POS",
        image: "/images/resto2.png",
        capabilities: [
            {
                icon: DevicePhoneMobileIcon,
                title: "Mobile Checkout",
                desc: "Sell anywhere with your phone or tablet.",
            },
            {
                icon: Battery100Icon,
                title: "All-day Battery",
                desc: "Optimized for long shifts and mobility.",
            },
            {
                icon: CubeIcon,
                title: "Inventory Sync",
                desc: "Real-time inventory updates across devices.",
            },
            {
                icon: DocumentIcon,
                title: "Digital Receipts",
                desc: "Send receipts via SMS or email instantly.",
            },
            {
                icon: BellIcon,
                title: "Instant Alerts",
                desc: "Get notified for important events and low stock.",
            },
            {
                icon: GlobeAltIcon,
                title: "Cloud Access",
                desc: "Manage your business from anywhere, anytime.",
            },
        ],
    },
    {
        name: "QuickJuan Kiosk",
        image: "/images/resto3.png",
        capabilities: [
            {
                icon: CpuChipIcon,
                title: "Self-Service",
                desc: "Let customers order and pay on their own.",
            },
            {
                icon: DocumentTextIcon,
                title: "Digital Receipts",
                desc: "Send receipts via SMS or email instantly.",
            },
            {
                icon: LockClosedIcon,
                title: "Secure Payments",
                desc: "PCI-compliant and safe for all transactions.",
            },
            {
                icon: CubeIcon,
                title: "Inventory Sync",
                desc: "Real-time inventory updates across devices.",
            },
            {
                icon: ChartBarIcon,
                title: "Sales Analytics",
                desc: "Visualize sales trends and make data-driven decisions.",
            },
            {
                icon: GlobeAltIcon,
                title: "Cloud Access",
                desc: "Manage your business from anywhere, anytime.",
            },
        ],
    },
];

const current = ref(0);
let interval: number | undefined;
function next() {
    current.value = (current.value + 1) % products.length;
}
function prev() {
    current.value = (current.value - 1 + products.length) % products.length;
}
function goTo(idx: number) {
    current.value = idx;
}
function pauseAutoSlide() {
    if (interval) {
        clearInterval(interval);
        interval = undefined;
    }
}
function resumeAutoSlide() {
    if (!interval) {
        interval = window.setInterval(() => {
            next();
        }, 4000);
    }
}
onMounted(() => {
    resumeAutoSlide();
});
onUnmounted(() => {
    if (interval) clearInterval(interval);
});
</script>

<style scoped>
@media (max-width: 768px) {
    .md\:flex {
        display: none !important;
    }
    .md\:w-\[70\%\] {
        width: 100% !important;
    }
}
</style>
