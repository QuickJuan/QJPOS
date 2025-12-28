<template>
    <div
        @click="$emit('click')"
        class="bg-white rounded-lg shadow-sm border border-neutral-200 p-4 hover:shadow-md transition-all cursor-pointer hover:scale-105 relative"
        :class="[getTableStatusClasses(table.status), isMerged && 'opacity-75']"
    >
        <!-- Header with name and location -->
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
                <h3
                    class="flex flex-row justify-between font-semibold text-neutral-900"
                >
                    <span> {{ table.name }}</span>

                    <!-- Merged Indicator -->
                    <span v-if="isMerged">
                        <p
                            class="text-xs text-neutral-600 bg-secondary-50 px-2 py-1 rounded inline-block"
                        >
                            <i class="pi pi-link text-xs"></i>
                            Merged to {{ mergedToName }}
                        </p>
                    </span>
                </h3>

                <p class="text-xs text-neutral-500">
                    {{
                        table.tableRoomLocation?.description ||
                        table.tableRoomLocation?.name ||
                        ""
                    }}
                </p>
            </div>
        </div>

        <!-- Status, Pax, and Dining Start -->
        <div class="mb-3 space-y-2">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs text-neutral-500 mb-1">Status</div>
                    <span
                        :class="[
                            'px-2 py-1 text-xs font-medium rounded-full capitalize',
                            table.status === 'occupied'
                                ? 'bg-red-100 text-red-800'
                                : table.status === 'reserved'
                                ? 'bg-yellow-100 text-yellow-800'
                                : table.status === 'available'
                                ? 'bg-green-100 text-green-800'
                                : 'bg-gray-100 text-gray-800',
                        ]"
                    >
                        {{ table.status }}
                    </span>
                </div>
                <div class="text-right">
                    <div class="text-xs text-neutral-500 mb-1">Pax</div>
                    <div class="text-sm font-semibold text-neutral-900">
                        {{ table.numberOfPax }}
                    </div>
                </div>
            </div>

            <!-- Dining Start Time -->
            <div
                v-if="table.dining_start"
                class="border-t border-neutral-200 pt-2"
            >
                <div class="text-xs text-neutral-500 mb-1">Dining Start</div>
                <div class="text-sm font-semibold text-neutral-900">
                    {{ table.dining_start }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
    table: any;
    isMerged?: boolean;
    mergedToName?: string;
}>();

const emit = defineEmits<{
    click: [];
}>();

const getTableStatusClasses = (status: string) => {
    const baseClasses = "border-l-4 border-neutral-200";
    switch (status) {
        case "occupied":
            return baseClasses + " border-l-red-500 bg-red-50";
        case "reserved":
            return baseClasses + " border-l-yellow-500 bg-yellow-50";
        case "available":
            return baseClasses + " border-l-green-500 bg-green-50";
        default:
            return baseClasses;
    }
};
</script>
