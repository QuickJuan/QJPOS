<template>
    <section
        id="contact"
        class="relative w-full py-20 px-8 flex flex-col items-center justify-center text-center overflow-hidden bg-gradient-to-b from-gray-50 to-white"
    >
        <!-- Decorative elements -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-20 left-10 w-32 h-32 bg-primary/5 rounded-full blur-xl"></div>
            <div class="absolute bottom-20 right-10 w-40 h-40 bg-secondary/5 rounded-full blur-xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-2xl mx-auto">
            <h2
                class="text-3xl md:text-5xl font-bold text-gray-900 mb-6"
            >
                Get in Touch
            </h2>
            <p class="text-lg text-gray-600 mb-12 max-w-lg mx-auto leading-relaxed">
                Have questions or want a demo? Fill out the form and our team
                will reach out to you as soon as possible.
            </p>

            <form
                class="w-full flex flex-col gap-6 bg-white rounded-2xl shadow-xl p-8 border border-gray-200"
                @submit.prevent="submitForm"
                aria-label="Contact form"
            >
                <div class="flex flex-col items-start w-full">
                    <label for="name" class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                        <UserIcon class="w-5 h-5" />
                        Name
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition-all duration-200 placeholder-gray-400"
                        placeholder="Your Name"
                    />
                </div>

                <div class="flex flex-col items-start w-full">
                    <label for="email" class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                        <EnvelopeIcon class="w-5 h-5" />
                        Email
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition-all duration-200 placeholder-gray-400"
                        placeholder="you@email.com"
                    />
                </div>

                <div class="flex flex-col items-start w-full">
                    <label for="message" class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                        <ChatBubbleLeftIcon class="w-5 h-5" />
                        Message
                    </label>
                    <textarea
                        id="message"
                        v-model="form.message"
                        required
                        rows="4"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition-all duration-200 placeholder-gray-400 resize-none"
                        placeholder="How can we help you?"
                    ></textarea>
                </div>

                <button
                    type="submit"
                    class="px-8 py-3 rounded-lg bg-primary text-white font-semibold text-lg shadow-lg hover:bg-primary/90 hover:shadow-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="loading"
                >
                    <span v-if="!loading" class="flex items-center justify-center gap-2">
                        Send Message
                        <EnvelopeIcon class="w-5 h-5" />
                    </span>
                    <span v-else class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        Sending...
                    </span>
                </button>

                <div
                    v-if="success"
                    class="text-green-600 font-semibold mt-4 flex items-center justify-center gap-2 p-3 bg-green-50 rounded-lg border border-green-200"
                >
                    <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Thank you! We will get back to you soon.
                </div>

                <div
                    v-if="error"
                    class="text-red-600 font-semibold mt-4 flex items-center justify-center gap-2 p-3 bg-red-50 rounded-lg border border-red-200"
                >
                    <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Something went wrong. Please try again.
                </div>
            </form>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { UserIcon, EnvelopeIcon, ChatBubbleLeftIcon } from "@heroicons/vue/24/outline";
const form = ref({ name: "", email: "", message: "" });
const success = ref(false);
const error = ref(false);
const loading = ref(false);

function submitForm() {
    success.value = false;
    error.value = false;
    loading.value = true;
    setTimeout(() => {
        loading.value = false;
        if (form.value.name && form.value.email && form.value.message) {
            success.value = true;
            form.value = { name: "", email: "", message: "" };
        } else {
            error.value = true;
        }
    }, 1200);
}
</script>

<style scoped>
/* Add a little floating animation to the background shapes for extra appeal */
.absolute.bg-gray-200 {
    animation: float1 8s ease-in-out infinite alternate;
}
.absolute.bg-gray-300 {
    animation: float2 10s ease-in-out infinite alternate;
}
@keyframes float1 {
    0% {
        transform: translateY(0) scale(1);
    }
    100% {
        transform: translateY(30px) scale(1.08);
    }
}
@keyframes float2 {
    0% {
        transform: translateY(0) scale(1);
    }
    100% {
        transform: translateY(-30px) scale(1.05);
    }
}
</style>
