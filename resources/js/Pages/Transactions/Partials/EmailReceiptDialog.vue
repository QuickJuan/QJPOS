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
                <InputText
                    v-model="emailInputValue"
                    placeholder="Enter email addresses (comma or space separated)"
                    class="w-full"
                    @keydown.enter.prevent="handleSubmit"
                    required
                />
                <div
                    v-if="emailTags.length > 0"
                    class="flex flex-wrap gap-2 mt-2"
                >
                    <span
                        v-for="(email, index) in emailTags"
                        :key="index"
                        class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary rounded-full text-sm"
                    >
                        {{ email }}
                        <button
                            type="button"
                            @click="removeEmail(index)"
                            class="hover:text-primary-700"
                        >
                            <i class="pi pi-times text-xs"></i>
                        </button>
                    </span>
                </div>
                <small class="text-gray-500 block mt-1">
                    Separate multiple email addresses with commas or spaces
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
import { computed, ref, watch } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";

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

const emailInputValue = ref("");

const visibleModel = computed({
    get: () => props.visible,
    set: (value: boolean) => emit("update:visible", value),
});

const emailsModel = computed({
    get: () => props.emails,
    set: (value: string) => emit("update:emails", value),
});

// Parse emails into tags
const emailTags = computed(() => {
    if (!emailsModel.value) return [];
    return emailsModel.value
        .split(/[,\s]+/)
        .map((e) => e.trim())
        .filter((e) => e && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e));
});

// Watch input and update model when comma or space is entered
watch(emailInputValue, (newValue) => {
    if (newValue.includes(",") || newValue.includes(" ")) {
        const emails = newValue
            .split(/[,\s]+/)
            .map((e) => e.trim())
            .filter((e) => e);

        // Add valid emails to the list
        const validEmails = emails.filter((e) =>
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e)
        );
        if (validEmails.length > 0) {
            const currentEmails = emailsModel.value
                ? emailsModel.value.split(",").map((e) => e.trim())
                : [];
            const allEmails = [...new Set([...currentEmails, ...validEmails])];
            emit("update:emails", allEmails.join(", "));
            emailInputValue.value = "";
        }
    }
});

// Initialize input with existing emails
watch(
    () => props.visible,
    (visible) => {
        if (visible && props.emails) {
            emailInputValue.value = "";
        }
    }
);

const removeEmail = (index: number) => {
    const emails = emailTags.value.filter((_, i) => i !== index);
    emit("update:emails", emails.join(", "));
};

const isValidEmail = () => {
    return emailTags.value.length > 0;
};

const handleSubmit = () => {
    // Add any remaining input as an email
    if (emailInputValue.value.trim()) {
        const email = emailInputValue.value.trim();
        if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            const currentEmails = emailsModel.value
                ? emailsModel.value.split(",").map((e) => e.trim())
                : [];
            const allEmails = [...new Set([...currentEmails, email])];
            emit("update:emails", allEmails.join(", "));
            emailInputValue.value = "";
        }
    }

    if (isValidEmail()) {
        emit("submit");
    }
};
</script>
