<template>
    <Transition name="slide-up">
        <div
            v-if="showInstallPrompt && !isInstalled"
            class="fixed bottom-0 left-0 right-0 z-[9999] bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-2xl"
        >
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between gap-4">
                    <!-- Icon and Text -->
                    <div class="flex items-center gap-3 flex-1">
                        <div
                            class="flex-shrink-0 w-12 h-12 bg-white rounded-xl flex items-center justify-center"
                        >
                            <svg
                                class="w-7 h-7 text-primary-600"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"
                                />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-lg mb-1">
                                Install QuickJuan POS
                            </h3>
                            <p class="text-sm text-primary-100 line-clamp-2">
                                Install our app for faster access, offline
                                support, and a better experience!
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button
                            @click="handleInstall"
                            class="px-6 py-2.5 bg-white text-primary-600 rounded-lg font-semibold hover:bg-primary-50 transition-colors shadow-lg"
                        >
                            Install
                        </button>
                        <button
                            @click="handleDismiss"
                            class="p-2 hover:bg-primary-500 rounded-lg transition-colors"
                            aria-label="Dismiss"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { usePWA } from "@/composables/usePWA";

const { showInstallPrompt, isInstalled, installPWA, dismissInstall } = usePWA();

const handleInstall = async () => {
    const installed = await installPWA();
    if (installed) {
        console.log("App installed successfully!");
    }
};

const handleDismiss = () => {
    dismissInstall();
};
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition: transform 0.3s ease-out, opacity 0.3s ease-out;
}

.slide-up-enter-from {
    transform: translateY(100%);
    opacity: 0;
}

.slide-up-leave-to {
    transform: translateY(100%);
    opacity: 0;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
