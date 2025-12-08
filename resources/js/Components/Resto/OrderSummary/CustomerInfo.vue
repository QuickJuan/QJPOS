<template>
    <div class="p-4 border-b border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-bold text-secondary-800">Order Summary</h2>
            <div
                class="text-sm text-secondary-600 font-medium bg-gray-200 px-3 py-1 rounded-full"
            >
                #Shift: {{ currentShift }}
            </div>
        </div>

        <!-- Customer Info -->
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <UserIcon class="w-4 h-4 text-secondary-500" />
                <span class="text-sm text-secondary-600">Customer:</span>
                <span class="text-sm font-medium text-secondary-800">
                    {{ tableInfo?.customer_name || "Walk-in Customer" }}
                </span>
            </div>

            <!-- Selected Table Label -->
            <div class="flex items-center gap-2">
                <span class="text-sm text-secondary-600">Table:</span>
                <span class="text-sm font-medium text-secondary-800">
                    {{ tableInfo?.name || "No Table Selected" }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { UserIcon } from "@heroicons/vue/24/outline";
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import PageProps from "@/Types/PageProps";
import table from "vendor/filament/tables/resources/js/components/table";

const props = defineProps<{
    cart: any;
    tableInfo: any;
}>();

const emit = defineEmits<{
    selectTable: [];
    tableChanged: [data: { table: any | null; cart: any | null }];
}>();

const page = usePage<PageProps>();
const currentShift = ref(props.cart?.id);

// Get branch ID from page props
const branchId = computed(() => page.props?.active_branch?.id);
const currentTableId = computed(() => props.tableInfo?.id || null);

const handleTableChanged = (tableData: {
    table: any | null;
    cart: any | null;
}) => {
    emit("tableChanged", tableData);
};
</script>
