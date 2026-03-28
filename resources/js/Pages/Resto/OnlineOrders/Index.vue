<template>
    <CashieringLayout :current-user="props.currentUser">
        <div class="min-h-screen bg-neutral-50">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div
                    class="mb-6 flex flex-col gap-4 rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm lg:flex-row lg:items-end lg:justify-between"
                >
                    <div class="space-y-2">
                        <p
                            class="text-sm font-semibold uppercase tracking-[0.32em] text-orange-600"
                        >
                            Online Customer Orders
                        </p>
                        <h1 class="text-3xl font-black text-neutral-950">
                            Assign and Confirm Orders
                        </h1>
                        <p class="max-w-2xl text-sm leading-7 text-neutral-600">
                            Review customer details, choose a table or room, and
                            send the order into the cashier and kitchen flow.
                        </p>
                    </div>

                    <div
                        class="inline-flex items-center gap-3 rounded-2xl bg-orange-50 px-4 py-3 text-sm font-semibold text-orange-700"
                    >
                        <span>{{ props.orders.length }} pending</span>
                        <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                        <span>{{ props.tableRooms.length }} available table/room slots</span>
                    </div>
                </div>

                <div
                    v-if="props.orders.length === 0"
                    class="rounded-3xl border border-dashed border-neutral-300 bg-white px-6 py-16 text-center shadow-sm"
                >
                    <h2 class="text-2xl font-bold text-neutral-900">
                        No online orders waiting right now
                    </h2>
                    <p class="mt-3 text-sm text-neutral-600">
                        New customer orders will appear here as soon as they are submitted.
                    </p>
                </div>

                <div v-else class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
                    <div class="space-y-5">
                        <article
                            v-for="order in props.orders"
                            :key="order.id"
                            class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm"
                        >
                            <div
                                class="flex flex-col gap-4 border-b border-neutral-200 pb-5 lg:flex-row lg:items-start lg:justify-between"
                            >
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h2 class="text-2xl font-black text-neutral-950">
                                            {{ order.reference_no }}
                                        </h2>
                                        <span
                                            class="rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-orange-700"
                                        >
                                            {{ order.customer.order_type }}
                                        </span>
                                    </div>
                                    <dl
                                        class="grid gap-x-6 gap-y-3 text-sm text-neutral-700 sm:grid-cols-2"
                                    >
                                        <div>
                                            <dt class="font-semibold text-neutral-500">
                                                Customer
                                            </dt>
                                            <dd class="mt-1 text-base font-semibold text-neutral-950">
                                                {{ order.customer.name || "N/A" }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="font-semibold text-neutral-500">
                                                Phone
                                            </dt>
                                            <dd class="mt-1 text-base text-neutral-900">
                                                {{ order.customer.phone || "N/A" }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="font-semibold text-neutral-500">
                                                Email
                                            </dt>
                                            <dd class="mt-1 text-base text-neutral-900">
                                                {{ order.customer.email || "N/A" }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="font-semibold text-neutral-500">
                                                Submitted
                                            </dt>
                                            <dd class="mt-1 text-base text-neutral-900">
                                                {{ formatDateTime(order.submitted_at) }}
                                            </dd>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <dt class="font-semibold text-neutral-500">
                                                Address
                                            </dt>
                                            <dd class="mt-1 text-base text-neutral-900">
                                                {{ order.customer.address || "N/A" }}
                                            </dd>
                                        </div>
                                        <div
                                            v-if="order.customer.notes"
                                            class="sm:col-span-2"
                                        >
                                            <dt class="font-semibold text-neutral-500">
                                                Notes
                                            </dt>
                                            <dd class="mt-1 rounded-2xl bg-neutral-50 px-4 py-3 text-base text-neutral-900">
                                                {{ order.customer.notes }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <div
                                    class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 lg:w-72"
                                >
                                    <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                        Summary
                                    </h3>
                                    <dl class="mt-4 space-y-3 text-sm">
                                        <div class="flex items-center justify-between gap-3">
                                            <dt class="text-neutral-600">Items</dt>
                                            <dd class="font-semibold text-neutral-950">
                                                {{ order.totals.items }}
                                            </dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <dt class="text-neutral-600">Subtotal</dt>
                                            <dd class="font-semibold text-neutral-950">
                                                {{ currency(order.totals.subtotal) }}
                                            </dd>
                                        </div>
                                        <div
                                            v-if="order.totals.discount > 0"
                                            class="flex items-center justify-between gap-3"
                                        >
                                            <dt class="text-neutral-600">
                                                Discount
                                            </dt>
                                            <dd class="font-semibold text-emerald-700">
                                                -{{ currency(order.totals.discount) }}
                                            </dd>
                                        </div>
                                        <div
                                            class="flex items-center justify-between gap-3 border-t border-neutral-200 pt-3"
                                        >
                                            <dt class="text-base font-semibold text-neutral-900">
                                                Total
                                            </dt>
                                            <dd class="text-lg font-black text-neutral-950">
                                                {{ currency(order.totals.total) }}
                                            </dd>
                                        </div>
                                    </dl>

                                    <div
                                        v-if="order.coupon?.code"
                                        class="mt-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                                    >
                                        Coupon <span class="font-bold">{{ order.coupon.code }}</span> applied
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                                <div class="overflow-hidden rounded-2xl border border-neutral-200">
                                    <div
                                        class="grid grid-cols-[minmax(0,1fr)_5rem_7rem] gap-3 bg-neutral-50 px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500"
                                    >
                                        <span>Item</span>
                                        <span class="text-center">Qty</span>
                                        <span class="text-right">Subtotal</span>
                                    </div>
                                    <ul class="divide-y divide-neutral-200">
                                        <li
                                            v-for="item in order.items"
                                            :key="item.id"
                                            class="grid grid-cols-[minmax(0,1fr)_5rem_7rem] gap-3 px-4 py-3 text-sm"
                                        >
                                            <div>
                                                <p class="font-semibold text-neutral-950">
                                                    {{ item.product_name }}
                                                </p>
                                                <p class="text-neutral-500">
                                                    {{ currency(item.price) }} each
                                                </p>
                                            </div>
                                            <div class="text-center font-semibold text-neutral-900">
                                                {{ item.quantity }}
                                            </div>
                                            <div class="text-right font-semibold text-neutral-950">
                                                {{ currency(item.subtotal) }}
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <form
                                    class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4"
                                    @submit.prevent="submitOrder(order)"
                                >
                                    <label
                                        :for="`table-room-${order.id}`"
                                        class="block text-sm font-semibold text-neutral-900"
                                    >
                                        Place this order to a table or room
                                    </label>
                                    <select
                                        :id="`table-room-${order.id}`"
                                        v-model="selectedTableByOrder[order.id]"
                                        class="mt-3 w-full rounded-2xl border border-neutral-300 bg-white px-4 py-3 text-sm text-neutral-900 focus:border-orange-500 focus:outline-none focus:ring-4 focus:ring-orange-100"
                                    >
                                        <option value="">Select table or room</option>
                                        <optgroup
                                            v-for="group in groupedTableRooms"
                                            :key="group.label"
                                            :label="group.label"
                                        >
                                            <option
                                                v-for="tableRoom in group.items"
                                                :key="tableRoom.id"
                                                :value="String(tableRoom.id)"
                                            >
                                                {{ tableRoom.name }} · {{ tableRoom.location_type }}
                                            </option>
                                        </optgroup>
                                    </select>

                                    <p class="mt-3 text-sm leading-6 text-neutral-600">
                                        Once confirmed, the cashier can continue the order from the assigned table or room, and the kitchen will receive it immediately.
                                    </p>

                                    <button
                                        type="submit"
                                        class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:bg-neutral-300"
                                        :disabled="processingOrderId === order.id || !selectedTableByOrder[order.id]"
                                    >
                                        {{
                                            processingOrderId === order.id
                                                ? "Processing..."
                                                : "Confirm and Send to Kitchen"
                                        }}
                                    </button>
                                </form>
                            </div>
                        </article>
                    </div>

                    <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
                        <section
                            class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm"
                        >
                            <h2 class="text-lg font-bold text-neutral-950">
                                Processing Reminder
                            </h2>
                            <ul class="mt-4 space-y-3 text-sm leading-6 text-neutral-600">
                                <li>Check the customer name, phone, and order type before assigning.</li>
                                <li>Pick the destination table or room that will handle the order.</li>
                                <li>After confirmation, the order is pushed to the kitchen screen.</li>
                            </ul>
                        </section>

                        <section
                            class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm"
                        >
                            <h2 class="text-lg font-bold text-neutral-950">
                                Available Tables and Rooms
                            </h2>
                            <ul class="mt-4 space-y-3 text-sm text-neutral-700">
                                <li
                                    v-for="tableRoom in props.tableRooms.slice(0, 8)"
                                    :key="tableRoom.id"
                                    class="flex items-center justify-between gap-3 rounded-2xl bg-neutral-50 px-4 py-3"
                                >
                                    <div>
                                        <p class="font-semibold text-neutral-950">
                                            {{ tableRoom.name }}
                                        </p>
                                        <p class="text-neutral-500">
                                            {{ tableRoom.location_name }} · {{ tableRoom.location_type }}
                                        </p>
                                    </div>
                                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                        {{ tableRoom.status }}
                                    </span>
                                </li>
                            </ul>
                        </section>
                    </aside>
                </div>
            </div>
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref } from "vue";

type OnlineOrder = {
    id: number;
    reference_no: string;
    submitted_at: string | null;
    status: string;
    customer: {
        name: string | null;
        phone: string | null;
        email: string | null;
        address: string | null;
        order_type: string | null;
        notes: string | null;
    };
    coupon?: {
        code?: string;
    } | null;
    totals: {
        items: number;
        subtotal: number;
        discount: number;
        total: number;
    };
    items: Array<{
        id: number;
        product_name: string;
        quantity: number;
        price: number;
        subtotal: number;
    }>;
};

type TableRoomOption = {
    id: number;
    name: string;
    status: string;
    location_name: string | null;
    location_type: string | null;
};

const props = defineProps<{
    orders: OnlineOrder[];
    tableRooms: TableRoomOption[];
    currentUser?: any;
}>();

const processingOrderId = ref<number | null>(null);
const selectedTableByOrder = ref<Record<number, string>>(
    Object.fromEntries(props.orders.map((order) => [order.id, ""])),
);

const groupedTableRooms = computed(() => {
    const grouped = new Map<string, TableRoomOption[]>();

    props.tableRooms.forEach((tableRoom) => {
        const key = tableRoom.location_name || "Other Areas";
        grouped.set(key, [...(grouped.get(key) || []), tableRoom]);
    });

    return Array.from(grouped.entries()).map(([label, items]) => ({
        label,
        items,
    }));
});

const currency = (value: number) =>
    new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(value || 0);

const formatDateTime = (value: string | null) => {
    if (!value) {
        return "N/A";
    }

    return new Intl.DateTimeFormat("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "numeric",
        minute: "2-digit",
    }).format(new Date(value));
};

const submitOrder = (order: OnlineOrder) => {
    const tableRoomId = selectedTableByOrder.value[order.id];

    if (!tableRoomId) {
        return;
    }

    processingOrderId.value = order.id;

    router.post(
        route("resto.online-orders.process", order.id),
        {
            table_room_id: Number(tableRoomId),
        },
        {
            preserveScroll: true,
            onFinish: () => {
                processingOrderId.value = null;
            },
        },
    );
};
</script>
