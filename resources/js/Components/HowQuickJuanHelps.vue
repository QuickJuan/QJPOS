<template>
    <section
        class="relative w-full flex flex-col items-center justify-center text-center mx-auto max-w-none bg-white/90 backdrop-blur px-4 py-16 overflow-hidden"
        aria-labelledby="how-quickjuan-helps-title"
    >
        <!-- Decorative SVG background -->
        <svg
            class="absolute -top-16 left-1/2 -translate-x-1/2 w-[120vw] h-40 opacity-20 pointer-events-none z-0"
            viewBox="0 0 1440 320"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <path
                fill="#fbbf24"
                fill-opacity="0.3"
                d="M0,160L60,170.7C120,181,240,203,360,197.3C480,192,600,160,720,133.3C840,107,960,85,1080,101.3C1200,117,1320,171,1380,197.3L1440,224L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"
            />
        </svg>
        <h2
            id="how-quickjuan-helps-title"
            class="text-4xl md:text-5xl font-bold text-amber-500 mb-10 relative z-10"
        >
            Powerful Capabilities of QuickJuan POS
        </h2>
        <div
            class="relative w-full max-w-6xl mx-auto flex flex-col items-start text-left z-10"
        >
            <ul
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-8 w-full"
            >
                <li
                    v-for="(item, idx) in items"
                    :key="idx"
                    class="relative flex items-start group fade-in-up-checklist"
                    :style="{ animationDelay: idx * 80 + 'ms' }"
                    tabindex="0"
                    :aria-label="item.title + ': ' + item.desc"
                >
                    <!-- Icon and content -->
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 text-2xl mr-4 shadow-sm"
                    >
                        <span aria-hidden="true">{{ item.icon }}</span>
                    </span>
                    <div class="flex-1">
                        <div class="font-bold text-lg text-gray-900 mb-1">
                            {{ item.title }}
                        </div>
                        <div class="text-gray-600 text-base">
                            {{ item.desc }}
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from "vue";
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

onMounted(async () => {
    await nextTick();
    // Animate checklist items in (staggered)
    document
        .querySelectorAll<HTMLElement>(".fade-in-up-checklist")
        .forEach((el, i) => {
            setTimeout(() => {
                el.classList.add("fade-in-up");
            }, i * 80);
        });
});
</script>

<style scoped>
.fade-in-up-checklist {
    opacity: 0;
    transform: translateY(40px);
}
.fade-in-up-checklist.fade-in-up {
    opacity: 1;
    transform: translateY(0);
    transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1),
        transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
li:focus {
    outline: none;
    box-shadow: 0 0 0 3px #fbbf24;
    border-radius: 0.5rem;
}
</style>
