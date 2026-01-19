<template>
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <h2 v-if="content.title" class="text-3xl font-bold text-gray-900 mb-2">
            {{ content.title }}
        </h2>
        <p v-if="content.description" class="text-gray-600 mb-6">
            {{ content.description }}
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
                v-for="product in content.products"
                :key="product.name"
                class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
            >
                <img
                    v-if="product.image"
                    :src="`/storage/${product.image}`"
                    :alt="product.name"
                    class="w-full h-48 object-cover"
                />
                <div class="p-4">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        {{ product.name }}
                    </h3>
                    <p
                        v-if="product.description"
                        class="text-gray-600 text-sm mb-3"
                    >
                        {{ product.description }}
                    </p>
                    <div class="flex items-center justify-between">
                        <span
                            v-if="product.price"
                            class="text-2xl font-bold text-orange-600"
                            >${{ product.price }}</span
                        >
                        <a
                            v-if="product.button_url"
                            :href="product.button_url"
                            class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 transition-colors"
                        >
                            {{ product.button_text || "View Product" }}
                        </a>
                    </div>
                </div>
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
