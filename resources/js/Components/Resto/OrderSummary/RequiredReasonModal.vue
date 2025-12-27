<template>
    <Dialog
        :visible="props.visible"
        @update:visible="handleDialogUpdate"
        modal
        header="Enter the reason for voiding this order"
        :style="{ width: '28rem' }"
    >
        <div class="flex flex-col gap-4 mb-4">
            <Textarea v-model="reason" label="Reason" required />

            <!-- Approver Selection (only show if item is from placed order) -->
            <div v-if="props.orderItem?.placed_order">
                <label class="block text-sm font-medium mb-2"
                    >Select Approver</label
                >
                <Dropdown
                    v-model="selectedApproverId"
                    :options="availableApprovers"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Choose an approver"
                    class="w-full"
                    :loading="loading"
                    required
                />
                <small class="text-gray-500 mt-1 block">
                    Only authorized users (Admin, Manager, Supervisor, OIC) with
                    OTP enabled can approve
                </small>
                <small
                    v-if="availableApprovers.length === 0 && !loading"
                    class="text-red-500 mt-1 block"
                >
                    No approvers available. Please ensure users with
                    Admin/Manager/Supervisor/OIC role have OTP enabled.
                </small>
            </div>

            <!-- OTP Input (only show if item is from placed order and approver selected) -->
            <div v-if="props.orderItem?.placed_order && selectedApproverId">
                <label class="block text-sm font-medium mb-2">OTP Code</label>
                <input
                    v-model="otpCode"
                    type="text"
                    placeholder="Enter 6-digit code"
                    maxlength="6"
                    inputmode="numeric"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    required
                    @input="otpCode = otpCode.replace(/[^0-9]/g, '')"
                />
                <small class="text-gray-500 mt-1 block">
                    Enter the 6-digit code from approver's authenticator app
                </small>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                @click="$emit('update:visible', false)"
            />
            <Button
                type="button"
                label="Save"
                @click="saveEdit"
                :disabled="
                    props.orderItem?.placed_order &&
                    (!selectedApproverId || !otpCode || otpCode.length < 6)
                "
                class="bg-primary hover:bg-primary-600"
            />
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import Textarea from "@/Components/Form/Textarea.vue";
import { Button, Dialog, Dropdown } from "primevue";
import { ref, watch } from "vue";
import axios from "axios";
import { route } from "ziggy-js";

const props = defineProps<{
    visible: boolean;
    orderItem: any;
}>();

const reason = ref(null);
const selectedApproverId = ref(null);
const otpCode = ref(null);
const availableApprovers = ref<any[]>([]);
const loading = ref(false);

const emit = defineEmits<{
    submit: [item: any];
    "update:visible": [value: boolean];
}>();

// Fetch available approvers
const fetchApprovers = async () => {
    if (!props.orderItem?.placed_order) {
        return;
    }

    loading.value = true;
    try {
        // Use dedicated endpoint to get approvers
        const response = await axios.get(
            route("resto.cart.get-approvers", {
                cartItemId: props.orderItem.id,
            })
        );
        console.log("Approvers received:", response.data.approvers);
        availableApprovers.value = response.data.approvers || [];
    } catch (error: any) {
        console.error("Error fetching approvers:", error);
        availableApprovers.value = [];
    } finally {
        loading.value = false;
    }
};

// Watch for modal visibility to fetch approvers
watch(
    () => props.visible,
    (newVal) => {
        if (newVal && props.orderItem?.placed_order) {
            fetchApprovers();
        }
    }
);

const handleDialogUpdate = (value: boolean) => {
    emit("update:visible", value);
};

const saveEdit = () => {
    const submitData: any = {
        orderItem: props.orderItem,
        reason: reason.value,
    };

    // Add approver and OTP if this is a placed order
    if (props.orderItem?.placed_order) {
        submitData.approverId = selectedApproverId.value;
        submitData.otpCode = otpCode.value;
    }

    emit("submit", submitData);
    emit("update:visible", false);

    // Reset form
    reason.value = null;
    selectedApproverId.value = null;
    otpCode.value = null;
};
</script>
