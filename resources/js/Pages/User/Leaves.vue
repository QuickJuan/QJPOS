<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import {
    CalendarDaysIcon,
    PlusIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";
import {
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
    NoSymbolIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    leaveRequests: Object,
    leaveCredits: Array,
    leaveTypes: Array,
});

const page = usePage();

const showForm = ref(false);

const form = useForm({
    leave_type_id: "",
    start_date: "",
    end_date: "",
    days_requested: "",
    is_half_day: false,
    reason: "",
});

// Auto-compute days when dates change
const computeDays = () => {
    if (form.start_date && form.end_date && !form.is_half_day) {
        const start = new Date(form.start_date);
        const end = new Date(form.end_date);
        if (end >= start) {
            const diff = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
            form.days_requested = diff;
        }
    }
    if (form.is_half_day) {
        form.days_requested = 0.5;
    }
};

const submit = () => {
    form.post(route("user.leaves.store"), {
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
};

const statusConfig = {
    pending: {
        label: "Pending",
        color: "bg-amber-100 text-amber-700",
        icon: ClockIcon,
    },
    approved: {
        label: "Approved",
        color: "bg-green-100 text-green-700",
        icon: CheckCircleIcon,
    },
    rejected: {
        label: "Rejected",
        color: "bg-red-100 text-red-700",
        icon: XCircleIcon,
    },
    cancelled: {
        label: "Cancelled",
        color: "bg-gray-100 text-gray-500",
        icon: NoSymbolIcon,
    },
};
</script>

<template>
    <AppLayout title="My Leaves">
        <template #header>
            <div class="flex items-center gap-3">
                <CalendarDaysIcon class="h-6 w-6 text-gray-500" />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    My Leaves
                </h2>
            </div>
        </template>

        <div class="max-w-5xl mx-auto py-10 sm:px-6 lg:px-8 space-y-8">
            <!-- Flash success -->
            <div
                v-if="$page.props.flash?.success"
                class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
            >
                {{ $page.props.flash.success }}
            </div>

            <!-- Leave Credits Summary -->
            <div v-if="leaveCredits.length">
                <h3 class="text-base font-semibold text-gray-700 mb-3">
                    Leave Balance ({{ new Date().getFullYear() }})
                </h3>
                <div
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <div
                        v-for="credit in leaveCredits"
                        :key="credit.id"
                        class="rounded-2xl border bg-white p-4 shadow-sm"
                        :class="
                            credit.remaining_days > 0
                                ? 'border-gray-200'
                                : 'border-red-200'
                        "
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-700/20"
                            >
                                {{ credit.leave_type_code }}
                            </span>
                            <span
                                class="text-xs font-bold"
                                :class="
                                    credit.remaining_days > 0
                                        ? 'text-green-600'
                                        : 'text-red-500'
                                "
                            >
                                {{ credit.remaining_days }}d left
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 truncate">
                            {{ credit.leave_type }}
                        </p>
                        <div
                            class="mt-2 h-1.5 w-full rounded-full bg-gray-200 overflow-hidden"
                        >
                            <div
                                class="h-1.5 rounded-full"
                                :class="
                                    credit.remaining_days > 0
                                        ? 'bg-green-500'
                                        : 'bg-red-500'
                                "
                                :style="`width: ${credit.total_days > 0 ? Math.min(100, (credit.used_days / credit.total_days) * 100) : 0}%`"
                            />
                        </div>
                        <p class="mt-1 text-[10px] text-gray-400">
                            {{ credit.used_days }} /
                            {{ credit.total_days }} used
                        </p>
                    </div>
                </div>
            </div>

            <!-- File New Leave Button / Form -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-700">
                        Leave Requests
                    </h3>
                    <button
                        class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-600 transition"
                        @click="showForm = !showForm"
                    >
                        <component
                            :is="showForm ? XMarkIcon : PlusIcon"
                            class="h-4 w-4"
                        />
                        {{ showForm ? "Cancel" : "File Leave Request" }}
                    </button>
                </div>

                <!-- New Leave Form -->
                <div
                    v-if="showForm"
                    class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
                >
                    <h4 class="text-sm font-semibold text-gray-700 mb-4">
                        New Leave Request
                    </h4>

                    <form class="space-y-4" @submit.prevent="submit">
                        <!-- No employee record error -->
                        <div
                            v-if="form.errors.employee_id"
                            class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700"
                        >
                            {{ form.errors.employee_id }}
                        </div>

                        <!-- Leave Type -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Leave Type
                                <span class="text-red-500">*</span></label
                            >
                            <select
                                v-model="form.leave_type_id"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                required
                            >
                                <option value="" disabled>
                                    Select leave type...
                                </option>
                                <option
                                    v-for="lt in leaveTypes"
                                    :key="lt.id"
                                    :value="lt.id"
                                >
                                    {{ lt.code }} — {{ lt.name }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.leave_type_id"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.leave_type_id }}
                            </p>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Start Date
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="form.start_date"
                                    type="date"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    required
                                    @change="computeDays"
                                    @input="computeDays"
                                />
                                <p
                                    v-if="form.errors.start_date"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ form.errors.start_date }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >End Date
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="form.end_date"
                                    type="date"
                                    :min="form.start_date"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    required
                                    @change="computeDays"
                                    @input="computeDays"
                                />
                                <p
                                    v-if="form.errors.end_date"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ form.errors.end_date }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Days Requested
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="form.days_requested"
                                    type="number"
                                    step="0.5"
                                    min="0.5"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    required
                                    placeholder="e.g. 1 or 0.5"
                                />
                                <p class="mt-0.5 text-[10px] text-gray-400">
                                    0.5 = half day
                                </p>
                                <p
                                    v-if="form.errors.days_requested"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ form.errors.days_requested }}
                                </p>
                            </div>
                        </div>

                        <!-- Half day toggle -->
                        <label
                            class="inline-flex items-center gap-2 cursor-pointer"
                        >
                            <input
                                v-model="form.is_half_day"
                                type="checkbox"
                                class="rounded border-gray-300 text-primary-500 focus:ring-primary-500"
                                @change="computeDays"
                            />
                            <span class="text-sm text-gray-700"
                                >Half day (0.5 days)</span
                            >
                        </label>

                        <!-- Reason -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Reason / Details</label
                            >
                            <textarea
                                v-model="form.reason"
                                rows="3"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                placeholder="Optional — briefly describe your reason for leave."
                            />
                            <p
                                v-if="form.errors.reason"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.reason }}
                            </p>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-600 disabled:opacity-50 transition"
                            >
                                {{
                                    form.processing
                                        ? "Submitting..."
                                        : "Submit Request"
                                }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Leave Request List -->
                <div
                    v-if="leaveRequests.data.length"
                    class="rounded-2xl border border-gray-200 bg-white shadow-sm divide-y divide-gray-100"
                >
                    <div
                        v-for="req in leaveRequests.data"
                        :key="req.id"
                        class="flex items-start justify-between gap-4 p-5"
                    >
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span
                                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-700/20"
                                >
                                    {{ req.leave_type_code }}
                                </span>
                                <span
                                    class="text-sm font-medium text-gray-800"
                                    >{{ req.leave_type }}</span
                                >
                                <span
                                    v-if="req.is_half_day"
                                    class="text-xs text-gray-400"
                                    >(Half day)</span
                                >
                            </div>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ req.start_date }} — {{ req.end_date }}
                                <span class="ml-2 text-xs text-gray-400"
                                    >{{ req.days_requested }} day(s)</span
                                >
                            </p>
                            <p
                                v-if="req.reason"
                                class="mt-1 text-xs text-gray-400 truncate"
                            >
                                {{ req.reason }}
                            </p>
                            <!-- Approval detail -->
                            <div
                                v-if="req.status === 'approved'"
                                class="mt-2 flex gap-3 text-xs"
                            >
                                <span class="text-green-600 font-medium"
                                    >{{ req.days_with_pay }}d paid</span
                                >
                                <span
                                    v-if="req.days_without_pay > 0"
                                    class="text-red-500 font-medium"
                                    >{{ req.days_without_pay }}d LWOP</span
                                >
                            </div>
                            <p
                                v-if="
                                    req.admin_notes && req.status !== 'pending'
                                "
                                class="mt-1 text-xs text-gray-500 italic"
                            >
                                Note: {{ req.admin_notes }}
                            </p>
                        </div>

                        <!-- Status badge -->
                        <div class="flex-shrink-0">
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="
                                    statusConfig[req.status]?.color ??
                                    'bg-gray-100 text-gray-500'
                                "
                            >
                                <component
                                    :is="statusConfig[req.status]?.icon"
                                    class="h-3.5 w-3.5"
                                />
                                {{
                                    statusConfig[req.status]?.label ??
                                    req.status
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-else-if="!showForm"
                    class="text-center py-16 rounded-2xl border border-dashed border-gray-300 bg-white"
                >
                    <CalendarDaysIcon class="mx-auto h-12 w-12 text-gray-300" />
                    <p class="mt-4 text-gray-500 text-sm">
                        No leave requests yet.
                    </p>
                    <p class="text-gray-400 text-xs mt-1">
                        Click "File Leave Request" to submit one.
                    </p>
                </div>

                <!-- Pagination -->
                <div
                    v-if="leaveRequests.last_page > 1"
                    class="mt-6 flex justify-center gap-2"
                >
                    <a
                        v-for="link in leaveRequests.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 rounded-lg text-sm border transition"
                        :class="
                            link.active
                                ? 'bg-primary-500 text-white border-primary-500'
                                : link.url
                                  ? 'border-gray-300 text-gray-600 hover:border-primary-400 hover:text-primary-600'
                                  : 'border-gray-200 text-gray-300 cursor-not-allowed pointer-events-none'
                        "
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
