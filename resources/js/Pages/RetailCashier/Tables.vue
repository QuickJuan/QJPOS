<template>
    <CashieringLayout :current-user="props.currentUser">
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
                                'cursor-pointer whitespace-nowrap px-3 py-1 rounded text-sm font-semibold border flex items-center gap-1 transition-colors',
                                selectedLocation === loc.id
                                    ? 'bg-blue-600 text-white border-blue-600 shadow'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-300',
                            ]"
                        >
                            <span>{{ loc.name }}</span>
                            <span class="text-[10px] font-normal opacity-80">
                                ({{ getTableCount(loc.id) }})
                            </span>
                        </div>
                    </div>

                    <!-- Back Button (right) -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button
                            @click="goBackToCashier"
                            class="px-3 py-1 rounded bg-gray-600 text-white text-sm font-semibold hover:bg-gray-700"
                        >
                            ← Back to Cashier
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tables Grid -->
            <div class="flex-1 overflow-y-auto p-6">
                <div
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4"
                >
                    <div
                        v-for="table in sortedTables"
                        :key="table.id"
                        @click="openTableModal(table)"
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all cursor-pointer hover:scale-105 relative"
                        :class="getTableStatusClasses(table.status)"
                    >
                        <!-- Merged indicator -->
                        <div
                            v-if="table.merge_to"
                            class="absolute top-2 left-2 bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full font-medium flex items-center gap-1"
                        >
                            <i class="pi pi-link text-xs"></i>
                            Merged
                        </div>
                        <!-- Status Badge -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div
                                    :class="[
                                        'w-3 h-3 rounded-full',
                                        table.status === 'occupied' &&
                                            'bg-red-500',
                                        table.status === 'reserved' &&
                                            'bg-yellow-500',
                                        table.status === 'vacant' &&
                                            'bg-green-500',
                                        table.status === 'merged' &&
                                            'bg-purple-500',
                                    ]"
                                ></div>
                                <span
                                    class="text-xs font-medium text-gray-600 capitalize"
                                >
                                    {{ table.status }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500">
                                #{{ table.sort_number || table.id }}
                            </div>
                        </div>

                        <!-- Table Name -->
                        <div class="text-center mb-3">
                            <h3 class="font-semibold text-gray-900 text-lg">
                                {{ table.name }}
                            </h3>
                        </div>

                        <!-- Table Info -->
                        <div
                            class="text-center text-sm text-gray-600 space-y-1"
                        >
                            <p
                                v-if="table.current_order"
                                class="text-blue-600 font-medium"
                            >
                                Order #{{ table.current_order.id }}
                            </p>
                            <div
                                v-if="
                                    table.status === 'occupied' &&
                                    table.number_of_pax
                                "
                                class="space-y-0.5"
                            >
                                <p class="text-gray-700 font-medium">
                                    {{ table.number_of_pax }} pax
                                </p>
                                <p
                                    v-if="table.time_in"
                                    class="text-xs text-gray-500"
                                >
                                    {{ formatTimeOccupied(table.time_in) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="sortedTables.length === 0" class="text-center py-12">
                    <i class="pi pi-table text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        No tables found
                    </h3>
                    <p class="text-gray-600">
                        {{
                            selectedLocation
                                ? "No tables in this location yet."
                                : "No tables available."
                        }}
                    </p>
                </div>
            </div>

            <!-- Table Action Modal -->
            <TableActionModal
                :show="showTableModal"
                :table="selectedTable"
                @close="closeTableModal"
                @takeOrder="handleTakeOrder"
                @viewOrder="handleViewOrder"
                @mergeTable="handleMergeTable"
                @reserveTable="handleReserveTable"
            />

            <!-- Merge Table Selection Modal -->
            <Dialog
                :visible="showMergeModal"
                modal
                :header="`Merge Table: ${tableToMerge?.name}`"
                :style="{ width: '600px' }"
                :closable="true"
                @hide="closeMergeModal"
                @update:visible="closeMergeModal"
            >
                <div class="space-y-4">
                    <!-- Current table info -->
                    <div
                        class="bg-blue-50 border border-blue-200 rounded-lg p-3"
                    >
                        <div class="flex items-center gap-2 mb-1">
                            <div
                                class="w-3 h-3 rounded-full bg-green-500"
                            ></div>
                            <span class="text-sm font-medium text-blue-900"
                                >Vacant Table to Merge</span
                            >
                        </div>
                        <p class="text-sm text-blue-800 font-semibold">
                            {{ tableToMerge?.name }}
                        </p>
                        <p class="text-xs text-blue-600">
                            {{ tableToMerge?.chairs }} chairs
                        </p>
                        <p
                            v-if="tableToMerge?.merge_to"
                            class="text-xs text-orange-600 mt-1"
                        >
                            ⚠️ This table is already merged into another table
                        </p>
                    </div>

                    <p class="text-sm text-gray-600">
                        Select an occupied table to merge this vacant table
                        into:
                    </p>

                    <div
                        class="grid grid-cols-1 gap-3 max-h-80 overflow-y-auto"
                    >
                        <div
                            v-for="table in availableMergeTargets"
                            :key="table.id"
                            @click="selectMergeTarget(table)"
                            class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all cursor-pointer hover:scale-102 relative"
                            :class="[
                                getTableStatusClasses(table.status),
                                selectedMergeTarget?.id === table.id
                                    ? 'ring-2 ring-blue-500 border-blue-500 bg-blue-50'
                                    : '',
                            ]"
                        >
                            <!-- Selection indicator -->
                            <div class="absolute top-3 right-3">
                                <div
                                    :class="[
                                        'w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors',
                                        selectedMergeTarget?.id === table.id
                                            ? 'border-blue-500 bg-blue-500'
                                            : 'border-gray-300 hover:border-gray-400',
                                    ]"
                                >
                                    <div
                                        v-if="
                                            selectedMergeTarget?.id === table.id
                                        "
                                        class="w-2.5 h-2.5 rounded-full bg-white"
                                    ></div>
                                </div>
                            </div>

                            <div class="flex items-start justify-between pr-8">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div
                                            :class="[
                                                'w-3 h-3 rounded-full',
                                                table.status === 'occupied' &&
                                                    'bg-red-500',
                                                table.status === 'reserved' &&
                                                    'bg-yellow-500',
                                                table.status === 'vacant' &&
                                                    'bg-green-500',
                                                table.status === 'merged' &&
                                                    'bg-purple-500',
                                            ]"
                                        ></div>
                                        <span
                                            class="text-sm font-medium text-gray-700 capitalize"
                                        >
                                            {{ table.status }}
                                        </span>
                                        <span
                                            class="text-xs text-gray-500 ml-auto"
                                        >
                                            #{{ table.sort_number || table.id }}
                                        </span>
                                    </div>

                                    <h4
                                        class="font-semibold text-gray-900 text-base mb-1"
                                    >
                                        {{ table.name }}
                                    </h4>

                                    <div
                                        class="flex items-center gap-4 text-sm text-gray-600"
                                    >
                                        <span>{{ table.chairs }} chairs</span>
                                        <span
                                            v-if="table.current_order"
                                            class="text-blue-600 font-medium"
                                        >
                                            Order #{{ table.current_order.id }}
                                        </span>
                                    </div>

                                    <!-- Show merged tables info -->
                                    <div
                                        v-if="getMergedTables(table).length > 0"
                                        class="mt-2"
                                    >
                                        <p
                                            class="text-xs text-purple-600 font-medium"
                                        >
                                            Merged tables:
                                            {{
                                                getMergedTables(table)
                                                    .map((t) => t.name)
                                                    .join(", ")
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="availableMergeTargets.length === 0"
                        class="text-center py-8"
                    >
                        <i
                            class="pi pi-info-circle text-2xl text-gray-300 mb-2"
                        ></i>
                        <p class="text-sm text-gray-600">
                            No occupied tables available for merging.
                        </p>
                    </div>
                </div>
                <template #footer>
                    <Button
                        label="Cancel"
                        severity="secondary"
                        @click="closeMergeModal"
                    />
                    <Button
                        label="Merge Tables"
                        severity="success"
                        :disabled="!selectedMergeTarget"
                        @click="confirmMerge"
                    />
                </template>
            </Dialog>
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import TableActionModal from "./Partials/TableActionModal.vue";
import PageProps from "@/Types/PageProps";

// Helper function to format time occupied
const formatTimeOccupied = (timeIn: string) => {
    const start = new Date(timeIn);
    // Format as date and time
    const dateStr = start.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
    const timeStr = start.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });

    return `${dateStr} ${timeStr}`;
};

// Props
const props = defineProps<{
    tables: any[];
    locations: any[];
    currentUser: any;
}>();

// Reactive State
const page = usePage<PageProps>();
const tables = ref(props.tables || []);
const locations = ref(props.locations || []);
const selectedLocation = ref<number | null>(null);
const showTableModal = ref(false);
const selectedTable = ref<any>(null);
const showMergeModal = ref(false);
const selectedMergeTarget = ref<any>(null);
const tableToMerge = ref<any>(null);

// Toast
const toast = useToast();

// Computeds
const filteredTables = computed(() =>
    selectedLocation.value === null
        ? tables.value
        : tables.value.filter(
              (t) => t.table_room_location_id === selectedLocation.value
          )
);

const sortedTables = computed(() =>
    [...filteredTables.value].sort((a, b) => {
        // Sort by sort_number first, then by name
        const aSort = a.sort_number || 0;
        const bSort = b.sort_number || 0;
        if (aSort !== bSort) return aSort - bSort;
        return a.name.localeCompare(b.name);
    })
);

const getTableCount = (locationId: number) =>
    tables.value.filter((t) => t.table_room_location_id === locationId).length;

const availableMergeTargets = computed(() =>
    filteredTables.value.filter((t) => t.status === "occupied")
);

const getMergedTables = (targetTable: any) => {
    return filteredTables.value.filter((t) => t.merge_to === targetTable.id);
};

// Methods
const getTableStatusClasses = (status: string) => {
    const baseClasses = "hover:border-gray-300";
    switch (status) {
        case "occupied":
            return `${baseClasses} border-red-200 bg-red-50`;
        case "reserved":
            return `${baseClasses} border-yellow-200 bg-yellow-50`;
        case "vacant":
            return `${baseClasses} border-green-200 bg-green-50`;
        case "merged":
            return `${baseClasses} border-purple-200 bg-purple-50`;
        default:
            return baseClasses;
    }
};

const getTableImage = (table: any) => {
    if (table.featured_image_url) {
        return table.featured_image_url;
    }
    // Default images based on chair count
    const images: { [key: number]: string } = {
        2: "/images/round-4.png",
        4: "/images/square-4.png",
        6: "/images/rec-6.png",
        8: "/images/rec-8.png",
    };
    return images[table.chairs] || "/images/round-4.png";
};

const openTableModal = (table: any) => {
    selectedTable.value = table;
    showTableModal.value = true;
};

const closeTableModal = () => {
    showTableModal.value = false;
    selectedTable.value = null;
};

const goBackToCashier = () => {
    router.visit(route("retail-cashier.index"));
};

const handleTakeOrder = (data: any) => {
    if (selectedTable.value.status === "vacant") {
        router.post(
            route("retail-cashier.cart.create-order"),
            {
                table_id: selectedTable.value.id,
                pax: data.pax,
                guest_name: data.guest_name,
            },
            {
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Order Started",
                        detail: `Started order for ${data.guest_name} (${data.pax} pax)`,
                        life: 3000,
                    });
                    closeTableModal();
                    router.visit(route("retail-cashier.index"));
                },
                onError: () => {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: "Failed to start order",
                        life: 3000,
                    });
                },
            }
        );
    } else {
        router.visit(
            route("retail-cashier.index", {
                tableId: selectedTable.value.id,
            })
        );
        closeTableModal();
    }
};

const handleViewOrder = () => {
    if (selectedTable.value.current_order) {
        router.visit(
            route("retail-cashier.index", {
                order_id: selectedTable.value.current_order.id,
            })
        );
        closeTableModal();
    }
};

const handleMergeTable = () => {
    tableToMerge.value = selectedTable.value;
    showMergeModal.value = true;
    closeTableModal();
};

const closeMergeModal = () => {
    showMergeModal.value = false;
    selectedMergeTarget.value = null;
    tableToMerge.value = null;
};

const selectMergeTarget = (table: any) => {
    selectedMergeTarget.value = table;
};

const confirmMerge = () => {
    if (!tableToMerge.value || !selectedMergeTarget.value) return;

    router.put(
        route("table-rooms.merge", tableToMerge.value.id),
        {
            merge_to: selectedMergeTarget.value.id,
        },
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Table Merged",
                    detail: `Table ${tableToMerge.value.name} has been merged into ${selectedMergeTarget.value.name}`,
                    life: 3000,
                });
                closeMergeModal();
                // Refresh tables
                router.reload({ only: ["tables"] });
            },
            onError: (error) => {
                console.log(error);
                toast.add({
                    severity: "error",
                    summary: "Merge Failed",
                    detail: "Failed to merge tables. Please try again.",
                    life: 3000,
                });
            },
        }
    );
};

const handleReserveTable = () => {
    // Toggle reservation status
    const newStatus =
        selectedTable.value.status === "reserved" ? "vacant" : "reserved";
    router.put(
        route("table-rooms.update", selectedTable.value.id),
        {
            status: newStatus,
        },
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Table Updated",
                    detail: `Table ${selectedTable.value.name} is now ${newStatus}`,
                    life: 3000,
                });
                closeTableModal();
                // Refresh tables
                router.reload({ only: ["tables"] });
            },
            onError: () => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to update table status",
                    life: 3000,
                });
            },
        }
    );
};

// Lifecycle
onMounted(() => {
    // Auto-select first location if none selected
    if (!selectedLocation.value && locations.value.length > 0) {
        selectedLocation.value = locations.value[0].id;
    }

    if (page.props.flash.success) {
        toast.add({
            severity: "success",
            summary: "Success",
            detail: page.props.flash.success,
            life: 3000,
        });
    }

    if (page.props.flash.error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: page.props.flash.error,
            life: 3000,
        });
    }
});
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
