b
<template>
    <TableManagementLayout>
        <div class="relative w-full h-screen bg-gray-100">
            <!-- main content -->
            <div class="flex flex-col h-full overflow-hidden">
                <!-- Location Tabs -->
                <div class="bg-white border-b border-gray-200 px-4">
                    <div
                        class="flex flex-col md:flex-row items-center justify-between"
                    >
                        <div
                            class="flex flex-wrap overflow-x-auto scrollbar-hide"
                        >
                            <button
                                @click="selectedLocation = null"
                                class="px-4 py-3 font-medium text-sm border-b-2 transition-colors whitespace-nowrap mr-2"
                                :class="
                                    selectedLocation === null
                                        ? 'border-blue-500 text-blue-600 bg-blue-50'
                                        : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'
                                "
                            >
                                <i class="pi pi-home mr-2"></i>
                                All Locations
                            </button>
                            <button
                                v-for="location in locations"
                                :key="location.id"
                                @click="selectedLocation = location.id"
                                class="px-4 py-3 font-medium text-sm border-b-2 transition-colors whitespace-nowrap mr-2 rounded-t-lg"
                                :class="
                                    selectedLocation === location.id
                                        ? 'border-blue-500 text-blue-600 bg-blue-50'
                                        : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'
                                "
                            >
                                <i class="pi pi-map-marker mr-2"></i>
                                {{ location.name }}
                                <span
                                    class="ml-2 px-2 py-1 text-xs bg-gray-200 rounded-full"
                                >
                                    {{ getTableCount(location.id) }}
                                </span>
                            </button>
                        </div>
                        <Button
                            label="Manage Locations"
                            icon="pi pi-cog"
                            size="small"
                            severity="secondary"
                            @click="showLocationManagerModal = true"
                            class="ml-4"
                        />
                        <div
                            class="flex justify-end gap-3 p-4 bg-white shadow-sm border-b border-gray-200 w-auto"
                        >
                            <PrimaryButton
                                type="button"
                                @click="openAddTableModal"
                            >
                                Add Table
                            </PrimaryButton>
                            <button
                                class="px-4 py-2 rounded-lg bg-gray-600 text-white hover:bg-gray-700 transition-colors font-medium"
                                @click="designMode = !designMode"
                            >
                                {{
                                    designMode
                                        ? "Exit Design Mode"
                                        : "Enter Design Mode"
                                }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Floor canvas (full width) -->
                <div
                    class="relative flex-grow bg-gray-50 overflow-auto"
                    :class="{ floor: designMode }"
                >
                    <div
                        v-for="(table, index) in filteredTables"
                        :key="table.id"
                        class="absolute text-center select-none rounded-lg shadow-sm border"
                        :class="[
                            designMode
                                ? 'cursor-move border-2 border-gray-400 bg-gray-100'
                                : 'cursor-pointer hover:shadow-md transition-shadow',
                            !designMode &&
                                table.status === 'occupied' &&
                                'bg-red-100 border-red-200',
                            !designMode &&
                                table.status === 'reserved' &&
                                'bg-yellow-100 border-yellow-200',
                            !designMode &&
                                table.status === 'vacant' &&
                                'bg-green-100 border-green-200',
                        ]"
                        :style="{
                            left: `${table.x}px`,
                            top: `${table.y}px`,
                            width: `${table.width}px`,
                            height: `${table.height}px`,
                            lineHeight: `${table.height}px`,
                        }"
                        @mousedown="startDrag($event, table.id)"
                        @click.stop="
                            !designMode ? openTableStatusModal(index) : null
                        "
                    >
                        <!-- Edit icon in top right corner (only in design mode) -->
                        <button
                            v-if="designMode"
                            @click.stop="openEditTableModal(index)"
                            class="absolute top-1 right-1 z-20 bg-white rounded-full p-1 shadow hover:bg-blue-100 focus:outline-none"
                            title="Edit Table"
                            tabindex="0"
                            aria-label="Edit Table"
                        >
                            <PencilIcon class="h-4 w-4 text-blue-600" />
                        </button>
                        <div
                            class="relative w-full h-full flex justify-center items-center p-2"
                        >
                            <img
                                :src="table.img"
                                :alt="`Table with ${table.chairs} chairs`"
                                class="w-auto h-auto transparent-blend mx-auto"
                                style="pointer-events: none"
                            />
                            <div
                                class="absolute inset-0 flex items-center justify-center z-10 pointer-events-none"
                            >
                                <div
                                    class="bg-white bg-opacity-90 px-3 py-1 rounded-full shadow-sm text-center"
                                >
                                    <p
                                        class="text-xs font-semibold text-gray-800"
                                    >
                                        {{
                                            table.name || `Table #${index + 1}`
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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

            <!-- Table Status Modal -->
            <TableStatusModal
                :show="showTableStatusModal"
                :table="selectedTableForStatus"
                @close="showTableStatusModal = false"
                @update="handleTableStatusUpdate"
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
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AddTableModal from "./Partials/AddTableModal.vue";
import EditTableModal from "./Partials/EditTableModal.vue";
import LocationManagerModal from "./Partials/LocationManagerModal.vue";
import { PencilIcon } from "@heroicons/vue/24/outline";
import { ref, watch, onMounted, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import TableManagementLayout from "@/Layouts/TableManagementLayout.vue";
import TableStatusModal from "./Partials/TableStatusModal.vue";
import { useToast } from "primevue";
import PageProps from "@/Types/PageProps";

// ===== TOAST NOTIFICATION =====
const toast = useToast();
const page = usePage<PageProps>();

// ===== PROPS =====
const props = defineProps<{
    tables: any[];
    locations: any[];
    selectedLocation: any;
}>();

// ===== CONSTANTS =====
const GRID_SIZE = 20;

// ===== REACTIVE STATE =====
const tables = ref(props.tables);
const designMode = ref(false);
const locations = ref(props.locations);
const selectedLocation = ref(props.selectedLocation);

// ===== LOCAL STORAGE FOR LOCATION PERSISTENCE =====
const LOCATION_STORAGE_KEY = "table-management-selected-location";

// Load selected location from localStorage on mount
onMounted(() => {
    const savedLocation = localStorage.getItem(LOCATION_STORAGE_KEY);
    if (savedLocation) {
        const locationId =
            savedLocation === "null" ? null : parseInt(savedLocation);
        if (
            locationId === null ||
            locations.value.some((loc) => loc.id === locationId)
        ) {
            selectedLocation.value = locationId;
        }
    }
});

// Watch for location changes and save to localStorage
watch(selectedLocation, (newLocation) => {
    localStorage.setItem(
        LOCATION_STORAGE_KEY,
        newLocation?.toString() || "null"
    );
});

// ===== COMPUTED PROPERTIES =====
const currentLocationName = computed(() => {
    if (selectedLocation.value === null) return "All Locations";
    const location = locations.value.find(
        (loc) => loc.id === selectedLocation.value
    );
    return location ? location.name : "Unknown Location";
});

const filteredTables = computed(() => {
    if (selectedLocation.value === null) return tables.value;
    return tables.value.filter(
        (table) => table.table_room_location_id === selectedLocation.value
    );
});

const getTableCount = (locationId: number) => {
    return tables.value.filter(
        (table) => table.table_room_location_id === locationId
    ).length;
};

// ===== MODAL STATE =====
const showAddTableModal = ref(false);
const showEditTableModal = ref(false);
const showTableStatusModal = ref(false);
const showLocationManagerModal = ref(false);

// ===== FORM DATA =====
const newTable = ref({
    name: "",
    chairs: 2,
    table_room_location_id: null,
    width: 150,
    height: 100,
    img: "/images/round-4.png",
});

const editTable = ref({
    name: "",
    chairs: 2,
    width: 150,
    height: 100,
    img: "/images/round-4.png",
});

const editTableIndex = ref(null);
const editTableData = ref(null);
const selectedTableForStatus = ref(null);

// ===== STATUS MODAL FUNCTIONS =====
const handleTableStatusUpdate = (data: {
    status: string;
    customer?: string;
}) => {
    if (selectedTableForStatus.value) {
        const tableIndex = selectedTableForStatus.value.index;
        tables.value[tableIndex] = {
            ...tables.value[tableIndex],
            status: data.status,
            customer: data.customer || "",
        };
    }
    showTableStatusModal.value = false;
};

// ===== LOCATION MANAGEMENT FUNCTIONS =====
const handleAddLocation = (name: string) => {
    try {
        router.post("/table-management/locations", { name });
        showLocationManagerModal.value = false;
    } catch (error) {
        console.error("Error adding location:", error);
    }
};

const handleEditLocation = (id: number, name: string) => {
    try {
        router.put(`/table-management/locations/${id}`, { name });
        showLocationManagerModal.value = false;
    } catch (error) {
        console.error("Error updating location:", error);
    }
};

const handleDeleteLocation = (id: number) => {
    try {
        router.delete(`/table-management/locations/${id}`);
        showLocationManagerModal.value = false;
    } catch (error) {
        console.error("Error deleting location:", error);
    }
};

// ===== DRAG STATE =====
let dragInfo = {
    dragging: false,
    tableId: null,
    offsetX: 0,
    offsetY: 0,
};

// ===== TABLE SIZE MAPPINGS =====
const tableSizeMap = {
    2: { width: 150, height: 100, img: "/images/round-4.png" },
    4: { width: 150, height: 100, img: "/images/square-4.png" },
    6: { width: 150, height: 100, img: "/images/rec-6.png" },
    8: { width: 150, height: 100, img: "/images/rec-8.png" },
};

// ===== UTILITY FUNCTIONS =====
const snapToGrid = (value: any) => Math.round(value / GRID_SIZE) * GRID_SIZE;

const isOverlapping = (tableA: any, tableB: any) => {
    const padding = 8;
    return !(
        tableA.x + tableA.width + padding < tableB.x ||
        tableA.x > tableB.x + tableB.width + padding ||
        tableA.y + tableA.height + padding < tableB.y ||
        tableA.y > tableB.y + tableB.height + padding
    );
};

// ===== DRAG FUNCTIONS =====
const startDrag = (event, id) => {
    if (!designMode.value) return;

    const table = tables.value.find((t) => t.id === id);
    dragInfo.dragging = true;
    dragInfo.tableId = id;
    dragInfo.offsetX = event.clientX - table.x;
    dragInfo.offsetY = event.clientY - table.y;

    window.addEventListener("mousemove", onDrag);
    window.addEventListener("mouseup", endDrag);
};

const onDrag = (event) => {
    if (!dragInfo.dragging) return;

    const table = tables.value.find((t) => t.id === dragInfo.tableId);

    let newX = event.clientX - dragInfo.offsetX;
    let newY = event.clientY - dragInfo.offsetY;

    newX = snapToGrid(newX);
    newY = snapToGrid(newY);

    const tempTable = { ...table, x: newX, y: newY };

    const collides = tables.value.some((other) => {
        if (other.id === table.id) return false;
        return isOverlapping(tempTable, other);
    });

    if (!collides) {
        table.x = newX;
        table.y = newY;
    }
};

const endDrag = () => {
    dragInfo.dragging = false;
    dragInfo.tableId = null;

    window.removeEventListener("mousemove", onDrag);
    window.removeEventListener("mouseup", endDrag);
};

// ===== MODAL FUNCTIONS =====
const openAddTableModal = () => {
    newTable.value = {
        name: "",
        chairs: 2,
        table_room_location_id: null,
        width: 150,
        height: 100,
        img: "/images/round-4.png",
    };
    showAddTableModal.value = true;
};

const openEditTableModal = (idx) => {
    const t = tables.value[idx];
    editTableIndex.value = idx;
    editTableData.value = { ...t };
    showEditTableModal.value = true;
};

const openTableStatusModal = (idx: number) => {
    const t = tables.value[idx];
    selectedTableForStatus.value = { ...t, index: idx };
    showTableStatusModal.value = true;
};

const updateTableDefaults = () => {
    const size = tableSizeMap[newTable.value.chairs];
    if (size) {
        newTable.value.width = size.width;
        newTable.value.height = size.height;
        newTable.value.img = size.img;
    }
};

const handleAddTableSubmit = (formData: any) => {
    try {
        router.post(
            "/table-rooms/tables",
            {
                name: formData.name,
                chairs: formData.chairs,
                table_room_location_id: formData.table_room_location_id,
                table_width: formData.width,
                table_height: formData.height,
                table_x: formData.x || 0,
                table_y: formData.y || 0,
            },
            {
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Success",
                        detail:
                            page.props.flash.success ||
                            "Table added successfully.",
                        life: 3000,
                    });
                },

                onError: () => {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail:
                            page.props.flash.error || "Failed to add table.",
                        life: 3000,
                    });
                },
            }
        );

        showAddTableModal.value = false;
    } catch (error) {
        console.error("Error adding table:", error);
    }
};

const handleEditTableSubmit = (formData: any) => {
    if (editTableIndex.value === null) return;

    try {
        const tableId = tables.value[editTableIndex.value].id;
        router.put(
            `/table-rooms/tables/${tableId}`,
            {
                name: formData.name,
                chairs: formData.chairs,
                table_room_location_id: formData.table_room_location_id,
                table_width: formData.width,
                table_height: formData.height,
                table_x: formData.x || 0,
                table_y: formData.y || 0,
            },
            {
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Success",
                        detail:
                            page.props.flash.success ||
                            "Table updated successfully.",
                        life: 3000,
                    });
                },

                onError: () => {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail:
                            page.props.flash.error || "Failed to update table.",
                        life: 3000,
                    });
                },
            }
        );

        showEditTableModal.value = false;
    } catch (error) {
        console.error("Error updating table:", error);
    }
};

const handleDeleteTableSubmit = () => {
    if (editTableIndex.value === null) return;

    try {
        const tableId = tables.value[editTableIndex.value].id;
        router.delete(`/table-rooms/tables/${tableId}`, {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail:
                        page.props.flash.success ||
                        "Table deleted successfully.",
                    life: 3000,
                });
            },

            onError: () => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: page.props.flash.error || "Failed to delete table.",
                    life: 3000,
                });
            },
        });

        showEditTableModal.value = false;
    } catch (error) {
        console.error("Error deleting table:", error);
    }
};

// ===== WATCHERS =====
watch(() => newTable.value.chairs, updateTableDefaults);
watch(
    () => editTable.value.chairs,
    () => {
        const size = tableSizeMap[editTable.value.chairs];
        if (size) {
            editTable.value.width = size.width;
            editTable.value.height = size.height;
            editTable.value.img = size.img;
        }
    }
);
</script>

<style scoped>
.floor {
    background-image: linear-gradient(#e5e7eb 1px, transparent 1px),
        linear-gradient(to right, #e5e7eb 1px, transparent 1px);
    background-size: 20px 20px;
}

.transparent-blend {
    mix-blend-mode: multiply;
}

/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active > div,
.modal-leave-active > div {
    transition: transform 0.3s ease;
}

.modal-enter-from > div,
.modal-leave-to > div {
    transform: scale(0.95);
}

/* Slide transition for sidebar */
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}
.slide-enter,
.slide-leave-to {
    transform: translateX(-100%);
}
</style>
