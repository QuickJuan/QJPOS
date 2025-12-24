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
                            Cash Drawer Controls
                        </p>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Record Cash Movement
                        </h1>
                        <p class="text-sm text-gray-600 mt-2">
                            Track every peso that leaves or returns to your
                            drawer so approvals and variance checks stay
                            accurate for this shift.
                        </p>
                    </div>
                    <div
                        class="bg-gray-50 rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-700"
                    >
                        <p class="font-semibold text-gray-900">
                            Active Shift #{{ props.cashierSession.id ?? "--" }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Started
                            {{
                                formatDateTime(
                                    props.cashierSession.started_time
                                )
                            }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Opening Cash
                            {{
                                formatCurrency(
                                    props.cashierSession.beginning_cash
                                )
                            }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-5 flex items-start gap-4"
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
                            Across {{ summary.total_records }} record(s)
                        </p>
                    </div>
                </div>
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-5 flex items-start gap-4"
                >
                    <span class="p-3 rounded-xl bg-emerald-50 text-emerald-600">
                        <ArrowDownTrayIcon class="w-6 h-6" />
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
                            Net Impact
                            {{ formatSignedCurrency(summary.net_cash) }}
                        </p>
                    </div>
                </div>
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-5 flex items-start gap-4"
                >
                    <span class="p-3 rounded-xl bg-amber-50 text-amber-600">
                        <ClockIcon class="w-6 h-6" />
                    </span>
                    <div>
                        <p
                            class="text-sm uppercase tracking-wide text-gray-500"
                        >
                            Pending Decisions
                        </p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ summary.pending_count }} request(s)
                        </p>
                        <p class="text-xs text-gray-500">
                            {{
                                formatCurrency(summary.pending_amount)
                            }}
                            awaiting approval
                        </p>
                    </div>
                </div>
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-5 flex items-start gap-4"
                >
                    <span class="p-3 rounded-xl bg-blue-50 text-blue-600">
                        <ArrowsRightLeftIcon class="w-6 h-6" />
                    </span>
                    <div>
                        <p
                            class="text-sm uppercase tracking-wide text-gray-500"
                        >
                            Latest Movement
                        </p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{
                                summary.last_cash_movement_at
                                    ? formatRelative(
                                          summary.last_cash_movement_at
                                      )
                                    : "No records yet"
                            }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{
                                summary.last_cash_movement_type === "cash_in"
                                    ? "Cash In"
                                    : summary.last_cash_movement_type ===
                                      "cash_out"
                                    ? "Cash Out"
                                    : ""
                            }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <div
                    class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6"
                >
                    <p class="text-sm font-semibold text-gray-900 mb-4">
                        Cash Movement Details
                    </p>
                    <form class="space-y-5" @submit.prevent="handleSubmit">
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-700">
                                Movement Type
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button
                                    v-for="option in typeOptions"
                                    :key="option.value"
                                    type="button"
                                    :class="[
                                        'flex items-center justify-between rounded-2xl border px-4 py-3 text-left transition',
                                        form.type === option.value
                                            ? 'border-primary bg-primary/10 text-primary'
                                            : 'border-gray-200 text-gray-600 hover:border-gray-300',
                                    ]"
                                    @click="form.type = option.value"
                                >
                                    <div class="flex items-center gap-2">
                                        <component
                                            :is="option.icon"
                                            class="w-5 h-5"
                                        />
                                        <span class="text-sm font-semibold">{{
                                            option.label
                                        }}</span>
                                    </div>
                                    <span class="text-[11px] text-gray-500">{{
                                        option.caption
                                    }}</span>
                                </button>
                            </div>
                        </div>

                        <TextField
                            v-model="form.amount"
                            label="Amount"
                            type="number"
                            required
                            placeholder="0.00"
                            :error="form.errors.amount"
                        />

                        <TextField
                            v-model="form.source_name"
                            label="Source / Person"
                            placeholder="Who will receive or return this cash?"
                            required
                            :error="form.errors.source_name"
                        />

                        <TextField
                            v-model="form.purpose"
                            label="Purpose"
                            placeholder="e.g. Emergency supply run"
                            required
                            :error="form.errors.purpose"
                        />

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700"
                                >Notes / Details</label
                            >
                            <textarea
                                v-model="form.details"
                                rows="4"
                                class="w-full rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm p-3"
                                placeholder="Explain why the movement is needed and any informal approvals you already have"
                                required
                            ></textarea>
                            <p
                                v-if="form.errors.details"
                                class="text-sm text-red-600"
                            >
                                {{ form.errors.details }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <button
                                type="button"
                                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50"
                                @click="resetForm"
                            >
                                Clear Form
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-primary-600 disabled:bg-gray-400 disabled:cursor-not-allowed"
                                :disabled="form.processing"
                            >
                                {{
                                    form.processing
                                        ? "Saving..."
                                        : "Submit Request"
                                }}
                            </button>
                        </div>
                    </form>
                </div>

                <div
                    class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">
                                Today's Cash Movements
                            </p>
                            <p class="text-xs text-gray-500">
                                Showing latest
                                {{ props.recentCashouts.length }} record(s)
                            </p>
                        </div>
                        <button
                            type="button"
                            class="text-xs font-semibold text-primary hover:text-primary-600"
                            @click="openLog"
                        >
                            View full log
                        </button>
                    </div>

                    <div v-if="props.recentCashouts.length" class="space-y-4">
                        <article
                            v-for="cashout in props.recentCashouts"
                            :key="cashout.id"
                            class="p-4 rounded-xl border border-gray-100 bg-gray-50"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-[11px] uppercase tracking-wide text-gray-500"
                                    >
                                        {{ cashout.type_label }} •
                                        {{ formatRelative(cashout.created_at) }}
                                    </p>
                                    <p
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ formatCurrency(cashout.amount) }}
                                    </p>
                                </div>
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-xs font-semibold',
                                        statusClasses(cashout.status),
                                    ]"
                                >
                                    {{ cashout.status_label }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-800 mt-2">
                                {{ cashout.purpose }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Source:
                                {{ cashout.source_name || "Unspecified" }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ cashout.details }}
                            </p>
                            <p
                                v-if="
                                    cashout.approver?.name &&
                                    cashout.approved_at_for_humans
                                "
                                class="text-[11px] text-gray-500 mt-1"
                            >
                                {{
                                    cashout.status === "approved"
                                        ? "Approved"
                                        : "Reviewed"
                                }}
                                by {{ cashout.approver.name }} •
                                {{ cashout.approved_at_for_humans }}
                            </p>
                        </article>
                    </div>
                    <div v-else class="text-center py-8 text-sm text-gray-500">
                        No cash movements recorded for this session yet.
                    </div>
                </div>
            </section>
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import TextField from "@/Components/Form/TextField.vue";
import {
    ArrowDownTrayIcon,
    ArrowUpTrayIcon,
    ArrowsRightLeftIcon,
    BanknotesIcon,
    ClockIcon,
} from "@heroicons/vue/24/outline";
import type PageProps from "@/Types/PageProps";

interface CashierSessionSummary {
    id: number;
    business_date: string | null;
    started_time: string | null;
    beginning_cash: number;
}

interface CashoutSummary {
    total_cash_out: number;
    total_cash_in: number;
    net_cash: number;
    pending_amount: number;
    pending_count: number;
    total_records: number;
    last_cash_movement_at?: string | null;
    last_cash_movement_type?: string | null;
}

interface CashoutResource {
    id: number;
    amount: number;
    purpose: string;
    details: string;
    status: string;
    status_label: string;
    type: string;
    type_label: string;
    source_name?: string | null;
    created_at?: string | null;
    created_at_for_humans?: string | null;
    approved_at_for_humans?: string | null;
    approver?: { id: number; name: string } | null;
}

const props = defineProps<{
    summary: CashoutSummary;
    cashierSession: CashierSessionSummary;
    recentCashouts: CashoutResource[];
}>();

const page = usePage<PageProps>();
const toast = useToast();
const summary = props.summary;

const typeOptions = [
    {
        value: "cash_out",
        label: "Cash Out",
        caption: "Funds leaving drawer",
        icon: ArrowUpTrayIcon,
    },
    {
        value: "cash_in",
        label: "Cash In",
        caption: "Funds returned to drawer",
        icon: ArrowDownTrayIcon,
    },
];

const form = useForm({
    type: "cash_out",
    amount: "",
    source_name: "",
    purpose: "",
    details: "",
});

const handleSubmit = () => {
    form.post(route("resto.cashier-cashouts.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset("amount", "source_name", "purpose", "details");
        },
    });
};

const resetForm = () => {
    form.reset();
};

const openLog = () => {
    router.visit(route("resto.cashier-cashouts.index"));
};

const formatCurrency = (value?: number | string | null): string => {
    const numericValue = Number(value || 0);
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 2,
    }).format(numericValue);
};

const formatSignedCurrency = (value?: number | string | null): string => {
    const numericValue = Number(value || 0);
    const formatted = formatCurrency(Math.abs(numericValue));
    return numericValue >= 0 ? `+${formatted}` : `-${formatted}`;
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

const formatRelative = (value?: string | null): string => {
    if (!value) return "--";
    const target = new Date(value).getTime();
    const now = Date.now();
    const diffMinutes = Math.round((now - target) / 60000);

    if (diffMinutes < 1) return "just now";
    if (diffMinutes < 60) return `${diffMinutes} min ago`;
    const diffHours = Math.round(diffMinutes / 60);
    if (diffHours < 24) return `${diffHours} hr${diffHours > 1 ? "s" : ""} ago`;
    const diffDays = Math.round(diffHours / 24);
    return `${diffDays} day${diffDays > 1 ? "s" : ""} ago`;
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

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            toast.add({
                severity: "success",
                summary: "Cash Movement Logged",
                detail: flash.success,
                life: 3000,
            });
        }

        if (flash?.error) {
            toast.add({
                severity: "error",
                summary: "Something went wrong",
                detail: flash.error,
                life: 4000,
            });
        }
    },
    { deep: true }
);
</script>
