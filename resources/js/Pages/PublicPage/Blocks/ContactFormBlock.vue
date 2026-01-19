<template>
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">
            {{ content.title }}
        </h2>
        <form @submit.prevent="submitForm" class="max-w-2xl space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Name</label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-600"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Email</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-600"
                    />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Message</label
                >
                <textarea
                    v-model="form.message"
                    rows="4"
                    required
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-600"
                ></textarea>
            </div>
            <button
                type="submit"
                class="bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-orange-700 transition-colors"
            >
                {{ content.button_text || "Send Message" }}
            </button>
            <p v-if="message" class="text-sm" :class="messageClass">
                {{ message }}
            </p>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive } from "vue";

defineProps({
    content: Object,
    settings: Object,
});

const form = reactive({
    name: "",
    email: "",
    message: "",
});

const message = ref("");
const messageClass = ref("");

const submitForm = () => {
    // TODO: Implement contact form submission
    message.value = "Thank you! We will get back to you soon.";
    messageClass.value = "text-green-600";
    form.name = "";
    form.email = "";
    form.message = "";
};
</script>
