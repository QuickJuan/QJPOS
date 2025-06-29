<template>
    <Head title="Home - QJPOS" />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo and Navigation -->
                    <div class="flex items-center space-x-8">
                        <div class="flex items-center space-x-3">
                            <div class="bg-primary p-2 rounded-lg">
                                <svg
                                    class="w-8 h-8 text-white"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M12 2L2 7V10C2 16 6 20.5 12 22C18 20.5 22 16 22 10V7L12 2ZM12 4.19L19 7.3V10C19 15.85 15.74 19.94 12 20.96C8.26 19.94 5 15.85 5 10V7.3L12 4.19Z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900">
                                    QJ<span class="text-primary">POS</span>
                                </h1>
                                <p class="text-xs text-gray-500">
                                    {{
                                        activeBranch?.name ||
                                        "No Branch Selected"
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Navigation Links -->
                        <nav class="hidden md:flex space-x-6">
                            <span
                                class="text-primary font-semibold px-3 py-2 rounded-md text-sm"
                            >
                                Home
                            </span>
                            <Link
                                :href="route('dashboard')"
                                class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors"
                            >
                                Dashboard
                            </Link>
                        </nav>
                    </div>

                    <!-- User Info and Logout -->
                    <div class="flex items-center space-x-4">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium text-gray-900">
                                {{ user.name }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ user.email }}
                            </p>
                        </div>
                        <button
                            @click="logout"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center space-x-2"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                ></path>
                            </svg>
                            <span>Logout</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Welcome Section -->
            <div
                class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center"
            >
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">
                        Welcome back, {{ user.name }}!
                    </h2>
                    <p class="text-lg text-gray-600">
                        Choose what you'd like to do today
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 text-right">
                    <p class="text-sm font-medium text-gray-600">
                        Current Branch
                    </p>
                    <p class="text-lg font-semibold text-primary">
                        {{ activeBranch?.name || "No Branch Selected" }}
                    </p>
                    <p class="text-xs text-gray-500">
                        Code: {{ activeBranch?.branch_code || "N/A" }}
                    </p>
                </div>
            </div>

            <!-- Navigation Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Dashboard -->
                <Link
                    :href="route('dashboard')"
                    class="group bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 border hover:border-primary transform hover:scale-105"
                >
                    <div
                        class="flex flex-col items-center text-center space-y-4"
                    >
                        <div
                            class="bg-blue-500 group-hover:bg-blue-600 p-4 rounded-full transition-colors duration-300"
                        >
                            <svg
                                class="w-8 h-8 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                ></path>
                            </svg>
                        </div>
                        <div>
                            <h3
                                class="text-xl font-semibold text-gray-900 group-hover:text-primary transition-colors duration-300"
                            >
                                Dashboard
                            </h3>
                            <p class="text-sm text-gray-600 mt-2">
                                View reports, analytics, and business insights
                            </p>
                        </div>
                    </div>
                </Link>

                <!-- Start Cashiering -->
                <button
                    @click="startCashiering"
                    class="group bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 border hover:border-green-500 transform hover:scale-105"
                >
                    <div
                        class="flex flex-col items-center text-center space-y-4"
                    >
                        <div
                            class="bg-green-500 group-hover:bg-green-600 p-4 rounded-full transition-colors duration-300"
                        >
                            <svg
                                class="w-8 h-8 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                                ></path>
                            </svg>
                        </div>
                        <div>
                            <h3
                                class="text-xl font-semibold text-gray-900 group-hover:text-green-600 transition-colors duration-300"
                            >
                                Start Cashiering
                            </h3>
                            <p class="text-sm text-gray-600 mt-2">
                                Process sales transactions and manage POS
                            </p>
                        </div>
                    </div>
                </button>

                <!-- Table Ordering -->
                <button
                    @click="tableOrdering"
                    class="group bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 border hover:border-purple-500 transform hover:scale-105"
                >
                    <div
                        class="flex flex-col items-center text-center space-y-4"
                    >
                        <div
                            class="bg-purple-500 group-hover:bg-purple-600 p-4 rounded-full transition-colors duration-300"
                        >
                            <svg
                                class="w-8 h-8 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                ></path>
                            </svg>
                        </div>
                        <div>
                            <h3
                                class="text-xl font-semibold text-gray-900 group-hover:text-purple-600 transition-colors duration-300"
                            >
                                Table Ordering
                            </h3>
                            <p class="text-sm text-gray-600 mt-2">
                                Manage restaurant orders and table service
                            </p>
                        </div>
                    </div>
                </button>

                <!-- Clock In/Out -->
                <button
                    @click="toggleAttendance"
                    class="group bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 border hover:border-orange-500 transform hover:scale-105"
                >
                    <div
                        class="flex flex-col items-center text-center space-y-4"
                    >
                        <div
                            class="bg-orange-500 group-hover:bg-orange-600 p-4 rounded-full transition-colors duration-300"
                        >
                            <svg
                                class="w-8 h-8 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                        </div>
                        <div>
                            <h3
                                class="text-xl font-semibold text-gray-900 group-hover:text-orange-600 transition-colors duration-300"
                            >
                                {{ isClockedIn ? "Clock Out" : "Clock In" }}
                            </h3>
                            <p class="text-sm text-gray-600 mt-2">
                                {{
                                    isClockedIn
                                        ? "End your work shift"
                                        : "Start your work shift"
                                }}
                            </p>
                            <div v-if="isClockedIn" class="mt-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    <div
                                        class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"
                                    ></div>
                                    On duty
                                </span>
                            </div>
                        </div>
                    </div>
                </button>
            </div>
        </main>

        <!-- Toast Notifications -->
        <Toast />
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { useToast } from "primevue";
import { Toast } from "primevue";

const page = usePage();
const toast = useToast();

// Props from the controller
const props = defineProps({
    user: Object,
    activeBranch: Object,
    attendanceStatus: Object,
});

// Reactive state
const isClockedIn = ref(props.attendanceStatus?.is_clocked_in || false);

// Computed properties
const user = computed(() => props.user);
const activeBranch = computed(() => props.activeBranch);

// Methods
const logout = () => {
    router.post(
        route("logout"),
        {},
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Logged out",
                    detail: "You have been successfully logged out.",
                    life: 3000,
                });
            },
            onError: () => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "There was an error logging out. Please try again.",
                    life: 4000,
                });
            },
        }
    );
};

const startCashiering = () => {
    if (!activeBranch.value) {
        toast.add({
            severity: "warn",
            summary: "No Branch Selected",
            detail: "Please select a branch before starting cashiering.",
            life: 4000,
        });
        return;
    }

    // Navigate to cashiering system
    // router.visit(route('cashier'));
    toast.add({
        severity: "info",
        summary: "Starting Cashier",
        detail: "Launching cashiering system...",
        life: 3000,
    });
};

const tableOrdering = () => {
    if (!activeBranch.value) {
        toast.add({
            severity: "warn",
            summary: "No Branch Selected",
            detail: "Please select a branch before accessing table ordering.",
            life: 4000,
        });
        return;
    }

    // Navigate to table ordering system
    // router.visit(route('table-ordering'));
    toast.add({
        severity: "info",
        summary: "Table Ordering",
        detail: "Launching table ordering system...",
        life: 3000,
    });
};

const toggleAttendance = () => {
    const action = isClockedIn.value ? "clock-out" : "clock-in";

    // Make API call to clock in/out
    // router.post(route('attendance.toggle'), { action }, {
    //     onSuccess: () => {
    //         isClockedIn.value = !isClockedIn.value;
    //         toast.add({
    //             severity: 'success',
    //             summary: isClockedIn.value ? 'Clocked In' : 'Clocked Out',
    //             detail: isClockedIn.value ? 'Your shift has started.' : 'Your shift has ended.',
    //             life: 3000,
    //         });
    //     },
    //     onError: () => {
    //         toast.add({
    //             severity: 'error',
    //             summary: 'Error',
    //             detail: 'There was an error processing your attendance.',
    //             life: 4000,
    //         });
    //     }
    // });

    // Temporary simulation
    isClockedIn.value = !isClockedIn.value;
    toast.add({
        severity: "success",
        summary: isClockedIn.value ? "Clocked In" : "Clocked Out",
        detail: isClockedIn.value
            ? "Your shift has started."
            : "Your shift has ended.",
        life: 3000,
    });
};
</script>
