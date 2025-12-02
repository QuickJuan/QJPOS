<template>
    <Dialog
        :visible="props.visible"
        modal
        header="More Options"
        :style="{ width: '20rem' }"
        @update:visible="handleClose"
    >
        <div class="space-y-3">
            <!-- <button
                @click="handleSaveOrder"
                :disabled="orderItems.length === 0"
                class="w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3 bg-gray-100 text-secondary-700 hover:bg-gray-200 disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
            >
                <BookmarkIcon class="w-5 h-5" />
                <div class="text-left">
                    <div class="font-semibold">Save Order</div>
                    <div class="text-xs opacity-75">
                        Save order for later processing
                    </div>
                </div>
            </button> -->

            <button
                @click="handleApplyDiscount"
                :disabled="selectedItemsForDiscount.length === 0"
                class="w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3 bg-gray-100 text-secondary-700 hover:bg-gray-200 disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
            >
                <TagIcon class="w-5 h-5" />
                <div class="text-left flex-1">
                    <div class="font-semibold flex items-center gap-2">
                        Apply Discount
                        <span
                            v-if="selectedItemsForDiscount.length > 0"
                            class="bg-yellow-600 text-white text-xs px-1.5 py-0.5 rounded-full"
                        >
                            {{ selectedItemsForDiscount.length }}
                        </span>
                    </div>
                    <div class="text-xs opacity-75">
                        Apply discount to selected items
                    </div>
                </div>
            </button>

            <button
                @click="handleAddModifier"
                :disabled="selectedItemsForDiscount.length === 0"
                class="w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3 bg-gray-100 text-secondary-700 hover:bg-gray-200 disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
            >
                <PlusIcon class="w-5 h-5" />
                <div class="text-left flex-1">
                    <div class="font-semibold flex items-center gap-2">
                        Add Modifier
                        <span
                            v-if="selectedItemsForDiscount.length > 0"
                            class="bg-blue-600 text-white text-xs px-1.5 py-0.5 rounded-full"
                        >
                            {{ selectedItemsForDiscount.length }}
                        </span>
                    </div>
                    <div class="text-xs opacity-75">
                        Add modifier to selected items
                    </div>
                </div>
            </button>

            <button
                @click="handlePrintBill"
                :disabled="orderItems.length === 0"
                class="w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3 bg-gray-100 text-secondary-700 hover:bg-gray-200 disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
            >
                <PrinterIcon class="w-5 h-5" />
                <div class="text-left">
                    <div class="font-semibold">Print Bill</div>
                    <div class="text-xs opacity-75">
                        Print bill for customer
                    </div>
                </div>
            </button>

            <button
                @click="handleViewTable"
                class="w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3 bg-gray-100 text-secondary-700 hover:bg-gray-200"
            >
                <TableCellsIcon class="w-5 h-5" />
                <div class="text-left">
                    <div class="font-semibold">View Table</div>
                    <div class="text-xs opacity-75">
                        View table layout and status
                    </div>
                </div>
            </button>

            <button
                @click="handleEndOfShift"
                class="w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3 bg-red-100 text-red-700 hover:bg-red-200"
            >
                <PowerIcon class="w-5 h-5" />
                <div class="text-left">
                    <div class="font-semibold">End of Shift</div>
                    <div class="text-xs opacity-75">Close cashier session</div>
                </div>
            </button>
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { Dialog } from "primevue";
import {
    BookmarkIcon,
    TagIcon,
    PlusIcon,
    PrinterIcon,
    TableCellsIcon,
    PowerIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps<{
    visible: boolean;
    orderItems: any[];
    selectedItemsForDiscount: number[];
}>();

const emit = defineEmits<{
    saveOrder: [];
    openDiscountModal: [];
    addModifier: [];
    printBill: [];
    viewTable: [];
    endOfShift: [];
    "update:visible": [value: boolean];
}>();

const handleSaveOrder = () => {
    emit("saveOrder");
    emit("update:visible", false);
};

const handleApplyDiscount = () => {
    emit("openDiscountModal");
    emit("update:visible", false);
};

const handleAddModifier = () => {
    emit("addModifier");
    emit("update:visible", false);
};

const handlePrintBill = () => {
    emit("printBill");
    emit("update:visible", false);
};

const handleViewTable = () => {
    emit("viewTable");
    emit("update:visible", false);
};

const handleEndOfShift = () => {
    emit("endOfShift");
    emit("update:visible", false);
};

const handleClose = () => {
    emit("update:visible", false);
};
</script>
