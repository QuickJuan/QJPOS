<template>
    <Dialog
        v-model:visible="visibleModel"
        modal
        header="Refund Transaction"
        :style="{ width: '32rem', maxWidth: '90vw' }"
        :breakpoints="{ '768px': '95vw', '576px': '100vw' }"
        :draggable="false"
        @hide="emit('closed')"
    >
        <p class="text-sm text-gray-500 mb-5">
            Are you sure you want to refund this transaction? This action
            requires supervisor authorization.
        </p>

        <!-- Error Alert -->
        <div
            v-if="errorModel"
            class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200"
        >
            <p class="text-sm text-red-700 font-medium">{{ errorModel }}</p>
        </div>

        <form class="space-y-4" @submit.prevent="emit('submit')">
            <Textarea
                v-model="notesModel"
                label="Refund Notes"
                rows="4"
                required
                placeholder="Enter reason for refund..."
            />

            <div>
                <label class="block text-sm font-medium mb-2"
                    >Supervisor/Manager *</label
                >
                <Dropdown
                    v-model="supervisorIdModel"
                    :options="supervisors"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Select supervisor for authorization"
                    class="w-full"
                    required
                />
                <small class="text-gray-500 block mt-1">
                    Only users with Supervisor, Manager, or Admin role with OTP
                    enabled
                </small>
            </div>

            <TextField
                v-model="otpCodeModel"
                label="OTP Code"
                required
                type="text"
                placeholder="Enter 6-digit OTP code"
                maxlength="6"
                inputmode="numeric"
                @input="otpCodeModel = otpCodeModel.replace(/[^0-9]/g, '')"
            />
            <small class="text-gray-500 block mt-1">
                Enter the 6-digit code from supervisor's authenticator app
            </small>

            <div class="flex justify-end gap-3 pt-2">
                <Button
                    type="button"
                    label="Cancel"
                    outlined
                    class="rounded-full border-gray-300 text-gray-700"
                    @click="emit('update:visible', false)"
                />
                <Button
                    type="submit"
                    label="Refund"
                    severity="danger"
                    class="rounded-full"
                    :loading="loading"
                    :disabled="
                        !supervisorIdModel ||
                        !otpCodeModel ||
                        otpCodeModel.length < 6
                    "
                />
            </div>
        </form>
    </Dialog>
</template>

<script setup lang="ts">
import { computed } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Textarea from "@/Components/Form/Textarea.vue";
import TextField from "@/Components/Form/TextField.vue";
import Dropdown from "primevue/dropdown";

const props = withDefaults(
    defineProps<{
        visible: boolean;
        notes: string;
        supervisorId: string | number | null;
        supervisors?: Array<{ id: number | string; name: string }>;
        otpCode?: string;
        loading: boolean;
        error?: string | null;
    }>(),
    {
        visible: false,
        notes: "",
        supervisorId: null,
        supervisors: () => [],
        otpCode: "",
        loading: false,
        error: null,
    }
);

const emit = defineEmits<{
    (e: "update:visible", value: boolean): void;
    (e: "update:notes", value: string): void;
    (e: "update:supervisorId", value: string | number | null): void;
    (e: "update:otpCode", value: string): void;
    (e: "submit"): void;
    (e: "closed"): void;
}>();

const visibleModel = computed({
    get: () => props.visible,
    set: (value: boolean) => emit("update:visible", value),
});

const notesModel = computed({
    get: () => props.notes,
    set: (value: string) => emit("update:notes", value),
});

const supervisorIdModel = computed({
    get: () => props.supervisorId,
    set: (value: string | number | null) => emit("update:supervisorId", value),
});

const otpCodeModel = computed({
    get: () => props.otpCode,
    set: (value: string) => emit("update:otpCode", value),
});

const errorModel = computed(() => props.error);
</script>
