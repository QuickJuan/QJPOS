<template>
    <Dialog
        :visible="props.visible"
        modal
        header="Add Modifier"
        :style="{ width: '30rem' }"
        @update:visible="handleClose"
    >
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select Modifier
                </label>
                <div class="flex gap-2">
                    <select
                        v-model="selectedModifier"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Choose a modifier...</option>
                        <option
                            v-for="modifier in modifiers"
                            :key="modifier.id"
                            :value="modifier.id"
                        >
                            {{ modifier.name }} (+{{ formatMoney(modifier.price || 0) }})
                        </option>
                    </select>
                    <button
                        @click="showCustomModifier = true"
                        class="px-3 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center justify-center"
                        title="Add custom modifier"
                    >
                        <PlusIcon class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Custom Modifier Input -->
            <div v-if="showCustomModifier" class="space-y-3 p-3 bg-gray-50 rounded-md">
                <h4 class="text-sm font-medium text-gray-700">Add Custom Modifier</h4>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Name
                        </label>
                        <input
                            v-model="customModifierName"
                            type="text"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="e.g., Extra spicy"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Price
                        </label>
                        <input
                            v-model="customModifierPrice"
                            type="number"
                            step="0.01"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="0.00"
                        />
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button
                        @click="showCustomModifier = false"
                        class="px-3 py-1 text-xs bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
                    >
                        Cancel
                    </button>
                    <button
                        @click="addCustomModifier"
                        :disabled="!customModifierName.trim() || !customModifierPrice"
                        class="px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                    >
                        Add
                    </button>
                </div>
            </div>

            <div v-if="selectedModifierData">
                <p class="text-sm text-gray-600">
                    <strong>{{ selectedModifierData.name }}</strong> will be added to {{ selectedItems.length }} selected item(s).
                </p>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end space-x-3">
                <Button
                    label="Cancel"
                    class="p-button-text"
                    @click="handleClose"
                />
                <Button
                    label="Add Modifier"
                    class="p-button-primary"
                    :disabled="!selectedModifier"
                    @click="handleAdd"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import { PlusIcon } from "@heroicons/vue/24/outline";
import { formatMoney } from "@/Utils/FormatMoney";

const props = defineProps<{
    visible: boolean;
    selectedItems: any[];
    modifiers: any[];
}>();

const emit = defineEmits<{
    add: [modifier: string | any];
    "update:visible": [value: boolean];
}>();

const selectedModifier = ref("");
const showCustomModifier = ref(false);
const customModifierName = ref("");
const customModifierPrice = ref("");

const selectedModifierData = computed(() => {
    return props.modifiers.find(m => m.id == selectedModifier);
});

watch(() => props.visible, (newValue) => {
    if (newValue) {
        selectedModifier.value = "";
    }
});

const addCustomModifier = () => {
    if (customModifierName.value.trim() && customModifierPrice.value) {
        // Create a custom modifier object
        const customModifier = {
            id: `custom-${Date.now()}`,
            name: customModifierName.value.trim(),
            price: parseFloat(customModifierPrice.value),
            isCustom: true,
        };
        emit("add", customModifier);
        // Reset form
        customModifierName.value = "";
        customModifierPrice.value = "";
        showCustomModifier.value = false;
    }
};

const handleAdd = () => {
    if (selectedModifier.value) {
        emit("add", selectedModifier.value);
        emit("update:visible", false);
    }
};

const handleClose = () => {
    emit("update:visible", false);
};
</script>
