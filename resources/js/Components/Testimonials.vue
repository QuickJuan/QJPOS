<template>
    <section
        class="relative w-full py-16 px-4 flex flex-col items-center justify-center text-center backdrop-blur z-10"
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
                class="flex flex-col items-center bg-amber-50 rounded-xl shadow-lg p-6 h-full relative"
            >
                <!-- Reviewer image placeholder -->
                <div
                    class="w-16 h-16 rounded-full bg-amber-200 flex items-center justify-center mb-4 overflow-hidden border-2 border-amber-300"
                >
                    <img
                        v-if="testimonial.image"
                        :src="testimonial.image"
                        alt="Photo of {{ testimonial.name }}"
                        class="object-cover w-full h-full"
                    />
                    <span v-else class="text-3xl">{{ testimonial.emoji }}</span>
                </div>
                <!-- Star rating -->
                <div
                    class="flex items-center mb-2"
                    aria-label="Rated {{ testimonial.rating }} out of 5 stars"
                >
                    <span
                        v-for="i in 5"
                        :key="i"
                        class="text-amber-400 text-xl"
                        aria-hidden="true"
                    >
                        <svg
                            v-if="i <= testimonial.rating"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            class="inline w-5 h-5"
                        >
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.38-2.455a1 1 0 00-1.175 0l-3.38 2.455c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.05 9.394c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.967z"
                            />
                        </svg>
                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 20 20"
                            stroke="currentColor"
                            class="inline w-5 h-5 text-gray-300"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.38-2.455a1 1 0 00-1.175 0l-3.38 2.455c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.05 9.394c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.967z"
                            />
                        </svg>
                    </span>
                </div>
                <blockquote class="text-gray-700 italic mb-4">
                    "{{ testimonial.quote }}"
                </blockquote>
                <div class="mt-auto flex flex-col items-center w-full pt-4">
                    <div class="font-bold text-gray-900">
                        {{ testimonial.name }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ testimonial.role }}
                    </div>
                </div>
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
        rating: 5,
        image: "", // Add image URL if available
    },
    {
        emoji: "💊",
        quote: "Inventory tracking is a breeze and compliance is never a worry.",
        name: "Dr. John D.",
        role: "Pharmacy Manager",
        rating: 4,
        image: "",
    },
    {
        emoji: "🍔",
        quote: "Fast food orders are lightning quick and our sales have grown!",
        name: "Alex T.",
        role: "Kiosk Franchisee",
        rating: 5,
        image: "",
    },
]);
</script>

<style scoped>
/* Ensure name/role always at the bottom of card */
.grid > div {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    height: 100%;
}
</style>
