<template>
    <Dialog
        :visible="props.show"
        modal
        header="Add New Table"
        :style="{ width: '45rem' }"
        :closable="false"
    >
        <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-4">
                <SelectField
                    v-model="form.table_room_location_id"
                    label="Table Room Location"
                    :options="props.locations"
                    option-label="name"
                    option-value="id"
                    placeholder="Select Location"
                    class="w-full"
                    required
                />
                <TextField
                    v-model="form.name"
                    label="Table Name"
                    type="text"
                    class="w-full"
                    placeholder="Enter table name"
                    required
                />
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <TextField
                    v-model="form.chairs"
                    label="Number of Chairs"
                    type="number"
                    min="1"
                    class="w-full"
                    placeholder="Enter number of chairs"
                    required
                />
                <TextField
                    v-model="form.width"
                    label="Width (px)"
                    type="number"
                    min="1"
                    class="w-full"
                    placeholder="Auto from image"
                    required
                />
                <TextField
                    v-model="form.height"
                    label="Height (px)"
                    type="number"
                    min="1"
                    class="w-full"
                    placeholder="Auto from image"
                    required
                />
            </div>
            <div class="mt-6">
                <label class="block text-sm font-semibold mb-2 text-gray-700"
                    >Table Image</label
                >
                <div
                    class="relative border-2 border-dashed rounded-lg p-4 flex flex-col items-center justify-center cursor-pointer hover:border-blue-400 transition-colors min-h-32"
                    @dragover.prevent
                    @dragleave.prevent
                    @drop.prevent="onDrop"
                    @click="triggerFileInput"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        class="hidden"
                        accept="image/*"
                        @change="onFileSelect"
                    />
                    <template v-if="!form.img">
                        <p class="text-xs text-gray-500">
                            Click or drag & drop an image here
                        </p>
                    </template>
                    <template v-else>
                        <img
                            :src="form.img"
                            :alt="form.name || 'Table image'"
                            class="max-h-48 object-contain"
                            @load="onImageLoad"
                        />
                        <p class="text-[11px] text-gray-500 mt-2">
                            Detected Size: {{ form.width }} x
                            {{ form.height }} px
                        </p>
                    </template>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    Size auto-detected from uploaded image. You can override
                    manually above.
                </p>
            </div>
            <div
                class="mt-6 p-3 bg-blue-50 rounded border border-blue-200 text-xs text-blue-700"
            >
                Position (X/Y) is set after creation by dragging the table in
                Design Mode.
            </div>
        </form>
        <template #footer>
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                @click="$emit('close')"
            />
            <PrimaryButton @click="handleSubmit" type="submit">
                Add Table
            </PrimaryButton>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import Dialog from "primevue/dialog";
import TextField from "@/Components/Form/TextField.vue";
import SelectField from "@/Components/Form/SelectField.vue";
import { Button, useToast } from "primevue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const toast = useToast();
const props = defineProps<{
    show: boolean;
    locations: any[];
}>();

const emit = defineEmits<{
    close: [];
    submit: [form: any];
}>();

const form = ref({
    name: "",
    chairs: 2,
    table_room_location_id: null,
    width: 150,
    height: 100,
    img: "/images/round-4.png",
});

// raw file for upload
const imageFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const triggerFileInput = () => fileInput.value?.click();

const validateAndPreviewFile = (file: File) => {
    const allowed = ["image/png", "image/jpeg", "image/webp"];
    if (!allowed.includes(file.type)) {
        toast.add({
            severity: "error",
            summary: "Invalid Image",
            detail: "Only PNG, JPG, or WEBP allowed.",
            life: 3000,
        });
        return false;
    }
    if (file.size > 3 * 1024 * 1024) {
        toast.add({
            severity: "error",
            summary: "Image Too Large",
            detail: "Max file size 3MB.",
            life: 3000,
        });
        return false;
    }
    imageFile.value = file;
    const reader = new FileReader();
    reader.onload = (ev) => {
        if (typeof ev.target?.result === "string") {
            form.value.img = ev.target.result;
        }
    };
    reader.readAsDataURL(file);
    return true;
};

const onFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) validateAndPreviewFile(file);
};

const onDrop = (e: DragEvent) => {
    const file = e.dataTransfer?.files?.[0];
    if (file) validateAndPreviewFile(file);
};

const onImageLoad = (e: Event) => {
    const img = e.target as HTMLImageElement;
    form.value.width = img.naturalWidth;
    form.value.height = img.naturalHeight;
};

// removed old PrimeVue FileUpload handler

const handleSubmit = () => {
    if (!form.value.table_room_location_id) {
        toast.add({
            severity: "error",
            summary: "Validation Error",
            detail: "Table room location is required.",
            life: 3000,
        });
        return;
    }

    if (!form.value.name.trim()) {
        toast.add({
            severity: "error",
            summary: "Validation Error",
            detail: "Table name is required.",
            life: 3000,
        });
        return;
    }

    // emit form values; position defaults handled server side; include raw file
    emit("submit", { ...form.value, imageFile: imageFile.value });
};

// removed auto-size watcher
</script>
