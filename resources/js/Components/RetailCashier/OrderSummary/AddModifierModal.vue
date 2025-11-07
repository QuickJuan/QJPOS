<template>
    <Dialog
        :visible="props.visible"
        modal
        header="Add Modifier"
        :style="{ width: '35rem' }"
        @update:visible="handleClose"
    >
        <div class="space-y-4">
            <div class="max-h-96 overflow-y-auto">
                <div class="space-y-3">
                    <div
                        v-for="modifier in modifiers"
                        :key="modifier.id"
                        class="border border-gray-200 rounded-lg p-3"
                    >
                        <div class="flex items-start gap-3">
                            <Checkbox
                                v-model="selectedModifiers"
                                :value="modifier.id"
                                class="mt-0.5"
                            />
                            <div class="flex-1">
                                <div
                                    class="flex items-center justify-between mb-2"
                                >
                                    <h4 class="font-medium text-gray-900">
                                        {{ modifier.name }}
                                    </h4>
                                </div>

                                <!-- Modifier Options as PrimeVue Radio Buttons -->
                                <div
                                    v-if="
                                        modifier.list &&
                                        parseModifierList(modifier.list)
                                            .length > 0
                                    "
                                    class="flex items-center gap-3"
                                >
                                    <div
                                        v-for="option in parseModifierList(
                                            modifier.list
                                        )"
                                        :key="option.name"
                                        class="flex items-center"
                                    >
                                        <RadioButton
                                            :name="`modifier-${modifier.id}`"
                                            :value="option.name"
                                            v-model="
                                                selectedModifierValues[
                                                    modifier.id
                                                ]
                                            "
                                            @change="
                                                selectModifierOption(
                                                    modifier.name,
                                                    option
                                                )
                                            "
                                            :disabled="
                                                !selectedModifiers.includes(
                                                    modifier.id
                                                )
                                            "
                                            class="mr-2"
                                        />
                                        <label
                                            class="text-sm text-gray-700 cursor-pointer"
                                        >
                                            {{ option.name }}
                                            <span
                                                v-if="option.price"
                                                class="text-green-600 font-medium ml-1"
                                            >
                                                (+{{
                                                    formatMoney(option.price)
                                                }})
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Notes Section -->
            <div class="border-t pt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Special Instructions
                </label>
                <textarea
                    v-model="specialInstructions"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    placeholder="Add any special instructions or notes for this order..."
                ></textarea>
            </div>

            <div
                v-if="selectedModifiers.length > 0"
                class="text-sm text-gray-600 bg-blue-50 p-3 rounded"
            >
                <strong>{{ selectedModifiers.length }} modifier(s)</strong>
                selected to be added to {{ selectedItems.length }} item(s).
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
import Checkbox from "primevue/checkbox";
import RadioButton from "primevue/radiobutton";
import { formatMoney } from "@/Utils/FormatMoney";

const props = defineProps<{
    visible: boolean;
    selectedItems: any[];
    modifiers: any[];
}>();

const emit = defineEmits<{
    add: [modifierData: any];
    "update:visible": [value: boolean];
}>();

const selectedModifiers = ref<string[]>([]);
const selectedModifierOptions = ref<Record<string, any[]>>({});
const selectedModifierValues = ref<Record<string, string>>({});
const specialInstructions = ref("");

const selectedModifierData = computed(() => {
    return props.modifiers.find((m) => selectedModifiers.value.includes(m.id));
});

watch(
    () => props.visible,
    (newValue) => {
        if (newValue) {
            selectedModifiers.value = [];
            selectedModifierOptions.value = {};
            selectedModifierValues.value = {};
        }
    }
);

const selectModifierOption = (modifierId: string, option: any) => {
    // For radio buttons, only one option can be selected per modifier
    // Also ensure the modifier checkbox is checked when selecting an option
    if (!selectedModifiers.value.includes(modifierId)) {
        selectedModifiers.value.push(modifierId);
    }
    selectedModifierOptions.value[modifierId] = [option];
};

const handleAdd = () => {
    const selectedData = {
        selectedCartItems: props.selectedItems,
        modifiers: selectedModifiers.value,
        modifierOptions: selectedModifierOptions.value,
        specialInstructions: specialInstructions.value.trim(),
    };
    emit("add", selectedData);
    emit("update:visible", false);
};

const parseModifierList = (list: any) => {
    if (typeof list === "string") {
        return list.split(",").map((item) => ({
            name: item.trim(),
            price: 0,
        }));
    } else if (Array.isArray(list)) {
        // Handle array format
        return list.map((item) => {
            if (typeof item === "string") {
                return { name: item, price: 0 };
            }
            return item;
        });
    }
    return [];
};

const handleClose = () => {
    emit("update:visible", false);
};
</script>
