<template>
    <div class="relative w-full h-screen bg-gray-100 overflow-auto">
        <!-- TableSidebar always at left-0 -->
        <TableSidebar
            :show="showTableSidebar"
            :tableData="tableSidebarTable"
            @close="closeTableSideBar"
            @save="handleTableSidebarSave"
            @action="handleTableSidebarAction"
            class="fixed top-0 left-0 z-50 h-full"
            style="width: 320px; min-height: 1000px;"
        />

        <!-- Add buttons to create tables -->
        <div class="z-50 space-x-2 p-4 bg-white shadow-md sticky top-0 w-full">
            <button @click="openAddTableModal" class="btn">Add Table</button>
            <button
                class="px-4 py-2 rounded bg-indigo-500 text-white hover:bg-indigo-600"
                @click="designMode = !designMode"
            >
                {{ designMode ? "Exit Design Mode" : "Enter Design Mode" }}
            </button>
            <button class="btn bg-green-500 hover:bg-green-600" @click="zoomIn">
                Zoom In
            </button>
            <button
                class="btn bg-yellow-500 hover:bg-yellow-600"
                @click="zoomOut"
            >
                Zoom Out
            </button>
        </div>

        <!-- Floor canvas, shifted right if sidebar is open -->
        <div
            class="relative transform origin-top-left transition-transform duration-200"
            :class="{ floor: designMode }"
            :style="{
                width: '2000px',
                height: '1000px',
                marginLeft: showTableSidebar ? '320px' : '0',
                transform: `scale(${zoomLevel})`,
                transition: 'margin-left 0.3s, width 0.3s',
            }"
        >
            <div
                v-for="(table, index) in tables"
                :key="table.id"
                class="absolute text-center select-none bg-green-200"
                :class="[
                    designMode
                        ? 'cursor-move border-2 bg-gray-200'
                        : 'cursor-pointer ',
                ]"
                :style="{
                    left: `${table.x}px`,
                    top: `${table.y}px`,
                    width: `${table.width}px`,
                    height: `${table.height}px`,
                    lineHeight: `${table.height}px`,
                    // Color by status if not in design mode
                    ...(!designMode && table.status === 'occupied'
                        ? { backgroundColor: '#fee2e2' }
                        : !designMode && table.status === 'reserved'
                        ? { backgroundColor: '#fef9c3' }
                        : !designMode && table.status === 'vacant'
                        ? { backgroundColor: '#bbf7d0' }
                        : {}),
                }"
                @mousedown="startDrag($event, table.id)"
                @click.stop="!designMode ? openTableSideBar(index) : null"
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
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 text-blue-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-2.828 0L9 13zm-6 6h6"
                        />
                    </svg>
                </button>

                <!-- <p class="text-sm font-bold">Table #{{ index + 1 }}</p>
                <p class="text-xs text-gray-500">Chairs: {{ table.chairs }}</p> -->
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
                            class="bg-white bg-opacity-75 p-2 rounded-full shadow text-center"
                        >
                            <p class="text-sm font-bold">T{{ index + 1 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Table Modal -->
        <transition name="modal">
            <div
                v-if="showAddTableModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
            >
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-xs">
                    <h2 class="text-lg font-bold mb-4">Add Table</h2>
                    <form @submit.prevent="handleAddTable">
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1"
                                >Table Name</label
                            >
                            <input
                                v-model="newTable.name"
                                type="text"
                                class="w-full border rounded px-2 py-1"
                                required
                            />
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1"
                                >Chairs</label
                            >
                            <select
                                v-model.number="newTable.chairs"
                                class="w-full border rounded px-2 py-1"
                            >
                                <option
                                    v-for="opt in chairOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                        <div class="mb-3 flex gap-2">
                            <div class="flex-1">
                                <label class="block text-sm font-medium mb-1"
                                    >Width</label
                                >
                                <input
                                    v-model.number="newTable.width"
                                    type="number"
                                    min="40"
                                    class="w-full border rounded px-2 py-1"
                                />
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium mb-1"
                                    >Height</label
                                >
                                <input
                                    v-model.number="newTable.height"
                                    type="number"
                                    min="40"
                                    class="w-full border rounded px-2 py-1"
                                />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1"
                                >Preview</label
                            >
                            <img
                                :src="newTable.img"
                                :alt="`Preview for ${newTable.chairs} chairs`"
                                class="w-full h-16 object-contain bg-gray-100 rounded mb-2"
                            />
                            <input
                                type="file"
                                accept="image/*"
                                @change="handleNewTableFile"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-1 file:px-2 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <button
                                type="button"
                                @click="showAddTableModal = false"
                                class="px-3 py-1 rounded bg-gray-300 hover:bg-gray-400"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700"
                            >
                                Add Table
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

        <!-- Edit Table Modal -->
        <transition name="modal">
            <div
                v-if="showEditTableModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
            >
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-xs">
                    <h2 class="text-lg font-bold mb-4">Edit Table</h2>
                    <form @submit.prevent="handleEditTable">
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1"
                                >Table Name</label
                            >
                            <input
                                v-model="editTable.name"
                                type="text"
                                class="w-full border rounded px-2 py-1"
                                required
                            />
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1"
                                >Chairs</label
                            >
                            <select
                                v-model.number="editTable.chairs"
                                class="w-full border rounded px-2 py-1"
                            >
                                <option
                                    v-for="opt in chairOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                        <div class="mb-3 flex gap-2">
                            <div class="flex-1">
                                <label class="block text-sm font-medium mb-1"
                                    >Width</label
                                >
                                <input
                                    v-model.number="editTable.width"
                                    type="number"
                                    min="40"
                                    class="w-full border rounded px-2 py-1"
                                />
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium mb-1"
                                    >Height</label
                                >
                                <input
                                    v-model.number="editTable.height"
                                    type="number"
                                    min="40"
                                    class="w-full border rounded px-2 py-1"
                                />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1"
                                >Preview</label
                            >
                            <img
                                :src="editTable.img"
                                :alt="`Preview for ${editTable.chairs} chairs`"
                                class="w-full h-16 object-contain bg-gray-100 rounded mb-2"
                            />
                            <input
                                type="file"
                                accept="image/*"
                                @change="handleEditTableFile"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-1 file:px-2 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                        </div>
                        <div class="flex justify-between gap-2 mt-4">
                            <button
                                type="button"
                                @click="handleDeleteTable"
                                class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600"
                            >
                                Delete Table
                            </button>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    @click="showEditTableModal = false"
                                    class="px-3 py-1 rounded bg-gray-300 hover:bg-gray-400"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700"
                                >
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from "vue";
import TableSidebar from "../Components/TableSidebar.vue";

const tables = ref([]);
const designMode = ref(true);

const GRID_SIZE = 20;

const snapToGrid = (value) => Math.round(value / GRID_SIZE) * GRID_SIZE;

const zoomLevel = ref(1);

const zoomIn = () => {
    zoomLevel.value = Math.min(zoomLevel.value + 0.1, 2); // max 2x
};

const zoomOut = () => {
    zoomLevel.value = Math.max(zoomLevel.value - 0.1, 0.5); // min 0.5x
};

// Add a new table
const addTable = (chairs) => {
    let tableSize = {
        2: { width: 150, height: 100, img: "/images/round-4.png" },
        4: { width: 150, height: 100, img: "/images/square-4.png" },
        6: { width: 150, height: 100, img: "/images/rec-6.png" },
        8: { width: 150, height: 100, img: "/images/rec-8.png" },
    };
    const { width, height, img } = tableSize[chairs];
    // Try to find a free spot
    let found = false;
    let x = 100,
        y = 100;
    const maxX = 2000 - width;
    const maxY = 1000 - height;
    for (let tryY = 0; tryY <= maxY && !found; tryY += GRID_SIZE) {
        for (let tryX = 0; tryX <= maxX && !found; tryX += GRID_SIZE) {
            const tempTable = { x: tryX, y: tryY, width, height };
            const collides = tables.value.some((other) =>
                isOverlapping(tempTable, other)
            );
            if (!collides) {
                x = tryX;
                y = tryY;
                found = true;
            }
        }
    }
    tables.value.push({
        id: Date.now() + Math.random(),
        chairs,
        x,
        y,
        width,
        height,
        img,
    });

    saveTables();
};

// Save to localStorage
const saveTables = () => {
    localStorage.setItem("table_layout", JSON.stringify(tables.value));
};

// Load from localStorage
const loadTables = () => {
    const saved = localStorage.getItem("table_layout");
    if (saved) {
        tables.value = JSON.parse(saved);
    }
};

const repositionTablesIfNeeded = () => {
    const maxX = 2000;
    const maxY = 1000;
    tables.value.forEach((table, idx) => {
        // If x or y is missing, null, or not a number, reposition
        if (
            typeof table.x !== "number" ||
            isNaN(table.x) ||
            typeof table.y !== "number" ||
            isNaN(table.y)
        ) {
            // Find a free spot
            let found = false;
            for (
                let tryY = 0;
                tryY <= maxY - table.height && !found;
                tryY += GRID_SIZE
            ) {
                for (
                    let tryX = 0;
                    tryX <= maxX - table.width && !found;
                    tryX += GRID_SIZE
                ) {
                    const tempTable = { ...table, x: tryX, y: tryY };
                    const collides = tables.value.some(
                        (other, otherIdx) =>
                            otherIdx !== idx && isOverlapping(tempTable, other)
                    );
                    if (!collides) {
                        table.x = tryX;
                        table.y = tryY;
                        found = true;
                    }
                }
            }
        }
    });
};

onMounted(() => {
    loadTables();
    repositionTablesIfNeeded();
});

// Dragging logic
let dragInfo = {
    dragging: false,
    tableId: null,
    offsetX: 0,
    offsetY: 0,
};

const startDrag = (event, id) => {
    if (!designMode.value) return; //

    const table = tables.value.find((t) => t.id === id);
    dragInfo.dragging = true;
    dragInfo.tableId = id;
    dragInfo.offsetX = event.clientX - table.x;
    dragInfo.offsetY = event.clientY - table.y;

    window.addEventListener("mousemove", onDrag);
    window.addEventListener("mouseup", endDrag);
};

// const onDrag = (event) => {
//     if (!dragInfo.dragging) return;

//     const table = tables.value.find((t) => t.id === dragInfo.tableId);
//     table.x = event.clientX - dragInfo.offsetX;
//     table.y = event.clientY - dragInfo.offsetY;
// };

const onDrag = (event) => {
    if (!dragInfo.dragging) return;

    const table = tables.value.find((t) => t.id === dragInfo.tableId);

    let newX = event.clientX - dragInfo.offsetX;
    let newY = event.clientY - dragInfo.offsetY;

    // Adjust for zoom level
    newX = newX / zoomLevel.value;
    newY = newY / zoomLevel.value;

    // Snap to grid
    newX = snapToGrid(newX);
    newY = snapToGrid(newY);

    // Use actual table size for collision detection
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
    saveTables();

    window.removeEventListener("mousemove", onDrag);
    window.removeEventListener("mouseup", endDrag);
};

const isOverlapping = (tableA, tableB) => {
    const padding = 8; // optional padding between tables
    return !(
        tableA.x + tableA.width + padding < tableB.x ||
        tableA.x > tableB.x + tableB.width + padding ||
        tableA.y + tableA.height + padding < tableB.y ||
        tableA.y > tableB.y + tableB.height + padding
    );
};

// Modal state and form data
const showAddTableModal = ref(false);
const newTable = ref({
    name: "",
    chairs: 2,
    width: 150,
    height: 100,
    img: "/images/round-4.png",
});

const newTableFile = ref(null);

// Handle file input for add modal
const handleNewTableFile = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
        if (typeof ev.target.result === "string") {
            newTable.value.img = ev.target.result;
        }
    };
    reader.readAsDataURL(file);
    newTableFile.value = file;
};

// For edit modal
const editTableFile = ref(null);
const handleEditTableFile = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
        if (typeof ev.target.result === "string") {
            editTable.value.img = ev.target.result;
        }
    };
    reader.readAsDataURL(file);
    editTableFile.value = file;
};

const chairOptions = [
    { value: 2, label: "2 Chairs" },
    { value: 4, label: "4 Chairs" },
    { value: 6, label: "6 Chairs" },
    { value: 8, label: "8 Chairs" },
];

const updateTableDefaults = () => {
    if (newTable.value.chairs === 2 || newTable.value.chairs === 4) {
        newTable.value.width = 150;
        newTable.value.height = 100;
        newTable.value.img = "/images/round-4.png";
    } else if (newTable.value.chairs === 6) {
        newTable.value.width = 150;
        newTable.value.height = 100;
        newTable.value.img = "/images/rec-6.png";
    } else if (newTable.value.chairs === 8) {
        newTable.value.width = 150;
        newTable.value.height = 100;
        newTable.value.img = "/images/rec-8.png";
    }
};

// Watch for chair changes to update defaults
watch(() => newTable.value.chairs, updateTableDefaults);

const openAddTableModal = () => {
    newTable.value = {
        name: "",
        chairs: 2,
        width: 150,
        height: 100,
        img: "/images/round-4.png",
    };
    showAddTableModal.value = true;
};

const handleAddTable = () => {
    if (!newTable.value.name.trim()) return; // Require name
    // Find free spot (reuse logic from addTable)
    const { width, height, img, chairs, name } = newTable.value;
    let found = false;
    let x = 100,
        y = 100;
    const maxX = 2000 - width;
    const maxY = 1000 - height;
    for (let tryY = 0; tryY <= maxY && !found; tryY += GRID_SIZE) {
        for (let tryX = 0; tryX <= maxX && !found; tryX += GRID_SIZE) {
            const tempTable = { x: tryX, y: tryY, width, height };
            const collides = tables.value.some((other) =>
                isOverlapping(tempTable, other)
            );
            if (!collides) {
                x = tryX;
                y = tryY;
                found = true;
            }
        }
    }
    tables.value.push({
        id: Date.now() + Math.random(),
        name,
        chairs,
        x,
        y,
        width,
        height,
        img,
    });
    saveTables();
    showAddTableModal.value = false;
};

// Edit Table modal state and logic
const showEditTableModal = ref(false);
const editTableIndex = ref(null);
const editTable = ref({
    name: "",
    chairs: 2,
    width: 150,
    height: 100,
    img: "/images/round-4.png",
});

const openEditTableModal = (idx) => {
    const t = tables.value[idx];
    editTableIndex.value = idx;
    editTable.value = { ...t };
    showEditTableModal.value = true;
};

const handleEditTable = () => {
    if (editTableIndex.value === null) return;
    if (!editTable.value.name.trim()) return;
    // Update table in place
    tables.value[editTableIndex.value] = {
        ...editTable.value,
        id: tables.value[editTableIndex.value].id,
    };
    saveTables();
    showEditTableModal.value = false;
};

const handleDeleteTable = () => {
    if (editTableIndex.value === null) return;
    tables.value.splice(editTableIndex.value, 1);
    saveTables();
    showEditTableModal.value = false;
};

// Update image/size on chair change in edit modal
watch(
    () => editTable.value.chairs,
    () => {
        if (editTable.value.chairs === 2 || editTable.value.chairs === 4) {
            editTable.value.width = 150;
            editTable.value.height = 100;
            editTable.value.img = "/images/round-4.png";
        } else if (editTable.value.chairs === 6) {
            editTable.value.width = 150;
            editTable.value.height = 100;
            editTable.value.img = "/images/rec-6.png";
        } else if (editTable.value.chairs === 8) {
            editTable.value.width = 150;
            editTable.value.height = 100;
            editTable.value.img = "/images/rec-8.png";
        }
    }
);

// Sidebar state and logic for table status (refactored)
const showTableSidebar = ref(false);
const tableSidebarIndex = ref<number | null>(null);
const tableSidebarTable = ref({
    status: "vacant",
    customer: "",
    name: "",
});

const openTableSideBar = (idx: number) => {
    const t = tables.value[idx];
    tableSidebarIndex.value = idx;
    tableSidebarTable.value = {
        status: t.status || "vacant",
        customer: t.customer || "",
        name: t.name || "",
    };
    showTableSidebar.value = true;
};

const closeTableSideBar = () => {
    showTableSidebar.value = false;
};

const handleTableSidebarSave = (data: any) => {
    if (tableSidebarIndex.value === null) return;
    const t = tables.value[tableSidebarIndex.value];
    t.status = data.status;
    t.customer = data.customer;
    // Optionally update name if editable
    saveTables();
    showTableSidebar.value = false;
};

const handleTableSidebarAction = (action: string, data: any) => {
    if (tableSidebarIndex.value === null) return;
    const t = tables.value[tableSidebarIndex.value];
    if (action === "occupy") {
        t.status = "occupied";
    } else if (action === "reserve") {
        t.status = "reserved";
    } else if (action === "vacant") {
        t.status = "vacant";
        t.customer = "";
    } else if (action === "checkout") {
        t.status = "vacant";
        t.customer = "";
        showTableSidebar.value = false;
        setTimeout(() => alert("Thank you!"), 100);
        saveTables();
        return;
    }
    saveTables();
    showTableSidebar.value = false;
};
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

/* Modal styles */
.modal-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-container {
    position: relative;
    width: 90%;
    max-width: 500px;
    margin: 1.5rem auto;
    background: white;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.modal-header {
    background: #f7fafc;
    border-bottom: 1px solid #e2e8f0;
}

.modal-body {
    padding: 1.5rem;
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
