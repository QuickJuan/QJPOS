<template>
    <div class="bg-gray-100 rounded-lg p-8 mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">
            {{ content.title }}
        </h2>
        <p v-if="content.description" class="text-gray-600 mb-6">
            {{ content.description }}
        </p>
        <form @submit.prevent="subscribe" class="max-w-md mx-auto flex gap-2">
            <input
                v-model="email"
                type="email"
                :placeholder="content.placeholder || 'Enter your email'"
                required
                class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-600"
            />
            <button
                type="submit"
                class="bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-orange-700 transition-colors"
            >
                {{ content.button_text || "Subscribe" }}
            </button>
        </form>
        <p v-if="message" class="mt-4 text-sm" :class="messageClass">
            {{ message }}
        </p>
    </div>
</template>

<script setup>
import { ref } from "vue";

defineProps({
    content: Object,
    settings: Object,
});

const email = ref("");
const message = ref("");
const messageClass = ref("");

const subscribe = () => {
    // TODO: Implement newsletter subscription
    message.value = "Thank you for subscribing!";
    messageClass.value = "text-green-600";
    email.value = "";
};
</script>
