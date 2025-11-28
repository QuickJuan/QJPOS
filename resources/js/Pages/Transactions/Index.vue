<template>
    <TransactionsLayout>
        <template #header>
            <div>
                <SearchAndFIlter
                    :search="search"
                    :dateRange="dateRange"
                    :status="filters.status"
                    :cashier_id="filters.cashier_id"
                    :statusOptions="statusOptions"
                    :cashierDropdownOptions="cashierDropdownOptions"
                    @search="(value: string) => { search = value }"
                    @dateRange="handleDateRangeChange"
                    @status="(value: string) => { filters.status = value }"
                    @cashier_id="(value: string) => { filters.cashier_id = value }"
                />
            </div>
        </template>
        <div class="flex flex-col h-full py-6 md:py-8 px-3 md:px-6 lg:px-8">
            <div class="space-y-6 h-full bg-red-400">
                <div
                    class="grid grid-cols-1 md:grid-cols-[360px_1fr] gap-6 h-full"
                >
                    <!-- Sidebar -->
                    <div class="space-y-4">
                        <Transactions
                            :orders="orders"
                            :activeOrder="activeOrder"
                            @selectOrder="selectOrder"
                            @goToPage="goToPage"
                        />
                    </div>

                    <!-- Detail Pane -->
                    <div
                        class="bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col"
                    >
                        <div v-if="activeOrder" class="flex flex-col h-full">
                            <div
                                class="px-4 py-5 md:px-6 md:py-6 border-b border-gray-100 sticky top-0 bg-white z-10"
                            >
                                <div
                                    class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between"
                                >
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            {{
                                                activeOrder.table_room
                                                    ?.customer_name ||
                                                "Walk-in Customer"
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="flex flex-wrap items-center gap-3 justify-end"
                                    >
                                        <span
                                            :class="[
                                                'px-3 py-1 rounded-full text-xs font-semibold',
                                                getStatusClass(
                                                    activeOrder.status
                                                ),
                                            ]"
                                        >
                                            {{
                                                getStatusLabel(
                                                    activeOrder.status
                                                )
                                            }}
                                        </span>
                                        <div class="flex flex-wrap gap-2">
                                            <Button
                                                label="Re-print"
                                                icon="pi pi-print"
                                                outlined
                                                :class="subtleActionButtonClass"
                                                @click="
                                                    reprintReceipt(
                                                        activeOrder.id
                                                    )
                                                "
                                            />
                                            <Button
                                                label="Send to Email"
                                                icon="pi pi-envelope"
                                                outlined
                                                :class="subtleActionButtonClass"
                                                @click="
                                                    sendReceiptEmail(
                                                        activeOrder
                                                    )
                                                "
                                            />
                                            <Button
                                                v-if="
                                                    activeOrder.status ===
                                                    'settled'
                                                "
                                                label="Refund"
                                                icon="pi pi-undo"
                                                outlined
                                                :class="
                                                    cautionActionButtonClass
                                                "
                                                @click="
                                                    openRefundModal(activeOrder)
                                                "
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="px-4 py-5 md:px-6 md:py-6 flex-1 space-y-8 overflow-y-auto"
                            >
                                <div class="space-y-1">
                                    <p
                                        class="text-xs uppercase tracking-wide text-gray-400"
                                    >
                                        Cashier
                                    </p>
                                    <p
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{
                                            activeOrder.cashier?.name ||
                                            "Unknown cashier"
                                        }}
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    <p
                                        class="text-xs uppercase tracking-wide text-gray-400"
                                    >
                                        Date
                                    </p>
                                    <p
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{
                                            formatDetailedDate(
                                                activeOrder.created_at
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs uppercase tracking-wide text-gray-400 mb-3"
                                    >
                                        Order Items
                                    </p>

                                    <Receipt
                                        :receipt-id="activeOrder.id.toString()"
                                        :embedded="true"
                                        :order-data="activeOrder"
                                        :receipt-footer="props.receiptFooter"
                                    />
                                </div>

                                <div
                                    v-if="refundMeta"
                                    class="rounded-2xl border border-red-100 bg-red-50/60 p-6 space-y-3"
                                >
                                    <p
                                        class="text-xs uppercase tracking-wide text-red-500"
                                    >
                                        Refund Details
                                    </p>
                                    <p class="text-sm text-gray-700">
                                        Requested by
                                        <span
                                            class="font-semibold text-gray-900"
                                        >
                                            {{ refundMeta.requested_by }}
                                        </span>
                                        &middot; Approved by
                                        <span
                                            class="font-semibold text-gray-900"
                                        >
                                            {{ refundMeta.supervisor }}
                                        </span>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Refunded
                                        {{
                                            formatDetailedDate(
                                                refundMeta.refunded_at
                                            )
                                        }}
                                    </p>
                                    <p class="text-sm text-gray-600 italic">
                                        "{{ refundMeta.notes }}"
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex flex-col items-center justify-center h-full py-16 text-center text-gray-400"
                        >
                            <p class="text-lg font-semibold">
                                No transaction selected
                            </p>
                            <p class="text-sm mt-2">
                                Use the list on the left to pick a receipt to
                                review.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <RefundDialog
            v-model:visible="showRefundModal"
            v-model:notes="refundForm.notes"
            v-model:supervisor-name="refundForm.supervisor_name"
            :loading="refundLoading"
            @submit="submitRefund"
            @closed="handleRefundDialogClosed"
        />
    </TransactionsLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch, reactive } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import axios from "axios";
import TransactionsLayout from "@/Layouts/TransactionsLayout.vue";
import PageProps from "@/Types/PageProps";
import Button from "primevue/button";
import Order from "@/Types/Order/Order";
import SearchAndFIlter from "./Partials/SearchAndFIlter.vue";
import Transactions from "./Partials/Transactions.vue";
import RefundDialog from "./Partials/RefundDialog.vue";
import Receipt from "../Receipt.vue";
import { debounce } from "lodash";
import { filter } from "lodash";

const props = defineProps<{
    orders: any;
    filters: {
        search: string;
        date_from: string;
        date_to: string;
        status: string;
        cashier_id: string;
    };
    receiptFooter: any;
}>();

const page = usePage<PageProps>();
const currentUser = computed(() => page.props?.auth?.user);
const cashierOptions = computed(() => page.props?.cashiers || []);
const cashierDropdownOptions = computed(() =>
    cashierOptions.value.map((cashier: { id: number; name: string }) => ({
        label: cashier.name,
        value: cashier.id,
    }))
);
const statusOptions = [
    { label: "Settled", value: "settled" },
    { label: "Refund", value: "refund" },
    { label: "Credit", value: "credit" },
];

const filters = reactive({
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
    status: props.filters.status || "",
    cashier_id: props.filters.cashier_id || "",
});

const search = ref(props.filters.search || "");

// Compute dateRange from filters
const dateRange = computed<[Date | null, Date | null] | null>(() => {
    if (!filters.date_from && !filters.date_to) return null;
    return [
        filters.date_from ? new Date(filters.date_from) : null,
        filters.date_to ? new Date(filters.date_to) : null,
    ];
});

const handleDateRangeChange = ([start, end]: [
    string | null,
    string | null
]) => {
    filters.date_from = start || "";
    filters.date_to = end || "";
};

const showRefundModal = ref(false);
const activeOrder = ref<Order | null>(null);
const refundOrder = ref<Order | null>(null);
const refundForm = ref({
    notes: "",
    supervisor_name: "",
});
const refundLoading = ref(false);
const refundMeta = computed(() => activeOrder.value?.meta_data?.refund || null);

const reprintReceipt = (orderId: number) => {
    const url = route("receipt", { id: orderId });
    window.open(`${url}?print=1`, "_blank");
};

const sendReceiptEmail = (order: Order) => {
    alert(`Sending receipt #${order.id} to customer's email...`);
};

const formatDetailedDate = (dateString: string) => {
    return new Date(dateString).toLocaleString(undefined, {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "numeric",
        minute: "2-digit",
    });
};

const openRefundModal = (order: Order) => {
    refundOrder.value = order;
    refundForm.value = { notes: "", supervisor_name: "" };
    showRefundModal.value = true;
};

const closeRefundModal = () => {
    showRefundModal.value = false;
    handleRefundDialogClosed();
};

const submitRefund = async () => {
    if (!refundOrder.value) return;

    refundLoading.value = true;
    try {
        await axios.post(
            route("transactions.api.orders.refund", {
                order: refundOrder.value.id,
            }),
            refundForm.value
        );
        closeRefundModal();
    } catch (error) {
        console.error("Error refunding order:", error);
    } finally {
        refundLoading.value = false;
    }
};

const goToPage = (url: string | null) => {
    if (!url) return;
    const urlObj = new URL(url);
    const page = urlObj.searchParams.get("page");
    if (page) {
        const data: any = { page: parseInt(page) };
        if (filters.date_from) data.date_from = filters.date_from;
        if (filters.date_to) data.date_to = filters.date_to;
        if (filters.status) data.status = filters.status;
        if (filters.cashier_id) data.cashier_id = filters.cashier_id;
        if (search.value) data.search = search.value;

        router.get(route("transactions.index"), data, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const selectOrder = (order: any) => {
    activeOrder.value = order;
};

const handleRefundDialogClosed = () => {
    refundOrder.value = null;
};

const subtleActionButtonClass =
    "!rounded-full !border !border-primary/20 !bg-primary/5 !text-primary !px-4 !py-2 !text-sm !font-semibold hover:!bg-primary/10";
const cautionActionButtonClass =
    "!rounded-full !border !border-amber-300 !bg-amber-50 !text-amber-700 !px-4 !py-2 !text-sm !font-semibold hover:!bg-amber-100";

const getStatusClass = (status: string) => {
    switch (status) {
        case "settled":
            return "bg-green-100 text-green-800";
        case "refund":
            return "bg-red-100 text-red-800";
        case "credit":
            return "bg-yellow-100 text-yellow-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case "settled":
            return "Settled";
        case "refund":
            return "Refund";
        case "credit":
            return "Credit";
        default:
            return status;
    }
};

watch(
    search,
    debounce((value: any) => {
        const routeParams = {
            search: value,
        };

        router.visit("/transactions", {
            data: routeParams,
            replace: true,
            only: ["orders"],
            preserveState: true,
            preserveScroll: true,
        });
    }, 250)
);

watch(
    () => filters,
    debounce(() => {
        const data: any = {};
        if (filters.date_from) data.date_from = filters.date_from;
        if (filters.date_to) data.date_to = filters.date_to;
        if (filters.status) data.status = filters.status;
        if (filters.cashier_id) data.cashier_id = filters.cashier_id;
        if (search.value) data.search = search.value;

        router.get(route("transactions.index"), data, {
            preserveScroll: true,
            preserveState: true,
        });
    }, 250),
    { deep: true }
);
</script>
