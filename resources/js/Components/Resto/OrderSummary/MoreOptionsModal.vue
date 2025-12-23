<template>
    <Dialog
        :visible="props.visible"
        modal
        header="More Options"
        class="bg-white more-options-dialog"
        :style="{ maxWidth: '95vw' }"
        @update:visible="handleClose"
    >
        <div class="grid gap-6 lg:grid-cols-[1.3fr_1fr]">
            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-slate-100 bg-gradient-to-r from-blue-50 via-emerald-50 to-white/80 p-4 text-sm text-secondary-600 shadow-inner"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-widest text-secondary-500"
                    >
                        Quick glance
                    </p>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-lg font-semibold text-secondary-900">
                                {{
                                    hasOrderItems
                                        ? `${orderItems.length} item${
                                              orderItems.length === 1 ? "" : "s"
                                          }`
                                        : "No items yet"
                                }}
                            </p>
                            <p class="text-xs text-secondary-500">
                                {{ selectedCount }} selected
                                {{ selectedCount === 1 ? "item" : "items" }}
                                ready for quick actions
                            </p>
                        </div>
                        <span
                            class="rounded-full bg-white px-3 py-1 text-sm font-semibold text-secondary-700 shadow"
                        >
                            {{ selectedCount }}
                        </span>
                    </div>
                </div>

                <section class="space-y-3">
                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-secondary-500"
                    >
                        Selected items
                    </p>

                    <button
                        @click="handleApplyDiscount"
                        :disabled="selectedCount === 0"
                        class="group relative flex w-full items-center gap-4 rounded-2xl border border-slate-200/70 bg-gradient-to-br from-white to-slate-50 p-4 text-left text-secondary-800 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-lg disabled:translate-y-0 disabled:border-slate-100 disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none"
                    >
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 group-disabled:bg-slate-100 group-disabled:text-slate-400"
                        >
                            <TagIcon class="w-5 h-5" />
                        </span>
                        <div class="flex-1 text-left">
                            <div
                                class="flex items-center gap-2 text-base font-semibold"
                            >
                                Apply Discount
                                <span
                                    v-if="selectedCount > 0"
                                    class="rounded-full bg-amber-500 px-2 py-0.5 text-xs font-semibold text-white"
                                >
                                    {{ selectedCount }}
                                </span>
                            </div>
                            <p class="text-xs text-secondary-500">
                                Apply preset promos to highlighted items.
                            </p>
                        </div>
                    </button>

                    <button
                        @click="handleAddModifier"
                        :disabled="selectedCount === 0"
                        class="group relative flex w-full items-center gap-4 rounded-2xl border border-slate-200/70 bg-gradient-to-br from-white to-slate-50 p-4 text-left text-secondary-800 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-lg disabled:translate-y-0 disabled:border-slate-100 disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none"
                    >
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600 group-disabled:bg-slate-100 group-disabled:text-slate-400"
                        >
                            <PlusIcon class="w-5 h-5" />
                        </span>
                        <div class="flex-1 text-left">
                            <div
                                class="flex items-center gap-2 text-base font-semibold"
                            >
                                Add Modifier
                                <span
                                    v-if="selectedCount > 0"
                                    class="rounded-full bg-sky-500 px-2 py-0.5 text-xs font-semibold text-white"
                                >
                                    {{ selectedCount }}
                                </span>
                            </div>
                            <p class="text-xs text-secondary-500">
                                Attach add-ons, remarks, or cooking
                                instructions.
                            </p>
                        </div>
                    </button>

                    <button
                        @click="handleTransferOrderItems"
                        :disabled="selectedCount === 0"
                        class="group relative flex w-full items-center gap-4 rounded-2xl border border-slate-200/70 bg-gradient-to-br from-white to-slate-50 p-4 text-left text-secondary-800 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-lg disabled:translate-y-0 disabled:border-slate-100 disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none"
                    >
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 group-disabled:bg-slate-100 group-disabled:text-slate-400"
                        >
                            <ArrowRightIcon class="w-5 h-5" />
                        </span>
                        <div class="flex-1 text-left">
                            <div
                                class="flex items-center gap-2 text-base font-semibold"
                            >
                                Transfer Order Items
                                <span
                                    v-if="selectedCount > 0"
                                    class="rounded-full bg-emerald-500 px-2 py-0.5 text-xs font-semibold text-white"
                                >
                                    {{ selectedCount }}
                                </span>
                            </div>
                            <p class="text-xs text-secondary-500">
                                Move the highlighted items to another table.
                            </p>
                        </div>
                    </button>
                </section>
            </div>

            <div class="space-y-6">
                <section class="space-y-3">
                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-secondary-500"
                    >
                        Order actions
                    </p>

                    <button
                        @click="handlePrintBill"
                        :disabled="!hasOrderItems"
                        class="group relative flex w-full items-center gap-4 rounded-2xl border border-slate-200/70 bg-white p-4 text-left text-secondary-800 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-lg disabled:translate-y-0 disabled:border-slate-100 disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none"
                    >
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-50 text-primary-600 group-disabled:bg-slate-100 group-disabled:text-slate-400"
                        >
                            <PrinterIcon class="w-5 h-5" />
                        </span>
                        <div>
                            <p class="text-base font-semibold">Print Bill</p>
                            <p class="text-xs text-secondary-500">
                                Generate a customer copy with the latest totals.
                            </p>
                        </div>
                    </button>

                    <button
                        @click="handleReprintOrder"
                        class="group relative flex w-full items-center gap-4 rounded-2xl border border-slate-200/70 bg-white p-4 text-left text-secondary-800 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
                        >
                            <ArrowPathIcon class="w-5 h-5" />
                        </span>
                        <div>
                            <p class="text-base font-semibold">
                                Re-print Order
                            </p>
                            <p class="text-xs text-secondary-500">
                                Fetch a previous print via batch reference.
                            </p>
                        </div>
                    </button>
                </section>

                <section class="space-y-3">
                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-secondary-500"
                    >
                        Navigation
                    </p>

                    <button
                        @click="handleViewTable"
                        class="group relative flex w-full items-center gap-4 rounded-2xl border border-slate-200/70 bg-white p-4 text-left text-secondary-800 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600"
                        >
                            <TableCellsIcon class="w-5 h-5" />
                        </span>
                        <div>
                            <p class="text-base font-semibold">View Table</p>
                            <p class="text-xs text-secondary-500">
                                Open the floor layout and seat status.
                            </p>
                        </div>
                    </button>

                    <button
                        @click="handleReviewTransactions"
                        class="group relative flex w-full items-center gap-4 rounded-2xl border border-slate-200/70 bg-white p-4 text-left text-secondary-800 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-50 text-slate-600"
                        >
                            <TableCellsIcon class="w-5 h-5" />
                        </span>
                        <div>
                            <p class="text-base font-semibold">
                                Review Transactions
                            </p>
                            <p class="text-xs text-secondary-500">
                                Jump to the transaction history dashboard.
                            </p>
                        </div>
                    </button>
                </section>
            </div>
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { Dialog } from "primevue";
import {
    TagIcon,
    PlusIcon,
    PrinterIcon,
    TableCellsIcon,
    ArrowRightIcon,
    ArrowPathIcon,
} from "@heroicons/vue/24/outline";
import { route } from "ziggy-js";
import { router } from "@inertiajs/vue3";

const props = defineProps<{
    visible: boolean;
    orderItems: any[];
    selectedItemsForDiscount?: number[];
}>();

const selectedCount = computed(
    () => props.selectedItemsForDiscount?.length ?? 0
);

const hasOrderItems = computed(() => (props.orderItems ?? []).length > 0);

const emit = defineEmits<{
    saveOrder: [];
    openDiscountModal: [];
    addModifier: [];
    transferOrderItems: [];
    printBill: [];
    reprintOrder: [];
    viewTable: [];
    printerConfig: [];
    endOfShift: [];
    "update:visible": [value: boolean];
}>();

const handleReviewTransactions = () => {
    //redirect to transactions page using router
    router.visit(route("transactions.index"));
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

const handleTransferOrderItems = () => {
    emit("transferOrderItems");
    emit("update:visible", false);
};

const handlePrintBill = () => {
    emit("printBill");
    emit("update:visible", false);
};

const handleReprintOrder = () => {
    emit("reprintOrder");
    emit("update:visible", false);
};

const handleViewTable = () => {
    emit("viewTable");
    emit("update:visible", false);
};

const handlePrinterConfig = () => {
    emit("printerConfig");
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

<style scoped>
.more-options-dialog {
    width: 24rem;
    max-width: 95vw;
}

@media (min-width: 768px) {
    .more-options-dialog {
        width: 48rem;
    }
}

@media (min-width: 1024px) {
    .more-options-dialog {
        width: 72rem;
    }
}
</style>
