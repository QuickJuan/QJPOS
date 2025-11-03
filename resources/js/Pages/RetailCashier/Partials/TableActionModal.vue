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
                <!-- Take Order -->
                <Button
                    label="Take Order"
                    icon="pi pi-plus"
                    class="w-full"
                    severity="success"
                    @click="handleTakeOrder"
                    :disabled="!table"
                />

                <!-- View Order (only if occupied) -->
                <Button
                    v-if="table && table.status === 'occupied'"
                    label="View Order"
                    icon="pi pi-eye"
                    class="w-full"
                    severity="info"
                    @click="$emit('viewOrder')"
                />

                <!-- Merge Table (only for vacant tables) -->
                <Button
                    v-if="table && table.status === 'vacant'"
                    label="Merge Table"
                    icon="pi pi-link"
                    class="w-full"
                    severity="secondary"
                    @click="$emit('mergeTable')"
                />

                <!-- Reserve/Unreserve -->
                <Button
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
import { ref, computed, watch } from "vue";
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
    viewOrder: [];
    mergeTable: [];
    reserveTable: [];
}>();

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
