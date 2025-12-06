<template>
    <GuestLayout>
        <div class="py-20 bg-gradient-to-br from-primary/5 via-white to-secondary/5">
            <div class="container mx-auto px-6 md:px-12 lg:px-24">
                <div class="max-w-4xl mx-auto">
                    <div class="text-center mb-12">
                        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
                            {{ product.name }}
                        </h1>
                        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                            {{ product.description }}
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-12">
                        <img :src="product.image" :alt="product.name" class="w-full h-64 md:h-80 object-cover" />
                    </div>

                    <div class="bg-white rounded-xl p-8 shadow-lg">
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Key Capabilities</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="cap in product.capabilities" :key="cap.title" class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ cap.title }}</h3>
                                <p class="text-gray-600">{{ cap.desc }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-12">
                        <Link :href="route('tenant-registration')" class="inline-flex items-center px-6 py-3 bg-primary-400 hover:bg-primary-700 text-white rounded-lg shadow font-semibold">
                            Start Free Trial
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineProps({
    product: {
        type: Object,
        required: true
    }
});
</script>
