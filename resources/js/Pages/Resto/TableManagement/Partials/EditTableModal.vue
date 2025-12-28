<template>
    <Dialog
        :visible="props.show"
        modal
        header="Edit Tabless"
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
            <div>
                <TextField
                    v-model="form.chairs"
                    label="Number of Chairs"
                    type="number"
                    class="w-full"
                    placeholder="Enter number of chairs"
                    required
                />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <TextField
                        v-model="form.width"
                        label="Width (px)"
                        type="number"
                        class="w-full"
                        placeholder="Enter width in pixels"
                        required
                    />
                </div>
                <div>
                    <TextField
                        v-model="form.height"
                        label="Height (px)"
                        type="number"
                        class="w-full"
                        placeholder="Enter height in pixels"
                        required
                    />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <TextField
                        v-model="form.x"
                        label="X Position (px)"
                        type="number"
                        class="w-full"
                        placeholder="Enter X position"
                        min="0"
                    />
                </div>
                <div>
                    <TextField
                        v-model="form.y"
                        label="Y Position (px)"
                        type="number"
                        class="w-full"
                        placeholder="Enter Y position"
                        min="0"
                    />
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-semibold mb-2 text-gray-700">
                    Table Image
                </label>
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
                    <img
                        :src="form.img"
                        :alt="`Preview for ${form.name}`"
                        class="max-h-48 object-contain"
                    />
                </div>
            </div>
        </form>
        <template #footer>
            <div class="flex gap-3 items-center">
                <Button
                    label="Delete Table"
                    severity="danger"
                    @click="handleDelete"
                />
                <Button
                    label="Cancel"
                    severity="secondary"
                    @click="$emit('close')"
                />
                <PrimaryButton type="button" @click="handleSubmit">
                    Save Changes
                </PrimaryButton>
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import SelectField from "@/Components/Form/SelectField.vue";
import TextField from "@/Components/Form/TextField.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Button, Dialog, useConfirm, useToast } from "primevue";
import { ref, watch } from "vue";

const toast = useToast();
const confirm = useConfirm();
const props = defineProps<{
    show: boolean;
    table: any;
    locations: any[];
}>();

const emit = defineEmits<{
    close: [];
    submit: [form: any];
    delete: [];
}>();

const form = ref({
    name: "",
    chairs: 2,
    table_room_location_id: null,
    width: 150,
    height: 100,
    x: 0,
    y: 0,
    img: "/images/round-4.png",
});

const imageFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const tableSizeMap = {
    2: { width: 150, height: 100, img: "/images/round-4.png" },
    4: { width: 150, height: 100, img: "/images/square-4.png" },
    6: { width: 150, height: 100, img: "/images/rec-6.png" },
    8: { width: 150, height: 100, img: "/images/rec-8.png" },
};

const updateTableDefaults = () => {
    const size = tableSizeMap[form.value.chairs as keyof typeof tableSizeMap];
    if (size) {
        form.value.width = size.width;
        form.value.height = size.height;
        form.value.img = size.img;
    }
};

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

const handleSubmit = () => {
    if (!form.value.table_room_location_id) {
        toast.add({
            severity: "error",
            summary: "Validation Error",
            detail: "Please enter a table room location.",
            life: 3000,
        });
        return;
    }

    if (!form.value.name.trim()) {
        toast.add({
            severity: "error",
            summary: "Validation Error",
            detail: "Please enter a table name.",
            life: 3000,
        });
        return;
    }

    emit("submit", { ...form.value, imageFile: imageFile.value });
};

const handleDelete = () => {
    confirm.require({
        message: "Are you sure you want to delete this table?",
        icon: "pi pi-exclamation-triangle",
        rejectProps: {
            label: "Cancel",
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: "Delete",
        },
        accept: () => {
            emit("delete");
        },
    });
};

// Watch for prop changes to update form
watch(
    () => props.table,
    (newTable) => {
        if (newTable) {
            form.value = {
                name: newTable.name || "",
                chairs: newTable.chairs || 2,
                table_room_location_id: newTable.table_room_location_id || null,
                width: newTable.width || newTable.table_width || 150,
                height: newTable.height || newTable.table_height || 100,
                x: newTable.x || newTable.table_x || 0,
                y: newTable.y || newTable.table_y || 0,
                img: newTable.featured_image_url || "",
            };
            imageFile.value = null; // Reset file when table changes
        }
    },
    { immediate: true }
);

watch(() => form.value.chairs, updateTableDefaults);
</script>
