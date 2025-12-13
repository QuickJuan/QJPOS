<template>
    <CashieringLayout>
        <div class="flex flex-col h-full overflow-hidden">
            <!-- Sticky Header -->
            <div
                class="flex-shrink-0 px-4 md:px-6 lg:px-8 py-4 bg-white border-b border-gray-200"
            >
                <div
                    class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
                >
                    <h1 class="text-2xl font-bold text-gray-900">
                        Review X Readings
                    </h1>

                    <!-- Filters in Header -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Per Page Selection -->
                        <select
                            v-model="perPage"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @change="handlePerPageChange"
                        >
                            <option :value="5">5 per page</option>
                            <option :value="10">10 per page</option>
                            <option :value="20">20 per page</option>
                            <option :value="50">50 per page</option>
                        </select>

                        <!-- Cashier Filter -->
                        <select
                            v-model="filters.cashier_id"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">All Cashiers</option>
                            <option
                                v-for="cashier in cashierOptions"
                                :key="cashier.id"
                                :value="cashier.id"
                            >
                                {{ cashier.name }}
                            </option>
                        </select>

                        <!-- Month Filter -->
                        <select
                            v-model="selectedMonth"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @change="handleMonthYearChange"
                        >
                            <option value="">All Months</option>
                            <option value="01">January</option>
                            <option value="02">February</option>
                            <option value="03">March</option>
                            <option value="04">April</option>
                            <option value="05">May</option>
                            <option value="06">June</option>
                            <option value="07">July</option>
                            <option value="08">August</option>
                            <option value="09">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>

                        <!-- Year Filter -->
                        <select
                            v-model="selectedYear"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @change="handleMonthYearChange"
                        >
                            <option value="">All Years</option>
                            <option
                                v-for="year in yearOptions"
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>

                        <!-- Clear Filters Button -->
                        <button
                            @click="clearFilters"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content Area with Scroll -->
            <div
                class="flex-1 flex flex-col md:flex-row gap-4 md:gap-6 px-4 md:px-6 lg:px-8 py-4 overflow-hidden"
            >
                <!-- Sidebar -->
                <div
                    class="w-full md:w-3/5 2xl:w-1/4 flex flex-col overflow-hidden"
                >
                    <!-- Sessions List with Scroll -->
                    <div class="flex-1 overflow-y-auto">
                        <CashierSessions
                            :sessions="props.sessions.data"
                            :activeSession="activeSession"
                            :paginatedSessions="props.sessions"
                            @selectSession="selectSession"
                            @goToPage="goToPage"
                        />
                    </div>
                </div>

                <!-- Detail Pane -->
                <div class="flex flex-col w-full overflow-hidden">
                    <div
                        v-if="activeSession"
                        class="flex flex-col h-full overflow-hidden"
                    >
                        <div
                            class="px-4 py-5 md:px-6 md:py-6 border-b border-gray-100 bg-white flex-shrink-0 sticky top-0 z-10"
                        >
                            <div
                                class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between"
                            >
                                <div>
                                    <p class="text-sm text-gray-500">
                                        Shift #{{ activeSession.id }} -
                                        {{ activeSession.cashier?.name }}
                                    </p>
                                </div>
                                <div
                                    class="flex flex-wrap items-center gap-3 justify-end"
                                >
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800"
                                    >
                                        Closed
                                    </span>

                                    <!-- Desktop action buttons -->
                                    <div class="hidden lg:flex flex-wrap gap-2">
                                        <Button
                                            label="Print Report"
                                            icon="pi pi-print"
                                            outlined
                                            :class="subtleActionButtonClass"
                                            @click="handlePrintReport"
                                        />
                                        <Button
                                            label="Close Session"
                                            icon="pi pi-lock"
                                            outlined
                                            :class="cautionActionButtonClass"
                                            @click="closeSession(activeSession)"
                                        />
                                    </div>

                                    <!-- Mobile hamburger menu -->
                                    <div class="lg:hidden relative z-50">
                                        <Button
                                            icon="pi pi-ellipsis-v"
                                            outlined
                                            :class="subtleActionButtonClass"
                                            @click="toggleActionMenu"
                                        />
                                        <div
                                            v-if="showActionMenu"
                                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-[100]"
                                        >
                                            <div
                                                class="fixed inset-0 z-[90] lg:hidden"
                                                @click="toggleActionMenu"
                                            ></div>
                                            <!-- Mobile menu -->
                                            <div
                                                v-if="showActionMenu"
                                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-[100]"
                                                data-menu-container
                                            >
                                                <button
                                                    class="w-full text-left px-4 py-3 hover:bg-blue-50 flex items-center gap-3 text-sm text-blue-700 transition-colors duration-150"
                                                    @click="
                                                        () => {
                                                            handlePrintReport();
                                                            toggleActionMenu();
                                                        }
                                                    "
                                                >
                                                    <i class="pi pi-print"></i>
                                                    Print Report
                                                </button>
                                                <button
                                                    class="w-full text-left px-4 py-3 hover:bg-amber-50 flex items-center gap-3 text-sm border-t border-gray-100 text-amber-700"
                                                    @click="
                                                        () => {
                                                            closeSession(
                                                                activeSession
                                                            );
                                                            toggleActionMenu();
                                                        }
                                                    "
                                                >
                                                    <i class="pi pi-lock"></i>
                                                    Close Session
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="px-4 py-5 md:px-6 md:py-6 space-y-8 flex-1 overflow-y-auto"
                        >
                            <div class="flex flex-col lg:flex-row gap-4">
                                <!-- Session Details -->
                                <div class="flex flex-col w-full">
                                    <!-- Session Summary Details -->
                                    <div
                                        v-if="sessionSummary"
                                        class="rounded-2xl border border-gray-100 bg-white p-6 space-y-6 mt-4"
                                    >
                                        <p
                                            class="text-xs uppercase tracking-wide text-gray-500"
                                        >
                                            Session Summary
                                        </p>

                                        <!-- Cash Summary -->
                                        <div>
                                            <h4
                                                class="text-sm font-semibold text-gray-700 mb-3"
                                            >
                                                Cash Summary
                                            </h4>
                                            <div
                                                class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                            >
                                                <div>
                                                    <p
                                                        class="text-sm text-gray-600"
                                                    >
                                                        Beginning Cash
                                                    </p>
                                                    <p class="font-semibold">
                                                        ₱{{
                                                            formatMoney(
                                                                sessionSummary.beginning_cash ||
                                                                    0
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-sm text-gray-600"
                                                    >
                                                        Cash Sales
                                                    </p>
                                                    <p class="font-semibold">
                                                        ₱{{
                                                            formatMoney(
                                                                sessionSummary.cash_sales ||
                                                                    0
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-sm text-gray-600"
                                                    >
                                                        Expected Cash
                                                    </p>
                                                    <p class="font-semibold">
                                                        ₱{{
                                                            formatMoney(
                                                                sessionSummary.expected_cash ||
                                                                    0
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-sm text-gray-600"
                                                    >
                                                        Actual Cash Count
                                                    </p>
                                                    <p class="font-semibold">
                                                        ₱{{
                                                            formatMoney(
                                                                sessionSummary.actual_cash_count ||
                                                                    0
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-sm text-gray-600"
                                                    >
                                                        Cash Variance
                                                    </p>
                                                    <p
                                                        :class="[
                                                            'font-semibold',
                                                            (sessionSummary.cash_variance ||
                                                                0) >= 0
                                                                ? 'text-green-600'
                                                                : 'text-red-600',
                                                        ]"
                                                    >
                                                        ₱{{
                                                            formatMoney(
                                                                sessionSummary.cash_variance ||
                                                                    0
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Payment Methods -->
                                        <div
                                            v-if="
                                                sessionSummary.payment_methods
                                            "
                                        >
                                            <h4
                                                class="text-sm font-semibold text-gray-700 mb-3"
                                            >
                                                Payment Methods
                                            </h4>
                                            <div
                                                class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                            >
                                                <div
                                                    v-for="(
                                                        amount, method
                                                    ) in sessionSummary.payment_methods"
                                                    :key="method"
                                                >
                                                    <p
                                                        class="text-sm text-gray-600 capitalize"
                                                    >
                                                        {{ method }}
                                                    </p>
                                                    <p class="font-semibold">
                                                        ₱{{
                                                            formatMoney(amount)
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sales Summary -->
                                        <div>
                                            <h4
                                                class="text-sm font-semibold text-gray-700 mb-3"
                                            >
                                                Sales Summary
                                            </h4>
                                            <div
                                                class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                            >
                                                <div>
                                                    <p
                                                        class="text-sm text-gray-600"
                                                    >
                                                        Total Transactions
                                                    </p>
                                                    <p class="font-semibold">
                                                        {{
                                                            sessionSummary.total_transactions ||
                                                            0
                                                        }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-sm text-gray-600"
                                                    >
                                                        Gross Sales
                                                    </p>
                                                    <p class="font-semibold">
                                                        ₱{{
                                                            formatMoney(
                                                                sessionSummary.gross_sales ||
                                                                    0
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-sm text-gray-600"
                                                    >
                                                        Total Discounts
                                                    </p>
                                                    <p class="font-semibold">
                                                        ₱{{
                                                            formatMoney(
                                                                sessionSummary.total_discounts ||
                                                                    0
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-sm text-gray-600"
                                                    >
                                                        Net Sales
                                                    </p>
                                                    <p
                                                        class="font-semibold text-green-600"
                                                    >
                                                        ₱{{
                                                            formatMoney(
                                                                sessionSummary.net_sales ||
                                                                    0
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="flex flex-col items-center justify-center h-full py-16 text-center text-gray-400"
                    >
                        <p class="text-lg font-semibold">No session selected</p>
                        <p class="text-sm mt-2">
                            Use the list on the left to pick a session to
                            review.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, reactive, onMounted, onUnmounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";
import PageProps from "@/Types/PageProps";
import CashieringSession from "@/Types/CashieringSession";
import Button from "primevue/button";
import debounce from "lodash/debounce";
import axios from "axios";
import CashierSessions from "./Partials/CashierSessions.vue";

const props = defineProps<{
    sessions: any;
    filters: {
        search: string;
        date_from: string;
        date_to: string;
        status: string;
        cashier_id: string;
        per_page: number;
    };
}>();

const page = usePage<PageProps>();
const cashierOptions = computed(() => page.props?.cashiers || []);

// Pagination
const perPage = ref(props.filters.per_page || 10);

// Date filters
const selectedMonth = ref("");
const selectedYear = ref("");

// Generate year options (current year and 5 years back)
const yearOptions = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let i = 0; i <= 5; i++) {
        years.push(currentYear - i);
    }
    return years;
});
const cashierDropdownOptions = computed(() =>
    cashierOptions.value.map((cashier: { id: number; name: string }) => ({
        label: cashier.name,
        value: cashier.id,
    }))
);
const statusOptions = [{ label: "Closed", value: "closed" }];

// const closedSessions = computed(() => props.sessions.filter(session => session.closing_time !== null));

const filters = reactive({
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
    status: props.filters.status || "closed",
    cashier_id: props.filters.cashier_id || "",
});

const search = ref(props.filters.search || "");

// Compute dateRange from filters
const dateRange = computed<[Date | null, Date | null] | null>(() => {
    if (!filters.date_from && !filters.date_to) return null;
    return [
        filters.date_from ? new Date(filters.date_from) : null,
        filters.date_to ? new Date(filters.date_to) : null,
    ];
});

const handleDateRangeChange = ([start, end]: [
    string | null,
    string | null
]) => {
    filters.date_from = start || "";
    filters.date_to = end || "";
};

const activeSession = ref<CashieringSession | null>(null);
const showActionMenu = ref(false);
const sessionSummary = ref<any>(null);

const formatMoney = (amount: number | string) => {
    const num = typeof amount === "string" ? parseFloat(amount) : amount;
    return num.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};
const toggleActionMenu = () => {
    showActionMenu.value = !showActionMenu.value;
};

// Close menu when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (
        !target.closest(".relative") &&
        !target.closest("[data-menu-container]")
    ) {
        showActionMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString(undefined, {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "numeric",
        minute: "2-digit",
    });
};

const closeSession = async (session: any) => {
    // Implement close session logic
    alert(`Closing session #${session.id}...`);
};

const goToPage = (url: string | null) => {
    if (!url) return;
    const urlObj = new URL(url);
    const page = urlObj.searchParams.get("page");
    if (page) {
        const data: any = { page: parseInt(page) };
        if (filters.date_from) data.date_from = filters.date_from;
        if (filters.date_to) data.date_to = filters.date_to;
        if (filters.status) data.status = filters.status;
        if (filters.cashier_id) data.cashier_id = filters.cashier_id;
        if (search.value) data.search = search.value;
        if (perPage.value) data.per_page = perPage.value;

        router.get(route("resto.review-x-readings"), data, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const selectSession = async (session: any) => {
    activeSession.value = session;

    // Fetch session summary when a session is selected
    try {
        const response = await axios.get(
            `/resto/api/session-summary/${session.id}`
        );
        sessionSummary.value = response.data;
    } catch (error) {
        console.error("Failed to fetch session summary:", error);
        sessionSummary.value = null;
    }
};

const handlePerPageChange = () => {
    const data: any = { per_page: perPage.value };
    if (filters.cashier_id) data.cashier_id = filters.cashier_id;
    if (filters.date_from) data.date_from = filters.date_from;
    if (filters.date_to) data.date_to = filters.date_to;
    if (search.value) data.search = search.value;

    router.get(route("resto.review-x-readings"), data, {
        preserveScroll: true,
        preserveState: true,
    });
};

const handleMonthYearChange = () => {
    if (selectedMonth.value && selectedYear.value) {
        // Calculate first and last day of selected month
        const firstDay = new Date(
            parseInt(selectedYear.value),
            parseInt(selectedMonth.value) - 1,
            1
        );
        const lastDay = new Date(
            parseInt(selectedYear.value),
            parseInt(selectedMonth.value),
            0
        );

        filters.date_from = firstDay.toISOString().split("T")[0];
        filters.date_to = lastDay.toISOString().split("T")[0];
    } else {
        filters.date_from = "";
        filters.date_to = "";
    }
};

const clearFilters = () => {
    filters.cashier_id = "";
    filters.date_from = "";
    filters.date_to = "";
    selectedMonth.value = "";
    selectedYear.value = "";
    perPage.value = 10;
    search.value = "";

    router.get(
        route("resto.review-x-readings"),
        {},
        {
            preserveScroll: true,
            preserveState: true,
        }
    );
};

const handlePrintReport = async () => {
    if (!activeSession.value || !sessionSummary.value) return;

    // Print the session summary (you can implement thermal printer logic here)
    console.log("Printing session summary:", sessionSummary.value);
    alert("Print functionality to be implemented");
};

const subtleActionButtonClass =
    "!rounded-full !border !border-primary/20 !bg-primary/5 !text-primary !px-4 !py-2 !text-sm !font-semibold hover:!bg-primary/10";
const cautionActionButtonClass =
    "!rounded-full !border !border-amber-300 !bg-amber-50 !text-amber-700 !px-4 !py-2 !text-sm !font-semibold hover:!bg-amber-100";

const getStatusClass = (status: string) => {
    switch (status) {
        case "open":
            return "bg-green-100 text-green-800";
        case "closed":
            return "bg-gray-100 text-gray-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case "open":
            return "Open";
        case "closed":
            return "Closed";
        default:
            return status;
    }
};

watch(
    search,
    debounce((newValue: string) => {
        const routeParams: any = {
            search: newValue,
        };
        if (perPage.value) routeParams.per_page = perPage.value;

        router.visit(route("resto.review-x-readings"), {
            data: routeParams,
            replace: true,
            only: ["sessions"],
            preserveState: true,
            preserveScroll: true,
        });
    }, 250) as any
);

watch(
    () => filters,
    debounce((newFilters) => {
        const data: any = {};
        if (newFilters.date_from) data.date_from = newFilters.date_from;
        if (newFilters.date_to) data.date_to = newFilters.date_to;
        if (newFilters.status) data.status = newFilters.status;
        if (newFilters.cashier_id) data.cashier_id = newFilters.cashier_id;
        if (search.value) data.search = search.value;
        if (perPage.value) data.per_page = perPage.value;

        router.get(route("resto.review-x-readings"), data, {
            preserveScroll: true,
            preserveState: true,
        });
    }, 250) as any,
    { deep: true }
);
</script>
