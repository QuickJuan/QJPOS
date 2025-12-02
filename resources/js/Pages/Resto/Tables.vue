<template>
    <CashieringLayout :current-user="props.currentUser">
        <!-- Fixed Header for Mobile/Tablet -->
        <div
            class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 shadow-md"
        >
            <div class="flex items-center justify-between px-3 py-2">
                <!-- Left: Menu Toggle -->
                <button
                    @click="toggleSidebar"
                    class="bg-primary text-white rounded-lg p-2.5 shadow hover:bg-primary-600 transition-all"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        ></path>
                    </svg>
                </button>

                <!-- Center: Title -->
                <h1 class="text-base font-bold text-gray-800">Tables</h1>

                <!-- Right: Back to Cashier -->
                <button
                    @click="goBackToCashier"
                    class="bg-gray-600 text-white rounded-lg p-2.5 shadow hover:bg-gray-700 transition-all"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        ></path>
                    </svg>
                </button>
            </div>
        </div>

        <div
            class="flex flex-col h-screen bg-gray-100 overflow-y-hidden pt-[56px] lg:pt-0"
        >
            <!-- Toolbar (sticky on desktop, part of content flow on mobile) -->
            <div class="p-3 bg-white shadow lg:sticky lg:top-0">
                <!-- Location Tabs Grid -->
                <div
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2"
                >
                    <div
                        v-for="loc in tableRooms"
                        :key="loc.id"
                        @click="selectedLocationId = loc.id"
                        :class="[
                            'cursor-pointer px-2 py-1.5 rounded text-xs sm:text-sm font-semibold border flex flex-col sm:flex-row items-center justify-center gap-1 transition-colors',
                            selectedLocationId === loc.id
                                ? 'bg-blue-600 text-white border-blue-600 shadow'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-300',
                        ]"
                    >
                        <span class="truncate">{{ loc.name }}</span>
                        <span class="text-[10px] opacity-80">
                            ({{ loc?.tableRoomCount || 0 }})
                        </span>
                    </div>
                </div>

                <!-- Back Button (Desktop only) -->
                <div class="hidden lg:flex justify-end mt-3">
                    <button
                        @click="goBackToCashier"
                        class="px-3 py-1 rounded bg-gray-600 text-white text-sm font-semibold hover:bg-gray-700"
                    >
                        ← Back to Cashier
                    </button>
                </div>
            </div>

            <!-- Tables Grid -->
            <div class="flex-1 overflow-y-auto p-6">
                <div
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4"
                >
                    <!-- Parent Tables -->
                    <template v-for="table in filteredTables" :key="table.id">
                        <!-- Parent Table -->
                        <div
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
                                            table.status === 'available' &&
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

                        <!-- Merged Tables (with recursive support) -->
                        <template
                            v-if="
                                table.mergedTables &&
                                table.mergedTables.length > 0
                            "
                        >
                            <template
                                v-for="mergedTable in table.mergedTables"
                                :key="mergedTable.id"
                            >
                                <!-- Merged Table Card -->
                                <div
                                    @click="openTableModal(mergedTable)"
                                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all cursor-pointer hover:scale-105 relative opacity-75"
                                    :class="
                                        getTableStatusClasses(
                                            mergedTable.status
                                        )
                                    "
                                >
                                    <!-- Status Badge -->
                                    <div
                                        class="flex items-center justify-between mb-3"
                                    >
                                        <div class="flex items-center gap-2">
                                            <div
                                                :class="[
                                                    'w-3 h-3 rounded-full',
                                                    mergedTable.status ===
                                                        'occupied' &&
                                                        'bg-red-500',
                                                    mergedTable.status ===
                                                        'reserved' &&
                                                        'bg-yellow-500',
                                                    mergedTable.status ===
                                                        'available' &&
                                                        'bg-green-500',
                                                    mergedTable.status ===
                                                        'merged' &&
                                                        'bg-purple-500',
                                                ]"
                                            ></div>
                                            <span
                                                class="text-xs font-medium text-gray-600 capitalize"
                                            >
                                                {{ mergedTable.status }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Merged Indicator -->
                                    <div
                                        class="flex flex-col items-center text-center mb-3"
                                    >
                                        <h4 class="text-gray-900 text-xs">
                                            {{ mergedTable.name }}
                                        </h4>
                                        <h3
                                            class="w-fit bg-purple-100 text-purple-800 text-sm px-2 py-1 rounded-full font-medium flex items-center gap-1 mb-2"
                                        >
                                            <i class="pi pi-link text-lg"></i>
                                            Merged to {{ table.name }}
                                        </h3>
                                    </div>

                                    <!-- Table Info -->
                                    <div
                                        class="text-center text-sm text-gray-600 space-y-1"
                                    >
                                        <p
                                            v-if="mergedTable.current_order"
                                            class="text-blue-600 font-medium"
                                        >
                                            Order #{{
                                                mergedTable.current_order.id
                                            }}
                                        </p>
                                        <div
                                            v-if="
                                                mergedTable.status ===
                                                    'occupied' &&
                                                mergedTable.number_of_pax
                                            "
                                            class="space-y-0.5"
                                        >
                                            <p
                                                class="text-gray-700 font-medium"
                                            >
                                                {{ mergedTable.number_of_pax }}
                                                pax
                                            </p>
                                            <p
                                                v-if="mergedTable.time_in"
                                                class="text-xs text-gray-500"
                                            >
                                                {{
                                                    formatTimeOccupied(
                                                        mergedTable.time_in
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nested Merged Tables (recursive) -->
                                <template
                                    v-if="
                                        mergedTable.mergedTables &&
                                        mergedTable.mergedTables.length > 0
                                    "
                                    v-for="nestedMergedTable in mergedTable.mergedTables"
                                    :key="nestedMergedTable.id"
                                >
                                    <div
                                        @click="
                                            openTableModal(nestedMergedTable)
                                        "
                                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all cursor-pointer hover:scale-105 relative opacity-50"
                                        :class="
                                            getTableStatusClasses(
                                                nestedMergedTable.status
                                            )
                                        "
                                    >
                                        <!-- Status Badge -->
                                        <div
                                            class="flex items-center justify-between mb-3"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <div
                                                    :class="[
                                                        'w-3 h-3 rounded-full',
                                                        nestedMergedTable.status ===
                                                            'occupied' &&
                                                            'bg-red-500',
                                                        nestedMergedTable.status ===
                                                            'reserved' &&
                                                            'bg-yellow-500',
                                                        nestedMergedTable.status ===
                                                            'vacant' &&
                                                            'bg-green-500',
                                                        nestedMergedTable.status ===
                                                            'merged' &&
                                                            'bg-purple-500',
                                                    ]"
                                                ></div>
                                                <span
                                                    class="text-xs font-medium text-gray-600 capitalize"
                                                >
                                                    {{
                                                        nestedMergedTable.status
                                                    }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Nested Merged Indicator -->
                                        <div
                                            class="flex flex-col items-center text-center mb-3"
                                        >
                                            <h4 class="text-gray-900 text-xs">
                                                {{ nestedMergedTable.name }}
                                            </h4>
                                            <h3
                                                class="w-fit bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium flex items-center gap-1 mb-2"
                                            >
                                                <i
                                                    class="pi pi-link text-sm"
                                                ></i>
                                                Merged to
                                                {{ mergedTable.name }}
                                            </h3>
                                        </div>

                                        <!-- Table Info -->
                                        <div
                                            class="text-center text-xs text-gray-600 space-y-1"
                                        >
                                            <p
                                                v-if="
                                                    nestedMergedTable.current_order
                                                "
                                                class="text-blue-600 font-medium"
                                            >
                                                Order #{{
                                                    nestedMergedTable
                                                        .current_order.id
                                                }}
                                            </p>
                                            <div
                                                v-if="
                                                    nestedMergedTable.status ===
                                                        'occupied' &&
                                                    nestedMergedTable.number_of_pax
                                                "
                                                class="space-y-0.5"
                                            >
                                                <p
                                                    class="text-gray-700 font-medium"
                                                >
                                                    {{
                                                        nestedMergedTable.number_of_pax
                                                    }}
                                                    pax
                                                </p>
                                                <p
                                                    v-if="
                                                        nestedMergedTable.time_in
                                                    "
                                                    class="text-xs text-gray-500"
                                                >
                                                    {{
                                                        formatTimeOccupied(
                                                            nestedMergedTable.time_in
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </template>
                    </template>
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredTables.length === 0"
                    class="text-center py-12"
                >
                    <i class="pi pi-table text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        No tables found
                    </h3>
                    <p class="text-gray-600">
                        {{
                            selectedLocationId
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
                @claimOrder="handleClaimOrder"
                @transferNumber="handleTransferNumber"
                @viewOrder="handleViewOrder"
                @mergeTable="handleMergeTable"
                @reserveTable="handleReserveTable"
                @unmergeTable="handleUnmergeTable"
                @refundOrder="handleRefundOrder"
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

            <!-- Transfer Table Modal -->
            <TransferTableModal
                :visible="showTransferModal"
                :source-table="selectedTable"
                :available-targets="availableTransferTargets"
                :selected-target="selectedTransferTarget"
                @update:visible="closeTransferModal"
                @selectTarget="selectTransferTarget"
                @confirmTransfer="confirmTransfer"
            />
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import axios from "axios";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import TableActionModal from "./Partials/TableActionModal.vue";
import ViewOrdersModal from "./Partials/ViewOrdersModal.vue";
import MergeTableModal from "./Partials/MergeTableModal.vue";
import ReserveTableModal from "./Partials/ReserveTableModal.vue";
import TransferTableModal from "./Partials/TransferTableModal.vue";
import PageProps from "@/Types/PageProps";
import { formatTimeOccupied } from "@/Utils/FormatTime";
import { useCashier } from "@/composables/useCashier";

// Props
const props = defineProps<{
    tableRooms: any[];
    currentUser: any;
}>();

// Reactive State
const page = usePage<PageProps>();
const locations = computed(() => {
    // Extract locations from tableRooms array
    return props.tableRooms || [];
});
const selectedLocationId = ref<number | null>(null);
const showTableModal = ref(false);
const selectedTable = ref<any>(null);
const showMergeModal = ref(false);
const selectedMergeTarget = ref<any>(null);
const tableToMerge = ref<any>(null);
const showOrdersModal = ref(false);
const tableOrders = ref<any[]>([]);
const showReserveModal = ref(false);
const showTransferModal = ref(false);
const selectedTransferTarget = ref<any>(null);

// Toast
const toast = useToast();

// Initialize cashier state
const { selectedCart, updateCart, setSelectedCart } = useCashier();

// Toggle sidebar (emit to parent layout)
const toggleSidebar = () => {
    window.dispatchEvent(new CustomEvent("toggle-sidebar"));
};

// Computeds
const currentLocation = computed(() => {
    if (selectedLocationId.value === null) return null;
    return locations.value.find((loc) => loc.id === selectedLocationId.value);
});

const filteredTables = computed(() => currentLocation.value?.tableRooms || []);

const availableMergeTargets = computed(() =>
    filteredTables.value.filter((t) => t.status === "occupied")
);

const availableTransferTargets = computed(() => {
    if (!selectedTable.value) return [];

    // Show only vacant tables from the same location as the source table
    // Filter out the source table itself
    return filteredTables.value.filter(
        (t) => t.status === "vacant" && t.id !== selectedTable.value?.id
    );
});

const getTableStatusClasses = (status: string) => {
    const baseClasses = "hover:border-gray-300";
    switch (status) {
        case "occupied":
            return `${baseClasses} border-red-200 bg-red-50`;
        case "reserved":
            return `${baseClasses} border-yellow-200 bg-yellow-50`;
        case "available":
            return `${baseClasses} border-green-200 bg-green-50`;
        case "merged":
            return `${baseClasses} border-purple-200 bg-purple-50`;
        default:
            return baseClasses;
    }
};

const getRootTable = (table: any): any => {
    // If table is merged to another, find the root parent
    if (table.merge_to) {
        // Look for the parent in all locations and their tables
        for (const location of locations.value) {
            // Check direct tables
            const directParent = location.tableRooms?.find(
                (t) => t.id === table.merge_to
            );
            if (directParent) {
                return getRootTable(directParent);
            }

            // Check merged tables recursively
            const searchMergedTables = (tables: any[]): any => {
                for (const t of tables || []) {
                    if (t.id === table.merge_to) {
                        return getRootTable(t);
                    }
                    const found = searchMergedTables(t.mergedTables);
                    if (found) return found;
                }
                return null;
            };

            const foundInMerged = searchMergedTables(location.tableRooms);
            if (foundInMerged) return foundInMerged;
        }
    }
    return table;
};

const openTableModal = (table: any) => {
    // If the clicked table is merged, show the root parent table instead
    const rootTable = getRootTable(table);
    selectedTable.value = rootTable;
    showTableModal.value = true;
};

const closeTableModal = () => {
    showTableModal.value = false;
    selectedTable.value = null;
};

const goBackToCashier = () => {
    router.visit(route("resto.index"));
};

const handleTakeOrder = (data: any) => {
    alert("emit");
    console.log(data);
    selectedTable.value = data.table;
    console.log("select teb", selectedTable.value);
    if (selectedTable.value.status === "vacant") {
        // Check if we have a selected cart without a table assigned
        if (selectedCart.value && !selectedCart.value.table_id) {
            // Update the existing cart with the table information
            router.put(
                route("resto.cart.update", selectedCart.value.id),
                {
                    table_id: selectedTable.value.id,
                    pax: data.pax,
                    guest_name: data.guest_name,
                    order_type: "dine-in",
                },
                {
                    onSuccess: (response) => {
                        // Update the cart in local storage
                        updateCart(selectedCart.value.id, {
                            table_id: selectedTable.value.id,
                            order_type: "dine-in",
                        });

                        // Navigate to cashier with the assigned table
                        router.visit(
                            route("resto.index", {
                                tableId: selectedTable.value.id,
                                locationType: "dine-in",
                            })
                        );
                    },
                    onError: () => {
                        toast.add({
                            severity: "error",
                            summary: "Error",
                            detail: "Failed to assign table to order",
                            life: 3000,
                        });
                    },
                }
            );
        } else {
            // Create a new order as before
            router.post(
                route("resto.cart.create-order"),
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
                        // Refresh tables to show updated status
                        setTimeout(() => {
                            router.reload({ only: ["tables"] });
                        }, 100);
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
        }
    } else {
        // Table is occupied - check if we have a current cart to merge
        if (
            selectedCart.value &&
            selectedCart.value.cart_items &&
            selectedCart.value.cart_items.length > 0
        ) {
            // We have items in current cart - need to merge with existing table cart
            const targetTableId = selectedTable.value.merged_to
                ? selectedTable.value.merge_to
                : selectedTable.value.id;

            // Call merge cart API
            router.post(
                route("resto.cart.merge"),
                {
                    source_cart_id: selectedCart.value.id,
                    target_table_id: targetTableId,
                },
                {
                    onSuccess: (response) => {
                        toast.add({
                            severity: "success",
                            summary: "Cart Merged",
                            detail: "Your current cart items have been merged with the table's existing order.",
                            life: 3000,
                        });
                        closeTableModal();

                        // Clear the current cart from localStorage since it's merged
                        setSelectedCart(null);

                        // Navigate to cashier with the target table
                        router.visit(
                            route("resto.index", {
                                tableId: targetTableId,
                            })
                        );
                    },
                    onError: () => {
                        toast.add({
                            severity: "error",
                            summary: "Merge Failed",
                            detail: "Failed to merge cart items with table order.",
                            life: 3000,
                        });
                    },
                }
            );
        } else {
            // No current cart items, just navigate to the occupied table
            router.visit(
                route("resto.index", {
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

const handleClaimOrder = () => {
    if (!selectedTable.value) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "No table selected",
            life: 3000,
        });
        return;
    }

    router.post(
        route("resto.cart.claim-order", {
            tableId: selectedTable.value.id,
        }),
        {},
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Order claimed successfully",
                    life: 3000,
                });
                closeTableModal();
                // Refresh tables to show updated status
                setTimeout(() => {
                    router.reload({ only: ["tables"] });
                }, 100);
            },
            onError: (errors) => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errors?.message || "Failed to claim order",
                    life: 3000,
                });
            },
        }
    );
};

const handleTransferNumber = () => {
    selectedTransferTarget.value = null;
    showTransferModal.value = true;
};

const closeTransferModal = () => {
    showTransferModal.value = false;
    selectedTransferTarget.value = null;
};

const selectTransferTarget = (table: any) => {
    selectedTransferTarget.value = table;
};

const confirmTransfer = () => {
    if (!selectedTable.value || !selectedTransferTarget.value) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Please select a table to transfer to",
            life: 3000,
        });
        return;
    }

    router.post(
        route("resto.order.transfer", {
            tableId: selectedTable.value.id,
        }),
        {
            target_table_id: selectedTransferTarget.value.id,
        },
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: `Order transferred from ${selectedTable.value.name} to ${selectedTransferTarget.value.name}`,
                    life: 3000,
                });
                closeTransferModal();
                closeTableModal();
                // Refresh tables to show updated status
                setTimeout(() => {
                    router.reload({ only: ["tables"] });
                }, 100);
            },
            onError: (errors) => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errors?.message || "Failed to transfer order",
                    life: 3000,
                });
            },
        }
    );
};

const handleViewOrder = () => {
    router.visit(
        route("resto.index", {
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

const handleRefundOrder = async () => {
    selectedLocation;
    try {
        await axios.post(
            route(
                "transactions.api.orders.refund",
                selectedTable.value.current_order.id
            ),
            {
                supervisor_name: props.currentUser?.name || "Supervisor",
                notes: "Refund requested from table action",
            }
        );
        toast.add({
            severity: "success",
            summary: "Success",
            detail: "Order refunded successfully",
            life: 3000,
        });
        closeTableModal();
        // Refresh tables
        setTimeout(() => {
            router.reload({ only: ["tables"] });
        }, 100);
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to refund order",
            life: 3000,
        });
    }
};

const closeOrdersModal = () => {
    showOrdersModal.value = false;
    tableOrders.value = [];
};

const viewOrderDetails = (order: any) => {
    router.visit(`/resto?tableId=${order.table_room.id}`);
};

onMounted(() => {
    // Auto-select first location if none selected
    if (!selectedLocationId.value && locations.value.length > 0) {
        selectedLocationId.value = locations.value[0].id;
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
