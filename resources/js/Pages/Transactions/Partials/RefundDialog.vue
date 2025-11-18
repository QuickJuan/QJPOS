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

        <form class="space-y-4" @submit.prevent="emit('submit')">
            <Textarea
                v-model="notesModel"
                label="Refund Notes"
                rows="4"
                required
                placeholder="Enter reason for refund..."
            />

            <TextField
                v-model="supervisorNameModel"
                label="Supervisor/Manager Name"
                required
                type="text"
                placeholder="Enter supervisor name for authorization"
            />

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

const props = withDefaults(
    defineProps<{
        visible: boolean;
        notes: string;
        supervisorName: string;
        loading: boolean;
    }>(),
    {
        visible: false,
        notes: "",
        supervisorName: "",
        loading: false,
    }
);

const emit = defineEmits<{
    (e: "update:visible", value: boolean): void;
    (e: "update:notes", value: string): void;
    (e: "update:supervisorName", value: string): void;
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

const supervisorNameModel = computed({
    get: () => props.supervisorName,
    set: (value: string) => emit("update:supervisorName", value),
});
</script>

