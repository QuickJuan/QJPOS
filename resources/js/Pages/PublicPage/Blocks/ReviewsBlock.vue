<template>
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <h2 v-if="content.title" class="text-3xl font-bold text-gray-900 mb-6">
            {{ content.title }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
                v-for="review in content.reviews"
                :key="review.customer_name"
                class="border rounded-lg p-6"
            >
                <div class="flex items-center mb-4">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900">
                            {{ review.customer_name }}
                        </h4>
                        <div class="flex text-yellow-400 text-sm">
                            <span v-for="n in 5" :key="n">{{
                                n <= review.rating ? "★" : "☆"
                            }}</span>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">{{ review.review_text }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    content: Object,
    settings: Object,
});
</script>
