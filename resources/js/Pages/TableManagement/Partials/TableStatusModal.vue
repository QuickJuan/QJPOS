<template>
    <Dialog
        :visible="props.show"
        modal
        :header="`Table: ${table?.name || 'Unknown'}`"
        :style="{ width: '30rem' }"
        :closable="false"
        @hide="$emit('close')"
    >
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-2 text-gray-700">
                    Current Status
                </label>
                <div class="flex gap-2">
                    <Button
                        :label="'Vacant'"
                        :class="
                            table?.status === 'vacant'
                                ? 'p-button-primary'
                                : 'p-button-outlined'
                        "
                        @click="updateStatus('vacant')"
                    />
                    <Button
                        :label="'Occupied'"
                        :class="
                            table?.status === 'occupied'
                                ? 'p-button-primary'
                                : 'p-button-outlined'
                        "
                        @click="updateStatus('occupied')"
                    />
                    <Button
                        :label="'Reserved'"
                        :class="
                            table?.status === 'reserved'
                                ? 'p-button-primary'
                                : 'p-button-outlined'
                        "
                        @click="updateStatus('reserved')"
                    />
                </div>
            </div>

            <div
                v-if="
                    table?.status === 'occupied' || table?.status === 'reserved'
                "
            >
                <TextField
                    v-model="customerName"
                    label="Customer Name"
                    class="w-full"
                    placeholder="Enter customer name"
                />
            </div>
        </div>

        <template #footer>
            <Button
                label="Close"
                severity="secondary"
                @click="$emit('close')"
            />
            <Button
                label="Save Status"
                @click="saveStatus"
            />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import TextField from "@/Components/Form/TextField.vue";

const props = defineProps<{
    show: boolean;
    table: any;
}>();

const emit = defineEmits<{
    close: [];
    update: [data: { status: string; customer?: string }];
}>();

const customerName = ref("");

watch(
    () => props.table,
    (newTable) => {
        if (newTable) {
            customerName.value = newTable.customer || "";
        }
    },
    { immediate: true }
);

const updateStatus = (status: string) => {
    if (props.table) {
        emit("update", {
            status,
            customer: status === "vacant" ? "" : customerName.value,
        });
    }
};

const saveStatus = () => {
    if (props.table) {
        emit("update", {
            status: props.table.status,
            customer: customerName.value,
        });
    }
    emit("close");
};
</script>
