<template>
    <div class="relative w-full h-screen bg-gray-100 overflow-auto">
        <!-- Add buttons to create tables -->
        <div class="z-50 space-x-2 p-4 bg-white shadow-md sticky top-0 w-full">
            <button @click="addTable(2)" class="btn">Add 2 Chairs</button>
            <button @click="addTable(4)" class="btn">Add 4 Chairs</button>
            <button @click="addTable(6)" class="btn">Add 6 Chairs</button>
            <button
                class="px-4 py-2 rounded bg-indigo-500 text-white hover:bg-indigo-600"
                @click="designMode = !designMode"
            >
                {{ designMode ? "Exit Design Mode" : "Enter Design Mode" }}
            </button>
        </div>

        <!-- Floor canvas -->
        <div class="relative" :style="{ width: '200%', height: '200%' }">
            <div
                v-for="(table, index) in tables"
                :key="table.id"
                class="absolute bg-teal-300 rounded shadow text-center select-none"
                :class="[
                    designMode
                        ? 'cursor-move bg-teal-300'
                        : 'cursor-pointer bg-gray-200',
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
                <p class="text-sm font-bold">Table #{{ index + 1 }}</p>
                <p class="text-xs text-gray-500">Chairs: {{ table.chairs }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const tables = ref([]);
const designMode = ref(true);

// Add a new table
const addTable = (chairs) => {
    tables.value.push({
        id: Date.now() + Math.random(),
        chairs,
        x: 100,
        y: 100,
        width: 100,
        height: 80,
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
    const newX = event.clientX - dragInfo.offsetX;
    const newY = event.clientY - dragInfo.offsetY;

    const tempTable = {
        ...table,
        x: newX,
        y: newY,
    };

    // Check collision
    const collides = tables.value.some((other) => {
        if (other.id === table.id) return false;

        // Set width/height (or estimate if missing)
        tempTable.width = 100;
        tempTable.height = 80;
        other.width = 100;
        other.height = 80;

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
</style>
