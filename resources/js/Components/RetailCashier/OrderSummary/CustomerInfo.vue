<template>
    <div class="p-4 border-b border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-bold text-secondary-800">Order Summary</h2>
            <div
                class="text-sm text-secondary-600 font-medium bg-gray-200 px-3 py-1 rounded-full"
            >
                #Shift: {{ currentShift }}
            </div>
        </div>

        <!-- Customer Info -->
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <UserIcon class="w-4 h-4 text-secondary-500" />
                <span class="text-sm text-secondary-600">Customer:</span>
                <span class="text-sm font-medium text-secondary-800">
                    {{ tableInfo?.customer_name || "Walk-in Customer" }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <MapPinIcon class="w-4 h-4 text-secondary-500" />
                <span class="text-sm text-secondary-600">Table:</span>
                <button
                    v-if="!tableInfo.name"
                    @click="$emit('selectTable')"
                    class="text-sm font-medium text-primary-600 hover:text-primary-700 underline cursor-pointer transition-colors duration-200"
                >
                    No Table - Click to select
                </button>
                <span v-else class="text-sm font-medium text-secondary-800">
                    {{ tableInfo.name }}
                </span>
                <span class="text-xs text-secondary-500">•</span>
                <span class="text-sm text-secondary-600">
                    {{ tableInfo?.number_of_pax || 1 }} guests
                </span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { UserIcon, MapPinIcon } from "@heroicons/vue/24/outline";
import { ref } from "vue";

const props = defineProps<{
    cart: any;
    tableInfo: any;
}>();

defineEmits<{
    selectTable: [];
}>();

const currentShift = ref(props.cart?.id);
</script>
