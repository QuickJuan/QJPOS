<template>
    <Dialog
        :visible="visible"
        modal
        :header="`Transfer Order from Table: ${sourceTable?.name}`"
        :style="{ width: '600px' }"
        :closable="true"
        @hide="$emit('update:visible', false)"
        @update:visible="$emit('update:visible', $event)"
    >
        <div class="space-y-4">
            <!-- Current table info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <span class="text-sm font-medium text-blue-900">
                        Current Table (Takeout)
                    </span>
                </div>
                <p class="text-sm text-blue-800 font-semibold">
                    {{ sourceTable?.name }}
                </p>
                <p class="text-xs text-blue-600">
                    {{ sourceTable?.chairs }} chairs
                </p>
            </div>

            <p class="text-sm text-gray-600">
                Select an available table to transfer this order to:
            </p>

            <div class="grid grid-cols-1 gap-3 max-h-80 overflow-y-auto">
                <div
                    v-for="table in availableTargets"
                    :key="table.id"
                    @click="$emit('selectTarget', table)"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all cursor-pointer hover:scale-102 relative"
                    :class="[
                        getTableStatusClasses(table.status),
                        selectedTarget?.id === table.id
                            ? 'ring-2 ring-blue-500 border-blue-500 bg-blue-50'
                            : '',
                    ]"
                >
                    <!-- Selection indicator -->
                    <div class="absolute top-3 right-3">
                        <div
                            :class="[
                                'w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors',
                                selectedTarget?.id === table.id
                                    ? 'border-blue-500 bg-blue-500'
                                    : 'border-gray-300 hover:border-gray-400',
                            ]"
                        >
                            <div
                                v-if="selectedTarget?.id === table.id"
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
                                        table.status === 'available' &&
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
                                    v-if="table.tableRoomLocation"
                                    class="text-gray-500"
                                >
                                    {{ table.tableRoomLocation.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="availableTargets.length === 0" class="text-center py-8">
                <i class="pi pi-info-circle text-2xl text-gray-300 mb-2"></i>
                <p class="text-sm text-gray-600">
                    No available tables available for transfer.
                </p>
            </div>
        </div>
        <template #footer>
            <Button
                label="Cancel"
                severity="secondary"
                @click="$emit('update:visible', false)"
            />
            <Button
                label="Transfer"
                severity="success"
                :disabled="!selectedTarget"
                @click="handleConfirmTransfer"
            />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import Dialog from "primevue/dialog";
import Button from "primevue/button";

const props = defineProps<{
    visible: boolean;
    sourceTable: any;
    availableTargets: any[];
    selectedTarget: any;
}>();

const emit = defineEmits<{
    "update:visible": [value: boolean];
    selectTarget: [table: any];
    confirmTransfer: [];
}>();

const handleConfirmTransfer = () => {
    console.log("handleConfirmTransfer called in modal", props.selectedTarget);
    emit("confirmTransfer");
};

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
</script>
