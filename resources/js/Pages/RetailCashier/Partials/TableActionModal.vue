<template>
    <Dialog
        :visible="show"
        modal
        :header="`Table ${table?.name || ''}`"
        :style="{ width: '400px' }"
        :closable="true"
        @hide="handleClose"
        @update:visible="handleClose"
    >
        <div class="space-y-4">
            <!-- Table Info -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">
                        Status:
                    </span>
                    <span
                        :class="[
                            'px-2 py-1 text-xs font-medium rounded-full capitalize',
                            table?.status === 'vacant' &&
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

                <!-- Take Order -->
                <Button
                    v-if="
                        !isTakeoutOccupied && table && table.status === 'vacant'
                    "
                    label="Take Order"
                    icon="pi pi-plus"
                    class="w-full"
                    severity="success"
                    @click="handleTakeOrder"
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
                    @click="$emit('viewOrder')"
                />

                <!-- Refund Order (only if occupied) -->
                <Button
                    v-if="
                        !isTakeoutOccupied &&
                        table &&
                        table.status === 'occupied'
                    "
                    label="Refund Order"
                    icon="pi pi-undo"
                    class="w-full"
                    severity="danger"
                    @click="$emit('refundOrder')"
                />

                <!-- Merge Table (only for vacant tables) -->
                <Button
                    v-if="
                        !isTakeoutOccupied && table && table.status === 'vacant'
                    "
                    label="Merge Table"
                    icon="pi pi-link"
                    class="w-full"
                    severity="secondary"
                    @click="$emit('mergeTable')"
                />

                <!-- Unmerge Table (only for merged tables) -->
                <Button
                    v-if="!isTakeoutOccupied && table.merge_to"
                    label="Unmerge Table"
                    icon="pi pi-arrow-up-right-and-arrow-down-left-from-center"
                    class="w-full"
                    severity="warning"
                    @click="$emit('unmergeTable')"
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

            <!-- Pax/Guest Input for Vacant Tables -->
            <div
                v-if="table && table.status === 'vacant' && showPaxInput"
                class="space-y-3 border-t pt-4"
            >
                <h4 class="font-medium text-gray-900">Order Details</h4>

                <div>
                    <TextField
                        label="Number of Pax"
                        v-model="pax"
                        :min="1"
                        :max="table ? table.chairs : 10"
                        class="w-full"
                        placeholder="Enter number of guests"
                    />
                </div>

                <div>
                    <TextField
                        label="Guest Name"
                        v-model="guestName"
                        :min="1"
                        :max="table ? table.chairs : 10"
                        class="w-full"
                        placeholder="Enter number of guests"
                        @keyup.enter="confirmTakeOrder"
                    />
                </div>

                <div class="flex gap-2">
                    <Button
                        label="Cancel"
                        severity="secondary"
                        class="flex-1"
                        @click="cancelPaxInput"
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

const props = defineProps<{
    show: boolean;
    table: any;
}>();

const emit = defineEmits<{
    close: [];
    takeOrder: [data: { pax: number; guest_name: string }];
    claimOrder: [];
    transferNumber: [];
    viewOrder: [];
    mergeTable: [];
    reserveTable: [];
    unmergeTable: [];
    refundOrder: [];
}>();

const isTakeoutOccupied = computed(() => {
    return (
        props.table &&
        props.table.status === "occupied" &&
        props.table.tableRoomLocation &&
        props.table.tableRoomLocation.location_type === "takeout"
    );
});

const pax = ref(1);
const guestName = ref("");
const showPaxInput = ref(false);

watch(
    () => props.show,
    (newShow) => {
        if (newShow) {
            pax.value = 1;
            guestName.value = "";
            showPaxInput.value = false;
        }
    }
);

const handleTakeOrder = () => {
    if (props.table && props.table.status === "vacant") {
        // Show pax input for vacant tables
        showPaxInput.value = true;
    } else if (props.table) {
        // For occupied tables, proceed directly
        emit("takeOrder", { pax: 1, guest_name: "Existing Guest" });
    }
};

const handleClose = () => {
    emit("close");
};

const confirmTakeOrder = () => {
    emit("takeOrder", {
        pax: pax.value,
        guest_name: guestName.value.trim(),
    });
};

const cancelPaxInput = () => {
    showPaxInput.value = false;
    pax.value = 1;
    guestName.value = "";
};
</script>
