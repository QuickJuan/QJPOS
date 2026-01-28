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

            <div v-if="props.orderItem?.placed_order">
                <label class="block text-sm font-medium mb-2"
                    >Supervisor Email</label
                >
                <input
                    v-model="approverEmail"
                    type="email"
                    placeholder="Enter supervisor email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    required
                />
                <small class="text-gray-500 mt-1 block">
                    Supervisor/approver email is required for placed orders.
                </small>
            </div>

            <div v-if="props.orderItem?.placed_order">
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
                    Enter the 6-digit code from the supervisor's authenticator
                    app
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
                    (!approverEmail || !otpCode || otpCode.length < 6)
                "
                class="bg-primary hover:bg-primary-600"
            />
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import Textarea from "@/Components/Form/Textarea.vue";
import { Button, Dialog } from "primevue";
import { ref } from "vue";

const props = defineProps<{
    visible: boolean;
    orderItem: any;
}>();

const reason = ref(null);
const approverEmail = ref<string | null>(null);
const otpCode = ref<string | null>(null);

const emit = defineEmits<{
    submit: [item: any];
    "update:visible": [value: boolean];
}>();

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
        submitData.approverEmail = approverEmail.value;
        submitData.otpCode = otpCode.value;
    }

    emit("submit", submitData);
    emit("update:visible", false);

    // Reset form
    reason.value = null;
    approverEmail.value = null;
    otpCode.value = null;
};
</script>
