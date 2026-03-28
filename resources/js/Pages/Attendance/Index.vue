<template>
    <Head title="Attendance - QJPOS" />

    <AttendanceLayout :activeBranch="activeBranch">
        <div
            class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(360px,0.8fr)]"
        >
            <section class="space-y-6">
                <section
                    class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_30px_80px_-50px_rgba(15,23,42,0.45)]"
                >
                    <div
                        class="border-b border-slate-200 bg-[linear-gradient(135deg,#0f172a_0%,#1e293b_56%,#155e75_100%)] px-6 py-6 text-white"
                    >
                        <div
                            class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
                        >
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.26em] text-sky-200"
                                >
                                    Clock Station
                                </p>
                                <h2
                                    class="mt-2 text-3xl font-semibold tracking-tight"
                                >
                                    Clock In and Clock Out
                                </h2>
                                <p
                                    class="mt-2 max-w-2xl text-sm leading-6 text-slate-300"
                                >
                                    Capture a live photo, verify the employee
                                    code, and record attendance directly to the
                                    active branch.
                                </p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div
                                    class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 backdrop-blur-sm"
                                >
                                    <p
                                        class="text-xs uppercase tracking-[0.22em] text-slate-300"
                                    >
                                        Employee
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-white"
                                    >
                                        {{
                                            employeeData?.name ||
                                            "Awaiting code"
                                        }}
                                    </p>
                                    <p class="text-xs text-slate-300">
                                        {{
                                            employeeData?.employee_no ||
                                            "No employee selected"
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 backdrop-blur-sm"
                                >
                                    <p
                                        class="text-xs uppercase tracking-[0.22em] text-slate-300"
                                    >
                                        Action
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-white"
                                    >
                                        {{
                                            isClockedIn
                                                ? "Prepare to clock out"
                                                : "Ready to clock in"
                                        }}
                                    </p>
                                    <p class="text-xs text-slate-300">
                                        {{ clockActionHint }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1fr)_320px]"
                    >
                        <div class="space-y-5">
                            <div
                                class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-slate-950"
                            >
                                <div
                                    class="relative aspect-[4/3] w-full bg-slate-900"
                                >
                                    <video
                                        ref="video"
                                        v-show="cameraActive && !photoTaken"
                                        class="h-full w-full object-cover"
                                        :style="{
                                            transform: `scale(${zoomLevel})`,
                                            transformOrigin: 'center center',
                                        }"
                                        autoplay
                                        playsinline
                                    />

                                    <img
                                        v-if="photoTaken"
                                        :src="capturedPhoto"
                                        alt="Captured employee photo"
                                        class="h-full w-full object-cover"
                                    />

                                    <div
                                        v-if="!cameraActive && !photoTaken"
                                        class="absolute inset-0 flex flex-col items-center justify-center gap-3 px-6 text-center text-white"
                                    >
                                        <CameraIcon
                                            classes="h-12 w-12 text-slate-300"
                                        />
                                        <div>
                                            <p class="text-lg font-semibold">
                                                Starting camera
                                            </p>
                                            <p
                                                class="mt-1 text-sm text-slate-300"
                                            >
                                                Allow access to continue
                                                capturing attendance.
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-if="cameraActive && !photoTaken"
                                        class="pointer-events-none absolute inset-5 rounded-[1.5rem] border border-white/35"
                                    />

                                    <div
                                        v-if="cameraActive && !photoTaken"
                                        class="absolute left-4 top-4 rounded-full bg-black/45 px-3 py-1 text-xs font-semibold text-white backdrop-blur"
                                    >
                                        {{ Math.round(zoomLevel * 100) }}% zoom
                                    </div>

                                    <div
                                        v-if="cameraActive && !photoTaken"
                                        class="absolute bottom-4 right-4 flex items-center gap-2 rounded-full bg-black/45 p-2 backdrop-blur"
                                    >
                                        <button
                                            type="button"
                                            class="rounded-full bg-white/90 p-2 text-slate-900 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50"
                                            :disabled="zoomLevel >= maxZoom"
                                            @click="zoomIn"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 6v12m6-6H6"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-full bg-white/90 p-2 text-slate-900 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50"
                                            :disabled="zoomLevel <= minZoom"
                                            @click="zoomOut"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M18 12H6"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-full bg-white/90 p-2 text-slate-900 transition hover:bg-white"
                                            @click="resetZoom"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 4v5h5m11 11v-5h-5m5-6a8 8 0 10-16 0 8 8 0 0016 0z"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto]"
                            >
                                <div class="space-y-3">
                                    <label
                                        for="employee_no"
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        Employee No
                                    </label>
                                    <input
                                        id="employee_no"
                                        v-model="employeeNo"
                                        type="text"
                                        placeholder="Enter your employee number"
                                        class="h-14 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-base text-slate-900 outline-none transition focus:border-sky-500 focus:bg-white focus:ring-4 focus:ring-sky-100"
                                        autocomplete="off"
                                        @keydown.enter.prevent="
                                            captureAttendance
                                        "
                                    />
                                    <InputError :message="formError" />

                                    <div
                                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                                    >
                                        <p
                                            class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500"
                                        >
                                            Current Date and Time
                                        </p>
                                        <p
                                            class="mt-2 text-base font-semibold tabular-nums text-slate-950 sm:text-lg"
                                        >
                                            {{ liveDateTime }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-end">
                                    <button
                                        type="button"
                                        class="inline-flex h-14 items-center justify-center gap-2 rounded-2xl bg-slate-950 px-6 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                                        :disabled="
                                            processing ||
                                            checkingStatus ||
                                            !employeeNo ||
                                            !cameraActive
                                        "
                                        @click="captureAttendance"
                                    >
                                        <LoadingIcon
                                            v-if="processing || checkingStatus"
                                            classes="h-4 w-4 animate-spin text-white"
                                        />
                                        <CameraIcon v-else classes="h-4 w-4" />
                                        {{
                                            checkingStatus
                                                ? "Checking"
                                                : "Capture"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <aside
                            class="space-y-4 rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5"
                        >
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500"
                                >
                                    Shift Status
                                </p>
                                <div
                                    class="mt-3 rounded-2xl border px-4 py-4"
                                    :class="
                                        isClockedIn
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
                                            : 'border-slate-200 bg-white text-slate-900'
                                    "
                                >
                                    <p class="text-sm font-semibold">
                                        {{ statusHeadline }}
                                    </p>
                                    <p class="mt-1 text-sm opacity-80">
                                        {{ statusSubline }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="
                                    isClockedIn &&
                                    !canClockOut &&
                                    timeUntilClockOut
                                "
                                class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900"
                            >
                                <p class="text-sm font-semibold">
                                    Clock out locked
                                </p>
                                <p class="mt-1 text-sm">
                                    Wait {{ countdownLabel }} before clocking
                                    out.
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-dashed border-slate-300 bg-white p-4"
                            >
                                <p class="text-sm font-semibold text-slate-900">
                                    Tips for clean captures
                                </p>
                                <ul
                                    class="mt-2 space-y-2 text-sm text-slate-600"
                                >
                                    <li>
                                        Keep the face centered in the frame.
                                    </li>
                                    <li>
                                        Enter the employee code before
                                        capturing.
                                    </li>
                                    <li>
                                        Use the zoom controls if the camera is
                                        too wide.
                                    </li>
                                </ul>
                            </div>
                        </aside>
                    </div>
                </section>
            </section>

            <section
                class="flex min-h-[720px] flex-col rounded-[2rem] border border-slate-200 bg-white shadow-[0_30px_80px_-50px_rgba(15,23,42,0.45)]"
            >
                <div class="border-b border-slate-200 px-6 py-6">
                    <div class="flex flex-col gap-5">
                        <div class="flex flex-col gap-2">
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-700"
                            >
                                Live Branch Feed
                            </p>
                            <h2
                                class="text-3xl font-semibold tracking-tight text-slate-950"
                            >
                                Today's Attendance
                            </h2>
                            <p class="text-sm text-slate-500">
                                {{ formatDate(new Date()) }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                v-for="filter in filters"
                                :key="filter.value"
                                type="button"
                                class="rounded-full px-4 py-2 text-sm font-semibold transition"
                                :class="
                                    attendanceFilter === filter.value
                                        ? filter.activeClass
                                        : 'border border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900'
                                "
                                @click="attendanceFilter = filter.value"
                            >
                                {{ filter.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <div v-if="filteredAttendance.length" class="space-y-4">
                        <article
                            v-for="attendance in filteredAttendance"
                            :key="attendance.id"
                            class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5 transition hover:border-slate-300 hover:bg-white"
                        >
                            <div
                                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div class="flex items-start gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white"
                                    >
                                        <UserIcon classes="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h3
                                            class="text-base font-semibold text-slate-950"
                                        >
                                            {{
                                                attendance.user?.name ||
                                                "Unknown employee"
                                            }}
                                        </h3>
                                        <p class="mt-1 text-sm text-slate-500">
                                            Employee No:
                                            {{
                                                attendance.user?.employee
                                                    ?.employee_no || "N/A"
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="
                                        attendance.actual_timeout
                                            ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'
                                            : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                                    "
                                >
                                    {{
                                        attendance.actual_timeout
                                            ? "Clocked Out"
                                            : "Clocked In"
                                    }}
                                </span>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div
                                    class="rounded-2xl border border-slate-200 bg-white p-4"
                                >
                                    <p
                                        class="text-xs uppercase tracking-[0.22em] text-slate-500"
                                    >
                                        Time In
                                    </p>
                                    <p
                                        class="mt-2 text-lg font-semibold text-slate-950"
                                    >
                                        {{
                                            formatTime(attendance.actual_timein)
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border border-slate-200 bg-white p-4"
                                >
                                    <p
                                        class="text-xs uppercase tracking-[0.22em] text-slate-500"
                                    >
                                        Time Out
                                    </p>
                                    <p
                                        class="mt-2 text-lg font-semibold text-slate-950"
                                    >
                                        {{
                                            attendance.actual_timeout
                                                ? formatTime(
                                                      attendance.actual_timeout,
                                                  )
                                                : "Still on shift"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div
                        v-else
                        class="flex h-full min-h-[400px] flex-col items-center justify-center rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 px-8 text-center"
                    >
                        <ClipboardIcon classes="h-14 w-14 text-slate-400" />
                        <h3 class="mt-5 text-xl font-semibold text-slate-900">
                            No attendance records yet
                        </h3>
                        <p
                            class="mt-2 max-w-sm text-sm leading-6 text-slate-500"
                        >
                            {{ emptyStateMessage }}
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <div
            v-if="showPhotoModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4 py-6 backdrop-blur-sm"
        >
            <div
                class="w-full max-w-2xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl"
            >
                <div class="border-b border-slate-200 px-6 py-5">
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-700"
                    >
                        Confirm Attendance
                    </p>
                    <h3
                        class="mt-2 text-2xl font-semibold tracking-tight text-slate-950"
                    >
                        Review before submitting
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Make sure the photo is clear and the employee
                        information is correct.
                    </p>
                </div>

                <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div
                        class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-100"
                    >
                        <img
                            :src="capturedPhoto"
                            alt="Attendance preview"
                            class="h-full w-full object-cover"
                        />
                    </div>

                    <div class="space-y-4">
                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <p
                                class="text-xs uppercase tracking-[0.22em] text-slate-500"
                            >
                                Employee
                            </p>
                            <p
                                class="mt-2 text-lg font-semibold text-slate-950"
                            >
                                {{ employeeData?.name }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                No: {{ employeeData?.employee_no }}
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <p
                                class="text-xs uppercase tracking-[0.22em] text-slate-500"
                            >
                                Action
                            </p>
                            <p
                                class="mt-2 text-lg font-semibold text-slate-950"
                            >
                                {{ isClockedIn ? "Clock Out" : "Clock In" }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ formatCurrentTime() }}
                            </p>
                        </div>

                        <div
                            v-if="
                                isClockedIn && !canClockOut && timeUntilClockOut
                            "
                            class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900"
                        >
                            <p class="text-sm font-semibold">Please wait</p>
                            <p class="mt-1 text-sm">
                                You can clock out in {{ countdownLabel }}.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="flex flex-col gap-3 border-t border-slate-200 px-6 py-5 sm:flex-row"
                >
                    <button
                        type="button"
                        class="inline-flex h-12 flex-1 items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900"
                        @click="cancelPhotoModal"
                    >
                        Retake Photo
                    </button>
                    <button
                        type="button"
                        class="inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-2xl px-4 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-50"
                        :class="
                            isClockedIn
                                ? 'bg-rose-600 hover:bg-rose-500'
                                : 'bg-emerald-600 hover:bg-emerald-500'
                        "
                        :disabled="processing || (isClockedIn && !canClockOut)"
                        @click="confirmAttendance"
                    >
                        <LoadingIcon
                            v-if="processing"
                            classes="h-4 w-4 animate-spin text-white"
                        />
                        <ClockIcon v-else classes="h-4 w-4" />
                        {{
                            processing
                                ? "Submitting"
                                : isClockedIn
                                  ? "Confirm Clock Out"
                                  : "Confirm Clock In"
                        }}
                    </button>
                </div>
            </div>
        </div>

        <Toast />
    </AttendanceLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import { Toast, useToast } from "primevue";
import axios from "axios";
import AttendanceLayout from "@/Layouts/AttendanceLayout.vue";
import InputError from "@/Components/Form/InputError.vue";
import CameraIcon from "@/Components/icons/CameraIcon.vue";
import LoadingIcon from "@/Components/icons/LoadingIcon.vue";
import ClockIcon from "@/Components/icons/ClockIcon.vue";
import UserIcon from "@/Components/icons/UserIcon.vue";
import ClipboardIcon from "@/Components/icons/ClipboardIcon.vue";

defineProps({
    activeBranch: Object,
});

const page = usePage();
const toast = useToast();

const employeeNo = ref("");
const formError = ref("");
const processing = ref(false);
const checkingStatus = ref(false);

const employeeData = ref(null);
const isClockedIn = ref(false);
const currentAttendance = ref(null);
const todayAttendance = ref([]);
const canClockOut = ref(true);
const timeUntilClockOut = ref(null);
const countdownInterval = ref(null);

const attendanceFilter = ref("all");

const video = ref(null);
const cameraActive = ref(false);
const photoTaken = ref(false);
const capturedPhoto = ref("");
const showPhotoModal = ref(false);
const cameraError = ref("");
const currentDateTime = ref(new Date());
let stream = null;
let dateTimeInterval = null;

const zoomLevel = ref(1.5);
const minZoom = 1;
const maxZoom = 3;
const zoomStep = 0.2;

const filters = [
    {
        label: "All",
        value: "all",
        activeClass: "bg-slate-950 text-white",
    },
    {
        label: "Clocked In",
        value: "clock_in",
        activeClass: "bg-emerald-600 text-white",
    },
    {
        label: "Clocked Out",
        value: "clock_out",
        activeClass: "bg-rose-600 text-white",
    },
];

const csrfToken = () =>
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "";

const liveDateTime = computed(() =>
    currentDateTime.value.toLocaleString("en-US", {
        weekday: "long",
        month: "long",
        day: "numeric",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    }),
);

const filteredAttendance = computed(() => {
    if (attendanceFilter.value === "clock_in") {
        return todayAttendance.value.filter((record) => !record.actual_timeout);
    }

    if (attendanceFilter.value === "clock_out") {
        return todayAttendance.value.filter((record) => record.actual_timeout);
    }

    return todayAttendance.value;
});

const emptyStateMessage = computed(() => {
    if (attendanceFilter.value === "clock_in") {
        return "No employees are currently clocked in for this branch today.";
    }

    if (attendanceFilter.value === "clock_out") {
        return "No completed clock out records have been captured for today.";
    }

    return "Attendance records will appear here once employees start using the clock station.";
});

const countdownLabel = computed(() => {
    if (!timeUntilClockOut.value) {
        return "";
    }

    const minutes = Math.floor(timeUntilClockOut.value.total_seconds / 60);
    const seconds = timeUntilClockOut.value.total_seconds % 60;

    return `${minutes}m ${String(seconds).padStart(2, "0")}s`;
});

const statusHeadline = computed(() => {
    if (isClockedIn.value && employeeData.value) {
        return `${employeeData.value.name} is clocked in`;
    }

    if (employeeData.value) {
        return `${employeeData.value.name} is ready`;
    }

    return "Ready for attendance capture";
});

const statusSubline = computed(() => {
    if (cameraError.value) {
        return cameraError.value;
    }

    if (isClockedIn.value && currentAttendance.value?.actual_timein) {
        return `Started at ${formatTime(currentAttendance.value.actual_timein)}`;
    }

    if (employeeData.value) {
        return "Capture a photo to continue.";
    }

    return "Enter an employee code and take a live photo.";
});

const clockActionHint = computed(() => {
    if (isClockedIn.value && !canClockOut.value && timeUntilClockOut.value) {
        return `Clock out available in ${countdownLabel.value}`;
    }

    return isClockedIn.value
        ? "Attendance will record a clock out entry."
        : "Attendance will record a clock in entry.";
});

const formatCurrentTime = () =>
    new Date().toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });

const formatTime = (value) => {
    if (!value) {
        return "N/A";
    }

    return new Date(value).toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

const formatDate = (value) =>
    new Date(value).toLocaleDateString("en-US", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });

const resetEmployeeState = () => {
    employeeData.value = null;
    isClockedIn.value = false;
    currentAttendance.value = null;
    canClockOut.value = true;
    stopCountdown();
};

const startCountdown = () => {
    stopCountdown();

    countdownInterval.value = setInterval(() => {
        if (!timeUntilClockOut.value) {
            stopCountdown();
            return;
        }

        if (timeUntilClockOut.value.total_seconds <= 1) {
            canClockOut.value = true;
            stopCountdown();
            return;
        }

        timeUntilClockOut.value.total_seconds -= 1;
    }, 1000);
};

const stopCountdown = () => {
    if (countdownInterval.value) {
        clearInterval(countdownInterval.value);
        countdownInterval.value = null;
    }

    timeUntilClockOut.value = null;
};

const startCamera = async () => {
    cameraError.value = "";

    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user" },
            audio: false,
        });

        if (video.value) {
            video.value.srcObject = stream;
            cameraActive.value = true;
            resetZoom();
        }
    } catch (error) {
        cameraActive.value = false;
        cameraError.value =
            "Unable to access the camera. Please check browser permissions.";
        toast.add({
            severity: "error",
            summary: "Camera Error",
            detail: cameraError.value,
            life: 4000,
        });
    }
};

const stopCamera = () => {
    if (stream) {
        stream.getTracks().forEach((track) => track.stop());
        stream = null;
    }

    cameraActive.value = false;
};

const takePhoto = () => {
    if (!video.value) {
        return;
    }

    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");

    canvas.width = video.value.videoWidth;
    canvas.height = video.value.videoHeight;

    context.drawImage(video.value, 0, 0);
    capturedPhoto.value = canvas.toDataURL("image/jpeg", 0.8);
    photoTaken.value = true;
    stopCamera();
};

const retakePhoto = async () => {
    photoTaken.value = false;
    capturedPhoto.value = "";
    showPhotoModal.value = false;
    resetZoom();
    await startCamera();
};

const zoomIn = () => {
    if (zoomLevel.value < maxZoom) {
        zoomLevel.value = Math.min(zoomLevel.value + zoomStep, maxZoom);
    }
};

const zoomOut = () => {
    if (zoomLevel.value > minZoom) {
        zoomLevel.value = Math.max(zoomLevel.value - zoomStep, minZoom);
    }
};

const resetZoom = () => {
    zoomLevel.value = 1.5;
};

const fetchTodayAttendance = async () => {
    try {
        const response = await axios.get(route("attendance.today"));
        todayAttendance.value = response.data.attendance || [];
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Attendance Error",
            detail: "Unable to load today's attendance records.",
            life: 4000,
        });
    }
};

const captureAttendance = async () => {
    formError.value = "";

    if (!employeeNo.value) {
        formError.value = "Employee number is required.";
        return;
    }

    if (!cameraActive.value) {
        formError.value = "Camera is not ready yet.";
        return;
    }

    takePhoto();
    checkingStatus.value = true;

    try {
        const response = await axios.post(route("attendance.check-status"), {
            employee_no: employeeNo.value,
            _token: csrfToken(),
        });

        employeeData.value = response.data.employee;
        isClockedIn.value = response.data.isClockedIn;
        currentAttendance.value = response.data.currentAttendance;
        canClockOut.value = response.data.canClockOut;
        timeUntilClockOut.value = response.data.timeUntilClockOut;

        if (!canClockOut.value && timeUntilClockOut.value) {
            startCountdown();
        } else {
            stopCountdown();
        }

        showPhotoModal.value = true;
    } catch (error) {
        const message =
            error.response?.data?.message ||
            "Unable to verify the employee code.";

        formError.value = message;
        resetEmployeeState();
        toast.add({
            severity: "error",
            summary: "Validation Error",
            detail: message,
            life: 4000,
        });
        await retakePhoto();
    } finally {
        checkingStatus.value = false;
    }
};

const confirmAttendance = async () => {
    if (!employeeNo.value || !capturedPhoto.value) {
        return;
    }

    processing.value = true;
    formError.value = "";

    try {
        const response = await axios.post(route("attendance.toggle"), {
            employee_no: employeeNo.value,
            photo: capturedPhoto.value,
            _token: csrfToken(),
        });

        toast.add({
            severity: "success",
            summary: "Attendance Recorded",
            detail:
                response.data.message || "Attendance recorded successfully.",
            life: 3000,
        });

        showPhotoModal.value = false;
        photoTaken.value = false;
        capturedPhoto.value = "";
        employeeNo.value = "";
        resetEmployeeState();
        await fetchTodayAttendance();
        await startCamera();
    } catch (error) {
        const message =
            error.response?.data?.message ||
            "Unable to process the attendance request.";

        formError.value = message;
        toast.add({
            severity: "error",
            summary: "Attendance Error",
            detail: message,
            life: 4000,
        });

        if (error.response?.status === 419) {
            toast.add({
                severity: "warn",
                summary: "Session Refreshed",
                detail: "The session token expired. Reload the page and try again.",
                life: 5000,
            });
        }
    } finally {
        processing.value = false;
    }
};

const cancelPhotoModal = async () => {
    await retakePhoto();
};

const handleKeyPress = (event) => {
    if (!cameraActive.value || photoTaken.value) {
        return;
    }

    if (
        event.target.tagName === "INPUT" ||
        event.target.tagName === "TEXTAREA"
    ) {
        return;
    }

    if (event.key === "+" || event.key === "=") {
        event.preventDefault();
        zoomIn();
    } else if (event.key === "-") {
        event.preventDefault();
        zoomOut();
    } else if (event.key === "0") {
        event.preventDefault();
        resetZoom();
    }
};

onMounted(async () => {
    document.addEventListener("keydown", handleKeyPress);
    dateTimeInterval = setInterval(() => {
        currentDateTime.value = new Date();
    }, 1000);
    await fetchTodayAttendance();
    await startCamera();
});

onUnmounted(() => {
    document.removeEventListener("keydown", handleKeyPress);
    if (dateTimeInterval) {
        clearInterval(dateTimeInterval);
    }
    stopCamera();
    stopCountdown();
});
</script>
