<template>
    <Dialog
        :visible="props.visible"
        modal
        :style="{ width: 'min(42rem, 94vw)' }"
        :closable="false"
        :showHeader="false"
        @update:visible="handleClose"
    >
        <div class="overflow-x-hidden">
            <div class="rounded-xl bg-white pt-6 pb-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold tracking-tight">
                            Add Modifier
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Applies to
                            <span class="font-semibold">{{
                                selectedItemCount
                            }}</span>
                            {{ selectedItemCount === 1 ? "item" : "items" }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-700 transition hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-200"
                        aria-label="Close"
                        @click="handleClose"
                    >
                        <span class="text-xl leading-none">×</span>
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                <div class="max-h-[26rem] overflow-y-auto pr-1">
                    <div
                        v-if="!modifiers.length"
                        class="py-10 text-center text-gray-500"
                    >
                        No modifiers available.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="modifier in modifiers"
                            :key="modifier.id"
                            class="rounded-xl border border-gray-200 bg-white p-4"
                        >
                            <div class="mb-3">
                                <h4 class="text-sm font-semibold text-gray-900">
                                    {{ modifier.name }}
                                </h4>
                                <p
                                    v-if="selectedModifierValues[modifier.id]"
                                    class="mt-1 text-xs text-gray-500"
                                >
                                    Selected:
                                    <span class="font-medium text-gray-700">
                                        {{
                                            selectedModifierValues[modifier.id]
                                        }}
                                    </span>
                                </p>
                            </div>

                            <div
                                v-if="
                                    modifier.list &&
                                    parseModifierList(modifier.list).length > 0
                                "
                                class="grid grid-cols-2 gap-2 sm:grid-cols-3"
                            >
                                <div
                                    v-for="option in parseModifierList(
                                        modifier.list,
                                    )"
                                    :key="option.name"
                                    class="min-w-0"
                                >
                                    <input
                                        :id="`modifier-${modifier.id}-${option.name}`"
                                        type="radio"
                                        :name="`modifier-${modifier.id}`"
                                        :value="option.name"
                                        v-model="
                                            selectedModifierValues[modifier.id]
                                        "
                                        class="peer sr-only"
                                        @change="
                                            selectModifierOption(
                                                modifier.id,
                                                option,
                                            )
                                        "
                                    />
                                    <label
                                        :for="`modifier-${modifier.id}-${option.name}`"
                                        class="flex w-full cursor-pointer select-none items-center justify-between gap-2 rounded-lg border px-3 py-2 text-sm shadow-sm transition duration-150 hover:-translate-y-px hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-200 active:translate-y-0 active:shadow"
                                        :class="
                                            selectedModifierValues[
                                                modifier.id
                                            ] === option.name
                                                ? 'border-primary-500 bg-primary-50 text-primary-800'
                                                : 'border-gray-300 bg-white text-gray-800 hover:border-primary-300 hover:bg-primary-50'
                                        "
                                    >
                                        <span
                                            class="min-w-0 truncate font-semibold"
                                            >{{ option.name }}</span
                                        >
                                        <div
                                            class="flex shrink-0 items-center gap-2"
                                        >
                                            <span
                                                v-if="option.price"
                                                class="text-xs font-semibold text-gray-600"
                                            >
                                                +{{ formatMoney(option.price) }}
                                            </span>
                                            <span
                                                v-if="
                                                    selectedModifierValues[
                                                        modifier.id
                                                    ] === option.name
                                                "
                                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-800"
                                                aria-hidden="true"
                                            >
                                                ✓
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-500">
                                No options.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <label
                        class="mb-2 block text-sm font-semibold text-gray-900"
                    >
                        Special Instructions
                    </label>
                    <textarea
                        v-model="specialInstructions"
                        rows="3"
                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200"
                        placeholder="Add any special instructions or notes for this order..."
                    ></textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        Tip: Use this for allergies, doneness, or packaging
                        requests.
                    </p>
                </div>
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
                    :disabled="!canSubmit"
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
import { formatMoney } from "@/Utils/FormatMoney";
import { usePage } from "@inertiajs/vue3";

const props = defineProps<{
    visible: boolean;
    selectedItems: any[];
    // modifiers: any[];
}>();

const page = usePage<any>();

const emit = defineEmits<{
    add: [modifierData: any];
    "update:visible": [value: boolean];
}>();

const selectedModifierOptions = ref<Record<string, any[]>>({});
const selectedModifierValues = ref<Record<string, string>>({});
const specialInstructions = ref("");

const modifiers = computed(() => {
    return page.props.available_modifiers || [];
});

const selectedItemCount = computed(() => props.selectedItems?.length || 0);

const canSubmit = computed(() => {
    const hasAnyOption = Object.values(selectedModifierOptions.value).some(
        (options) => Array.isArray(options) && options.length > 0,
    );
    const hasNote = specialInstructions.value.trim().length > 0;
    return selectedItemCount.value > 0 && (hasAnyOption || hasNote);
});

watch(
    () => props.visible,
    (newValue) => {
        if (newValue) {
            selectedModifierOptions.value = {};
            selectedModifierValues.value = {};
            specialInstructions.value = "";
        }
    },
);

const selectModifierOption = (modifierId: string, option: any) => {
    selectedModifierOptions.value[modifierId] = [option];
};

const handleAdd = () => {
    const selectedData = {
        selectedCartItems: props.selectedItems,
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
        }));
    } else if (Array.isArray(list)) {
        // Handle array format
        return list.map((item) => {
            if (typeof item === "string") {
                return { name: item };
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
