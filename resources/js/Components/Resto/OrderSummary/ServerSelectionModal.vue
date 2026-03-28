<template>
    <Dialog
        v-model:visible="visible"
        modal
        :closable="false"
        :draggable="false"
        class="w-full max-w-md"
        header="Select Server/Waiter"
    >
        <div class="space-y-4">
            <p class="text-sm text-gray-600">
                Please select a server or waiter for this order before placing
                it.
            </p>

            <!-- Server Selection -->
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700">
                    Server/Waiter <span class="text-red-500">*</span>
                </label>
                <Select
                    v-model="selectedServerId"
                    :options="servers"
                    option-label="name"
                    option-value="id"
                    placeholder="Select a server"
                    class="w-full"
                    filter
                    show-clear
                />
            </div>

            <!-- Serving Number (Optional) -->
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700">
                    Serving Number <span class="text-gray-400">(optional)</span>
                </label>
                <InputNumber
                    v-model="servingNumber"
                    input-id="serving-number"
                    class="w-full"
                    placeholder="e.g. 1"
                    :use-grouping="false"
                    :min="1"
                    :allow-empty="true"
                />
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 justify-end pt-4">
                <button
                    @click="handleCancel"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors"
                >
                    Cancel
                </button>
                <button
                    @click="handleConfirm"
                    :disabled="!selectedServerId"
                    class="px-4 py-2 bg-success-600 hover:bg-success-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors"
                >
                    Confirm & Place Order
                </button>
            </div>
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import Dialog from "primevue/dialog";
import Select from "primevue/select";
import InputNumber from "primevue/inputnumber";
import { usePage } from "@inertiajs/vue3";

interface Server {
    id: number;
    name: string;
}

const props = defineProps<{
    visible: boolean;
}>();

const emit = defineEmits<{
    "update:visible": [value: boolean];
    confirm: [payload: { serverId: number; servingNumber: number | null }];
    cancel: [];
}>();

const page = usePage();

const visible = computed({
    get: () => props.visible,
    set: (value) => emit("update:visible", value),
});

const selectedServerId = ref<number | null>(null);
const servingNumber = ref<number | null>(null);

// Get servers from shared Inertia data (cached daily)
const servers = computed<Server[]>(() => {
    return (page.props.available_servers as Server[]) || [];
});

// Reset selection when modal closes
watch(
    () => props.visible,
    (isVisible) => {
        if (!isVisible) {
            selectedServerId.value = null;
            servingNumber.value = null;
        }
    },
);

const handleConfirm = () => {
    if (selectedServerId.value) {
        emit("confirm", {
            serverId: selectedServerId.value,
            servingNumber: servingNumber.value,
        });
        visible.value = false;
    }
};

const handleCancel = () => {
    emit("cancel");
    servingNumber.value = null;
    visible.value = false;
};
</script>
