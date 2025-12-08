<template>
    <Dialog
        :visible="props.visible"
        modal
        header="Item Modifiers"
        :style="{ width: '35rem', maxWidth: '90vw' }"
        :closable="true"
        @update:visible="$emit('update:visible', $event)"
        class="item-modifiers-modal"
    >
        <div class="space-y-6">
            <!-- Item Header -->
            <div v-if="item" class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="pi pi-shopping-cart text-blue-600 text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-lg text-gray-900 truncate">
                            {{ item.name }}
                        </h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="pi pi-hashtag mr-1"></i>
                                Qty: {{ item.quantity }}
                            </span>
                            <span class="text-sm text-gray-500">
                                {{ formatMoney(item.price * item.quantity) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modifiers List -->
            <div v-if="modifiers && modifiers.length > 0" class="space-y-4">
                <div class="flex items-center gap-2 mb-4">
                    <i class="pi pi-sliders-h text-gray-600"></i>
                    <h4 class="font-semibold text-gray-900">Applied Modifiers</h4>
                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs font-medium">
                        {{ modifiers.length }}
                    </span>
                </div>

                <div class="grid gap-3 max-h-96 overflow-y-auto">
                    <div
                        v-for="(modifier, index) in modifiers"
                        :key="index"
                        class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow duration-200"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="pi pi-check-circle text-green-600"></i>
                            </div>
                            <div class="flex-1 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h5 class="font-medium text-gray-900">
                                        Modifier
                                    </h5>
                                </div>

                                <div v-if="modifier.modifier" class="space-y-3">
                                    <!-- Special Instructions -->
                                    <div
                                        v-if="modifier.modifier.specialInstructions"
                                        class="bg-amber-50 border border-amber-200 rounded-lg p-3"
                                    >
                                        <div class="flex items-start gap-2">
                                            <i class="pi pi-exclamation-triangle text-amber-600 mt-0.5 flex-shrink-0"></i>
                                            <div class="flex-1">
                                                <span class="font-medium text-amber-800 text-sm">
                                                    Special Instructions
                                                </span>
                                                <p class="text-amber-700 mt-1 text-sm leading-relaxed">
                                                    {{ modifier.modifier.specialInstructions }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modifier Options -->
                                    <div
                                        v-if="
                                            modifier.modifier &&
                                            Object.keys(modifier.modifier).length > 1
                                        "
                                        class="bg-gray-50 border border-gray-200 rounded-lg p-4"
                                    >
                                        <div class="flex items-center gap-2 mb-3">
                                            <i class="pi pi-cog text-gray-600"></i>
                                            <span class="font-medium text-gray-800 text-sm">
                                                Customization Details
                                            </span>
                                        </div>

                                        <div class="space-y-2">
                                            <div
                                                v-for="(value, key) in modifier.modifier"
                                                :key="key"
                                                v-show="String(key) !== 'specialInstructions'"
                                                class="flex items-start gap-3 py-2"
                                            >
                                                <span class="font-medium text-gray-600 text-sm capitalize min-w-0 flex-shrink-0" style="width: 120px;">
                                                    {{
                                                        String(key)
                                                            .replace(/([A-Z])/g, " $1")
                                                            .toLowerCase()
                                                    }}:
                                                </span>
                                                <div class="flex-1 min-w-0">
                                                    <span
                                                        v-if="Array.isArray(value)"
                                                        class="text-gray-800 text-sm bg-white px-2 py-1 rounded border"
                                                    >
                                                        {{ value.map(item => item.name || item).join(', ') }}
                                                    </span>
                                                    <span
                                                        v-else
                                                        class="text-gray-800 text-sm bg-white px-2 py-1 rounded border font-medium"
                                                    >
                                                        {{ value }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="pi pi-sliders-h text-gray-400 text-2xl"></i>
                </div>
                <h4 class="font-medium text-gray-900 mb-2">No Modifiers</h4>
                <p class="text-gray-500 text-sm">This item doesn't have any custom modifiers applied.</p>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <Button
                    label="Close"
                    severity="secondary"
                    outlined
                    @click="$emit('update:visible', false)"
                    class="px-6"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { Dialog, Button } from "primevue";
import { formatMoney } from "@/Utils/FormatMoney";

const props = defineProps<{
    visible: boolean;
    item: any;
    modifiers: any[];
}>();

defineEmits<{
    "update:visible": [value: boolean];
}>();
</script>

<style scoped>
.item-modifiers-modal :deep(.p-dialog-header) {
    @apply bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200;
}

.item-modifiers-modal :deep(.p-dialog-header .p-dialog-title) {
    @apply text-gray-900 font-semibold;
}

.item-modifiers-modal :deep(.p-dialog-content) {
    @apply p-6;
}

.item-modifiers-modal :deep(.p-dialog-footer) {
    @apply px-6 py-4 bg-gray-50;
}

/* Custom scrollbar for modifier list */
.item-modifiers-modal :deep(.overflow-y-auto) {
    scrollbar-width: thin;
    scrollbar-color: rgb(156 163 175) transparent;
}

.item-modifiers-modal :deep(.overflow-y-auto::-webkit-scrollbar) {
    width: 6px;
}

.item-modifiers-modal :deep(.overflow-y-auto::-webkit-scrollbar-track) {
    background: transparent;
}

.item-modifiers-modal :deep(.overflow-y-auto::-webkit-scrollbar-thumb) {
    background-color: rgb(156 163 175);
    border-radius: 3px;
}

.item-modifiers-modal :deep(.overflow-y-auto::-webkit-scrollbar-thumb:hover) {
    background-color: rgb(107 114 128);
}

/* Smooth transitions */
.item-modifiers-modal :deep(.transition-shadow) {
    transition-property: box-shadow;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
