<template>
    <Head title="Attendance - QJPOS" />

    <AttendanceLayout :activeBranch="activeBranch">
        <div class="h-full flex flex-col lg:flex-row overflow-hidden">
            <!-- Left Side - Attendance Section (50%) -->
            <div
                class="w-full lg:w-1/2 p-4 border-r border-gray-200 flex flex-col overflow-hidden"
            >
                <div class="flex flex-col h-full space-y-4">
                    <!-- Header -->
                    <div class="text-center flex-shrink-0">
                        <h2 class="text-xl font-bold text-gray-900 mb-1">
                            Clock In/Out
                        </h2>
                        <p class="text-sm text-gray-600">
                            Enter your employee code to capture attendance
                        </p>
                    </div>

                    <!-- Camera Preview Section -->
                    <div
                        class="flex-1 flex items-center justify-center min-h-0"
                    >
                        <div class="w-full relative">
                            <!-- Camera Active -->
                            <div
                                class="h-full w-full lg:w-[500px] mx-auto relative overflow-hidden rounded-lg"
                            >
                                <video
                                    ref="video"
                                    v-show="cameraActive && !photoTaken"
                                    class="w-full h-auto shadow-md object-cover"
                                    :style="{
                                        transform: `scale(${zoomLevel})`,
                                        transformOrigin: 'center center',
                                    }"
                                    autoplay
                                    playsinline
                                ></video>

                                <!-- Zoom Controls -->
                                <div
                                    v-if="cameraActive && !photoTaken"
                                    class="absolute bottom-4 right-4 flex flex-col space-y-2 bg-black bg-opacity-50 rounded-lg p-2"
                                >
                                    <button
                                        @click="zoomIn"
                                        :disabled="zoomLevel >= maxZoom"
                                        class="p-2 bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Zoom In"
                                    >
                                        <svg
                                            class="w-4 h-4 text-gray-800"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                            ></path>
                                        </svg>
                                    </button>
                                    <button
                                        @click="zoomOut"
                                        :disabled="zoomLevel <= minZoom"
                                        class="p-2 bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Zoom Out"
                                    >
                                        <svg
                                            class="w-4 h-4 text-gray-800"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M18 12H6"
                                            ></path>
                                        </svg>
                                    </button>
                                    <button
                                        @click="resetZoom"
                                        class="p-2 bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full transition-all"
                                        title="Reset Zoom"
                                    >
                                        <svg
                                            class="w-4 h-4 text-gray-800"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                            ></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Zoom Level Indicator -->
                                <div
                                    v-if="
                                        cameraActive &&
                                        !photoTaken &&
                                        zoomLevel !== 1
                                    "
                                    class="absolute top-4 left-4 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-sm"
                                >
                                    {{ Math.round(zoomLevel * 100) }}%
                                </div>
                            </div>

                            <!-- Photo Preview -->
                            <div v-show="photoTaken" class="w-full h-48">
                                <img
                                    :src="capturedPhoto"
                                    alt="Captured photo"
                                    class="w-full h-full rounded-lg shadow-md object-cover"
                                />
                            </div>

                            <!-- Camera Placeholder -->
                            <div
                                v-show="!cameraActive && !photoTaken"
                                class="flex flex-col items-center justify-center w-full h-48 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300"
                            >
                                <CameraIcon
                                    classes="w-12 h-12 text-gray-400 mb-2"
                                />
                                <h4
                                    class="text-sm font-medium text-gray-600 mb-1"
                                >
                                    Starting Camera...
                                </h4>
                                <p
                                    class="text-xs text-gray-500 text-center px-4"
                                >
                                    Please allow camera access when prompted
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Code Input -->
                    <div class="flex-shrink-0">
                        <div class="space-y-3 flex flex-col">
                            <TextField
                                id="employee_code"
                                v-model="form.employee_code"
                                label="Employee Code"
                                type="text"
                                placeholder="Enter your employee code"
                                :error="form.errors.employee_code"
                                required
                                autocomplete="Employee Code"
                                class=""
                            />

                            <!-- Capture Attendance Button -->
                            <PrimaryButton
                                v-if="cameraActive && !photoTaken"
                                @click="captureAttendance"
                                :disabled="
                                    form.processing || !form.employee_code
                                "
                                class="px-4 py-2 text-sm font-medium flex items-center justify-center"
                            >
                                <LoadingIcon
                                    v-if="form.processing"
                                    classes="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                />
                                <CameraIcon v-else classes="w-4 h-4 mr-2" />
                                Capture
                            </PrimaryButton>
                        </div>
                        <InputError
                            class="mt-2"
                            :message="form.errors.employee_code"
                        />
                    </div>
                </div>
            </div>

            <!-- Right Side - Attendance List (50%) -->
            <div class="w-full lg:w-1/2 p-4 flex flex-col overflow-hidden">
                <div class="h-full flex flex-col">
                    <!-- Header -->
                    <div class="mb-4 flex-shrink-0">
                        <h2 class="text-xl font-bold text-gray-900 mb-1">
                            Today's Attendance
                        </h2>
                        <p class="text-sm text-gray-600">
                            {{ formatDate(new Date()) }}
                        </p>
                    </div>

                    <!-- Filter Controls -->
                    <div class="mb-4 flex-shrink-0">
                        <div class="flex space-x-2">
                            <button
                                @click="attendanceFilter = 'all'"
                                :class="[
                                    'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                                    attendanceFilter === 'all'
                                        ? 'bg-blue-100 text-blue-700 border border-blue-300'
                                        : 'bg-gray-100 text-gray-700 border border-gray-300 hover:bg-gray-200',
                                ]"
                            >
                                All
                            </button>
                            <button
                                @click="attendanceFilter = 'clock_in'"
                                :class="[
                                    'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                                    attendanceFilter === 'clock_in'
                                        ? 'bg-green-100 text-green-700 border border-green-300'
                                        : 'bg-gray-100 text-gray-700 border border-gray-300 hover:bg-gray-200',
                                ]"
                            >
                                Clock In
                            </button>
                            <button
                                @click="attendanceFilter = 'clock_out'"
                                :class="[
                                    'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                                    attendanceFilter === 'clock_out'
                                        ? 'bg-red-100 text-red-700 border border-red-300'
                                        : 'bg-gray-100 text-gray-700 border border-gray-300 hover:bg-gray-200',
                                ]"
                            >
                                Clock Out
                            </button>
                        </div>
                        <div class="text-sm text-gray-500 mt-2">
                            {{ filteredAttendance.length }} record{{
                                filteredAttendance.length !== 1 ? "s" : ""
                            }}
                        </div>
                    </div>

                    <!-- Attendance List -->
                    <div
                        class="h-[500px] overflow-y-scroll border border-gray-200 rounded-lg p-3"
                    >
                        <div
                            v-if="
                                filteredAttendance &&
                                filteredAttendance.length > 0
                            "
                            class="space-y-3"
                        >
                            <div
                                v-for="attendance in filteredAttendance"
                                :key="attendance.id"
                                class="bg-white rounded-lg shadow-sm p-3 border"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="bg-gray-200 p-2 rounded-full flex-shrink-0"
                                        >
                                            <UserIcon
                                                classes="w-4 h-4 text-gray-600"
                                            />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4
                                                class="font-semibold text-gray-900 text-sm truncate"
                                            >
                                                {{ attendance.user?.name }}
                                            </h4>
                                            <p class="text-xs text-gray-600">
                                                Code:
                                                {{
                                                    attendance.user
                                                        ?.employee_code
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        :class="[
                                            'px-3 py-2 rounded-full text-sm font-medium flex-shrink-0',
                                            attendance.actual_timeout
                                                ? 'bg-red-100 text-red-800'
                                                : 'bg-green-100 text-green-800',
                                        ]"
                                    >
                                        {{
                                            attendance.actual_timeout
                                                ? "Clock Out"
                                                : "Clock In"
                                        }}
                                    </div>
                                </div>
                                <div
                                    class="mt-3 grid grid-cols-2 gap-3 text-sm"
                                >
                                    <div>
                                        <span class="text-gray-500 font-medium"
                                            >Clock In:</span
                                        >
                                        <span
                                            class="font-semibold text-gray-900 ml-1 block"
                                        >
                                            {{
                                                formatTime(
                                                    attendance.actual_timein
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div v-if="attendance.actual_timeout">
                                        <span class="text-gray-500 font-medium"
                                            >Clock Out:</span
                                        >
                                        <span
                                            class="font-semibold text-gray-900 ml-1 block"
                                        >
                                            {{
                                                formatTime(
                                                    attendance.actual_timeout
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex flex-col items-center justify-center h-full text-center"
                        >
                            <ClipboardIcon
                                classes="w-12 h-12 text-gray-400 mb-3"
                            />
                            <h3
                                class="text-base font-medium text-gray-900 mb-1"
                            >
                                No Attendance Records
                            </h3>
                            <p class="text-sm text-gray-500">
                                <span v-if="attendanceFilter === 'all'">
                                    No records found for today.
                                </span>
                                <span
                                    v-else-if="attendanceFilter === 'clock_in'"
                                >
                                    No clock in records found for today.
                                </span>
                                <span
                                    v-else-if="attendanceFilter === 'clock_out'"
                                >
                                    No clock out records found for today.
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo Preview Modal -->
        <div
            v-if="showPhotoModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <!-- Modal Header -->
                <div class="mb-4 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Confirm Attendance
                    </h3>
                    <p class="text-sm text-gray-600">
                        Review your photo and confirm attendance
                    </p>
                </div>

                <!-- Photo Preview -->
                <div class="mb-4">
                    <img
                        :src="capturedPhoto"
                        alt="Captured photo"
                        class="w-full h-48 object-cover rounded-lg border"
                    />
                </div>

                <!-- Employee Info -->
                <div v-if="employeeData" class="mb-4 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <UserIcon classes="w-8 h-8 text-gray-600" />
                        <div>
                            <h4 class="font-semibold text-gray-900">
                                {{ employeeData.name }}
                            </h4>
                            <p class="text-sm text-gray-600">
                                Code: {{ employeeData.employee_code }}
                            </p>
                            <p
                                class="text-sm font-medium"
                                :class="
                                    isClockedIn
                                        ? 'text-red-600'
                                        : 'text-green-600'
                                "
                            >
                                {{
                                    isClockedIn ? "Clocking Out" : "Clocking In"
                                }}
                            </p>
                            <!-- Clock out countdown -->
                            <div
                                v-if="
                                    isClockedIn &&
                                    !canClockOut &&
                                    timeUntilClockOut
                                "
                                class="mt-2 p-2 bg-orange-100 rounded text-center"
                            >
                                <p class="text-xs text-orange-800 font-medium">
                                    Wait
                                    {{
                                        Math.ceil(
                                            timeUntilClockOut.total_seconds / 60
                                        )
                                    }}
                                    more minute(s) before clocking out
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Time -->
                <div class="mb-6 text-center p-3 bg-blue-50 rounded-lg">
                    <ClockIcon classes="w-6 h-6 text-blue-600 mx-auto mb-1" />
                    <p class="text-sm text-gray-600">Current Time</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ formatCurrentTime() }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-3">
                    <button
                        @click="cancelPhotoModal"
                        class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg font-medium transition-colors"
                    >
                        Cancel
                    </button>
                    <PrimaryButton
                        @click="confirmAttendance"
                        :disabled="
                            form.processing || (isClockedIn && !canClockOut)
                        "
                        :class="[
                            'flex-1 justify-center',
                            isClockedIn
                                ? canClockOut
                                    ? 'bg-red-500 hover:bg-red-600'
                                    : 'bg-gray-400 cursor-not-allowed'
                                : 'bg-green-500 hover:bg-green-600',
                        ]"
                    >
                        <LoadingIcon
                            v-if="form.processing"
                            classes="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        />
                        <ClockIcon v-else classes="w-4 h-4 mr-2" />
                        {{
                            isClockedIn
                                ? canClockOut
                                    ? "Clock Out"
                                    : `Wait ${Math.ceil(
                                          (timeUntilClockOut?.total_seconds ||
                                              0) / 60
                                      )} min`
                                : "Clock In"
                        }}
                    </PrimaryButton>
                </div>
            </div>
        </div>

        <!-- Toast Notifications -->
        <Toast />
    </AttendanceLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { useToast } from "primevue";
import { Toast } from "primevue";
import axios from "axios";
import AttendanceLayout from "@/Layouts/AttendanceLayout.vue";
import InputLabel from "@/Components/Form/InputLabel.vue";
import TextField from "@/Components/Form/TextField.vue";
import InputError from "@/Components/Form/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CameraIcon from "@/Components/icons/CameraIcon.vue";
import LoadingIcon from "@/Components/icons/LoadingIcon.vue";
import ClockIcon from "@/Components/icons/ClockIcon.vue";
import UserIcon from "@/Components/icons/UserIcon.vue";
import ClipboardIcon from "@/Components/icons/ClipboardIcon.vue";

const props = defineProps({
    activeBranch: Object,
});

const toast = useToast();

// Form state
const form = useForm({
    employee_code: "",
    photo: "",
});

// Employee state
const employeeData = ref(null);
const isClockedIn = ref(false);
const currentAttendance = ref(null);
const checkingStatus = ref(false);
const todayAttendance = ref([]);
const canClockOut = ref(true);
const timeUntilClockOut = ref(null);
const countdownInterval = ref(null);

// Filter state
const attendanceFilter = ref("all"); // 'all', 'clock_in', 'clock_out'

// Camera state
const video = ref(null);
const cameraActive = ref(false);
const photoTaken = ref(false);
const capturedPhoto = ref("");
const showConfirmModal = ref(false);
const showPhotoModal = ref(false);
let stream = null;

// Zoom state
const zoomLevel = ref(1.5);
const minZoom = 1;
const maxZoom = 3;
const zoomStep = 0.2;

// Computed properties
const showModal = computed(() => capturedPhoto.value !== "");

const filteredAttendance = computed(() => {
    if (attendanceFilter.value === "all") {
        return todayAttendance.value;
    }
    return todayAttendance.value.filter((record) => {
        if (attendanceFilter.value === "clock_in") {
            return record.actual_timein && !record.actual_timeout;
        } else if (attendanceFilter.value === "clock_out") {
            return record.actual_timeout;
        }
        return true;
    });
});

// New methods for streamlined workflow
const captureAttendance = async () => {
    if (!form.employee_code || !cameraActive.value) return;

    // First, take the photo
    takePhoto();

    // Then validate the employee
    checkingStatus.value = true;

    try {
        const response = await axios.post(route("attendance.check-status"), {
            employee_code: form.employee_code,
        });

        if (response.data.success) {
            employeeData.value = response.data.employee;
            isClockedIn.value = response.data.isClockedIn;
            currentAttendance.value = response.data.currentAttendance;
            canClockOut.value = response.data.canClockOut;
            timeUntilClockOut.value = response.data.timeUntilClockOut;

            // Start countdown if user can't clock out yet
            if (!canClockOut.value && timeUntilClockOut.value) {
                startCountdown();
            }

            // Show photo modal for confirmation
            showPhotoModal.value = true;
        }
    } catch (error) {
        const errorMessage =
            error.response?.data?.message || "Error validating employee.";
        toast.add({
            severity: "error",
            summary: "Validation Error",
            detail: errorMessage,
            life: 4000,
        });

        // Reset on validation error
        employeeData.value = null;
        isClockedIn.value = false;
        currentAttendance.value = null;
        canClockOut.value = true;
        timeUntilClockOut.value = null;
        stopCountdown();
        retakePhoto();
    } finally {
        checkingStatus.value = false;
    }
};

const submitAttendance = () => {
    if (!employeeData.value || !photoTaken.value) return;

    form.post(route("attendance.toggle"), {
        onSuccess: (page) => {
            // Reset form state
            photoTaken.value = false;
            capturedPhoto.value = "";
            form.reset();
            employeeData.value = null;
            isClockedIn.value = false;
            currentAttendance.value = null;
            stopCamera();

            // Update attendance list
            fetchTodayAttendance();

            // Get flash data from the page props
            const flashData = page.props.flash || {};

            toast.add({
                severity: "success",
                summary: "Success",
                detail:
                    flashData.message || "Attendance recorded successfully.",
                life: 3000,
            });
        },
        onError: (errors) => {
            const errorMessage = errors.message || "An error occurred.";
            toast.add({
                severity: "error",
                summary: "Error",
                detail: errorMessage,
                life: 4000,
            });
        },
    });
};

const confirmAttendance = () => {
    if (!employeeData.value || !photoTaken.value) return;

    form.post(route("attendance.toggle"), {
        onSuccess: (page) => {
            // Get flash data from the page props
            const flashData = page.props.flash || {};

            toast.add({
                severity: "success",
                summary: "Success",
                detail:
                    flashData.message || "Attendance recorded successfully.",
                life: 3000,
            });

            // Close modal and reset form state
            showPhotoModal.value = false;
            photoTaken.value = false;
            capturedPhoto.value = "";
            form.reset();
            employeeData.value = null;
            isClockedIn.value = false;
            currentAttendance.value = null;
            stopCountdown(); // Stop any running countdown

            // Refresh attendance list and restart camera
            setTimeout(async () => {
                await fetchTodayAttendance();
                startCamera();
            }, 500);
        },
        onError: (errors) => {
            showPhotoModal.value = false;
            const errorMessage = errors.message || "An error occurred.";
            toast.add({
                severity: "error",
                summary: "Error",
                detail: errorMessage,
                life: 4000,
            });
        },
    });
};

const cancelPhotoModal = () => {
    showPhotoModal.value = false;
    retakePhoto(); // This will restart the camera and reset photo state
};

const formatCurrentTime = () => {
    return new Date().toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

const fetchTodayAttendance = async () => {
    try {
        const response = await axios.get(route("attendance.today"));
        if (response.data.success) {
            todayAttendance.value = response.data.attendance;
        }
    } catch (error) {
        console.error("Error fetching today's attendance:", error);
    }
};

// Remove old computed properties and add new methods
const handleClockInOut = async () => {
    if (!form.employee_code) return;

    checkingStatus.value = true;

    try {
        const response = await axios.post(route("attendance.check-status"), {
            employee_code: form.employee_code,
        });

        if (response.data.success) {
            employeeData.value = response.data.employee;
            isClockedIn.value = response.data.isClockedIn;
            currentAttendance.value = response.data.currentAttendance;

            toast.add({
                severity: "success",
                summary: "Employee Found",
                detail: `Found employee: ${response.data.employee.name}`,
                life: 3000,
            });
        }
    } catch (error) {
        const errorMessage =
            error.response?.data?.message || "Error checking employee status.";
        toast.add({
            severity: "error",
            summary: "Error",
            detail: errorMessage,
            life: 4000,
        });

        // Reset employee data on error
        employeeData.value = null;
        isClockedIn.value = false;
        currentAttendance.value = null;
    } finally {
        checkingStatus.value = false;
    }
};

const checkEmployeeStatus = async () => {
    if (!form.employee_code) return;

    checkingStatus.value = true;

    try {
        const response = await axios.post(route("attendance.check-status"), {
            employee_code: form.employee_code,
        });

        if (response.data.success) {
            employeeData.value = response.data.employee;
            isClockedIn.value = response.data.isClockedIn;
            currentAttendance.value = response.data.currentAttendance;

            toast.add({
                severity: "success",
                summary: "Employee Found",
                detail: `Found employee: ${response.data.employee.name}`,
                life: 3000,
            });
        }
    } catch (error) {
        const errorMessage =
            error.response?.data?.message || "Error checking employee status.";
        toast.add({
            severity: "error",
            summary: "Error",
            detail: errorMessage,
            life: 4000,
        });

        // Reset employee data on error
        employeeData.value = null;
        isClockedIn.value = false;
        currentAttendance.value = null;
    } finally {
        checkingStatus.value = false;
    }
};

// Methods
const startCamera = async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user" },
            audio: false,
        });

        if (video.value) {
            video.value.srcObject = stream;
            cameraActive.value = true;
            resetZoom(); // Reset zoom when starting camera
        }
    } catch (error) {
        console.error("Error accessing camera:", error);
        toast.add({
            severity: "error",
            summary: "Camera Error",
            detail: "Unable to access camera. Please check permissions.",
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
    if (!video.value) return;

    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");

    canvas.width = video.value.videoWidth;
    canvas.height = video.value.videoHeight;

    context.drawImage(video.value, 0, 0);

    capturedPhoto.value = canvas.toDataURL("image/jpeg", 0.8);
    form.photo = capturedPhoto.value;
    photoTaken.value = true;

    stopCamera();
};

const retakePhoto = () => {
    photoTaken.value = false;
    capturedPhoto.value = "";
    form.photo = "";
    resetZoom(); // Reset zoom when retaking photo
    startCamera();
};

// Zoom control methods
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

const handleClockToggle = async () => {
    // Check if employee exists and photo is taken
    if (!employeeData.value) {
        toast.add({
            severity: "warn",
            summary: "Employee Required",
            detail: "Please enter your employee code first.",
            life: 3000,
        });
        return;
    }

    if (!photoTaken.value) {
        toast.add({
            severity: "warn",
            summary: "Photo Required",
            detail: "Please take a photo before clocking in/out.",
            life: 3000,
        });
        return;
    }

    showConfirmModal.value = true;
};

const confirmClockToggle = () => {
    form.post(route("attendance.toggle"), {
        onSuccess: (response) => {
            showConfirmModal.value = false;
            photoTaken.value = false;
            capturedPhoto.value = "";
            form.reset("photo");

            // Update local state
            const data = response.props.flash || {};
            if (data.success) {
                isClockedIn.value = data.action === "clock_in";
                if (data.attendance) {
                    currentAttendance.value = data.attendance;
                }
            }

            toast.add({
                severity: "success",
                summary: "Success",
                detail:
                    data.message ||
                    (isClockedIn.value
                        ? "Successfully clocked in."
                        : "Successfully clocked out."),
                life: 3000,
            });
        },
        onError: (errors) => {
            showConfirmModal.value = false;

            const errorMessage = errors.message || "An error occurred.";
            toast.add({
                severity: "error",
                summary: "Error",
                detail: errorMessage,
                life: 4000,
            });
        },
    });
};

const cancelConfirmation = () => {
    showConfirmModal.value = false;
};

const formatTime = (datetime) => {
    if (!datetime) return "";
    return new Date(datetime).toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

const formatDate = (date) => {
    if (!date) return "";
    return new Date(date).toLocaleDateString("en-US", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

// Countdown functionality
const startCountdown = () => {
    if (countdownInterval.value) {
        clearInterval(countdownInterval.value);
    }

    countdownInterval.value = setInterval(() => {
        if (
            timeUntilClockOut.value &&
            timeUntilClockOut.value.total_seconds > 0
        ) {
            timeUntilClockOut.value.total_seconds--;
            timeUntilClockOut.value.minutes = Math.floor(
                timeUntilClockOut.value.total_seconds / 60
            );
            timeUntilClockOut.value.seconds =
                timeUntilClockOut.value.total_seconds % 60;

            if (timeUntilClockOut.value.total_seconds <= 0) {
                canClockOut.value = true;
                timeUntilClockOut.value = null;
                clearInterval(countdownInterval.value);
                countdownInterval.value = null;
            }
        }
    }, 1000);
};

const stopCountdown = () => {
    if (countdownInterval.value) {
        clearInterval(countdownInterval.value);
        countdownInterval.value = null;
    }
    timeUntilClockOut.value = null;
    canClockOut.value = true;
};

// Lifecycle
onMounted(() => {
    // Auto-fill employee code if available
    if (props.user?.employee_code) {
        form.employee_code = props.user.employee_code;
    }

    // Fetch today's attendance
    fetchTodayAttendance();

    // Auto-start camera
    startCamera();

    // Add keyboard shortcuts for zoom
    const handleKeyPress = (event) => {
        if (!cameraActive.value || photoTaken.value) return;

        // Don't interfere with typing in input fields
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

    document.addEventListener("keydown", handleKeyPress);

    // Store the handler for cleanup
    window.zoomKeyHandler = handleKeyPress;
});

onUnmounted(() => {
    stopCamera();
    stopCountdown(); // Clean up countdown

    // Remove keyboard event listener
    if (window.zoomKeyHandler) {
        document.removeEventListener("keydown", window.zoomKeyHandler);
        delete window.zoomKeyHandler;
    }
});
</script>
