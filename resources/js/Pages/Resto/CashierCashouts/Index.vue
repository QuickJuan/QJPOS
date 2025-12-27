<template>
    <CashieringLayout>
        <div class="max-w-6xl mx-auto px-4 lg:px-0 py-8 space-y-8">
            <section
                class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6"
            >
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <p
                            class="text-sm uppercase tracking-wide text-gray-500"
                        >
                            Cash Drawer Oversight
                        </p>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Cash In / Cash Out Log
                        </h1>
                        <p class="text-sm text-gray-600 mt-2">
                            Review submission history, monitor approvals, and
                            keep every adjustment tied to a clear purpose.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold shadow-sm hover:bg-primary-600"
                        @click="
                            router.visit(route('resto.cashier-cashouts.create'))
                        "
                    >
                        Log Movement
                    </button>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-2">
                <div
                    class="bg-white border border-gray-200 rounded-2xl p-5 flex items-start gap-4"
                >
                    <span class="p-3 rounded-xl bg-emerald-50 text-emerald-600">
                        <ArrowTrendingDownIcon class="w-6 h-6" />
                    </span>
                    <div>
                        <p
                            class="text-sm uppercase tracking-wide text-gray-500"
                        >
                            Cash In Logged
                        </p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ formatCurrency(summary.total_cash_in) }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Funds returned to drawer
                        </p>
                    </div>
                </div>
                <div
                    class="bg-white border border-gray-200 rounded-2xl p-5 flex items-start gap-4"
                >
                    <span class="p-3 rounded-xl bg-rose-50 text-rose-600">
                        <BanknotesIcon class="w-6 h-6" />
                    </span>
                    <div>
                        <p
                            class="text-sm uppercase tracking-wide text-gray-500"
                        >
                            Cash Out Logged
                        </p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ formatCurrency(summary.total_cash_out) }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Total removed from drawer
                        </p>
                    </div>
                </div>
            </section>

            <section
                class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 space-y-4"
            >
                <div class="grid gap-4 lg:grid-cols-4">
                    <div class="lg:col-span-2">
                        <label
                            class="block text-xs font-semibold text-gray-500 mb-1"
                            >Search</label
                        >
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Source, purpose, or details"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary"
                            @keydown.enter.prevent="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 mb-1"
                            >Status</label
                        >
                        <select
                            v-model="filters.status"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm bg-white"
                        >
                            <option value="">All</option>
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 mb-1"
                            >Type</label
                        >
                        <select
                            v-model="filters.type"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm bg-white"
                        >
                            <option value="">All</option>
                            <option
                                v-for="option in typeOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div
                    class="grid gap-4 lg:grid-cols-4 pt-4 border-t border-gray-100"
                >
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 mb-1"
                            >Date From</label
                        >
                        <input
                            v-model="filters.date_from"
                            type="date"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 mb-1"
                            >Date To</label
                        >
                        <input
                            v-model="filters.date_to"
                            type="date"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 mb-1"
                            >Per Page</label
                        >
                        <select
                            v-model.number="filters.per_page"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm bg-white"
                            @change="applyFilters"
                        >
                            <option :value="10">10</option>
                            <option :value="15">15</option>
                            <option :value="25">25</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-3">
                        <button
                            type="button"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            @click="resetFilters"
                        >
                            Reset
                        </button>
                        <button
                            type="button"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold shadow-sm hover:bg-primary-600"
                            @click="applyFilters"
                        >
                            Apply
                        </button>
                    </div>
                </div>
            </section>

            <section
                class="bg-white border border-gray-200 rounded-2xl shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead
                            class="bg-gray-50 text-left text-xs font-semibold text-gray-500"
                        >
                            <tr>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Source / Purpose</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Approver</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="entry in tableRows"
                                :key="entry.id"
                                class="odd:bg-white even:bg-gray-50 border-b border-gray-100"
                            >
                                <td class="px-6 py-4 align-top">
                                    <p class="font-medium text-gray-900">
                                        {{ formatDateTime(entry.created_at) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ entry.created_at_for_humans || "" }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-700"
                                    >
                                        {{ entry.type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <p class="font-medium text-gray-900">
                                        {{ entry.source_name || "Unspecified" }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ entry.purpose }} —
                                        {{ entry.details }}
                                    </p>
                                </td>
                                <td
                                    class="px-6 py-4 align-top text-right font-semibold text-gray-900"
                                >
                                    {{ formatCurrency(entry.amount) }}
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold',
                                            statusClasses(entry.status),
                                        ]"
                                    >
                                        {{ entry.status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-top text-gray-700">
                                    <p
                                        v-if="entry.approver?.name"
                                        class="font-medium"
                                    >
                                        {{ entry.approver.name }}
                                    </p>
                                    <p v-else class="text-gray-400">—</p>
                                    <p
                                        v-if="entry.approved_at_for_humans"
                                        class="text-xs text-gray-500"
                                    >
                                        {{ entry.approved_at_for_humans }}
                                    </p>
                                </td>
                            </tr>
                            <tr v-if="!tableRows.length">
                                <td
                                    colspan="6"
                                    class="px-6 py-8 text-center text-sm text-gray-500"
                                >
                                    No cash movements match the current filters.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 border-t border-gray-200 text-sm text-gray-600"
                >
                    <p>
                        Showing
                        <span class="font-semibold text-gray-900">
                            {{
                                totalItems
                                    ? (currentPage - 1) * filters.per_page + 1
                                    : 0
                            }}
                        </span>
                        to
                        <span class="font-semibold text-gray-900">
                            {{
                                Math.min(
                                    currentPage * filters.per_page,
                                    totalItems
                                )
                            }}
                        </span>
                        of
                        <span class="font-semibold text-gray-900">{{
                            totalItems
                        }}</span>
                        records
                    </p>
                    <div class="flex items-center gap-2">
                        <button
                            v-for="link in pageLinks"
                            :key="link.label"
                            :disabled="!link.url"
                            v-html="link.label"
                            :class="[
                                'min-w-[40px] px-3 py-1.5 rounded-lg text-sm font-medium border transition',
                                link.active
                                    ? 'bg-primary text-white border-primary'
                                    : link.url
                                    ? 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
                                    : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed',
                            ]"
                            @click="goToPage(link.url)"
                        />
                    </div>
                </div>
            </section>
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { computed, reactive } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import {
    ArrowTrendingDownIcon,
    BanknotesIcon,
} from "@heroicons/vue/24/outline";

type CashoutResource = {
    id: number;
    type: string;
    type_label: string;
    status: string;
    status_label: string;
    source_name: string | null;
    purpose: string;
    details: string;
    amount: number;
    created_at?: string | null;
    created_at_for_humans?: string | null;
    approved_at_for_humans?: string | null;
    approver?: { id: number; name: string } | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type CashoutCollection = {
    data: CashoutResource[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginationLink[];
    };
};

type CashoutSummary = {
    pending_count: number;
    pending_amount: number;
    total_cash_in: number;
    total_cash_out: number;
    total_records: number;
    net_cash: number;
};

const props = defineProps<{
    cashouts: CashoutCollection;
    filters: Record<string, any>;
    summary: CashoutSummary;
}>();

const summary = props.summary;

const filters = reactive({
    search: props.filters.search ?? "",
    status: props.filters.status ?? "",
    type: props.filters.type ?? "",
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
    per_page: Number(props.filters.per_page ?? 10) || 10,
});

const tableRows = computed(() => props.cashouts?.data ?? []);
const pageLinks = computed(() => props.cashouts?.meta?.links ?? []);
const currentPage = computed(() => props.cashouts?.meta?.current_page ?? 1);
const totalItems = computed(() => props.cashouts?.meta?.total ?? 0);

const statusOptions = [
    { label: "Pending", value: "pending" },
    { label: "Approved", value: "approved" },
    { label: "Rejected", value: "rejected" },
];

const typeOptions = [
    { label: "Cash Out", value: "cash_out" },
    { label: "Cash In", value: "cash_in" },
];

const applyFilters = () => {
    router.get(
        route("resto.cashier-cashouts.index"),
        { ...filters },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const resetFilters = () => {
    filters.search = "";
    filters.status = "";
    filters.type = "";
    filters.date_from = "";
    filters.date_to = "";
    filters.per_page = 10;
    applyFilters();
};

const goToPage = (url: string | null) => {
    if (!url) return;
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatCurrency = (value?: number | string | null): string => {
    const numericValue = Number(value || 0);
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 2,
    }).format(numericValue);
};

const formatDateTime = (value?: string | null): string => {
    if (!value) return "--";
    return new Intl.DateTimeFormat("en-US", {
        month: "short",
        day: "numeric",
        hour: "numeric",
        minute: "numeric",
    }).format(new Date(value));
};

const statusClasses = (status: string) => {
    if (status === "approved") {
        return "bg-emerald-100 text-emerald-700";
    }
    if (status === "rejected") {
        return "bg-rose-100 text-rose-700";
    }
    return "bg-amber-100 text-amber-700";
};
</script>
