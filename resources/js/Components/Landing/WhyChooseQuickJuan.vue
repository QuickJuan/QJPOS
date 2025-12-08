<template>
    <section
        class="relative w-full flex flex-col items-center justify-center text-center mx-auto max-w-none bg-gradient-to-br from-primary/5 to-secondary/5 px-8 py-20 overflow-hidden"
    >
        <!-- Decorative elements -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-10 right-10 w-40 h-40 bg-primary/10 rounded-full blur-2xl"></div>
            <div class="absolute bottom-10 left-10 w-32 h-32 bg-secondary/10 rounded-full blur-2xl"></div>
        </div>

        <h2 class="text-3xl md:text-5xl font-bold text-gray-900 mb-16 relative z-10">
            Why Choose QuickJuan?
        </h2>

        <div
            class="relative w-full max-w-5xl mx-auto z-10"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div
                    v-for="(reason, idx) in reasons"
                    :key="idx"
                    class="bg-white rounded-xl p-8 border border-gray-200 hover:border-primary/30 hover:shadow-lg transition-all duration-300 fade-in-up-checklist"
                    :style="{ animationDelay: idx * 100 + 'ms' }"
                    tabindex="0"
                    :aria-label="reason.title + ': ' + reason.desc"
                >
                    <!-- Icon -->
                    <div class="flex items-center justify-center w-14 h-14 rounded-full bg-primary/10 border border-primary/20 text-primary mb-6">
                        <component :is="reason.icon" class="w-7 h-7" />
                    </div>

                    <!-- Checkmark -->
                    <div class="flex justify-center mb-4">
                        <div class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Verified
                        </div>
                    </div>

                    <div class="font-semibold text-xl text-gray-900 mb-3">
                        {{ reason.title }}
                    </div>
                    <div class="text-gray-600 text-base leading-relaxed">
                        {{ reason.desc }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from "vue";
import {
    BoltIcon,
    DevicePhoneMobileIcon,
    LockClosedIcon,
    ChartBarIcon,
    WrenchScrewdriverIcon,
    LightBulbIcon,
} from "@heroicons/vue/24/outline";

const reasons = ref([
    {
        icon: BoltIcon,
        title: "Fast & Reliable",
        desc: "Lightning-fast transactions and robust uptime for your business.",
    },
    {
        icon: DevicePhoneMobileIcon,
        title: "Mobile Friendly",
        desc: "Works seamlessly on any device, anywhere you do business.",
    },
    {
        icon: LockClosedIcon,
        title: "Secure",
        desc: "Enterprise-grade security keeps your data and customers safe.",
    },
    {
        icon: ChartBarIcon,
        title: "Real-Time Analytics",
        desc: "Instant insights into sales, inventory, and customer trends.",
    },
    {
        icon: WrenchScrewdriverIcon,
        title: "Easy Setup",
        desc: "Get started in minutes with intuitive onboarding.",
    },
    {
        icon: LightBulbIcon,
        title: "Smart Inventory",
        desc: "Automated stock tracking and low-inventory alerts.",
    },
]);

onMounted(async () => {
    await nextTick();
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
.checkmark {
    min-width: 38px;
    min-height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
}
li:focus {
    outline: none;
    box-shadow: 0 0 0 3px #fbbf24;
    border-radius: 0.5rem;
}
</style>
