<template>
    <section
        class="relative w-full flex flex-col items-center justify-center text-center mx-auto max-w-none backdrop-blur px-4 bg-secondary py-10"
    >
        <h2 class="text-4xl md:text-5xl font-bold text-fourth mb-10">
            Focused on What You Need
        </h2>
        <div
            class="relative w-full max-w-6xl mx-auto flex flex-col items-start text-left z-10"
        >
            <ul
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-8 w-full"
            >
                <li
                    v-for="(reason, idx) in reasons"
                    :key="idx"
                    class="relative flex items-start group fade-in-up-checklist"
                    :style="{ animationDelay: idx * 80 + 'ms' }"
                    tabindex="0"
                    :aria-label="reason.title + ': ' + reason.desc"
                >
                    <!-- Large checkmark only -->
                    <span class="checkmark flex-shrink-0 mt-1 mr-4">
                        <svg
                            width="38"
                            height="38"
                            viewBox="0 0 38 38"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M11 20.5l6 6 10-13"
                                stroke="#fbbf24"
                                stroke-width="4"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>
                    <!-- Icon and content -->
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-full text-2xl mr-4 shadow-sm"
                    >
                        <span aria-hidden="true">{{ reason.icon }}</span>
                    </span>
                    <div class="flex-1 tracking-wider">
                        <div class="font-bold text-lg text-dark-800 mb-1">
                            {{ reason.title }}
                        </div>
                        <div class="text-primary text-base">
                            {{ reason.desc }}
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from "vue";
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
