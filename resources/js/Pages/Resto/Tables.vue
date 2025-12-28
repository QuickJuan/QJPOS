<template>
    <CashieringLayout :current-user="props.currentUser">
        <!-- Fixed Header for Mobile/Tablet -->
        <div
            class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-white border-b border-neutral-200 shadow-md"
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
                <h1 class="text-base font-bold text-neutral-900">Tables</h1>

                <!-- Right: Back to Cashier -->
                <button
                    @click="goBackToCashier"
                    class="bg-neutral-600 text-white rounded-lg p-2.5 shadow hover:bg-neutral-700 transition-all"
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
            class="flex flex-col h-screen bg-neutral-100 overflow-y-hidden pt-[56px] lg:pt-0"
        >
            <!-- Toolbar (sticky on desktop, part of content flow on mobile) -->
            <div class="p-3 bg-white shadow lg:sticky lg:top-0">
                <!-- Location Tabs grouped by type -->
                <div class="space-y-5">
                    <div
                        v-for="group in groupedLocations"
                        :key="group.type"
                        class="space-y-2"
                    >
                        <div
                            class="flex items-center gap-3 text-[11px] font-semibold uppercase tracking-wide text-neutral-500"
                        >
                            <span>{{ group.label }}</span>
                            <span class="h-px flex-1 bg-neutral-200"></span>
                            <span class="text-neutral-400">
                                {{ group.locations.length }} area{{
                                    group.locations.length === 1 ? "" : "s"
                                }}
                            </span>
                        </div>

                        <div
                            class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
                        >
                            <button
                                v-for="loc in group.locations"
                                :key="loc.id"
                                @click="selectedLocationId = loc.id"
                                :class="[
                                    'h-14 cursor-pointer px-2 py-1 rounded text-[11px] sm:text-xs font-semibold border flex flex-col sm:flex-row items-center justify-center gap-1 transition-colors',
                                    selectedLocationId === loc.id
                                        ? 'bg-primary text-white border-primary shadow'
                                        : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200 border-neutral-300',
                                ]"
                            >
                                <span class="truncate">{{ loc.name }}</span>
                                <span class="text-sm opacity-80">
                                    ({{ loc?.tableRoomCount || 0 }})
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Grid -->
            <div class="flex-1 overflow-y-auto p-6">
                <div
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4"
                >
                    <!-- Parent Tables -->
                    <template
                        v-for="(table, index) in filteredTables"
                        :key="table.id"
                    >
                        <!-- Parent Table -->
                        <TableCard
                            :table="table"
                            :general-settings="page.props.generalSettings"
                            @click="openTableModal(table)"
                        />

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
                                <TableCard
                                    :table="mergedTable"
                                    :general-settings="
                                        page.props.generalSettings
                                    "
                                    :is-merged="true"
                                    :merged-to-name="table.name"
                                    @click="openTableModal(mergedTable)"
                                />
                                <div
                                    v-if="false"
                                    @click="openTableModal(mergedTable)"
                                    class="bg-white rounded-lg shadow-sm border border-neutral-200 p-4 hover:shadow-md transition-all cursor-pointer hover:scale-105 relative opacity-75"
                                    :class="
                                        getTableStatusClasses(
                                            mergedTable.status
                                        )
                                    "
                                >
                                    <!-- Name with Merged Indicator -->
                                    <div class="mb-3">
                                        <h3
                                            class="font-semibold text-neutral-900 mb-1"
                                        >
                                            {{ mergedTable.name }}
                                        </h3>
                                        <p
                                            class="text-xs text-neutral-600 bg-secondary-50 px-2 py-1 rounded inline-block"
                                        >
                                            <i class="pi pi-link text-xs"></i>
                                            Merged to {{ table.name }}
                                        </p>
                                    </div>

                                    <!-- Status and Pax -->
                                    <div
                                        class="mb-3 flex items-center justify-between"
                                    >
                                        <div>
                                            <div
                                                class="text-xs text-neutral-500 mb-1"
                                            >
                                                Status
                                            </div>
                                            <span
                                                :class="[
                                                    'px-2 py-1 text-xs font-medium rounded-full capitalize',
                                                    mergedTable.status ===
                                                    'occupied'
                                                        ? 'bg-red-100 text-red-800'
                                                        : mergedTable.status ===
                                                          'reserved'
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : mergedTable.status ===
                                                          'available'
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-gray-100 text-gray-800',
                                                ]"
                                            >
                                                {{ mergedTable.status }}
                                            </span>
                                        </div>
                                        <div class="text-right">
                                            <div
                                                class="text-xs text-neutral-500 mb-1"
                                            >
                                                Pax
                                            </div>
                                            <div
                                                class="text-sm font-semibold text-neutral-900"
                                            >
                                                {{ mergedTable.numberOfPax }}
                                            </div>
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
                                    <TableCard
                                        :table="nestedMergedTable"
                                        is-merged
                                        :merged-to-name="mergedTable.name"
                                        @click="
                                            openTableModal(nestedMergedTable)
                                        "
                                    />
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
                    <i class="pi pi-table text-4xl text-neutral-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-neutral-900 mb-2">
                        No tables found
                    </h3>
                    <p class="text-neutral-600">
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
                @claimOrder="handleClaimOrder"
                @transferNumber="handleTransferNumber"
                @transferGuest="handleTransferGuest"
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

            <!-- Transfer Guess Modal -->
            <TransferGuestModal
                :key="showTransferGuestModal"
                :visible="showTransferGuestModal"
                :source-table="transferSourceTable"
                :available-targets="availableTransferTargets"
                :selected-target="selectedTransferGuestTarget"
                @update:visible="closeTransferGuestModal"
                @selectTarget="selectTransferGuestTarget"
                @confirmTransfer="confirmTransferGuest"
            />
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import axios from "axios";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import TableCard from "@/Components/Resto/TableCard.vue";
import TableActionModal from "./Partials/TableActionModal.vue";
import ViewOrdersModal from "./Partials/ViewOrdersModal.vue";
import MergeTableModal from "./Partials/MergeTableModal.vue";
import ReserveTableModal from "./Partials/ReserveTableModal.vue";
import TransferTableModal from "./Partials/TransferTableModal.vue";
import TransferGuestModal from "./Partials/TransferGuestModal.vue";
import PageProps from "@/Types/PageProps";
import { formatTimeOccupied } from "@/Utils/FormatTime";
import { useCashier } from "@/composables/useCashier";

// Props
const props = defineProps<{
    tableRooms: any[];
    // currentUser: any;
}>();

// Reactive State
const page = usePage<PageProps>();
const normalizeLocationType = (type: unknown): string => {
    if (!type && type !== 0) {
        return "other";
    }
    return String(type).toLowerCase().trim();
};

const canonicalizeLocationType = (rawType: unknown): string => {
    const type = normalizeLocationType(rawType);

    if (["dine-in", "dining", "restaurant"].includes(type)) {
        return "dine-in";
    }

    if (
        [
            "takeout",
            "take-out",
            "take away",
            "take-away",
            "delivery",
            "pickup",
            "pick-up",
            "take away",
        ].includes(type)
    ) {
        return "takeout";
    }

    return "other";
};

const normalizeTableRooms = (raw: any[] = []) => {
    return raw.map((location) => {
        const normalizedType = canonicalizeLocationType(
            location.location_type ?? location.locationType ?? location.type
        );

        return {
            ...location,
            location_type: normalizedType,
            name: location.name ?? location.title ?? "Unnamed",
            tableRooms: Array.isArray(location.tableRooms)
                ? location.tableRooms
                : location.table_rooms ?? [],
        };
    });
};

const locations = computed(() => {
    // Extract locations from tableRooms array, ensuring structure compatibility
    return normalizeTableRooms(props.tableRooms || []);
});

const LOCATION_TYPE_ORDER: Record<string, number> = {
    "dine-in": 0,
    takeout: 1,
    other: 2,
};

const getLocationTypeLabel = (type: string): string => {
    switch (type) {
        case "dine-in":
            return "Dine-In Areas";
        case "takeout":
            return "Takeout & Delivery Areas";
        default:
            return "Other Areas";
    }
};

const groupedLocations = computed(() => {
    const groupMap = new Map<
        string,
        {
            type: string;
            label: string;
            order: number;
            locations: any[];
        }
    >();

    for (const location of locations.value) {
        const type = location.location_type ?? "other";
        if (!groupMap.has(type)) {
            groupMap.set(type, {
                type,
                label: getLocationTypeLabel(type),
                order: LOCATION_TYPE_ORDER[type] ?? 99,
                locations: [],
            });
        }

        groupMap.get(type)?.locations.push(location);
    }

    return Array.from(groupMap.values())
        .map((group) => ({
            ...group,
            locations: [...group.locations].sort((a, b) =>
                String(a.name || "").localeCompare(String(b.name || ""))
            ),
        }))
        .sort((a, b) => {
            if (a.order !== b.order) {
                return a.order - b.order;
            }
            return a.label.localeCompare(b.label);
        });
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
const showTransferGuestModal = ref(false);
const selectedTransferTarget = ref<any>(null);
const selectedTransferGuestTarget = ref<any>(null);
const transferSourceTable = ref<any>(null);

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
    if (!transferSourceTable.value) return [];

    // Show only available tables from the same location as the source table
    const targets = filteredTables.value.filter(
        (t) =>
            t.status === "available" && t.id !== transferSourceTable.value?.id
    );

    return [...targets];
});

const getTableStatusClasses = (status: string) => {
    const baseClasses = "hover:border-neutral-300";
    switch (status) {
        case "occupied":
            return `${baseClasses} border-error-200 bg-error-50`;
        case "reserved":
            return `${baseClasses} border-warning-200 bg-warning-50`;
        case "available":
            return `${baseClasses} border-success-200 bg-success-50`;
        case "merged":
            return `${baseClasses} border-secondary-200 bg-secondary-50`;
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

const findLocationForTable = (tableId: number): any | null => {
    for (const location of locations.value) {
        const stack = [...(location.tableRooms || [])];

        while (stack.length) {
            const candidate = stack.pop();

            if (!candidate) {
                continue;
            }

            if (candidate.id === tableId) {
                return location;
            }

            if (candidate.mergedTables && candidate.mergedTables.length) {
                stack.push(...candidate.mergedTables);
            }
        }
    }

    return null;
};

const openTableModal = (table: any) => {
    // If the clicked table is merged, show the root parent table instead
    const rootTable = getRootTable(table);
    const location = findLocationForTable(rootTable.id);
    const resolvedLocationName =
        location?.name ??
        rootTable.tableRoomLocation?.name ??
        rootTable.tableRoomLocation?.description ??
        "Unassigned";

    selectedTable.value = {
        ...rootTable,
        tableRoomLocation:
            rootTable.tableRoomLocation ??
            (location
                ? {
                      id: location.id,
                      name: location.name,
                      location_type: location.location_type ?? null,
                  }
                : null),
        locationName: resolvedLocationName,
    };
    showTableModal.value = true;
};

const closeTableModal = () => {
    showTableModal.value = false;
    selectedTable.value = null;
};

const goBackToCashier = () => {
    router.visit(route("resto.index"));
};

const handleMergeTable = (table: any) => {
    tableToMerge.value = table;
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

const handleTransferGuest = () => {
    transferSourceTable.value = selectedTable.value;
    selectedTransferGuestTarget.value = null;
    showTransferGuestModal.value = true;
};

const closeTransferGuestModal = () => {
    showTransferGuestModal.value = false;
    selectedTransferGuestTarget.value = null;
    transferSourceTable.value = null;
};

const selectTransferTarget = (table: any) => {
    selectedTransferTarget.value = table;
};

const selectTransferGuestTarget = (table: any) => {
    selectedTransferGuestTarget.value = table;
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

const confirmTransferGuest = () => {
    if (!transferSourceTable.value || !selectedTransferGuestTarget.value) {
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
            tableId: transferSourceTable.value.id,
        }),
        {
            target_table_id: selectedTransferGuestTarget.value.id,
        },
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: `Order transferred from ${transferSourceTable.value.name} to ${selectedTransferGuestTarget.value.name}`,
                    life: 3000,
                });
                closeTransferGuestModal();
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

// Watch for changes in selectedLocationId and update URL
watch(
    () => selectedLocationId.value,
    (newLocationId) => {
        if (newLocationId) {
            // Update URL without full page reload
            const url = new URL(window.location);
            url.searchParams.set("locationId", newLocationId.toString());
            window.history.replaceState({}, "", url);
        }
    }
);

onMounted(() => {
    // Get locationId from URL query parameters
    const urlParams = new URLSearchParams(window.location.search);
    const locationIdFromUrl = urlParams.get("locationId");

    if (locationIdFromUrl) {
        // Try to parse as number and check if it exists in locations
        const locationId = parseInt(locationIdFromUrl, 10);
        if (locations.value.some((loc) => loc.id === locationId)) {
            selectedLocationId.value = locationId;
        } else {
            // If location doesn't exist, use first location
            if (locations.value.length > 0) {
                selectedLocationId.value = locations.value[0].id;
            }
        }
    } else if (locations.value.length > 0) {
        // Auto-select first location if none selected
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
