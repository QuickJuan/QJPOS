<template>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            <MapPinIcon class="w-4 h-4 inline mr-1" />
            Select Table
        </label>
        <Dropdown
            v-model="selectedTable"
            :options="tableOptions"
            option-label="display_name"
            option-value="id"
            placeholder="No Table - Click to select"
            class="w-full"
            :loading="loading"
            @change="handleTableChange"
        />
        <div v-if="currentTableInfo" class="mt-2 text-xs text-gray-500">
            <span class="inline-flex items-center">
                <span
                    class="w-2 h-2 rounded-full mr-1"
                    :class="
                        currentTableInfo.status === 'occupied'
                            ? 'bg-red-500'
                            : 'bg-green-500'
                    "
                ></span>
                {{ currentTableInfo.capacity }} guests •
                {{ currentTableInfo.status }}
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import Dropdown from "primevue/dropdown";
import { MapPinIcon } from "@heroicons/vue/24/outline";
import axios from "axios";
import { useToast } from "primevue/usetoast";

interface Table {
    id: number | null;
    name: string;
    capacity: number;
    status: string;
    cart_id?: number;
    has_orders?: boolean;
}

interface Props {
    currentTableId?: number | null;
    branchId: number;
    selectedCartId?: number | null;
}

interface Emits {
    (
        e: "table-changed",
        tableData: {
            table: Table | null;
            cart: any | null;
            shouldMerge: boolean;
        }
    ): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const toast = useToast();

// Reactive data
const selectedTable = ref<number | null>(props.currentTableId || null);
const tables = ref<Table[]>([]);
const loading = ref(false);
const isInitializing = ref(false);

// Watch for prop changes to update selected table (but don't override localStorage)
watch(
    () => props.currentTableId,
    (newId) => {
        // Only update from props if we don't have a value in localStorage
        const cashierStateKey = "quickjuan_cashier_state";
        const existingState = localStorage.getItem(cashierStateKey);
        let hasStoredTable = false;

        if (existingState) {
            try {
                const cashierState = JSON.parse(existingState);
                hasStoredTable =
                    cashierState.tableId &&
                    typeof cashierState.tableId === "number";
            } catch (error) {
                console.error("Error parsing localStorage:", error);
            }
        }

        // Only update from props if no localStorage value exists
        if (!hasStoredTable) {
            selectedTable.value = newId;
            console.log(
                "Updated selectedTable from props (no localStorage):",
                newId
            );
        } else {
            console.log("Ignoring props update, localStorage has table");
        }
    },
    { immediate: true }
);

// Computed
const tableOptions = computed(() => [
    {
        id: null,
        display_name: "No Table - Walk-in Customer",
        name: "Walk-in",
        capacity: 1,
        status: "available",
    },
    ...tables.value.map((table) => ({
        ...table,
        display_name: `${table.name} (${
            table.capacity
        } guests) - ${getStatusLabel(table.status)}`,
    })),
]);

const currentTableInfo = computed(() => {
    if (selectedTable.value === null) {
        return { name: "Walk-in", capacity: 1, status: "available" };
    }
    return tables.value.find((t) => t.id === selectedTable.value) || null;
});

// Methods
const getStatusLabel = (status: string) => {
    const labels = {
        available: "Available",
        occupied: "Occupied",
        reserved: "Reserved",
        maintenance: "Maintenance",
    };
    return labels[status] || status;
};

const fetchTables = async () => {
    try {
        loading.value = true;
        const response = await axios.get(
            `/api/branches/${props.branchId}/tables`
        );
        tables.value = response.data;
    } catch (error) {
        console.error("Failed to fetch tables:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to load tables",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const fetchTableWithCart = async (tableId: number | null) => {
    try {
        loading.value = true;

        if (tableId === null) {
            // Handle walk-in customer (no table)
            emit("table-changed", {
                table: null,
                cart: null,
                shouldMerge: false,
            });
            return;
        }

        const response = await axios.get(`/api/tables/${tableId}/with-cart`);
        const tableData = response.data;

        console.log("response ", tableData);

        // Check if table has existing cart items that need to be merged
        const shouldMerge =
            tableData.cart &&
            tableData.cart.items &&
            tableData.cart.items.length > 0;

        emit("table-changed", {
            table: tableData.table,
            cart: tableData.cart,
            shouldMerge: shouldMerge,
        });

        // Show toast message only if not during initialization
        // if (!isInitializing.value) {
        //     setTimeout(() => {
        //         const statusMessage = shouldMerge
        //             ? `Selected ${tableData.table.name} - Will merge with existing ${tableData.cart.items.length} items`
        //             : `Selected ${tableData.table.name}`;

        //         toast.add({
        //             severity: "success",
        //             summary: "Table Selected",
        //             detail: statusMessage,
        //             life: 3000,
        //         });
        //     }, 100);
        // }
    } catch (error) {
        console.error("Failed to fetch table with cart:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to load table data",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

// Update cashier state in localStorage
const updateCashierStateInLocalStorage = (tableId: number | null) => {
    try {
        const cashierStateKey = "quickjuan_cashier_state";
        const existingState = localStorage.getItem(cashierStateKey);
        let cashierState = existingState ? JSON.parse(existingState) : {};

        // Update the tableId in the cashier state
        cashierState.tableId = tableId;
        cashierState.lastUpdated = new Date().toISOString();

        // Save back to localStorage
        localStorage.setItem(cashierStateKey, JSON.stringify(cashierState));

        console.log(
            "Updated cashier state with tableId:",
            tableId,
            "- Stack:",
            new Error().stack?.split("\n")[2]?.trim()
        );
    } catch (error) {
        console.error("Failed to update cashier state in localStorage:", error);
    }
};

const handleTableChange = async (event: any) => {
    const newTableId = event.value;

    // If selecting walk-in, no confirmation needed
    if (newTableId === null) {
        selectedTable.value = newTableId;
        // Update localStorage for walk-in (null table)
        updateCashierStateInLocalStorage(newTableId);
        fetchTableWithCart(newTableId);
        return;
    }

    // Check if the selected table is occupied
    const selectedTableData = tables.value.find((t) => t.id === newTableId);

    if (selectedTableData && selectedTableData.status === "occupied") {
        // Show confirmation dialog for occupied table
        const confirmed = confirm(
            `Table ${selectedTableData.name} is currently occupied with existing orders. ` +
                `Do you want to merge new items with the existing cart items? ` +
                `Click OK to merge, or Cancel to select a different table.`
        );

        if (!confirmed) {
            // Reset to previous selection if user cancels
            selectedTable.value = props.currentTableId || null;
            toast.add({
                severity: "info",
                summary: "Action Cancelled",
                detail: "Please select a vacant table or continue with walk-in customer",
                life: 3000,
            });
            return;
        }
    } else {
        // For non-occupied tables, no confirmation needed
        alert("Table selected: " + selectedTableData?.name);
    }

    selectedTable.value = newTableId;

    // Update localStorage with the selected table ID
    updateCashierStateInLocalStorage(newTableId);

    fetchTableWithCart(newTableId);
};

// This watch is now handled at the top of the reactive data section

// Find table by cart ID
const findTableByCartId = async (cartId: number): Promise<number | null> => {
    try {
        // First ensure tables are loaded
        if (tables.value.length === 0) {
            await fetchTables();
        }

        // Find table that has the specified cart ID
        const tableWithCart = tables.value.find(
            (table) => table.cart_id === cartId
        );

        if (tableWithCart) {
            console.log(
                `Found table ${tableWithCart.name} for cart ID ${cartId}`
            );
            return tableWithCart.id;
        }

        console.log(`No table found for cart ID ${cartId}`);
        return null;
    } catch (error) {
        console.error("Error finding table by cart ID:", error);
        return null;
    }
};

// Initialize from selectedCartId if provided
const initializeFromSelectedCart = async () => {
    if (props.selectedCartId) {
        console.log("Initializing from selectedCartId:", props.selectedCartId);
        const tableId = await findTableByCartId(props.selectedCartId);

        if (tableId) {
            selectedTable.value = tableId;
            // Update localStorage with the found table
            updateCashierStateInLocalStorage(tableId);
            // Fetch table data to emit the change event
            await fetchTableWithCart(tableId);
        }
    }
};

// Read table from localStorage and update state
const initializeFromLocalStorage = async () => {
    try {
        const cashierStateKey = "quickjuan_cashier_state";
        const existingState = localStorage.getItem(cashierStateKey);

        if (existingState) {
            const cashierState = JSON.parse(existingState);
            const tableId = cashierState.tableId;

            if (tableId && typeof tableId === "number") {
                // Update selected table
                selectedTable.value = tableId;

                console.log("Initialized table from localStorage:", tableId);

                // Silently fetch table data without toast messages
                try {
                    const response = await axios.get(
                        `/api/tables/${tableId}/with-cart`
                    );
                    const tableData = response.data;

                    // Check if table has existing cart items that need to be merged
                    const shouldMerge =
                        tableData.cart &&
                        tableData.cart.items &&
                        tableData.cart.items.length > 0;

                    emit("table-changed", {
                        table: tableData.table,
                        cart: tableData.cart,
                        shouldMerge: shouldMerge,
                    });

                    console.log(
                        "Silently initialized table data for:",
                        tableId
                    );
                } catch (error) {
                    console.error(
                        "Failed to fetch table data during initialization:",
                        error
                    );
                    // Only clear localStorage if it's a 404 error (table doesn't exist)
                    // Don't clear for network errors or temporary issues
                    if (error.response && error.response.status === 404) {
                        console.log(
                            "Table not found, clearing from localStorage"
                        );
                        updateCashierStateInLocalStorage(null);
                        selectedTable.value = null;
                    } else {
                        console.log(
                            "Temporary error, keeping table in localStorage"
                        );
                    }
                }
            }
        }
    } catch (error) {
        console.error("Failed to initialize from localStorage:", error);
    }
};

// Lifecycle
onMounted(() => {
    fetchTables().then(async () => {
        // Initialize table selection from selectedCartId first, then localStorage
        setTimeout(async () => {
            isInitializing.value = true;

            // First try to initialize from selectedCartId if provided
            if (props.selectedCartId) {
                await initializeFromSelectedCart();
            } else {
                // Fallback to localStorage initialization
                await initializeFromLocalStorage();
            }

            // Reset flag after a short delay to allow normal toast messages again
            setTimeout(() => {
                isInitializing.value = false;
            }, 1000);
        }, 500);
    });
});
</script>
