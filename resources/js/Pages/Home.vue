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
                <template v-for="(action, key) in actions" :key="key">
                    <!-- Dashboard -->
                    <Link
                        :href="route(action.route)"
                        class="group bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6 border hover:border-primary transform hover:scale-105"
                    >
                        <div
                            class="flex flex-col items-center text-center space-y-4"
                        >
                            <component :is="action.icon" />

                            <div>
                                <h3
                                    class="text-xl font-semibold text-gray-900 group-hover:text-primary transition-colors duration-300"
                                >
                                    {{ action.name }}
                                </h3>
                                <p class="text-sm text-gray-600 mt-2">
                                    {{ action.description }}
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
                    </Link>
                </template>
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
import AnalyticsIcon from "@/Components/icons/HomeIcons/AnalyticsIcon.vue";
import CashieringIcon from "@/Components/icons/HomeIcons/CashieringIcon.vue";
import TableOrderingIcon from "@/Components/icons/HomeIcons/TableOrderingIcon.vue";
import ClockInOutIcon from "@/Components/icons/HomeIcons/ClockInOutIcon.vue";

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

const actions = [
    {
        route: "dashboard",
        name: "Dashboard",
        description: "View reports, analytics, and business insights",
        icon: AnalyticsIcon,
    },
    {
        route: "retail-cashier",
        name: "Start Cashiering",
        description: "Process sales transactions and manage POS",
        icon: CashieringIcon,
    },
    {
        route: "dashboard",
        name: "Table Ordering",
        description: "Manage restaurant orders and table service",
        icon: TableOrderingIcon,
    },
    {
        route: "attendance.index",
        name: "Clock In/Out",
        description: "Manage your work attendance",
        icon: ClockInOutIcon,
    },
];
</script>
