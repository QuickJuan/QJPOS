<template>
    <Dialog
        :visible="show"
        modal
        :header="`${
            table?.mergedTo
                ? 'Merged to ' + table.mergedToTable
                : table?.name || ''
        }`"
        :style="{ width: '760px', maxWidth: '95vw' }"
        :closable="true"
        @hide="handleClose"
        @update:visible="handleClose"
    >
        <div class="space-y-3">
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
                    </p>
                </div>
            </div>

            <!-- Table Info -->
            <div
                class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
            >
                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                    <div class="space-y-1">
                        <p
                            class="text-xs uppercase tracking-wide text-gray-500"
                        >
                            Status
                        </p>
                        <span :class="statusBadgeClass">{{ statusLabel }}</span>
                    </div>
                    <div class="space-y-1">
                        <p
                            class="text-xs uppercase tracking-wide text-gray-500"
                        >
                            Seats
                        </p>
                        <p class="text-base font-semibold text-gray-900">
                            {{ tableCapacity || "—" }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p
                            class="text-xs uppercase tracking-wide text-gray-500"
                        >
                            Location
                        </p>
                        <p class="text-base font-semibold text-gray-900">
                            {{ locationName }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="table?.mergedTo"
                    class="mt-3 rounded-lg border border-dashed border-gray-200 bg-gray-50 px-3 py-2 text-xs"
                >
                    <p class="font-semibold text-gray-700">
                        Merged into {{ table.mergedToTable }}
                    </p>
                    <p class="text-gray-500">Original: {{ table.name }}</p>
                </div>
            </div>

            <!-- Running Total Summary -->
            <div
                v-if="showCartSummary"
                class="rounded-xl border border-blue-100 bg-blue-50/70 p-4 shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs uppercase tracking-wide text-blue-700"
                        >
                            Running Amount Due
                        </p>
                        <p class="text-2xl font-bold text-blue-900">
                            {{ formatCurrency(cartSummary?.runningTotal || 0) }}
                        </p>
                        <p class="text-xs text-blue-600/80">
                            Includes service charge
                        </p>
                    </div>
                    <div class="flex-1 space-y-2 text-sm text-blue-900">
                        <div class="flex items-center justify-between">
                            <span class="text-blue-700/80">Order Total</span>
                            <span class="font-semibold">
                                {{
                                    formatCurrency(cartSummary?.orderTotal || 0)
                                }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-blue-700/80">Service Charge</span>
                            <span class="font-semibold">
                                {{
                                    formatCurrency(
                                        cartSummary?.serviceCharge || 0
                                    )
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-gray-500"
                    >
                        Quick Actions
                    </p>
                    <span class="text-xs text-gray-400" v-if="table?.status">
                        Manage {{ table?.status }} table
                    </span>
                </div>

                <div
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <Button
                        v-if="isTakeoutOccupied"
                        label="Order Claim"
                        icon="pi pi-inbox"
                        class="table-action-btn w-full h-14 justify-start gap-3 rounded-xl text-left"
                        severity="success"
                        outlined
                        @click="handleClaimOrder"
                        :disabled="!table"
                    />

                    <Button
                        v-if="isTakeoutOccupied"
                        label="Transfer Number"
                        icon="pi pi-arrow-right-arrow-left"
                        class="table-action-btn w-full h-14 justify-start gap-3 rounded-xl text-left"
                        severity="success"
                        outlined
                        @click="handleTransferNumber"
                        :disabled="!table"
                    />

                    <Button
                        v-if="
                            !isTakeoutOccupied &&
                            table &&
                            table.status === 'occupied'
                        "
                        label="View Order"
                        icon="pi pi-eye"
                        class="table-action-btn w-full h-14 justify-start gap-3 rounded-xl text-left"
                        severity="info"
                        outlined
                        @click="handleViewOrder"
                    />

                    <Button
                        v-if="
                            !isTakeoutOccupied &&
                            table &&
                            table.status === 'occupied'
                        "
                        label="Transfer Guest"
                        icon="pi pi-users"
                        class="table-action-btn w-full h-14 justify-start gap-3 rounded-xl text-left"
                        severity="warn"
                        outlined
                        @click="handleTransferGuest"
                        :disabled="!table"
                    />

                    <Button
                        v-if="
                            table &&
                            table.status === 'occupied' &&
                            !table.mergedTo
                        "
                        label="Vacant Table"
                        icon="pi pi-undo"
                        class="table-action-btn w-full h-14 justify-start gap-3 rounded-xl text-left"
                        severity="warning"
                        outlined
                        @click="vacantTable"
                    />

                    <Button
                        v-if="table?.mergedTo"
                        label="Unmerge from Parent"
                        icon="pi pi-external-link"
                        class="table-action-btn w-full h-14 justify-start gap-3 rounded-xl text-left"
                        severity="warning"
                        outlined
                        @click="handleUnmergeFromTable"
                    />

                    <Button
                        v-if="
                            table?.mergedTables && table.mergedTables.length > 0
                        "
                        label="Unmerge All Tables"
                        icon="pi pi-table"
                        class="table-action-btn"
                        severity="warning"
                        outlined
                        @click="handleUnmergeTables"
                    />

                    <Button
                        v-if="canPrintBill"
                        label="Print Bill"
                        icon="pi pi-print"
                        class="table-action-btn w-full h-14 justify-start gap-3 rounded-xl text-left"
                        severity="secondary"
                        outlined
                        :loading="isPrintingBill"
                        :disabled="isPrintingBill"
                        @click="handlePrintBill"
                    />
                </div>
            </div>

            <!-- Pax/Guest Input for Available Tables: show immediately -->
            <div
                v-if="table && table.status === 'available' && !table?.mergedTo"
                class="border-t pt-5 space-y-5"
            >
                <div
                    class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div>
                        <h4 class="text-base font-semibold text-gray-900">
                            Guest Count
                        </h4>
                        <p class="text-sm text-gray-500">
                            Tap numbers to add to the total or enter it
                            manually.
                        </p>
                    </div>
                    <div
                        class="flex flex-wrap items-center gap-3 text-xs uppercase tracking-wide text-gray-400"
                    >
                        <span v-if="tableCapacity">
                            Seats: {{ tableCapacity }}
                        </span>
                        <button
                            type="button"
                            class="font-semibold text-primary transition hover:text-primary/80"
                            @click="resetPax"
                        >
                            Reset count
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-5 gap-2 sm:grid-cols-10">
                    <button
                        v-for="n in quickPaxOptions"
                        :key="n"
                        type="button"
                        class="rounded-lg border border-gray-200 bg-white py-2 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary"
                        @click="incrementPax(n)"
                    >
                        {{ n }}
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <TextField
                        label="Number of Pax"
                        v-model="pax"
                        :min="0"
                        class="w-full"
                        placeholder="Enter number of guests"
                        type="number"
                    />
                    <TextField
                        label="Guest Name"
                        v-model="guestName"
                        class="w-full md:col-span-3"
                        placeholder="Enter guest name"
                        @keyup.enter="confirmTakeOrder"
                    />
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <Button
                        label="Start Order"
                        icon="pi pi-play"
                        severity="success"
                        class="h-12 w-full text-base font-semibold shadow-md"
                        :disabled="!hasValidPax"
                        @click="confirmTakeOrder"
                    />

                    <Button
                        v-if="canMergeTable"
                        label="Merge Table"
                        icon="pi pi-link"
                        class="h-12 w-full text-base font-semibold"
                        outlined
                        @click="handleMergeTable"
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
import { useToast } from "primevue/usetoast";
import { usePrintBill } from "@/composables/usePrintBill";

const props = defineProps<{
    show: boolean;
    table: any;
}>();

const emit = defineEmits<{
    close: [];
    takeOrder: [data: { pax: number; guest_name: string; table: any }];
    claimOrder: [];
    transferNumber: [];
    transferGuest: [];
    viewOrder: [];
    mergeTable: [table: any];
    reserveTable: [];
    unmergeTable: [];
    refundOrder: [];
}>();

const toast = useToast();
const isPrintingBill = ref(false);
const { fetchBillData, sendBillToPrinter } = usePrintBill();

const {
    vacantTable: vacantTableAction,
    takeOrder,
    unmergeFromTable,
    unmergeTables,
    viewOrder,
    mergeTable,
    claimOrder,
    reserveTable,
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

const quickPaxOptions = Array.from({ length: 10 }, (_, index) => index + 1);

const tableCapacity = computed(() => {
    return props.table?.chairs ?? props.table?.capacity ?? 0;
});

const parseCurrencyValue = (value: unknown): number => {
    const numeric = Number(value);
    return Number.isFinite(numeric) ? numeric : 0;
};

const normalizeCollection = (collection: unknown): any[] => {
    if (Array.isArray(collection)) {
        return collection;
    }

    if (
        collection &&
        typeof collection === "object" &&
        Array.isArray((collection as Record<string, any>).data)
    ) {
        return (collection as Record<string, any>).data;
    }

    return [];
};

const flattenSubItems = (item: any): any[] => {
    const subItems = normalizeCollection(item?.sub_items ?? item?.subItems);
    if (!subItems.length) {
        return [];
    }

    const flattened: any[] = [];
    subItems.forEach((subItem) => {
        flattened.push(subItem);
        flattened.push(...flattenSubItems(subItem));
    });

    return flattened;
};

const flattenItems = (items: any[]): any[] => {
    const normalizedItems = normalizeCollection(items);
    const flattened: any[] = [];

    normalizedItems.forEach((item) => {
        if (!item) {
            return;
        }
        flattened.push(item);
        flattened.push(...flattenSubItems(item));
    });

    return flattened;
};

const extractItemsFromGroups = (groups: any[]): any[] => {
    const normalizedGroups = normalizeCollection(groups);
    return normalizedGroups.flatMap((group) =>
        flattenItems(group?.cartItems ?? group?.cart_items ?? [])
    );
};

const getCartItems = (cart: Record<string, any> | null | undefined): any[] => {
    if (!cart) {
        return [];
    }

    const rawGroups = normalizeCollection(
        (cart as any).cart_items ?? (cart as any).cartItems
    );

    if (!rawGroups.length) {
        return [];
    }

    const hasGroupStructure = rawGroups.some((entry) =>
        Array.isArray(entry?.cartItems ?? entry?.cart_items)
    );

    return hasGroupStructure
        ? extractItemsFromGroups(rawGroups)
        : flattenItems(rawGroups);
};

const sumCartItemSubtotals = (items: any[]): number => {
    return items.reduce((total, item) => {
        const subTotal = parseCurrencyValue(
            item?.sub_total ?? item?.subTotal ?? item?.subtotal ?? 0
        );
        return total + subTotal;
    }, 0);
};

const cartSummary = computed(() => {
    const cart = props.table?.cart;
    if (!cart) {
        return null;
    }

    const cartItems = getCartItems(cart);
    const precomputedOrderTotal = parseCurrencyValue(
        cart.order_total ?? cart.totals?.sub_total ?? cart.totals?.total_amount
    );
    const orderTotal =
        precomputedOrderTotal > 0
            ? precomputedOrderTotal
            : cartItems.length
            ? sumCartItemSubtotals(cartItems)
            : parseCurrencyValue(cart.total_amount ?? 0);
    const serviceCharge = parseCurrencyValue(
        cart.totals?.service_charge ?? cart.service_charge ?? cart.serviceCharge
    );
    const runningTotal = orderTotal + serviceCharge;

    return {
        orderTotal,
        serviceCharge,
        runningTotal,
    };
});

const showCartSummary = computed(() => {
    return props.table?.status === "occupied" && Boolean(cartSummary.value);
});

const canPrintBill = computed(() => {
    return Boolean(props.table?.status === "occupied" && props.table?.cart?.id);
});

const formatCurrency = (value: number): string => {
    const safeValue = Number.isFinite(value) ? value : 0;
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 2,
    }).format(safeValue);
};

const locationName = computed(() => {
    if (props.table?.locationName) {
        return props.table.locationName;
    }

    const location = props.table?.tableRoomLocation;
    const fallbackLocation = props.table?.location;

    return (
        location?.name ??
        fallbackLocation?.name ??
        location?.description ??
        fallbackLocation?.description ??
        "Unassigned"
    );
});

const statusLabel = computed(() => {
    const status = props.table?.status ?? "unknown";
    switch (status) {
        case "available":
            return "Available";
        case "occupied":
            return "Occupied";
        case "reserved":
            return "Reserved";
        case "cleaning":
            return "Cleaning";
        default:
            return status.charAt(0).toUpperCase() + status.slice(1);
    }
});

const statusBadgeClass = computed(() => {
    const base =
        "px-3 py-1 text-xs font-semibold rounded-full border border-transparent";
    switch (props.table?.status) {
        case "available":
            return `${base} bg-green-50 text-green-700 border-green-200`;
        case "occupied":
            return `${base} bg-orange-50 text-orange-700 border-orange-200`;
        case "reserved":
            return `${base} bg-blue-50 text-blue-700 border-blue-200`;
        case "cleaning":
            return `${base} bg-amber-50 text-amber-700 border-amber-200`;
        default:
            return `${base} bg-gray-100 text-gray-600 border-gray-200`;
    }
});

const canMergeTable = computed(() => {
    return (
        props.table &&
        props.table.status === "available" &&
        !props.table.mergedTo &&
        !isTakeoutOccupied.value
    );
});

const normalizePaxValue = (value: unknown): number => {
    const numeric = Number(value);
    return Number.isFinite(numeric) ? numeric : 0;
};

const ensureNonNegative = (value: number): number => {
    return Math.max(value, 0);
};

const pax = ref(0);
const guestName = ref("");

const hasValidPax = computed(() => normalizePaxValue(pax.value) >= 1);

const incrementPax = (value: number) => {
    const current = normalizePaxValue(pax.value);
    pax.value = ensureNonNegative(current + value);
};

const resetPax = () => {
    pax.value = 0;
};

watch(
    () => props.show,
    (newShow) => {
        if (newShow) {
            pax.value = 0;
            guestName.value = "";
        }
    }
);

const handleClose = () => {
    emit("close");
};

const confirmTakeOrder = () => {
    if (!props.table?.id) {
        return;
    }

    const safePax = ensureNonNegative(normalizePaxValue(pax.value));

    if (safePax < 1) {
        return;
    }

    pax.value = safePax;

    takeOrder(props.table.id, {
        pax: safePax,
        guest_name: guestName.value.trim(),
    });
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

const handleMergeTable = () => {
    emit("mergeTable", props.table);
    handleClose();
};

const handleClaimOrder = () => {
    claimOrder(props.table.id);
    handleClose();
};

const handleTransferNumber = () => {
    emit("transferNumber");
    handleClose();
};

const handleTransferGuest = () => {
    emit("transferGuest");
    handleClose();
};

const handleReserveTable = () => {
    reserveTable(props.table);
    handleClose();
};

const handlePrintBill = async () => {
    if (!props.table?.cart?.id) {
        toast.add({
            severity: "warn",
            summary: "No Active Order",
            detail: "This table does not have a bill to print yet.",
            life: 3000,
        });
        return;
    }

    try {
        isPrintingBill.value = true;
        const response = await fetchBillData(props.table.cart.id);

        if (!response.success || !response.data) {
            throw new Error(response.error || "Failed to fetch bill data");
        }

        await sendBillToPrinter(response.data);

        toast.add({
            severity: "success",
            summary: "Bill Sent",
            detail: "Bill has been sent to the thermal printer.",
            life: 3000,
        });
    } catch (error) {
        console.error("Print bill failed:", error);
        const errorMessage =
            error instanceof Error
                ? error.message
                : (error as { message?: string })?.message ||
                  "Failed to print bill. Please try again.";

        toast.add({
            severity: "error",
            summary: "Print Failed",
            detail: errorMessage,
            life: 4000,
        });
    } finally {
        isPrintingBill.value = false;
    }
};
</script>
