<template>
    <Dialog
        :visible="show"
        modal
        header="Manage Locations"
        :style="{ width: '40rem' }"
        :closable="false"
        @hide="$emit('close')"
    >
        <div class="space-y-4">
            <!-- Add New Location -->
            <div class="p-4 border border-gray-200 rounded-lg">
                <h3 class="text-lg font-semibold mb-3">Add New Location</h3>
                <div class="flex gap-2">
                    <TextField
                        v-model="newLocationName"
                        placeholder="Location name"
                        class="flex-1"
                        @keyup.enter="addLocation"
                    />
                    <Button
                        label="Add"
                        icon="pi pi-plus"
                        @click="addLocation"
                        :disabled="!newLocationName.trim()"
                    />
                </div>
            </div>

            <!-- Existing Locations -->
            <div class="space-y-2">
                <h3 class="text-lg font-semibold">Existing Locations</h3>
                <div
                    v-for="location in locations"
                    :key="location.id"
                    class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
                >
                    <div class="flex items-center gap-3">
                        <span class="font-medium">{{ location.name }}</span>
                        <span class="text-sm text-gray-500">
                            ({{ getTableCount(location.id) }} tables)
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            severity="secondary"
                            text
                            @click="editLocation(location)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            @click="deleteLocation(location)"
                            :disabled="getTableCount(location.id) > 0"
                        />
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <Button
                label="Close"
                icon="pi pi-times"
                class="p-button-text"
                @click="$emit('close')"
            />
        </template>
    </Dialog>

    <!-- Edit Location Dialog -->
    <Dialog
        v-model:visible="showEditDialog"
        modal
        header="Edit Location"
        :style="{ width: '30rem' }"
        :closable="true"
        @hide="closeEditDialog"
    >
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-2"
                    >Location Name</label
                >
                <InputText
                    v-model="editLocationName"
                    class="w-full"
                    placeholder="Enter location name"
                    @keyup.enter="saveEditLocation"
                />
            </div>
        </div>

        <template #footer>
            <Button
                label="Cancel"
                icon="pi pi-times"
                class="p-button-text"
                @click="closeEditDialog"
            />
            <Button
                label="Save"
                icon="pi pi-check"
                class="p-button-primary"
                @click="saveEditLocation"
                :disabled="!editLocationName.trim()"
            />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import TextField from "@/Components/Form/TextField.vue";

const props = defineProps<{
    show: boolean;
    locations: any[];
    tables: any[];
}>();

const emit = defineEmits<{
    close: [];
    add: [name: string];
    edit: [id: number, name: string];
    delete: [id: number];
}>();

const newLocationName = ref("");
const showEditDialog = ref(false);
const editingLocation = ref(null);
const editLocationName = ref("");

const getTableCount = (locationId: number) => {
    return props.tables.filter(
        (table) => table.table_room_location_id === locationId
    ).length;
};

const addLocation = () => {
    if (newLocationName.value.trim()) {
        emit("add", newLocationName.value.trim());
        newLocationName.value = "";
    }
};

const editLocation = (location: any) => {
    editingLocation.value = location;
    editLocationName.value = location.name;
    showEditDialog.value = true;
};

const closeEditDialog = () => {
    showEditDialog.value = false;
    editingLocation.value = null;
    editLocationName.value = "";
};

const saveEditLocation = () => {
    if (editingLocation.value && editLocationName.value.trim()) {
        emit("edit", editingLocation.value.id, editLocationName.value.trim());
        closeEditDialog();
    }
};

const deleteLocation = (location: any) => {
    if (
        confirm(
            `Are you sure you want to delete "${location.name}"? This action cannot be undone.`
        )
    ) {
        emit("delete", location.id);
    }
};
</script>
