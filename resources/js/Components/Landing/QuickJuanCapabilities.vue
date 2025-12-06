<template>
    <div class="min-h-[70vh] md:min-h-[90vh] w-full flex flex-row bg-gradient-to-r from-fourth/20 to-fourth/10">
        <!-- Product Slider -->
        <div
            class="w-full flex flex-col items-center justify-center relative overflow-hidden"
        >
            <!-- Capabilities -->
            <div class="w-full max-w-6xl flex flex-col justify-center px-8 md:px-12 py-12 md:py-16 gap-10">
                <div class="flex flex-col gap-12">
                    <div
                        v-for="(prod, idx) in products"
                        :key="idx"
                        class="bg-white rounded-xl p-6 border border-gray-200 hover:border-primary/30 hover:shadow-lg transition-all duration-300"
                    >
                        <div class="flex flex-col md:flex-row gap-6 items-center">
                            <div v-if="idx % 2 === 0" class="md:w-1/2">
                                <img :src="prod.image" alt="Product image" class="w-full h-auto rounded-lg shadow-md" />
                            </div>
                            <div class="md:w-1/2 text-center md:text-left">
                                <div class="font-semibold text-2xl text-gray-900 mb-2">
                                    {{ prod.name }}
                                </div>
                                <p class="text-lg text-gray-700 mb-4">
                                    {{ prod.description }}
                                </p>
                                <Link :href="'/product/' + prod.slug" class="inline-block bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors">
                                    Learn More
                                </Link>
                            </div>
                            <div v-if="idx % 2 !== 0" class="md:w-1/2">
                                <img :src="prod.image" alt="Product image" class="w-full h-auto rounded-lg shadow-md" />
                            </div>
                        </div>
                    </div>
                </div>
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
import { Link } from "@inertiajs/vue3";


const products = [
    {
        name: "QuickJuan POS Terminal",
        image: "/images/resto.png",
        slug: "pos-terminal",
        description: "Comprehensive point of sale solution for restaurants and retail businesses.",
        capabilities: [
            {
                title: "Flexible Payments",
                desc: "Accept cash, card, QR, and e-wallets with ease.",
            },
            {
                title: "Inventory Management",
                desc: "Track stock in real time and get low-inventory alerts.",
            },
            {
                title: "Sales Analytics",
                desc: "Visualize sales trends and make data-driven decisions.",
            },
            {
                title: "Customer Database",
                desc: "Build loyalty with customer profiles and purchase history.",
            },
            {
                title: "Fast Checkout",
                desc: "Speedy transactions keep lines moving and customers happy.",
            },
            {
                title: "Smart Notifications",
                desc: "Get notified for important events and low stock.",
            },
            {
                title: "Digital Receipts",
                desc: "Send receipts via SMS or email instantly.",
            },
            {
                title: "Cloud Access",
                desc: "Manage your business from anywhere, anytime.",
            },
            {
                title: "Integrations",
                desc: "Connect with accounting, delivery, and more.",
            },
        ],
    },
    {
        name: "QuickJuan Mobile POS",
        image: "/images/resto2.png",
        slug: "mobile-pos",
        description: "Mobile point of sale system for on-the-go transactions and flexibility.",
        capabilities: [
            {
                title: "Mobile Checkout",
                desc: "Sell anywhere with your phone or tablet.",
            },
            {
                title: "All-day Battery",
                desc: "Optimized for long shifts and mobility.",
            },
            {
                title: "Inventory Sync",
                desc: "Real-time inventory updates across devices.",
            },
            {
                title: "Digital Receipts",
                desc: "Send receipts via SMS or email instantly.",
            },
            {
                title: "Instant Alerts",
                desc: "Get notified for important events and low stock.",
            },
            {
                title: "Cloud Access",
                desc: "Manage your business from anywhere, anytime.",
            },
        ],
    },
    {
        name: "QuickJuan Kiosk",
        image: "/images/resto3.png",
        slug: "kiosk",
        description: "Self-service kiosk for efficient customer ordering and payment processing.",
        capabilities: [
            {
                title: "Self-Service",
                desc: "Let customers order and pay on their own.",
            },
            {
                title: "Digital Receipts",
                desc: "Send receipts via SMS or email instantly.",
            },
            {
                title: "Secure Payments",
                desc: "PCI-compliant and safe for all transactions.",
            },
            {
                title: "Inventory Sync",
                desc: "Real-time inventory updates across devices.",
            },
            {
                title: "Sales Analytics",
                desc: "Visualize sales trends and make data-driven decisions.",
            },
            {
                title: "Cloud Access",
                desc: "Manage your business from anywhere, anytime.",
            },
        ],
    },
];

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
