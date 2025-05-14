<template>
    <section
        class="relative w-full flex flex-col items-center justify-center text-center border border-gray-200 mb-12 mx-auto max-w-none bg-white/90 backdrop-blur shadow-xl px-4 py-16"
    >
        <h2 class="text-4xl md:text-5xl font-bold text-amber-500 mb-10">
            How QuickJuan POS Helps Your Business Grow
        </h2>
        <div
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-10 w-full max-w-6xl mx-auto"
        >
            <div
                v-for="(item, idx) in items"
                :key="idx"
                class="flex flex-col items-center bg-white rounded-xl shadow p-6 border border-gray-100"
            >
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-full bg-amber-100 mb-4"
                >
                    <span class="text-3xl">{{ item.icon }}</span>
                </div>
                <div class="font-bold text-lg text-gray-900 mb-2">
                    {{ item.title }}
                </div>
                <div class="text-gray-600 text-base">{{ item.desc }}</div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref } from "vue";
const items = ref([
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
]);
</script>

<style scoped></style>
