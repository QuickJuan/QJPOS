<template>
    <section
        class="relative w-full flex flex-col items-center justify-center text-center mx-auto max-w-none bg-gradient-to-b from-white to-gray-50 px-8 py-20 overflow-hidden"
        aria-labelledby="how-quickjuan-helps-title"
    >
        <!-- Simplified decorative elements -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-20 left-10 w-32 h-32 bg-primary/5 rounded-full blur-xl"></div>
            <div class="absolute bottom-20 right-10 w-40 h-40 bg-secondary/5 rounded-full blur-xl"></div>
            <div class="absolute top-1/2 left-1/4 w-24 h-24 bg-primary/10 rounded-full blur-lg"></div>
        </div>

        <h2
            id="how-quickjuan-helps-title"
            class="text-3xl md:text-5xl font-bold text-gray-900 mb-12 relative z-10"
        >
            How QuickJuan Helps Your Business
        </h2>

        <div
            class="relative w-full max-w-4xl mx-auto z-10"
        >
            <!-- Timeline container -->
            <div class="relative">
                <!-- Timeline line -->
                <div class="hidden md:block absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-primary/50 to-secondary/50"></div>

                <ul class="space-y-8">
                    <li
                        v-for="(item, idx) in items"
                        :key="idx"
                        class="relative flex items-start gap-6 fade-in-up-checklist"
                        :style="{ animationDelay: idx * 120 + 'ms' }"
                        tabindex="0"
                        :aria-label="item.title + ': ' + item.desc"
                    >
                        <!-- Timeline dot -->
                        <div class="hidden md:flex items-center justify-center w-12 h-12 rounded-full bg-white border-4 border-primary shadow-md flex-shrink-0 relative z-10">
                            <component :is="item.icon" class="w-5 h-5 text-primary" />
                        </div>

                        <!-- Content card -->
                        <div class="flex-1 bg-white rounded-2xl p-6 border border-gray-200 hover:border-primary/30 hover:shadow-xl transition-all duration-300 group">
                            <!-- Mobile icon -->
                            <div class="md:hidden flex items-center justify-center w-10 h-10 rounded-full border-2 border-primary/30 text-primary mb-4">
                                <component :is="item.icon" class="w-5 h-5" />
                            </div>

                            <div class="font-semibold text-xl text-gray-900 mb-3 group-hover:text-primary transition-colors">
                                {{ item.title }}
                            </div>
                            <div class="text-gray-600 text-base leading-relaxed">
                                {{ item.desc }}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from "vue";
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
} from "@heroicons/vue/24/outline";

const items = ref([
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
