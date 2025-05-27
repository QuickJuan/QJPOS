<template>
    <div class="relative w-full h-screen bg-gray-100 overflow-auto">
        <!-- Add buttons to create tables -->
        <div class="z-50 space-x-2 p-4 bg-white shadow-md sticky top-0 w-full">
            <button @click="addTable(2)" class="btn">Add 2 Chairs</button>
            <button @click="addTable(4)" class="btn">Add 4 Chairs</button>
            <button @click="addTable(6)" class="btn">Add 6 Chairs</button>
            <button @click="addTable(8)" class="btn">Add 8 Chairs</button>
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

        <!-- Floor canvas -->
        <div
            class="relative transform origin-top-left transition-transform duration-200"
            :class="{ floor: designMode }"
            :style="{
                width: '2000px',
                height: '1000px',
                transform: `scale(${zoomLevel})`,
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
                }"
                @mousedown="startDrag($event, table.id)"
            >
                <!-- <p class="text-sm font-bold">Table #{{ index + 1 }}</p>
                <p class="text-xs text-gray-500">Chairs: {{ table.chairs }}</p> -->
                <div
                    class="relative w-full h-full flex justify-center items-center p-2"
                >
                    <img
                        :src="table.img"
                        :alt="`Table with ${table.chairs} chairs`"
                        class="w-auto h-full transparent-blend mx-auto"
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
    </div>
</template>

<script setup>
// import table from "vendor/filament/tables/resources/js/components/table";
import { ref, onMounted } from "vue";

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
    tables.value.push({
        id: Date.now() + Math.random(),
        chairs,
        x: 100,
        y: 100,
        ...tableSize[chairs],
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

onMounted(() => {
    loadTables();
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

    const tempTable = { ...table, x: newX, y: newY, width: 150, height: 100 };

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
</script>

<style scoped>
.btn {
    @apply px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700;
}

.floor {
    background-image: linear-gradient(#e5e7eb 1px, transparent 1px),
        linear-gradient(to right, #e5e7eb 1px, transparent 1px);
    background-size: 20px 20px;
}

.transparent-blend {
    mix-blend-mode: multiply;
}
</style>
