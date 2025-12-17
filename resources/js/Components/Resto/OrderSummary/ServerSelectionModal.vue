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
                    :loading="loading"
                    filter
                    show-clear
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
import { httpGet } from "@/Utils/axiosHelper";

interface Server {
    id: number;
    name: string;
}

const props = defineProps<{
    visible: boolean;
}>();

const emit = defineEmits<{
    "update:visible": [value: boolean];
    confirm: [serverId: number];
    cancel: [];
}>();

const visible = computed({
    get: () => props.visible,
    set: (value) => emit("update:visible", value),
});

const selectedServerId = ref<number | null>(null);
const servers = ref<Server[]>([]);
const loading = ref(false);

// Fetch servers when modal opens
watch(
    () => props.visible,
    async (isVisible) => {
        if (isVisible && servers.value.length === 0) {
            await fetchServers();
        }
        if (!isVisible) {
            // Reset selection when modal closes
            selectedServerId.value = null;
        }
    }
);

const fetchServers = async () => {
    loading.value = true;
    try {
        const response = await httpGet("/api/users/servers");
        if (response.success) {
            servers.value = response.data;
        }
    } catch (error) {
        console.error("Failed to fetch servers:", error);
    } finally {
        loading.value = false;
    }
};

const handleConfirm = () => {
    if (selectedServerId.value) {
        emit("confirm", selectedServerId.value);
        visible.value = false;
    }
};

const handleCancel = () => {
    emit("cancel");
    visible.value = false;
};
</script>
