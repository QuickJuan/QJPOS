<template>
    <HomeLayout>
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
                <p class="text-sm font-medium text-gray-600">Current Branch</p>
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
                                    />
                                    On duty
                                </span>
                            </div>
                        </div>
                    </div>
                </Link>
            </template>
        </div>
    </HomeLayout>
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
import HomeLayout from "@/Layouts/HomeLayout.vue";

const page = usePage();
const toast = useToast();

// Props from the controller
const props = defineProps({
    user: Object,
    attendanceStatus: Object,
});

// Reactive state
const isClockedIn = ref(props.attendanceStatus?.is_clocked_in || false);

// Computed properties
const user = computed(() => props.user);
const activeBranch = computed(() => page.props.active_branch);

const actions = [
    // {
    //     route: "dashboard",
    //     name: "Dashboard",
    //     description: "View reports, analytics, and business insights",
    //     icon: AnalyticsIcon,
    // },
    {
        route: "resto.preview",
        name: "Start Cashiering",
        description: "Process sales transactions and manage POS",
        icon: CashieringIcon,
    },
    {
        route: "table-management.index",
        name: "Table Management",
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
