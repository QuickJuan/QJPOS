<template>
    <section
        class="relative w-full py-16 px-4 flex flex-col items-center justify-center text-center"
    >
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-10">
            What Our Clients Say
        </h2>
        <div
            class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full max-w-6xl mx-auto"
        >
            <div
                v-for="(testimonial, idx) in testimonials"
                :key="idx"
                class="flex flex-col items-center bg-amber-50 rounded-xl shadow-lg p-6 h-full"
            >
                <div
                    class="w-16 h-16 rounded-full bg-amber-200 flex items-center justify-center mb-4"
                >
                    <span class="text-3xl">{{ testimonial.emoji }}</span>
                </div>
                <blockquote class="text-gray-700 italic mb-4">
                    "{{ testimonial.quote }}"
                </blockquote>
                <div class="font-bold text-gray-900">
                    {{ testimonial.name }}
                </div>
                <div class="text-sm text-gray-500">{{ testimonial.role }}</div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref } from "vue";
const testimonials = ref([
    {
        emoji: "🍽️",
        quote: "QuickJuan POS made our restaurant operations seamless. Staff learned it in a day!",
        name: "Maria S.",
        role: "Restaurant Owner",
    },
    {
        emoji: "💊",
        quote: "Inventory tracking is a breeze and compliance is never a worry.",
        name: "Dr. John D.",
        role: "Pharmacy Manager",
    },
    {
        emoji: "🍔",
        quote: "Fast food orders are lightning quick and our sales have grown!",
        name: "Alex T.",
        role: "Kiosk Franchisee",
    },
]);
</script>

<style scoped></style>
