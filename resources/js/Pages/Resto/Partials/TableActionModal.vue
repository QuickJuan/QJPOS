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
                v-if="table?.mergedTo"
                class="mt-4 rounded-lg border border-dashed border-gray-200 bg-gray-50 px-3 py-2 text-xs"
            >
                <p class="font-semibold text-gray-700">
                    Merged into {{ table.mergedToTable }}
                </p>
                <p class="text-gray-500">Original: {{ table.name }}</p>
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
                    />

                    <Button
                        v-if="
                            table &&
                            (table.status === 'occupied' ||
                                table.status === 'reserved') &&
                            (!cartSummary ||
                                !cartSummary.runningTotal ||
                                cartSummary.runningTotal === 0)
                        "
                        label="Vacant Table"
                        icon="pi pi-check-circle"
                        class="table-action-btn w-full h-14 justify-start gap-3 rounded-xl text-left"
                        severity="success"
                        outlined
                        @click="handleVacantTable"
                    />

                    <Button
                        v-if="hasMergedTables"
                        label="Unmerge Tables"
                        icon="pi pi-unlink"
                        class="table-action-btn w-full h-14 justify-start gap-3 rounded-xl text-left"
                        severity="danger"
                        outlined
                        @click="handleUnmergeTables"
                    />

                    <Button
                        v-if="table?.mergedTo"
                        label="Leave Merge"
                        icon="pi pi-sign-out"
                        class="table-action-btn w-full h-14 justify-start gap-3 rounded-xl text-left"
                        severity="secondary"
                        outlined
                        @click="handleUnmergeFromTable"
                    />

                    <!-- <Button
                        v-if="
                            !isTakeoutOccupied &&
                            table &&
                            table.status === 'available' &&
                            !table?.mergedTo
                        "
                        label="Reserve Table"
                        icon="pi pi-calendar-plus"
                        class="table-action-btn w-full h-14 justify-start gap-3 rounded-xl text-left"
                        severity="info"
                        outlined
                        @click="handleReserveTable"
                    /> -->

                    <Button
                        v-if="
                            !isTakeoutOccupied &&
                            table &&
                            table.status !== 'available' &&
                            (!cartSummary ||
                                !cartSummary.runningTotal ||
                                cartSummary.runningTotal === 0)
                        "
                        label="Vacate Table"
                        icon="pi pi-door-open"
                        class="table-action-btn w-full h-14 justify-start gap-3 rounded-xl text-left"
                        severity="danger"
                        outlined
                        @click="vacantTable"
                    />
                </div>

                <div
                    v-if="
                        table &&
                        table.status === 'available' &&
                        !table?.mergedTo
                    "
                    class="mt-5 border-t border-dashed pt-5"
                >
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
                        <div class="flex-1 space-y-5">
                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div>
                                    <h4
                                        class="text-base font-semibold text-gray-900"
                                    >
                                        {{ locationName }}
                                        <span
                                            :class="statusBadgeClass"
                                            class="mt-1 inline-flex w-fit items-center"
                                        >
                                            {{ statusLabel }}
                                        </span>
                                    </h4>
                                    <p class="text-sm text-gray-500"></p>
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

                            <div class="space-y-4">
                                <TextField
                                    label="Number of Pax"
                                    v-model="pax"
                                    :min="0"
                                    class="w-full"
                                    :placeholder="paxPlaceholder"
                                    type="number"
                                />

                                <div class="space-y-1">
                                    <label
                                        class="text-sm font-medium text-gray-700"
                                        for="customer-search"
                                    >
                                        Customer (optional)
                                    </label>
                                    <div class="relative">
                                        <input
                                            id="customer-search"
                                            type="text"
                                            v-model="customerSearchQuery"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                            placeholder="Search customers or leave blank for walk-in"
                                            autocomplete="off"
                                        />
                                        <span
                                            v-if="
                                                isCustomerSearchLoading &&
                                                !selectedCustomer
                                            "
                                            class="absolute inset-y-0 right-3 flex items-center text-gray-400"
                                        >
                                            <i
                                                class="pi pi-spin pi-spinner text-sm"
                                            ></i>
                                        </span>
                                        <button
                                            v-if="selectedCustomer"
                                            type="button"
                                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 transition hover:text-gray-600"
                                            @click="clearSelectedCustomer"
                                            aria-label="Clear selected customer"
                                        >
                                            <i class="pi pi-times"></i>
                                        </button>

                                        <div
                                            v-if="showCustomerDropdown"
                                            class="absolute left-0 right-0 top-full z-30 mt-1 max-h-60 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-xl"
                                        >
                                            <button
                                                v-for="customer in customerResults"
                                                :key="customer.id"
                                                type="button"
                                                class="flex w-full flex-col items-start px-3 py-2 text-left transition hover:bg-gray-50"
                                                @click="
                                                    selectCustomer(customer)
                                                "
                                            >
                                                <span
                                                    class="text-sm font-semibold text-gray-900"
                                                >
                                                    {{ customer.customer_name }}
                                                </span>
                                                <span
                                                    v-if="
                                                        customer.contact_no ||
                                                        customer.email
                                                    "
                                                    class="text-xs text-gray-500"
                                                >
                                                    {{
                                                        customer.contact_no ||
                                                        customer.email
                                                    }}
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        {{ customerHelperText }}
                                    </p>
                                    <p
                                        v-if="customerSearchError"
                                        class="text-xs text-red-500"
                                    >
                                        {{ customerSearchError }}
                                    </p>
                                </div>
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

                        <div
                            class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm lg:w-64"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Guest Keypad
                            </p>
                            <div class="mt-4 space-y-2">
                                <div
                                    v-for="(row, rowIndex) in keypadRows"
                                    :key="`keypad-row-${rowIndex}`"
                                    class="grid grid-cols-3 gap-2"
                                >
                                    <button
                                        v-for="entry in row"
                                        :key="entry"
                                        type="button"
                                        class="rounded-xl border border-gray-200 bg-gray-50 py-3 text-lg font-semibold text-gray-700 transition hover:border-primary hover:bg-primary/5 hover:text-primary"
                                        @click="handleKeypadPress(entry)"
                                    >
                                        {{ entry }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="entry in keypadActionRow"
                                        :key="`action-${entry}`"
                                        type="button"
                                        class="rounded-xl border border-gray-200 bg-gray-50 py-3 text-sm font-semibold text-gray-700 transition hover:border-primary hover:bg-primary/5 hover:text-primary"
                                        @click="handleKeypadPress(entry)"
                                    >
                                        <span v-if="entry === 'clear'">
                                            Clear
                                        </span>
                                        <span v-else-if="entry === 'backspace'">
                                            Del
                                        </span>
                                        <span v-else>
                                            {{ entry }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <p class="mt-3 text-center text-xs text-gray-400">
                                Keypad buttons append digits to guest count.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed, onBeforeUnmount } from "vue";
import axios from "axios";
import { route } from "ziggy-js";
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

type KeypadEntry = number | "clear" | "backspace";

const keypadRows: number[][] = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];
const keypadActionRow: KeypadEntry[] = ["clear", 0, "backspace"];
const MAX_PAX = 999;

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
const customerSearchQuery = ref("");
const customerResults = ref<any[]>([]);
const selectedCustomer = ref<any | null>(null);
const customerSearchError = ref<string | null>(null);
const isCustomerSearchLoading = ref(false);
const lastSearchTerm = ref("");
let customerSearchTimeout: ReturnType<typeof setTimeout> | null = null;
const WALK_IN_LABEL = "Walk-in Guest";
const MIN_CUSTOMER_SEARCH_LENGTH = 2;

const hasValidPax = computed(() => normalizePaxValue(pax.value) >= 1);

const paxPlaceholder = computed(() => {
    const current = ensureNonNegative(normalizePaxValue(pax.value));
    if (!current) {
        return "Enter number of guests";
    }
    return `Enter number of guests (current: ${current})`;
});

const showCustomerDropdown = computed(() => {
    const trimmedQuery = customerSearchQuery.value.trim();
    return (
        !selectedCustomer.value &&
        Boolean(trimmedQuery) &&
        trimmedQuery.length >= MIN_CUSTOMER_SEARCH_LENGTH &&
        customerResults.value.length > 0
    );
});

const customerHelperText = computed(() => {
    if (selectedCustomer.value) {
        return "Customer will be linked to this cart.";
    }

    return "Leave blank if this is a walk-in guest.";
});

const appendPaxDigit = (digit: number) => {
    const safeDigit = Number(digit);
    if (!Number.isFinite(safeDigit)) {
        return;
    }

    const currentValue = ensureNonNegative(normalizePaxValue(pax.value));
    const currentString = currentValue.toString();
    const nextString =
        currentString === "0"
            ? String(safeDigit)
            : `${currentString}${safeDigit}`;
    const nextValue = ensureNonNegative(Number(nextString));
    pax.value = Math.min(nextValue, MAX_PAX);
};

const removeLastPaxDigit = () => {
    const currentString = ensureNonNegative(
        normalizePaxValue(pax.value)
    ).toString();
    const trimmed =
        currentString.length <= 1 ? "0" : currentString.slice(0, -1);
    pax.value = ensureNonNegative(Number(trimmed));
};

const handleKeypadPress = (entry: KeypadEntry) => {
    if (entry === "clear") {
        resetPax();
        return;
    }

    if (entry === "backspace") {
        removeLastPaxDigit();
        return;
    }

    appendPaxDigit(entry);
};

const resetPax = () => {
    pax.value = 0;
};

const resetCustomerSelection = () => {
    customerSearchQuery.value = "";
    customerResults.value = [];
    selectedCustomer.value = null;
    customerSearchError.value = null;
    isCustomerSearchLoading.value = false;
    if (customerSearchTimeout) {
        clearTimeout(customerSearchTimeout);
        customerSearchTimeout = null;
    }
};

const fetchCustomers = async (term: string) => {
    try {
        isCustomerSearchLoading.value = true;
        customerSearchError.value = null;
        const { data } = await axios.get(route("customers.search"), {
            params: { query: term },
        });

        if (Array.isArray(data)) {
            customerResults.value = data;
        } else if (Array.isArray(data?.data)) {
            customerResults.value = data.data;
        } else {
            customerResults.value = [];
        }
    } catch (error) {
        console.error("Customer search failed", error);
        customerResults.value = [];
        customerSearchError.value =
            "Unable to load customers right now. Please try again.";
    } finally {
        isCustomerSearchLoading.value = false;
        lastSearchTerm.value = term;
    }
};

const handleCustomerSearch = (nextQuery: string) => {
    const trimmed = nextQuery.trim();

    if (customerSearchTimeout) {
        clearTimeout(customerSearchTimeout);
        customerSearchTimeout = null;
    }

    if (!trimmed) {
        customerResults.value = [];
        selectedCustomer.value = null;
        customerSearchError.value = null;
        lastSearchTerm.value = "";
        return;
    }

    if (
        selectedCustomer.value &&
        trimmed === (selectedCustomer.value.customer_name ?? "").trim()
    ) {
        customerResults.value = [];
        customerSearchError.value = null;
        return;
    }

    if (trimmed.length < MIN_CUSTOMER_SEARCH_LENGTH) {
        customerResults.value = [];
        customerSearchError.value = null;
        lastSearchTerm.value = "";
        return;
    }

    if (trimmed === lastSearchTerm.value) {
        return;
    }

    customerSearchTimeout = setTimeout(() => {
        fetchCustomers(trimmed);
    }, 300);
};

watch(customerSearchQuery, handleCustomerSearch);

watch(
    () => props.show,
    (newShow) => {
        if (newShow) {
            pax.value = 0;
            resetCustomerSelection();
        } else {
            resetCustomerSelection();
        }
    }
);

onBeforeUnmount(() => {
    if (customerSearchTimeout) {
        clearTimeout(customerSearchTimeout);
    }
});

const selectCustomer = (customer: any) => {
    if (!customer) {
        return;
    }

    selectedCustomer.value = customer;
    customerSearchQuery.value = customer.customer_name ?? "";
    customerResults.value = [];
    customerSearchError.value = null;
};

const clearSelectedCustomer = () => {
    resetCustomerSelection();
};

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
        guest_name: selectedCustomer.value?.customer_name ?? WALK_IN_LABEL,
        customer_id: selectedCustomer.value?.id ?? null,
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

const handleVacantTable = () => {
    if (!props.table?.id) {
        return;
    }
    vacantTableAction(props.table.id);
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
