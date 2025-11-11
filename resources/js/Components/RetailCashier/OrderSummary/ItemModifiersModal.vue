<template>
    <Dialog
        :visible="props.visible"
        modal
        header="Item Modifiers"
        :style="{ width: '30rem' }"
        @update:visible="$emit('update:visible', $event)"
    >
        <div class="space-y-4">
            <div v-if="item" class="border-b pb-3">
                <h3 class="font-semibold text-lg text-gray-900">
                    {{ item.name }}
                </h3>
                <p class="text-sm text-gray-600">
                    Quantity: {{ item.quantity }}
                </p>
            </div>

            <div v-if="modifiers && modifiers.length > 0" class="space-y-3">
                <div
                    v-for="(modifier, index) in modifiers"
                    :key="index"
                    class="border border-gray-200 rounded-lg p-3 bg-gray-50"
                >
                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-900">
                            Modifier {{ index + 1 }}
                        </h4>

                        <div v-if="modifier.modifier" class="space-y-1">
                            <!-- Special Instructions -->
                            <div
                                v-if="modifier.modifier.specialInstructions"
                                class="text-sm"
                            >
                                <span class="font-medium text-gray-700">
                                    Special Instructions:
                                </span>
                                <p
                                    class="text-gray-600 mt-1 bg-white p-2 rounded border"
                                >
                                    {{ modifier.modifier.specialInstructions }}
                                </p>
                            </div>

                            <!-- Modifier Options -->
                            <div
                                v-if="
                                    modifier.modifier &&
                                    Object.keys(modifier.modifier).length > 1
                                "
                                class="text-sm"
                            >
                                <span class="font-medium text-gray-700">
                                    Options:
                                </span>
                                <div class="mt-1 space-y-1">
                                    <div
                                        v-for="(
                                            value, key
                                        ) in modifier.modifier"
                                        :key="key"
                                        v-show="
                                            String(key) !==
                                            'specialInstructions'
                                        "
                                        class="bg-white p-2 rounded border text-gray-600"
                                    >
                                        <span class="font-medium capitalize">
                                            {{
                                                String(key)
                                                    .replace(/([A-Z])/g, " $1")
                                                    .toLowerCase()
                                            }}:
                                        </span>
                                        <span v-if="Array.isArray(value)">
                                            {{ value.map(item => item.name || item).join(', ') }}
                                        </span>
                                        <span v-else>
                                            {{ value }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
                <p>No modifiers found for this item.</p>
            </div>
        </div>

        <template #footer>
            <Button
                label="Close"
                severity="secondary"
                @click="$emit('update:visible', false)"
            />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { Dialog, Button } from "primevue";

const props = defineProps<{
    visible: boolean;
    item: any;
    modifiers: any[];
}>();

defineEmits<{
    "update:visible": [value: boolean];
}>();
</script>
