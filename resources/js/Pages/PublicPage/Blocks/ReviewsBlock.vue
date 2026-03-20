<template>
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <h2 v-if="content.title" class="text-3xl font-bold text-gray-900 mb-6">
            {{ content.title }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
                v-for="(review, index) in content.reviews"
                :key="index"
                class="border rounded-lg p-6"
            >
                <div class="flex items-center mb-4 gap-3">
                    <img
                        v-if="review.image"
                        :src="`/storage/${review.image}`"
                        :alt="review.author"
                        class="w-10 h-10 rounded-full object-cover flex-shrink-0"
                    />
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900">
                            {{ review.author }}
                        </h4>
                        <p v-if="review.title" class="text-sm text-gray-500">
                            {{ review.title }}
                        </p>
                        <div class="flex text-yellow-400 text-sm mt-1">
                            <span v-for="n in 5" :key="n">{{
                                n <= Number(review.rating) ? "★" : "☆"
                            }}</span>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">{{ review.text }}</p>
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
