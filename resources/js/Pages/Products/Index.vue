<template>
    <div class="min-h-[90vh] w-full flex flex-row bg-fourth/60">
        <!-- Product Slider -->
        <div
            class="w-full flex flex-row items-stretch justify-center relative overflow-hidden"
        >
            <!-- Slider Indicators (moved to top) -->
            <div
                class="flex gap-4 absolute top-8 left-1/2 -translate-x-1/2 z-10"
            >
                <button
                    v-for="(prod, idx) in products"
                    :key="idx"
                    @click="goTo(idx)"
                    :aria-label="'Go to ' + prod.name"
                    :class="[
                        'w-7 h-7 rounded-full flex items-center justify-center border-2 transition-all duration-300',
                        current === idx
                            ? 'bg-primary border-primary scale-110 shadow-lg text-white'
                            : 'bg-primary-light border-primary-light opacity-60 text-primary',
                    ]"
                >
                    <span class="text-lg font-bold">{{ idx + 1 }}</span>
                </button>
            </div>
            <!-- Left: Capabilities -->
            <div
                class="w-full md:w-[70%] flex flex-col justify-center px-6 py-10 gap-8 transition-all duration-500"
            >
                <h1 class="text-3xl md:text-5xl font-bold text-primary mb-4">
                    {{ products[current].name }} Capabilities
                </h1>
                <ul class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <li
                        v-for="(item, idx) in products[current].capabilities"
                        :key="idx"
                        class="flex items-start gap-4 bg-white/80 rounded-xl p-4 shadow-sm"
                    >
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-light text-2xl"
                            >{{ item.icon }}</span
                        >
                        <div>
                            <div class="font-bold text-lg text-secondary mb-1">
                                {{ item.title }}
                            </div>
                            <div class="text-gray-600 text-base">
                                {{ item.desc }}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <!-- Right: Product Image -->
            <div
                class="hidden md:flex w-[30%] flex-col items-center justify-center bg-white/70 rounded-l-3xl shadow-lg p-0 relative overflow-hidden"
            >
                <div
                    class="w-full h-full flex-1 flex items-center justify-center relative min-h-[90vh]"
                >
                    <img
                        :src="products[current].image"
                        :alt="products[current].name + ' Showcase'"
                        class="object-cover w-full h-full rounded-l-3xl shadow-md transition-all duration-500"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";

const products = [
    {
        name: "QuickJuan POS Terminal",
        image: "/images/resto.png",
        capabilities: [
            {
                icon: "💳",
                title: "Flexible Payments",
                desc: "Accept cash, card, QR, and e-wallets with ease.",
            },
            {
                icon: "📦",
                title: "Inventory Management",
                desc: "Track stock in real time and get low-inventory alerts.",
            },
            {
                icon: "📈",
                title: "Sales Analytics",
                desc: "Visualize sales trends and make data-driven decisions.",
            },
            {
                icon: "👥",
                title: "Customer Database",
                desc: "Build loyalty with customer profiles and purchase history.",
            },
            {
                icon: "🕒",
                title: "Fast Checkout",
                desc: "Speedy transactions keep lines moving and customers happy.",
            },
            {
                icon: "🔔",
                title: "Smart Notifications",
                desc: "Get notified for important events and low stock.",
            },
            {
                icon: "🧾",
                title: "Digital Receipts",
                desc: "Send receipts via SMS or email instantly.",
            },
            {
                icon: "🌐",
                title: "Cloud Access",
                desc: "Manage your business from anywhere, anytime.",
            },
            {
                icon: "🔗",
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
                icon: "📱",
                title: "Mobile Checkout",
                desc: "Sell anywhere with your phone or tablet.",
            },
            {
                icon: "🔋",
                title: "All-day Battery",
                desc: "Optimized for long shifts and mobility.",
            },
            {
                icon: "📦",
                title: "Inventory Sync",
                desc: "Real-time inventory updates across devices.",
            },
            {
                icon: "🧾",
                title: "Digital Receipts",
                desc: "Send receipts via SMS or email instantly.",
            },
            {
                icon: "🔔",
                title: "Instant Alerts",
                desc: "Get notified for important events and low stock.",
            },
            {
                icon: "🌐",
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
                icon: "🖥️",
                title: "Self-Service",
                desc: "Let customers order and pay on their own.",
            },
            {
                icon: "🧾",
                title: "Digital Receipts",
                desc: "Send receipts via SMS or email instantly.",
            },
            {
                icon: "🔒",
                title: "Secure Payments",
                desc: "PCI-compliant and safe for all transactions.",
            },
            {
                icon: "📦",
                title: "Inventory Sync",
                desc: "Real-time inventory updates across devices.",
            },
            {
                icon: "📈",
                title: "Sales Analytics",
                desc: "Visualize sales trends and make data-driven decisions.",
            },
            {
                icon: "🌐",
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
onMounted(() => {
    interval = window.setInterval(() => {
        next();
    }, 4000);
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
