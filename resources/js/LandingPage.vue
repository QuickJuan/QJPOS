<template>
  <div class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Background Media: Video or Image -->
    <div class="absolute inset-0 z-0 w-full h-full overflow-hidden">
      <transition name="fade" mode="out-in">
        <iframe
          v-if="showVideo"
          key="video"
          class="w-full h-full object-cover"
          src="https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1&mute=1&controls=0&loop=1&playlist=dQw4w9WgXcQ&modestbranding=1&showinfo=0&rel=0"
          title="Landing Video Background"
          frameborder="0"
          allow="autoplay; encrypted-media"
          allowfullscreen
          style="pointer-events: none"
        ></iframe>
        <img
          v-else
          key="image"
          :src="posImages[currentImage]"
          class="w-full h-full object-cover"
          alt="POS Machine"
        />
      </transition>
      <div class="absolute inset-0 bg-black bg-opacity-60"></div>
      <!-- Swap Button -->
      <button
        @click="toggleMedia"
        class="absolute top-6 right-6 z-20 px-4 py-2 rounded-lg bg-amber-400 text-gray-900 font-bold shadow hover:bg-amber-300 transition-all duration-200"
      >
        {{ showVideo ? 'Show POS Images' : 'Show Video' }}
      </button>
      <!-- Image navigation -->
      <div v-if="!showVideo" class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-2 z-20">
        <button
          v-for="(img, idx) in posImages"
          :key="img"
          @click="currentImage = idx"
          :class="[
            'w-4 h-4 rounded-full border-2 border-amber-400',
            idx === currentImage ? 'bg-amber-400' : 'bg-white opacity-60',
          ]"
        ></button>
      </div>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex flex-col items-center justify-center text-center px-6">
      <h1 class="text-5xl md:text-7xl font-extrabold text-white drop-shadow-lg mb-6 font-sans tracking-tight">
        Welcome to <span class="text-amber-400">QuickJuan POS</span>
      </h1>
      <p class="text-xl md:text-2xl text-white mb-8 max-w-2xl font-light drop-shadow">
        The modern, multi-tenant Point of Sale platform for retail, restaurants, and fast food. Manage your business, inventory, and sales with ease.
      </p>
      <router-link to="/register">
        <button class="px-8 py-4 rounded-lg bg-amber-400 text-gray-900 font-bold text-xl shadow-lg hover:bg-amber-300 transition-all duration-200">
          Get Started Free
        </button>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const showVideo = ref(true);
const posImages = [
  'https://images.unsplash.com/photo-1515168833906-d2a3b82b302b?auto=format&fit=crop&w=900&q=80',
  'https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=900&q=80',
  'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=900&q=80',
];
const currentImage = ref(0);

function toggleMedia() {
  showVideo.value = !showVideo.value;
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
