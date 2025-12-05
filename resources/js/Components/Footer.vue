<template>
    <footer
        class="w-full bg-gray-800 text-white pt-12 pb-8 mt-0"
    >
        <div class="max-w-6xl mx-auto px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <!-- Brand/Info -->
                <div class="text-center md:text-left">
                    <div class="text-2xl font-bold text-primary mb-4">QuickJuan POS</div>
                    <div class="text-gray-300 text-sm leading-relaxed">
                        Empowering businesses with smart, reliable point-of-sale solutions.
                    </div>
                </div>

                <!-- Office Hours -->
                <div class="text-center">
                    <div class="text-lg font-semibold text-primary mb-2 flex items-center justify-center gap-2">
                        <ClockIcon class="w-5 h-5" />
                        Office Hours
                    </div>
                    <div class="text-gray-300 text-sm">
                        Monday to Saturday<br>
                        8:00 AM – 7:00 PM
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="text-center md:text-right">
                    <div class="text-lg font-semibold text-primary mb-4">Quick Links</div>
                    <nav aria-label="Footer menu">
                        <ul class="space-y-2 text-sm">
                            <li>
                                <a href="/terms-and-conditions" class="text-gray-300 hover:text-primary transition-colors flex items-center gap-2 justify-center md:justify-end">
                                    <DocumentTextIcon class="w-4 h-4" />
                                    Terms & Conditions
                                </a>
                            </li>
                            <li>
                                <a href="/privacy-policy" class="text-gray-300 hover:text-primary transition-colors flex items-center gap-2 justify-center md:justify-end">
                                    <ShieldCheckIcon class="w-4 h-4" />
                                    Privacy Policy
                                </a>
                            </li>
                            <li>
                                <a href="/customer-support" class="text-gray-300 hover:text-primary transition-colors flex items-center gap-2 justify-center md:justify-end">
                                    <ChatBubbleLeftRightIcon class="w-4 h-4" />
                                    Support
                                </a>
                            </li>
                            <li>
                                <a href="/faq" class="text-gray-300 hover:text-primary transition-colors flex items-center gap-2 justify-center md:justify-end">
                                    <QuestionMarkCircleIcon class="w-4 h-4" />
                                    FAQ
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-white/20 pt-6 text-center">
                <div class="text-sm text-white/80">
                    &copy; {{ new Date().getFullYear() }} QuickJuan POS. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup lang="ts">
import {
    ClockIcon,
    DocumentTextIcon,
    ShieldCheckIcon,
    ChatBubbleLeftRightIcon,
    QuestionMarkCircleIcon,
} from "@heroicons/vue/24/outline";
</script>
