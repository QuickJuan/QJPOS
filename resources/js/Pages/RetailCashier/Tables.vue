<template>
    <CashieringLayout :current-user="props.currentUser">
        <div class="flex flex-col h-screen bg-gray-100 overflow-y-hidden">
            <!-- Toolbar (sticky) -->
            <div
                class="flex flex-col md:flex-row md:items-center gap-3 p-3 bg-white shadow sticky top-0"
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
                        </div>

                        <!-- Table Name -->
                        <div
                            class="flex flex-col items-center text-center mb-3"
                        >
                            <!-- Merged indicator -->
                            <div
                                v-if="table.merge_to"
                                class="w-fit bg-purple-100 text-purple-800 text-sm px-2 py-1 rounded-full font-medium flex items-center gap-1"
                            >
                                <i class="pi pi-link text-xs"></i>
                                Merged to {{ table.merged_to.name }}
                            </div>
                            <h3
                                v-else
                                class="font-semibold text-gray-900 text-lg"
                            >
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
                @unmergeTable="handleUnmergeTable"
            />

            <!-- View Orders Modal -->
            <ViewOrdersModal
                :visible="showOrdersModal"
                :orders="tableOrders"
                @update:visible="closeOrdersModal"
                @viewOrderDetails="viewOrderDetails"
            />

            <!-- Merge Table Selection Modal -->
            <MergeTableModal
                :visible="showMergeModal"
                :table-to-merge="tableToMerge"
                :available-targets="availableMergeTargets"
                :selected-target="selectedMergeTarget"
                @update:visible="closeMergeModal"
                @selectTarget="selectMergeTarget"
                @confirmMerge="confirmMerge"
            />

            <!-- Reserve Table Modal -->
            <ReserveTableModal
                :visible="showReserveModal"
                :table="selectedTable"
                @update:visible="showReserveModal = $event"
            />
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import TableActionModal from "./Partials/TableActionModal.vue";
import ViewOrdersModal from "./Partials/ViewOrdersModal.vue";
import MergeTableModal from "./Partials/MergeTableModal.vue";
import ReserveTableModal from "./Partials/ReserveTableModal.vue";
import PageProps from "@/Types/PageProps";
import { formatTimeOccupied } from "@/Utils/FormatTime";

// Props
const props = defineProps<{
    tables: any[];
    locations: any[];
    currentUser: any;
}>();

// Reactive State
const page = usePage<PageProps>();
const tables = computed(() => props.tables || []);
const locations = ref(props.locations || []);
const selectedLocation = ref<number | null>(null);
const showTableModal = ref(false);
const selectedTable = ref<any>(null);
const showMergeModal = ref(false);
const selectedMergeTarget = ref<any>(null);
const tableToMerge = ref<any>(null);
const showOrdersModal = ref(false);
const tableOrders = ref<any[]>([]);
const showReserveModal = ref(false);

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
                // Check if the selected table is merged to the other table.
                // If yes, use the merge_to column as tableId.
                // If no, use the id of the selected table
                tableId: selectedTable.value.merged_to
                    ? selectedTable.value.merge_to
                    : selectedTable.value.id,
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
                    detail: `${tableToMerge.value.name} has been merged into ${selectedMergeTarget.value.name}`,
                    life: 3000,
                });
                closeMergeModal();
                // Refresh tables to show real-time changes
                setTimeout(() => {
                    router.reload({ only: ["tables"] });
                }, 100);
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
    showReserveModal.value = true;
};

const handleViewOrder = () => {
    router.visit(
        route("retail-cashier.index", {
            // Check if the selected table is merged to the other table.
            // If yes, use the merge_to column as tableId.
            // If no, use the id of the selected table
            tableId: selectedTable.value.merged_to
                ? selectedTable.value.merge_to
                : selectedTable.value.id,
        })
    );
    closeTableModal();
};

const handleUnmergeTable = () => {
    if (!selectedTable.value) return;

    router.put(
        route("table-rooms.unmerge", selectedTable.value.id),
        {},
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Table Unmerged",
                    detail: `Table ${selectedTable.value.name} has been unmerged`,
                    life: 3000,
                });
                closeTableModal();

                setTimeout(() => {
                    router.reload({ only: ["tables"] });
                }, 100);
            },
            onError: (error) => {
                console.log(error);
                toast.add({
                    severity: "error",
                    summary: "Unmerge Failed",
                    detail: "Failed to unmerge table. Please try again.",
                    life: 3000,
                });
            },
        }
    );
};

const closeOrdersModal = () => {
    showOrdersModal.value = false;
    tableOrders.value = [];
};

const viewOrderDetails = (order: any) => {
    router.visit(`/retail-cashier?tableId=${order.table_room.id}`);
};

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
