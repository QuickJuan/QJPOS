<template>
    <TableManagementLayout title="Table Rooms">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Table Rooms
                    </h1>
                    <p class="text-gray-600">
                        Manage all your table rooms and their locations
                    </p>
                </div>
                <div class="flex gap-3">
                    <Button
                        label="Manage Locations"
                        icon="pi pi-cog"
                        severity="secondary"
                        @click="showLocationManagerModal = true"
                    />
                    <PrimaryButton @click="openAddTableModal">
                        <i class="pi pi-plus mr-2"></i>
                        Add Table
                    </PrimaryButton>
                </div>
            </div>

            <!-- Location Filter -->
            <div class="mb-6">
                <div class="flex gap-2 flex-wrap">
                    <Button
                        :label="`All Locations (${totalTables})`"
                        :outlined="selectedLocation !== null"
                        @click="selectedLocation = null"
                        class="text-sm"
                    />
                    <Button
                        v-for="location in locations"
                        :key="location.id"
                        :label="`${location.name} - ${
                            location.location_type
                        } (${getTableCount(location.id)})`"
                        :outlined="selectedLocation !== location.id"
                        @click="selectedLocation = location.id"
                        class="text-sm"
                    />
                </div>
            </div>

            <!-- Tables Grid -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
            >
                <div
                    v-for="table in filteredTables"
                    :key="table.id"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">
                                {{ table.name }}
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ table.chairs }} chairs •
                                {{
                                    table.tableRoomLocation?.name ||
                                    "No Location"
                                }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="openEditTableModal(table)"
                                class="w-8 h-8 rounded-full bg-secondary-100 hover:bg-secondary-200 flex items-center justify-center transition-colors group"
                                title="Edit Table"
                            >
                                <img
                                    v-if="table.featured_image_url"
                                    :src="table.featured_image_url"
                                    :alt="table.name"
                                    class="w-5 h-5 object-contain"
                                />
                                <svg
                                    v-else
                                    class="w-4 h-4 text-secondary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                            </button>
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                size="small"
                                text
                                @click="deleteTable(table)"
                            />
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mb-3">
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 mb-1">Status</div>
                            <span
                                :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    table.status === 'vacant'
                                        ? 'bg-green-100 text-green-800'
                                        : table.status === 'occupied'
                                        ? 'bg-red-100 text-red-800'
                                        : 'bg-yellow-100 text-yellow-800',
                                ]"
                            >
                                {{ table.status }}
                            </span>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500">Position</div>
                            <div class="text-sm font-mono">
                                {{ table.table_x }}, {{ table.table_y }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 mb-1">
                                Running Total
                            </div>
                            <div class="text-sm font-semibold text-gray-900">
                                {{ getRunningTotal(table) }} of
                                {{ totalTables }}
                            </div>
                        </div>
                        <div
                            class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0"
                        >
                            <img
                                v-if="table.featured_image_url"
                                :src="table.featured_image_url"
                                :alt="`${table.name} image`"
                                class="w-8 h-8 object-contain"
                            />
                            <svg
                                v-else
                                class="w-6 h-6 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredTables.length === 0" class="text-center py-12">
                <i class="pi pi-table text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    No tables found
                </h3>
                <p class="text-gray-600 mb-4">
                    {{
                        selectedLocation
                            ? "No tables in this location yet."
                            : "Get started by adding your first table."
                    }}
                </p>
                <PrimaryButton @click="openAddTableModal">
                    <i class="pi pi-plus mr-2"></i>
                    Add Table
                </PrimaryButton>
            </div>

            <!-- Add Table Modal -->
            <AddTableModal
                :show="showAddTableModal"
                :locations="locations"
                @close="showAddTableModal = false"
                @submit="handleAddTableSubmit"
            />

            <!-- Edit Table Modal -->
            <EditTableModal
                :show="showEditTableModal"
                :table="editTableData"
                :locations="locations"
                @close="showEditTableModal = false"
                @submit="handleEditTableSubmit"
                @delete="handleDeleteTableSubmit"
            />

            <!-- Location Manager Modal -->
            <LocationManagerModal
                :show="showLocationManagerModal"
                :locations="locations"
                :tables="tables"
                @close="showLocationManagerModal = false"
                @add="handleAddLocation"
                @edit="handleEditLocation"
                @delete="handleDeleteLocation"
            />
        </div>
    </TableManagementLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { Button, useConfirm, useToast } from "primevue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TableManagementLayout from "@/Layouts/TableManagementLayout.vue";
import AddTableModal from "./Partials/AddTableModal.vue";
import EditTableModal from "./Partials/EditTableModal.vue";
import LocationManagerModal from "./Partials/LocationManagerModal.vue";

// ===== PROPS =====
const props = defineProps<{
    tables: any[];
    locations: any[];
}>();

// ===== REACTIVE STATE =====
const tables = ref(props.tables);
const locations = ref(props.locations);
const selectedLocation = ref<number | null>(null);
const showAddTableModal = ref(false);
const showEditTableModal = ref(false);
const showLocationManagerModal = ref(false);
const editTableData = ref(null);

// ===== COMPUTED PROPERTIES =====
const filteredTables = computed(() => {
    if (selectedLocation.value === null) return tables.value;
    return tables.value.filter(
        (table) => table.table_room_location_id === selectedLocation.value
    );
});

const totalTables = computed(() => tables.value.length);

const getTableCount = (locationId: number) => {
    return tables.value.filter(
        (table) => table.table_room_location_id === locationId
    ).length;
};

const getDefaultTableImage = (chairs: number) => {
    const images: { [key: number]: string } = {
        2: "/images/round-4.png",
        4: "/images/square-4.png",
        6: "/images/rec-6.png",
        8: "/images/rec-8.png",
    };
    return images[chairs] || "/images/round-4.png";
};

const getRunningTotal = (table: any) => {
    const displayTables =
        selectedLocation.value === null
            ? tables.value
            : tables.value.filter(
                  (t) => t.table_room_location_id === selectedLocation.value
              );
    const index = displayTables.findIndex((t) => t.id === table.id);
    return index !== -1 ? index + 1 : 0;
};

// ===== MODAL FUNCTIONS =====
const openAddTableModal = () => {
    showAddTableModal.value = true;
};

const openEditTableModal = (table: any) => {
    editTableData.value = table;
    showEditTableModal.value = true;
};

// ===== TABLE MANAGEMENT FUNCTIONS =====
const handleAddTableSubmit = async (formData: any) => {
    try {
        const data = new FormData();
        data.append("name", formData.name);
        data.append("chairs", formData.chairs);
        data.append("table_room_location_id", formData.table_room_location_id);
        data.append("table_width", formData.width);
        data.append("table_height", formData.height);
        data.append("table_x", formData.x || 0);
        data.append("table_y", formData.y || 0);

        if (formData.imageFile) {
            data.append("featured_image", formData.imageFile);
            console.log("Image file to upload:", formData.imageFile);
        }

        console.log("Add Table Payload - FormData entries:");
        for (let [key, value] of data.entries()) {
            console.log(`  ${key}:`, value);
        }

        router.post("/table-rooms/tables", data, {
            onSuccess: () => {
                console.log("Table added successfully");
                showAddTableModal.value = false;
            },
            onError: (errors) => {
                console.error("Error adding table:", errors);
            },
        });
    } catch (error) {
        console.error("Error in handleAddTableSubmit:", error);
    }
};

const handleEditTableSubmit = async (formData: any) => {
    if (!editTableData.value) return;

    try {
        const data = new FormData();
        data.append("_method", "PUT");
        data.append("name", formData.name);
        data.append("chairs", formData.chairs);
        data.append("table_room_location_id", formData.table_room_location_id);
        data.append("table_width", formData.width);
        data.append("table_height", formData.height);
        data.append("table_x", formData.x || 0);
        data.append("table_y", formData.y || 0);

        // Only append image if it's an actual File object (not a string/URL)
        if (formData.imageFile && formData.imageFile instanceof File) {
            data.append("featured_image", formData.imageFile);
            console.log("Image file to upload:", formData.imageFile);
        } else if (formData.imageFile) {
            console.log(
                "Image file is not a File object, skipping upload:",
                formData.imageFile
            );
        }

        console.log("Edit Table Payload - FormData entries:");
        for (let [key, value] of data.entries()) {
            console.log(`  ${key}:`, value);
        }

        router.post(`/table-rooms/tables/${editTableData.value.id}`, data, {
            onSuccess: () => {
                console.log("Table updated successfully");
                showEditTableModal.value = false;
            },
            onError: (errors) => {
                console.error("Error updating table:", errors);
            },
        });
    } catch (error) {
        console.error("Error in handleEditTableSubmit:", error);
    }
};

const handleDeleteTableSubmit = async () => {
    if (!editTableData.value) return;

    try {
        const response = await fetch(
            `/table-rooms/tables/${editTableData.value.id}`,
            {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content"),
                },
            }
        );

        if (response.ok) {
            tables.value = tables.value.filter(
                (t) => t.id !== editTableData.value.id
            );
            showEditTableModal.value = false;
        } else {
            console.error("Failed to delete table");
        }
    } catch (error) {
        console.error("Error deleting table:", error);
    }
};

// ===== LOCATION MANAGEMENT FUNCTIONS =====
const handleAddLocation = async (name: string) => {
    try {
        const response = await fetch("/table-management/locations", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content"),
            },
            body: JSON.stringify({ name }),
        });

        if (response.ok) {
            const result = await response.json();
            locations.value.push(result.location);
            showLocationManagerModal.value = false;
        } else {
            console.error("Failed to add location");
        }
    } catch (error) {
        console.error("Error adding location:", error);
    }
};

const handleEditLocation = async (id: number, name: string) => {
    try {
        const response = await fetch(`/table-management/locations/${id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content"),
            },
            body: JSON.stringify({ name }),
        });

        if (response.ok) {
            const result = await response.json();
            const index = locations.value.findIndex((loc) => loc.id === id);
            if (index !== -1) {
                locations.value[index] = result.location;
            }
            showLocationManagerModal.value = false;
        } else {
            console.error("Failed to update location");
        }
    } catch (error) {
        console.error("Error updating location:", error);
    }
};

const handleDeleteLocation = async (id: number) => {
    try {
        const response = await fetch(`/table-management/locations/${id}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content"),
            },
        });

        if (response.ok) {
            locations.value = locations.value.filter((loc) => loc.id !== id);
            if (selectedLocation.value === id) {
                selectedLocation.value = null;
            }
            showLocationManagerModal.value = false;
        } else {
            const error = await response.json();
            console.error("Failed to delete location:", error.message);
        }
    } catch (error) {
        console.error("Error deleting location:", error);
    }
};

// ===== UTILITY FUNCTIONS =====
const deleteTable = (table: any) => {
    editTableData.value = table;
    const confirm = useConfirm();
    confirm.require({
        message: `Are you sure you want to delete "${table.name}"?`,
        icon: "pi pi-exclamation-triangle",
        rejectProps: {
            label: "Cancel",
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: "Delete",
            severity: "danger",
        },
        accept: handleDeleteTableSubmit,
    });
};
</script>

<style scoped>
/* Custom scrollbar for location filter */
.overflow-x-auto::-webkit-scrollbar {
    height: 4px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
