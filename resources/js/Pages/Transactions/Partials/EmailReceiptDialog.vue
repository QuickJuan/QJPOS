<template>
    <Dialog
        v-model:visible="visibleModel"
        modal
        header="Send Receipt via Email"
        :style="{ width: '32rem', maxWidth: '90vw' }"
        :breakpoints="{ '768px': '95vw', '576px': '100vw' }"
        :draggable="false"
    >
        <form class="space-y-4" @submit.prevent="handleSubmit">
            <div>
                <label class="block text-sm font-medium mb-2"
                    >Customer Email(s) *</label
                >
                <Textarea
                    v-model="emailsModel"
                    placeholder="Enter email addresses (comma-separated)&#10;e.g. customer@example.com, another@example.com"
                    rows="4"
                    required
                />
                <small class="text-gray-500 block mt-1">
                    Separate multiple email addresses with commas
                </small>
            </div>

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
                    label="Send Email"
                    icon="pi pi-envelope"
                    class="rounded-full"
                    :loading="loading"
                    :disabled="!isValidEmail()"
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

const props = withDefaults(
    defineProps<{
        visible: boolean;
        emails: string;
        loading: boolean;
    }>(),
    {
        visible: false,
        emails: "",
        loading: false,
    }
);

const emit = defineEmits<{
    (e: "update:visible", value: boolean): void;
    (e: "update:emails", value: string): void;
    (e: "submit"): void;
}>();

const visibleModel = computed({
    get: () => props.visible,
    set: (value: boolean) => emit("update:visible", value),
});

const emailsModel = computed({
    get: () => props.emails,
    set: (value: string) => emit("update:emails", value),
});

const isValidEmail = () => {
    if (!emailsModel.value.trim()) return false;
    const emails = emailsModel.value
        .split(",")
        .map((e) => e.trim())
        .filter((e) => e);

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emails.length > 0 && emails.every((email) => emailRegex.test(email));
};

const handleSubmit = () => {
    if (isValidEmail()) {
        emit("submit");
    }
};
</script>
