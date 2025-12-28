<template>
    <TableManagementLayout>
        <div class="flex flex-col h-screen bg-gray-100 overflow-y-hidden">
            <!-- Toolbar (sticky) -->
            <div
                class="flex flex-col md:flex-row md:items-center gap-3 p-3 bg-white shadow sticky top-0 z-40"
            >
                <div class="flex w-full items-center justify-between gap-4">
                    <!-- Location Tabs (left) -->
                    <div
                        class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0 flex-1"
                    >
                        <div
                            v-for="loc in locations"
                            :key="loc.id"
                            @click="selectedLocation = loc.id"
                            :class="[
                                'cursor-pointer whitespace-nowrap px-3 py-1  rounded text-sm font-semibold border flex items-center gap-1 transition-colors',
                                selectedLocation === loc.id
                                    ? 'bg-blue-600 text-white border-blue-600 shadow'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-300',
                            ]"
                        >
                            <span>{{ loc.name }}</span>
                            <span class="text-[10px] font-normal opacity-80"
                                >({{ getTableCount(loc.id) }})</span
                            >
                        </div>
                    </div>
                    <!-- Action Buttons (right) -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button
                            @click="designMode = !designMode"
                            class="px-3 py-1 rounded text-sm font-semibold"
                            :class="
                                designMode
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-200 text-gray-700'
                            "
                        >
                            {{
                                designMode ? "Exit Design Mode" : "Design Mode"
                            }}
                        </button>
                        <button
                            v-if="designMode && dirtyCount > 0"
                            @click="saveAllPositions"
                            class="px-3 py-1 rounded bg-green-600 text-white text-sm font-semibold"
                        >
                            Save {{ dirtyCount }} Position{{
                                dirtyCount > 1 ? "s" : ""
                            }}
                        </button>
                        <button
                            @click="openAddTableModal"
                            class="px-3 py-1 rounded bg-indigo-600 text-white text-sm font-semibold"
                        >
                            + New Table
                        </button>
                        <button
                            @click="showLocationManagerModal = true"
                            class="px-3 py-1 rounded bg-yellow-500 text-white text-sm font-semibold"
                        >
                            Manage Locations
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scrollable Floor Grid (single scroll area) -->
            <div
                ref="floorContainer"
                class="flex-1 bg-gray-50 relative custom-scroll-container"
            >
                <!-- Canvas sized dynamically -->
                <div
                    class="relative"
                    :style="{
                        width: floorWidth + 'px',
                        height: floorHeight + 'px',
                    }"
                >
                    <div
                        class="floor absolute inset-0 pointer-events-none"
                    ></div>
                    <div
                        v-for="(table, index) in filteredTables"
                        :key="table.id"
                        class="absolute shadow rounded select-none group bg-transparent"
                        :style="{
                            left: table.x + 'px',
                            top: table.y + 'px',
                            width: table.width + 'px',
                            height: table.height + 'px',
                            minWidth: '96px',
                            minHeight: '96px',
                        }"
                        @mousedown="startDrag($event, table.id)"
                        @click="!designMode ? openEditTableModal(index) : null"
                    >
                        <!-- Status Badge -->
                        <div
                            v-if="!designMode"
                            class="absolute -top-2 -left-2 z-30"
                        >
                            <span
                                :class="[
                                    'px-2 py-1 text-[10px] rounded-full font-semibold shadow',
                                    table.status === 'occupied' &&
                                        'bg-red-600 text-white',
                                    table.status === 'reserved' &&
                                        'bg-yellow-500 text-white',
                                    table.status === 'vacant' &&
                                        'bg-green-600 text-white',
                                ]"
                            >
                                {{ table.status }}
                            </span>
                        </div>

                        <!-- Edit Button -->
                        <button
                            v-if="designMode"
                            @click.stop="openEditTableModal(index)"
                            class="absolute top-1 right-1 z-20 bg-white rounded-full p-1 shadow hover:bg-blue-100 focus:outline-none"
                            title="Edit Table"
                        >
                            <PencilIcon class="h-4 w-4 text-blue-600" />
                        </button>

                        <!-- Image & Centered Name Overlay -->
                        <div
                            class="relative w-full h-full flex items-center justify-center p-1"
                        >
                            <img
                                v-if="getTableImage(table)"
                                :src="getTableImage(table)"
                                :alt="table.name || 'Table image'"
                                class="absolute inset-0 w-full h-full object-contain pointer-events-none select-none"
                                draggable="false"
                            />
                            <div class="flex items-center justify-center">
                                <div
                                    class="px-3 py-2 flex items-center justify-center"
                                >
                                    <span
                                        class="text-[11px] font-semibold text-gray-800 whitespace-nowrap"
                                    >
                                        {{
                                            table.name || `Table #${index + 1}`
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unified Table Modal -->
            <TableModal
                :show="showTableModal"
                :table="activeTable"
                :locations="locations"
                :active-location="selectedLocation"
                :resetToken="modalResetToken"
                @close="closeTableModal"
                @submit="handleUnifiedModalSubmit"
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
import { ref, watch, onMounted, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "primevue";
import { PencilIcon } from "@heroicons/vue/24/outline";
import TableManagementLayout from "@/Layouts/TableManagementLayout.vue";
import TableModal from "./Partials/TableModal.vue";
import LocationManagerModal from "./Partials/LocationManagerModal.vue";
import PageProps from "@/Types/PageProps";

// Props
const props = defineProps<{
    tables: any[];
    locations: any[];
    selectedLocation: any;
}>();

// Toast & page
const toast = useToast();
const page = usePage<PageProps>();

// Constants
const GRID_SIZE = 20;
const LOCATION_STORAGE_KEY = "table-management-selected-location";

// Reactive State
const tables = ref(props.tables);
const locations = ref(props.locations);
// Selected location (will also sync with URL ?location=ID)
const selectedLocation = ref<number | null>(props.selectedLocation);
const designMode = ref(false);
const showTableModal = ref(false);
const showLocationManagerModal = ref(false);
const editTableIndex = ref<number | null>(null);
const activeTable = ref<any>(null);
const modalResetToken = ref(0);
// Floor container ref for potential future viewport-based calculations
const floorContainer = ref<HTMLElement | null>(null);

// Dynamic canvas sizing: depend on table extents + generous padding.
// Additionally enforce a minimum base size so that when moving from a large screen to a very small one
// you still retain horizontal/vertical scroll room to reach distant tables placed previously.
const FLOOR_PADDING = 400; // extra workspace beyond farthest table
const MIN_BASE_WIDTH = 1800; // guaranteed minimum virtual width
const MIN_BASE_HEIGHT = 1200; // guaranteed minimum virtual height
const floorWidth = computed(() => {
    const locTables = filteredTables.value;
    const maxRight = locTables.length
        ? Math.max(...locTables.map((t) => t.x + (t.width || 0)))
        : 0;
    // width based on farthest right table + padding or minimum baseline (whichever larger)
    return snapToGrid(Math.max(maxRight + FLOOR_PADDING, MIN_BASE_WIDTH));
});
const floorHeight = computed(() => {
    const locTables = filteredTables.value;
    const maxBottom = locTables.length
        ? Math.max(...locTables.map((t) => t.y + (t.height || 0)))
        : 0;
    return snapToGrid(Math.max(maxBottom + FLOOR_PADDING, MIN_BASE_HEIGHT));
});

// Lifecycle: load persisted location
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const paramLoc = urlParams.get("location");
    if (paramLoc) {
        const locId = parseInt(paramLoc);
        if (locations.value.some((l) => l.id === locId)) {
            selectedLocation.value = locId;
        }
    }
    // 2. Fallback to localStorage
    if (!selectedLocation.value) {
        const savedLocation = localStorage.getItem(LOCATION_STORAGE_KEY);
        if (savedLocation) {
            const locationId = parseInt(savedLocation);
            if (locations.value.some((l) => l.id === locationId)) {
                selectedLocation.value = locationId;
            }
        }
    }
    // 3. Default to first
    if (!selectedLocation.value && locations.value.length) {
        selectedLocation.value = locations.value[0].id;
    }
    // Ensure URL reflects current selection
    syncLocationParam();
});

// Persist location changes
watch(selectedLocation, (loc) => {
    if (loc === null) return;
    localStorage.setItem(LOCATION_STORAGE_KEY, loc.toString());
    syncLocationParam();
    // If adding a new table (modal open and no active table), trigger modal reset so default location field updates
    if (showTableModal.value && !activeTable.value) {
        modalResetToken.value++;
    }
});

// Update browser URL without full reload
const syncLocationParam = () => {
    if (selectedLocation.value === null) return;
    const url = new URL(window.location.href);
    url.searchParams.set("location", selectedLocation.value.toString());
    window.history.replaceState({}, "", url.toString());
};

// Computeds
const filteredTables = computed(() =>
    tables.value.filter(
        (t) => t.table_room_location_id === selectedLocation.value
    )
);
const getTableCount = (locationId: number) =>
    tables.value.filter((t) => t.table_room_location_id === locationId).length;

// Auto-scroll horizontally if new tables extend beyond current viewport
watch(filteredTables, (newTables) => {
    const container = floorContainer.value;
    if (!container || !newTables.length) return;
    const maxRight = Math.max(...newTables.map((t) => t.x + (t.width || 0)));
    if (maxRight > container.scrollLeft + container.clientWidth) {
        container.scrollLeft = Math.max(
            0,
            maxRight - container.clientWidth + 80
        );
    }
    const maxBottom = Math.max(...newTables.map((t) => t.y + (t.height || 0)));
    if (maxBottom > container.scrollTop + container.clientHeight) {
        container.scrollTop = Math.max(
            0,
            maxBottom - container.clientHeight + 80
        );
    }
});

// Drag State
interface DragInfo {
    dragging: boolean;
    tableId: number | null;
    offsetX: number;
    offsetY: number;
}
let dragInfo: DragInfo = {
    dragging: false,
    tableId: null,
    offsetX: 0,
    offsetY: 0,
};

const snapToGrid = (val: number) => Math.round(val / GRID_SIZE) * GRID_SIZE;
const isOverlapping = (a: any, b: any) => {
    const padding = 8;
    return !(
        a.x + a.width + padding < b.x ||
        a.x > b.x + b.width + padding ||
        a.y + a.height + padding < b.y ||
        a.y > b.y + b.height + padding
    );
};
const startDrag = (e: MouseEvent, id: number) => {
    if (!designMode.value) return;
    const table = tables.value.find((t) => t.id === id);
    if (!table) return;
    dragInfo.dragging = true;
    dragInfo.tableId = id;
    // Account for scroll offsets of the floor container
    const scrollLeft = floorContainer.value?.scrollLeft || 0;
    const scrollTop = floorContainer.value?.scrollTop || 0;
    dragInfo.offsetX = e.clientX + scrollLeft - table.x;
    dragInfo.offsetY = e.clientY + scrollTop - table.y;
    window.addEventListener("mousemove", onDrag);
    window.addEventListener("mouseup", endDrag);
};
const onDrag = (e: MouseEvent) => {
    if (!dragInfo.dragging || dragInfo.tableId === null) return;
    const table = tables.value.find((t) => t.id === dragInfo.tableId);
    if (!table) return;
    const scrollLeft = floorContainer.value?.scrollLeft || 0;
    const scrollTop = floorContainer.value?.scrollTop || 0;
    let newX = snapToGrid(e.clientX + scrollLeft - dragInfo.offsetX);
    let newY = snapToGrid(e.clientY + scrollTop - dragInfo.offsetY);

    // Auto-scroll near right/bottom edges to expand working area
    const container = floorContainer.value;
    if (container) {
        const edgeThreshold = 60; // px from edge to trigger scroll
        const speed = 25; // scroll delta
        const relativeX = e.clientX - container.getBoundingClientRect().left;
        const relativeY = e.clientY - container.getBoundingClientRect().top;

        // Horizontal auto-scroll
        if (relativeX > container.clientWidth - edgeThreshold) {
            container.scrollLeft += speed;
        } else if (relativeX < edgeThreshold) {
            container.scrollLeft = Math.max(container.scrollLeft - speed, 0);
        }
        // Vertical auto-scroll
        if (relativeY > container.clientHeight - edgeThreshold) {
            container.scrollTop += speed;
        } else if (relativeY < edgeThreshold) {
            container.scrollTop = Math.max(container.scrollTop - speed, 0);
        }
        // Recalculate with new scroll positions after auto-scroll
        const updatedScrollLeft = container.scrollLeft;
        const updatedScrollTop = container.scrollTop;
        newX = snapToGrid(e.clientX + updatedScrollLeft - dragInfo.offsetX);
        newY = snapToGrid(e.clientY + updatedScrollTop - dragInfo.offsetY);
    }
    const temp = { ...table, x: newX, y: newY };
    // Only check collision against tables in the same location
    const collides = tables.value.some((other) => {
        if (other.id === table.id) return false;
        if (other.table_room_location_id !== selectedLocation.value)
            return false;
        return isOverlapping(temp, other);
    });
    if (!collides) {
        table.x = newX;
        table.y = newY;
    }
};
const endDrag = () => {
    if (dragInfo.dragging && dragInfo.tableId) {
        const t = tables.value.find((tb) => tb.id === dragInfo.tableId);
        if (t) markDirty(t);
    }
    dragInfo.dragging = false;
    dragInfo.tableId = null;
    window.removeEventListener("mousemove", onDrag);
    window.removeEventListener("mouseup", endDrag);
};

// Bulk Position Save
const dirtyPositions = ref<Record<number, { x: number; y: number }>>({});
const markDirty = (table: any) => {
    if (!designMode.value) return;
    dirtyPositions.value[table.id] = { x: table.x, y: table.y };
};
const dirtyCount = computed(() => Object.keys(dirtyPositions.value).length);
const saveAllPositions = () => {
    if (!designMode.value || dirtyCount.value === 0) return;
    const payload = Object.entries(dirtyPositions.value).map(([id, pos]) => ({
        id: Number(id),
        table_x: pos.x,
        table_y: pos.y,
    }));
    router.post(
        "/table-rooms/tables/bulk-update-positions",
        { positions: payload },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Positions Saved",
                    detail: "All table positions updated.",
                    life: 2500,
                });
                dirtyPositions.value = {};
            },
            onError: () => {
                toast.add({
                    severity: "error",
                    summary: "Save Failed",
                    detail: "Bulk save failed. Please try again.",
                    life: 3000,
                });
            },
        }
    );
};

// Modal Helpers
const openAddTableModal = () => {
    activeTable.value = null;
    editTableIndex.value = null;
    modalResetToken.value++;
    showTableModal.value = true;
};
const openEditTableModal = (idx: number) => {
    const t = tables.value[idx];
    editTableIndex.value = idx;
    activeTable.value = { ...t };
    showTableModal.value = true;
};
const closeTableModal = () => {
    showTableModal.value = false;
    activeTable.value = null;
    editTableIndex.value = null;
};

// CRUD Operations
const handleAddTableSubmit = (formData: any) => {
    const data = new FormData();
    // Ensure location defaults to active selectedLocation if form didn't set it
    const locationId =
        formData.table_room_location_id || selectedLocation.value;
    data.append("name", formData.name);
    data.append("chairs", formData.chairs);
    data.append("table_room_location_id", locationId);
    data.append("table_width", formData.width);
    data.append("table_height", formData.height);
    data.append("table_x", formData.x || 0);
    data.append("table_y", formData.y || 0);
    if (formData.imageFile) data.append("featured_image", formData.imageFile);
    router.post("/table-rooms/tables", data, {
        forceFormData: true,
        onSuccess: () => {
            toast.add({
                severity: "success",
                summary: "Success",
                detail: page.props.flash.success || "Table added successfully.",
                life: 3000,
            });
            // Pull latest tables from page props if reloaded or append optimistically
            // Prefer optimistic append if backend returns new table in flash or we can infer values
            if (selectedLocation.value === locationId) {
                // Attempt to find newly added table from updated page props (if inertia replaced props)
                const newPropsTables: any[] =
                    (usePage<PageProps>().props as any).tables || [];
                if (
                    newPropsTables.length &&
                    newPropsTables.length > tables.value.length
                ) {
                    tables.value = newPropsTables as any[];
                } else {
                    // Optimistic append (minimal fields); actual record will be corrected on next reload
                    tables.value.push({
                        id: Date.now(), // temporary id placeholder
                        name: formData.name,
                        chairs: formData.chairs,
                        table_room_location_id: locationId,
                        width: formData.width,
                        height: formData.height,
                        x: formData.x || 0,
                        y: formData.y || 0,
                        status: "vacant",
                        featured_image_url: "",
                    });
                }
            }
        },
        onError: () => {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: page.props.flash.error || "Failed to add table.",
                life: 3000,
            });
        },
    });
    closeTableModal();
};
const handleEditTableSubmit = (formData: any) => {
    if (editTableIndex.value === null) return;
    const tableId = tables.value[editTableIndex.value].id;
    const data = new FormData();
    data.append("name", formData.name);
    data.append("chairs", formData.chairs);
    data.append("table_room_location_id", formData.table_room_location_id);
    data.append("table_width", formData.width);
    data.append("table_height", formData.height);
    data.append("table_x", formData.x || 0);
    data.append("table_y", formData.y || 0);
    if (formData.imageFile) data.append("featured_image", formData.imageFile);
    router.post(`/table-rooms/tables/${tableId}?_method=PUT`, data, {
        forceFormData: true,
        onSuccess: () => {
            toast.add({
                severity: "success",
                summary: "Success",
                detail:
                    page.props.flash.success || "Table updated successfully.",
                life: 3000,
            });
            // Refresh only tables; then apply cache-bust to updated table
            router.reload({
                only: ["tables"],
                onSuccess: () => {
                    const newPropsTables: any[] =
                        (usePage<PageProps>().props as any).tables || [];
                    if (newPropsTables.length) {
                        tables.value = newPropsTables.map((t) => {
                            if (t.id === tableId && t.featured_image_url) {
                                const v = Date.now();
                                return {
                                    ...t,
                                    featured_image_url: `${t.featured_image_url}?v=${v}`,
                                };
                            }
                            return t;
                        });
                    }
                },
            });
        },
        onError: () => {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: page.props.flash.error || "Failed to update table.",
                life: 3000,
            });
        },
    });
    closeTableModal();
};
const handleDeleteTableSubmit = () => {
    if (editTableIndex.value === null || !activeTable.value) return;
    const tableId = tables.value[editTableIndex.value].id;
    router.delete(`/table-rooms/tables/${tableId}`, {
        onSuccess: () => {
            toast.add({
                severity: "success",
                summary: "Success",
                detail:
                    page.props.flash.success || "Table deleted successfully.",
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
    closeTableModal();
};
const handleUnifiedModalSubmit = (formData: any) => {
    if (activeTable.value && activeTable.value.id) {
        handleEditTableSubmit(formData);
    } else {
        handleAddTableSubmit(formData);
    }
};

// Image helper
const getTableImage = (table: any) => {
    if (!table.featured_image_url) return "";
    // If URL already has a version param, return as-is; else add updated_at or timestamp for cache bust.
    if (/\bv=\d+/.test(table.featured_image_url))
        return table.featured_image_url;
    const version = table.updated_at
        ? new Date(table.updated_at).getTime()
        : Date.now();
    const separator = table.featured_image_url.includes("?") ? "&" : "?";
    return `${table.featured_image_url}${separator}v=${version}`;
};

// Location CRUD handlers with immediate tab updates
const refreshLocationsFromProps = () => {
    const newLocs: any[] = (usePage<PageProps>().props as any).locations || [];
    if (newLocs.length) {
        locations.value = newLocs;
    }
};
const handleAddLocation = (name: string) => {
    router.post(
        "/table-management/locations",
        { name },
        {
            preserveScroll: true,
            onSuccess: () => {
                refreshLocationsFromProps();
                // Select newly added location (last in list) so user can start adding tables immediately
                const last = locations.value[locations.value.length - 1];
                if (last) {
                    selectedLocation.value = last.id;
                }
                showLocationManagerModal.value = false;
            },
            onError: () => {
                toast.add({
                    severity: "error",
                    summary: "Location Error",
                    detail: "Failed to add location.",
                    life: 3000,
                });
            },
        }
    );
};
const handleEditLocation = (id: number, name: string) => {
    router.put(
        `/table-management/locations/${id}`,
        { name },
        {
            preserveScroll: true,
            onSuccess: () => {
                refreshLocationsFromProps();
                showLocationManagerModal.value = false;
            },
            onError: () => {
                toast.add({
                    severity: "error",
                    summary: "Location Error",
                    detail: "Failed to update location.",
                    life: 3000,
                });
            },
        }
    );
};
const handleDeleteLocation = (id: number) => {
    router.delete(`/table-management/locations/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            refreshLocationsFromProps();
            // If deleted selected location, choose first available
            if (!locations.value.some((l) => l.id === selectedLocation.value)) {
                selectedLocation.value = locations.value.length
                    ? locations.value[0].id
                    : null;
            }
            showLocationManagerModal.value = false;
        },
        onError: () => {
            toast.add({
                severity: "error",
                summary: "Location Error",
                detail: "Failed to delete location.",
                life: 3000,
            });
        },
    });
};
</script>

<style scoped>
/* Ensure scrollbars reserve space and become visible across platforms */
.custom-scroll-container {
    overflow: auto; /* both axes as needed */
    scrollbar-gutter: stable both-axis; /* keep layout stable when scrollbars appear */
}
/* Optional: always show horizontal scrollbar track even if macOS auto-hides (will show when content wider) */
@supports not (scrollbar-gutter: stable) {
    .custom-scroll-container {
        overflow: scroll;
    }
}
</style>
