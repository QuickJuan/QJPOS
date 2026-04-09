<template>
    <HomeLayout>
        <section
            class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-950 px-6 py-8 text-white shadow-[0_30px_80px_-45px_rgba(15,23,42,0.95)] sm:px-8 lg:px-10"
        >
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.3),_transparent_38%),radial-gradient(circle_at_bottom_right,_rgba(16,185,129,0.24),_transparent_34%)]"
            />
            <div class="relative">
                <div class="space-y-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-sky-100"
                        >
                            Operations hub
                        </span>
                        <span
                            class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold"
                            :class="
                                isClockedIn
                                    ? 'border-emerald-400/40 bg-emerald-400/10 text-emerald-100'
                                    : 'border-white/15 bg-white/10 text-slate-100'
                            "
                        >
                            <span
                                class="h-2 w-2 rounded-full"
                                :class="
                                    isClockedIn
                                        ? 'bg-emerald-300 animate-pulse'
                                        : 'bg-slate-300'
                                "
                            />
                            {{ isClockedIn ? "On duty" : "Ready to start" }}
                        </span>
                    </div>

                    <div class="max-w-3xl">
                        <h1
                            class="text-3xl font-semibold tracking-tight text-white sm:text-4xl lg:text-5xl"
                        >
                            Welcome back, {{ user.name }}.
                        </h1>
                        <p
                            class="mt-3 max-w-2xl text-base text-slate-300 sm:text-lg"
                        >
                            Pick the workspace you need and keep cashiering,
                            floor operations, and attendance moving from one
                            clean dashboard.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div
                            class="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur-sm"
                        >
                            <p
                                class="text-xs uppercase tracking-[0.22em] text-slate-400"
                            >
                                Branch
                            </p>
                            <p class="mt-2 text-lg font-semibold text-white">
                                {{ activeBranch?.name || "No branch selected" }}
                            </p>
                            <p class="mt-1 text-sm text-slate-300">
                                {{
                                    activeBranch?.branch_code ||
                                    "Code unavailable"
                                }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur-sm"
                        >
                            <p
                                class="text-xs uppercase tracking-[0.22em] text-slate-400"
                            >
                                Role
                            </p>
                            <p class="mt-2 text-lg font-semibold text-white">
                                {{ roleLabel }}
                            </p>
                            <p class="mt-1 text-sm text-slate-300">
                                Access tailored to your daily tasks
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur-sm"
                        >
                            <p
                                class="text-xs uppercase tracking-[0.22em] text-slate-400"
                            >
                                Last attendance
                            </p>
                            <p class="mt-2 text-lg font-semibold text-white">
                                {{ attendanceLabel }}
                            </p>
                            <p class="mt-1 text-sm text-slate-300">
                                {{ attendanceSubLabel }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-8">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <h2
                        class="text-2xl font-semibold tracking-tight text-slate-900"
                    >
                        Workspaces
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Open the area you need for your current shift.
                    </p>
                </div>
                <div
                    v-if="!activeBranch"
                    class="rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800"
                >
                    Select a branch to unlock operations.
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <Link
                    v-for="action in actions"
                    :key="action.name"
                    :href="
                        action.route === 'resto.pending-orders.index'
                            ? route(action.route, {
                                  branchId: activeBranch?.id,
                              })
                            : route(action.route)
                    "
                    class="group relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-[0_20px_50px_-35px_rgba(15,23,42,0.35)] transition duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-[0_28px_60px_-35px_rgba(15,23,42,0.45)]"
                >
                    <div
                        class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r"
                        :class="action.accent"
                    />
                    <div class="flex h-full flex-col">
                        <div
                            class="inline-flex h-14 w-14 items-center justify-center rounded-2xl text-white shadow-lg"
                            :class="action.badge"
                        >
                            <component :is="action.icon" />
                        </div>

                        <div class="mt-6 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <h3
                                    class="text-xl font-semibold text-slate-900"
                                >
                                    {{ action.name }}
                                </h3>
                                <span
                                    v-if="
                                        action.route === 'attendance.index' &&
                                        isClockedIn
                                    "
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"
                                >
                                    Live
                                </span>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                {{ action.description }}
                            </p>
                        </div>

                        <div
                            class="mt-6 flex items-center justify-between text-sm font-medium"
                        >
                            <span class="text-slate-500">{{
                                action.meta
                            }}</span>
                            <span
                                class="text-slate-900 transition group-hover:translate-x-1"
                            >
                                Open →
                            </span>
                        </div>
                    </div>
                </Link>
            </div>
        </section>
    </HomeLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import AnalyticsIcon from "@/Components/icons/HomeIcons/AnalyticsIcon.vue";
import CashieringIcon from "@/Components/icons/HomeIcons/CashieringIcon.vue";
import TableOrderingIcon from "@/Components/icons/HomeIcons/TableOrderingIcon.vue";
import ClockInOutIcon from "@/Components/icons/HomeIcons/ClockInOutIcon.vue";
import HomeLayout from "@/Layouts/HomeLayout.vue";

const page = usePage();

const props = defineProps({
    user: Object,
    attendanceStatus: Object,
});

const isClockedIn = ref(props.attendanceStatus?.is_clocked_in || false);

const user = computed(() => props.user);
const activeBranch = computed(() => page.props.active_branch);

const currentRole = computed(() => {
    const raw =
        page.props?.auth?.user?.user_interface ??
        user.value?.user_interface ??
        "";

    return String(raw).toLowerCase().replaceAll(" ", "_").replaceAll("-", "_");
});

const roleLabel = computed(() => {
    const label =
        page.props?.auth?.user?.user_interface ??
        user.value?.user_interface ??
        "Staff";

    return String(label)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (char) => char.toUpperCase());
});

const isOrderTaking = computed(() => currentRole.value === "order_taking");

const attendanceLabel = computed(() => {
    if (isClockedIn.value) {
        return "Clocked in";
    }

    if (props.attendanceStatus?.last_clock_out) {
        return formatDateTime(props.attendanceStatus.last_clock_out);
    }

    if (props.attendanceStatus?.last_clock_in) {
        return formatDateTime(props.attendanceStatus.last_clock_in);
    }

    return "No record today";
});

const attendanceSubLabel = computed(() => {
    if (isClockedIn.value && props.attendanceStatus?.last_clock_in) {
        return `Started at ${formatTime(props.attendanceStatus.last_clock_in)}`;
    }

    if (props.attendanceStatus?.last_clock_out) {
        return "Latest completed attendance";
    }

    return "Use Clock In/Out to begin your shift";
});

const actions = computed(() => {
    const commonActions = [
        {
            route: "attendance.index",
            name: "Clock In/Out",
            description:
                "Capture employee attendance with camera validation and a live branch log.",
            icon: ClockInOutIcon,
            meta: "Attendance",
            badge: "bg-gradient-to-br from-amber-500 to-orange-500",
            accent: "from-amber-400 via-orange-400 to-rose-400",
        },
    ];

    if (isOrderTaking.value) {
        return [
            {
                route: "table-rooms.index",
                name: "Start Order Taking",
                description:
                    "Open the ordering workspace for table-side service and cart management.",
                icon: TableOrderingIcon,
                meta: "Dining floor",
                badge: "bg-gradient-to-br from-sky-500 to-cyan-500",
                accent: "from-sky-400 via-cyan-400 to-emerald-400",
            },
            ...commonActions,
        ];
    }

    return [
        {
            route: "resto.start-cashiering",
            name: "Start Cashiering",
            description:
                "Launch the POS workspace for transactions, payments, and cashier operations.",
            icon: CashieringIcon,
            meta: "Point of sale",
            badge: "bg-gradient-to-br from-emerald-500 to-teal-500",
            accent: "from-emerald-400 via-teal-400 to-cyan-400",
        },
        {
            route: "resto.pending-orders.index",
            name: "Pending Orders",
            description:
                "Review queued orders, monitor kitchen progress, and keep service moving.",
            icon: TableOrderingIcon,
            meta: "Kitchen queue",
            badge: "bg-gradient-to-br from-violet-500 to-fuchsia-500",
            accent: "from-violet-400 via-fuchsia-400 to-pink-400",
        },
        {
            route: "table-management.index",
            name: "Table Management",
            description:
                "Track tables, guest flow, and service activity across the restaurant floor.",
            icon: AnalyticsIcon,
            meta: "Dining control",
            badge: "bg-gradient-to-br from-slate-700 to-slate-900",
            accent: "from-slate-500 via-slate-700 to-slate-900",
        },
        ...commonActions,
    ];
});

const formatDateTime = (value) => {
    if (!value) {
        return "No record";
    }

    return new Date(value).toLocaleString("en-US", {
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatTime = (value) => {
    if (!value) {
        return "";
    }

    return new Date(value).toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>
