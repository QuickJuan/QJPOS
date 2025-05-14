<template>
    <section
        class="relative w-full flex flex-col items-center justify-center text-center border border-gray-200 mb-12 mx-auto min-h-screen max-w-none bg-white/90 backdrop-blur shadow-xl px-4 py-16"
    >
        <h2 class="text-4xl md:text-5xl font-bold text-amber-500 mb-10">
            Why Choose QuickJuan POS?
        </h2>
        <div
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-10 w-full max-w-6xl mx-auto"
        >
            <div
                v-for="(reason, idx) in reasons"
                :key="idx"
                class="flex flex-col items-center bg-white rounded-xl shadow p-6 border border-gray-100"
            >
                <div
                    class="w-14 h-14 flex items-center justify-center rounded-full bg-amber-100 mb-4"
                >
                    <span class="text-3xl">{{ reason.icon }}</span>
                </div>
                <div class="font-bold text-lg text-gray-900 mb-2">
                    {{ reason.title }}
                </div>
                <div class="text-gray-600 text-base">{{ reason.desc }}</div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref } from "vue";
const reasons = ref([
    {
        icon: "⚡",
        title: "Fast & Reliable",
        desc: "Lightning-fast transactions and robust uptime for your business.",
    },
    {
        icon: "📱",
        title: "Mobile Friendly",
        desc: "Works seamlessly on any device, anywhere you do business.",
    },
    {
        icon: "🔒",
        title: "Secure",
        desc: "Enterprise-grade security keeps your data and customers safe.",
    },
    {
        icon: "📊",
        title: "Real-Time Analytics",
        desc: "Instant insights into sales, inventory, and customer trends.",
    },
    {
        icon: "🛠️",
        title: "Easy Setup",
        desc: "Get started in minutes with intuitive onboarding.",
    },
    {
        icon: "💡",
        title: "Smart Inventory",
        desc: "Automated stock tracking and low-inventory alerts.",
    },
]);
</script>

<style scoped></style>
