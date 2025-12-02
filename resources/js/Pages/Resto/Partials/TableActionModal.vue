<template>
    <Dialog
        :visible="show"
        modal
        :header="`${table?.name || ''}`"
        :style="{ width: '400px' }"
        :closable="true"
        @hide="handleClose"
        @update:visible="handleClose"
    >
        <div class="space-y-4">
            <!-- Merged Table Info Badge -->
            <div
                v-if="hasMergedTables"
                class="bg-purple-50 border border-purple-200 p-3 rounded-lg"
            >
                <p class="text-sm font-medium text-purple-900">
                    <i class="pi pi-info-circle mr-2"></i>
                    This table has {{ allNestedMergedTables.length }} merged
                    {{
                        allNestedMergedTables.length === 1 ? "table" : "tables"
                    }}
                </p>

                <div class="mt-2 space-y-1">
                    <p
                        v-for="mergedTable in allNestedMergedTables"
                        :key="mergedTable.id"
                        class="text-xs text-purple-700"
                    >
                        <span class="inline-block mr-2">•</span>
                        <span class="font-medium">{{ mergedTable.name }}</span>
                        <span
                            v-if="mergedTable.merge_to"
                            class="text-purple-600 ml-1"
                        >
                            (merged to another table)
                        </span>
                    </p>
                </div>
            </div>

            <!-- Table Info -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">
                        Status:
                    </span>
                    <span
                        :class="[
                            'px-2 py-1 text-xs font-medium rounded-full capitalize',
                            table?.status === 'available' &&
                                'bg-green-100 text-green-800',
                            table?.status === 'occupied' &&
                                'bg-red-100 text-red-800',
                            table?.status === 'reserved' &&
                                'bg-yellow-100 text-yellow-800',
                            table?.status === 'merged' &&
                                'bg-purple-100 text-purple-800',
                        ]"
                    >
                        {{ table?.status ?? "" }}
                    </span>
                </div>
                <div class="text-sm text-gray-600">
                    <p>{{ table?.chairs }} chairs</p>
                    <p v-if="table?.current_order">
                        Current Order: #{{ table.current_order.id }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <!-- Order Claim -->
                <Button
                    v-if="isTakeoutOccupied"
                    label="Order Claim"
                    icon="pi pi-plus"
                    class="w-full"
                    severity="success"
                    @click="$emit('claimOrder')"
                    :disabled="!table"
                />

                <!-- Transfer Number -->
                <Button
                    v-if="isTakeoutOccupied"
                    label="Transfer Number"
                    icon="pi pi-arrow-right"
                    class="w-full"
                    severity="success"
                    @click="$emit('transferNumber')"
                    :disabled="!table"
                />

                <!-- View Order (only if occupied) -->
                <Button
                    v-if="
                        !isTakeoutOccupied &&
                        table &&
                        table.status === 'occupied'
                    "
                    label="View Order"
                    icon="pi pi-eye"
                    class="w-full"
                    severity="info"
                    @click="handleViewOrder"
                />

                <!-- Vacant table (only if occupied and not merged) -->
                <Button
                    v-if="
                        table && table.status === 'occupied' && !table.merge_to
                    "
                    label="Vacant Table"
                    icon="pi pi-undo"
                    class="w-full"
                    severity="warning"
                    @click="vacantTable"
                />

                <!-- Merge Table (only for vacant tables) -->
                <Button
                    v-if="
                        !isTakeoutOccupied &&
                        table &&
                        table.status === 'vacant' &&
                        !table.merge_to
                    "
                    label="Merge Table"
                    icon="pi pi-link"
                    class="w-full"
                    severity="secondary"
                    @click="$emit('mergeTable')"
                />

                <!-- Unmerge Table (this table from its parent) -->
                <pre>{{ table }}</pre>
                <Button
                    v-if="table?.mergedTo"
                    label="Unmerge from Parent Table"
                    icon="pi pi-arrow-up-right-and-arrow-down-left-from-center"
                    class="w-full"
                    severity="warning"
                    @click="handleUnmergeFromTable(table.id)"
                />

                <!-- Unmerge all merged tables from this table -->
                <Button
                    v-if="table?.mergedTables && table.mergedTables.length > 0"
                    label="Unmerge All Tables"
                    icon="pi pi-arrow-up-right-and-arrow-down-left-from-center"
                    class="w-full"
                    severity="warning"
                    @click="handleUnmergeTables"
                />

                <!-- Reserve/Unreserve -->
                <Button
                    v-if="!isTakeoutOccupied"
                    :label="
                        table && table.status === 'reserved'
                            ? 'Unreserve Table'
                            : 'Reserve Table'
                    "
                    :icon="
                        table && table.status === 'reserved'
                            ? 'pi pi-times'
                            : 'pi pi-clock'
                    "
                    class="w-full"
                    :severity="
                        table && table.status === 'reserved'
                            ? 'danger'
                            : 'warning'
                    "
                    @click="$emit('reserveTable')"
                    :disabled="!table"
                />
            </div>

            <!-- Pax/Guest Input for Vacant Tables: show immediately -->
            <div
                v-if="table && table.status === 'vacant' && !table.merge_to"
                class="space-y-3 border-t pt-4"
            >
                <h4 class="font-medium text-gray-900">Guest Count</h4>
                <div class="flex flex-wrap gap-2 mb-2">
                    <button
                        v-for="n in [1, 2, 3, 4, 6, 8, 9, 0]"
                        :key="n"
                        type="button"
                        class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-800 font-semibold hover:bg-primary hover:text-white transition-colors"
                        :class="{ 'bg-primary text-white': pax === n }"
                        @click="pax = n"
                    >
                        {{ n }}
                    </button>
                </div>
                <div>
                    <TextField
                        label="Number of Pax"
                        v-model="pax"
                        :min="1"
                        :max="table ? table.chairs : 10"
                        class="w-full"
                        placeholder="Enter number of guests"
                        type="number"
                    />
                </div>
                <div>
                    <TextField
                        label="Guest Name"
                        v-model="guestName"
                        :min="1"
                        :max="table ? table.chairs : 10"
                        class="w-full"
                        placeholder="Enter guest name"
                        @keyup.enter="confirmTakeOrder"
                    />
                </div>
                <div class="flex gap-2">
                    <Button
                        label="Cancel"
                        severity="secondary"
                        class="flex-1"
                        @click="handleClose"
                    />
                    <Button
                        label="Start Order"
                        severity="success"
                        class="flex-1"
                        @click="confirmTakeOrder"
                    />
                </div>
            </div>
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import TextField from "@/Components/Form/TextField.vue";
import { useTable } from "@/composables/useTable";
import TableManagementLayout from "@/Layouts/TableManagementLayout.vue";

const props = defineProps<{
    show: boolean;
    table: any;
}>();

const emit = defineEmits<{
    close: [];
    takeOrder: [data: { pax: number; guest_name: string; table: any }];
    claimOrder: [];
    transferNumber: [];
    viewOrder: [];
    mergeTable: [];
    reserveTable: [];
    unmergeTable: [];
    refundOrder: [];
}>();

const {
    vacantTable: vacantTableAction,
    takeOrder,
    unmergeFromTable,
    unmergeTables,
    viewOrder,
} = useTable();

const isTakeoutOccupied = computed(() => {
    return (
        props.table &&
        props.table.status === "occupied" &&
        props.table.tableRoomLocation &&
        props.table.tableRoomLocation.location_type === "takeout"
    );
});

const hasMergedTables = computed(() => {
    return props.table?.mergedTables && props.table.mergedTables.length > 0;
});

const getAllMergedTables = (tables: any[] = [], result: any[] = []): any[] => {
    for (const table of tables || []) {
        result.push(table);
        if (table.mergedTables && table.mergedTables.length > 0) {
            getAllMergedTables(table.mergedTables, result);
        }
    }
    return result;
};

const allNestedMergedTables = computed(() => {
    return getAllMergedTables(props.table?.mergedTables);
});

const pax = ref(1);
const guestName = ref("");

watch(
    () => props.show,
    (newShow) => {
        if (newShow) {
            pax.value = 1;
            guestName.value = "";
        }
    }
);

const handleClose = () => {
    emit("close");
};

const confirmTakeOrder = () => {
    takeOrder(props.table.id, {
        pax: pax.value,
        guest_name: guestName.value.trim(),
    });
    handleClose();
};

const vacantTable = () => {
    if (!props.table?.id) {
        return;
    }
    vacantTableAction(props.table.id);
    handleClose();
};

const handleUnmergeFromTable = () => {
    if (!props.table?.id) {
        return;
    }
    unmergeFromTable(props.table.id);
    handleClose();
};

const handleUnmergeTables = () => {
    if (!props.table?.id) {
        return;
    }
    unmergeTables(props.table.id);
    handleClose();
};

const handleViewOrder = () => {
    viewOrder(props.table);
    handleClose();
};
</script>
