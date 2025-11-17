<template>
    <CashieringLayout :current-user="currentUser">
        <div class="min-h-screen bg-[#f5f6fb] py-8 px-4 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="grid grid-cols-1 xl:grid-cols-[360px_1fr] gap-6">
                    <!-- Sidebar -->
                    <div class="space-y-4">
                        <SearchAndFIlter
                            :search="filters.search"
                            :dateFrom="dateFrom"
                            :dateTo="dateTo"
                            :status="filters.status"
                            :cashier_id="filters.cashier_id"
                            :statusOptions="statusOptions"
                            :cashierDropdownOptions="cashierDropdownOptions"
                            @search="debouncedSearch"
                            @dateFrom="() => fetchOrders()"
                            @dateTo="() => fetchOrders()"
                            @status="() => fetchOrders()"
                            @cashier_id="() => fetchOrders()"
                        />

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
                            <div class="px-8 py-6 border-b border-gray-100">
                                <div
                                    class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between"
                                >
                                    <div>
                                        <p class="text-sm text-gray-400">
                                            Receipt #{{ activeOrder.id }}
                                        </p>
                                        <h2
                                            class="text-2xl font-bold text-gray-900"
                                        >
                                            Design Review
                                        </h2>
                                        <p class="text-sm text-gray-500">
                                            {{
                                                activeOrder.table_room
                                                    ?.customer_name ||
                                                "Customer"
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
                                                label="View Invoice"
                                                icon="pi pi-eye"
                                                outlined
                                                :class="subtleActionButtonClass"
                                                @click="
                                                    viewReceipt(activeOrder.id)
                                                "
                                            />
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
                                class="px-8 py-6 flex-1 space-y-8 overflow-y-auto"
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
                                    <div
                                        class="rounded-2xl border border-gray-100 divide-y divide-gray-100"
                                    >
                                        <div
                                            class="grid grid-cols-[1.5fr_0.8fr_0.8fr_1fr] text-xs font-semibold uppercase text-gray-400 px-5 py-3 bg-gray-50"
                                        >
                                            <span>Item</span>
                                            <span class="text-center"
                                                >Quantity</span
                                            >
                                            <span class="text-right"
                                                >Price</span
                                            >
                                            <span class="text-right"
                                                >Amount</span
                                            >
                                        </div>
                                        <div
                                            v-if="
                                                activeOrder.order_items &&
                                                activeOrder.order_items.length
                                            "
                                        >
                                            <div
                                                v-for="item in activeOrder.order_items"
                                                :key="item.id"
                                                class="grid grid-cols-[1.5fr_0.8fr_0.8fr_1fr] items-center px-5 py-4 text-sm text-gray-700"
                                            >
                                                <div class="flex flex-col">
                                                    <span class="font-medium">
                                                        {{
                                                            item.product
                                                                ?.name ||
                                                            "Unnamed item"
                                                        }}
                                                    </span>
                                                    <span
                                                        v-if="item.order_type"
                                                        class="text-xs text-gray-400"
                                                    >
                                                        {{ item.order_type }}
                                                    </span>
                                                </div>
                                                <span class="text-center">
                                                    {{ item.quantity }}
                                                </span>
                                                <span class="text-right">
                                                    {{
                                                        formatMoney(item.price)
                                                    }}
                                                </span>
                                                <span
                                                    class="text-right font-semibold"
                                                >
                                                    {{
                                                        formatMoney(
                                                            item.sub_total
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                        <div
                                            v-else
                                            class="px-5 py-6 text-sm text-gray-400"
                                        >
                                            No order items available for this
                                            receipt.
                                        </div>
                                        <div
                                            v-if="
                                                activeOrder.order_items &&
                                                activeOrder.order_items.length
                                            "
                                            class="flex items-center justify-between px-5 py-4 text-sm font-semibold text-gray-900 bg-gray-50"
                                        >
                                            <span>Total</span>
                                            <span>
                                                {{
                                                    formatMoney(orderItemsTotal)
                                                }}
                                            </span>
                                        </div>
                                    </div>
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
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import axios from "axios";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import PageProps from "@/Types/PageProps";
import Button from "primevue/button";
import Order from "@/Types/Order/Order";
import OrderResponse from "@/Types/Order/OrderResponse";
import SearchAndFIlter from "./Partials/SearchAndFIlter.vue";
import Transactions from "./Partials/Transactions.vue";
import RefundDialog from "./Partials/RefundDialog.vue";
import { formatMoney } from "@/Utils/FormatMoney";

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

const orders = ref<OrderResponse>({
    data: [],
    current_page: 1,
    from: 0,
    to: 0,
    total: 0,
    links: [],
});

const filters = ref({
    search: "",
    date_from: "",
    date_to: "",
    status: "",
    cashier_id: "",
});

const dateFrom = ref<Date | null>(null);
const dateTo = ref<Date | null>(null);

const showRefundModal = ref(false);
const activeOrder = ref<Order | null>(null);
const refundOrder = ref<Order | null>(null);
const refundForm = ref({
    notes: "",
    supervisor_name: "",
});
const refundLoading = ref(false);
const refundMeta = computed(() => activeOrder.value?.meta_data?.refund || null);
const receiptPreviewUrl = computed(() =>
    activeOrder.value ? route("receipt", { id: activeOrder.value.id }) : ""
);

const orderItemsTotal = computed(
    () =>
        activeOrder.value?.order_items?.reduce(
            (sum, item) => sum + Number(item.sub_total),
            0
        ) ?? 0
);

// Debounced search
let searchTimeout: NodeJS.Timeout;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchOrders();
    }, 500);
};

const syncActiveOrder = (data: Order[]) => {
    if (!data.length) {
        activeOrder.value = null;
        return;
    }

    if (activeOrder.value) {
        const stillExists = data.find(
            (order) => order.id === activeOrder.value?.id
        );
        activeOrder.value = stillExists || data[0];
    } else {
        activeOrder.value = data[0];
    }
};

const fetchOrders = async (page = 1) => {
    try {
        const params = new URLSearchParams({
            page: page.toString(),
            ...Object.fromEntries(
                Object.entries(filters.value).filter(
                    ([_, value]) => value !== ""
                )
            ),
        });

        const response = await axios.get(
            route("transactions.api.orders") + "?" + params
        );
        orders.value = response.data;
        syncActiveOrder(response.data.data || []);
    } catch (error) {
        console.error("Error fetching orders:", error);
    }
};

const viewReceipt = (orderId: number) => {
    window.open(route("receipt", { id: orderId }), "_blank");
};

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
        fetchOrders();
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
        fetchOrders(parseInt(page));
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

// Watchers to sync Date objects with string filters
watch(dateFrom, (newDate) => {
    filters.value.date_from = newDate
        ? newDate.toISOString().split("T")[0]
        : "";
});

watch(dateTo, (newDate) => {
    filters.value.date_to = newDate ? newDate.toISOString().split("T")[0] : "";
});

watch(
    () => filters.value.status,
    (value) => {
        if (value === null) {
            filters.value.status = "";
        }
    }
);

watch(
    () => filters.value.cashier_id,
    (value) => {
        if (value === null) {
            filters.value.cashier_id = "";
        }
    }
);

// Initialize dates from filters if they exist
const initializeDates = () => {
    if (filters.value.date_from) {
        dateFrom.value = new Date(filters.value.date_from);
    }
    if (filters.value.date_to) {
        dateTo.value = new Date(filters.value.date_to);
    }
};

onMounted(() => {
    initializeDates();
    fetchOrders();
});
</script>
