<template>
    <TransactionsLayout>
        <!-- <template #header>
            <div>
                <SearchAndFIlter
                    :search="search"
                    :dateRange="dateRange"
                    :status="filters.status"
                    :cashier_id="filters.cashier_id"
                    :statusOptions="statusOptions"
                    :cashierDropdownOptions="cashierDropdownOptions"
                    @search="(value: string) => { search = value }"
                    @dateRange="handleDateRangeChange"
                    @status="(value: string) => { filters.status = value }"
                    @cashier_id="(value: string) => { filters.cashier_id = value }"
                />
            </div>
        </template> -->
        <div class="flex flex-col h-full px-4 md:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row gap-4 md:gap-6 h-full">
                <!-- Sidebar -->
                <div
                    class="w-full md:w-3/5 2xl:w-1/4 h-auto md:h-full flex flex-col min-h-[300px] md:min-h-0"
                >
                    <CashierSessions
                        :sessions="props.sessions.data"
                        :activeSession="activeSession"
                        :paginatedSessions="props.sessions"
                        @selectSession="selectSession"
                        @goToPage="goToPage"
                    />
                </div>

                <!-- Detail Pane -->
                <div class="flex flex-col w-full h-auto md:h-full">
                    <div v-if="activeSession" class="flex flex-col h-full">
                        <div
                            class="px-4 py-5 md:px-6 md:py-6 border-b border-gray-100 bg-white flex-shrink-0"
                        >
                            <div
                                class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between"
                            >
                                <div>
                                    <p class="text-sm text-gray-500">
                                        Session #{{ activeSession.id }} - {{ activeSession.cashier?.name }}
                                    </p>
                                </div>
                                <div
                                    class="flex flex-wrap items-center gap-3 justify-end"
                                >
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
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
                                                            closeSession(activeSession);
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
                                    <div class="rounded-2xl border border-gray-100 bg-white p-6 space-y-3">
                                        <p class="text-xs uppercase tracking-wide text-gray-500">
                                            Session Details
                                        </p>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-600">Started At</p>
                                                <p class="font-semibold">{{ formatDate(activeSession.started_time) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Ended At</p>
                                                <p class="font-semibold">{{ activeSession.closing_time ? formatDate(activeSession.closing_time) : 'Ongoing' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Total Sales</p>
                                                <p class="font-semibold">₱{{ activeSession.total_sales }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Beginning Cash</p>
                                                <p class="font-semibold">₱{{ activeSession.beginning_cash }}</p>
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
                        <p class="text-lg font-semibold">
                            No session selected
                        </p>
                        <p class="text-sm mt-2">
                            Use the list on the left to pick a session to review.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Session Summary Modal -->
        <SessionSummaryModal
            :showSessionSummaryModal="showSessionSummaryModal"
            :openSession="activeSession"
            :sessionSummary="sessionSummary"
            @closeModal="showSessionSummaryModal = false"
            @confirmClose="showSessionSummaryModal = false"
        />
    </TransactionsLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, reactive, onMounted, onUnmounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import TransactionsLayout from "@/Layouts/TransactionsLayout.vue";
import PageProps from "@/Types/PageProps";
import CashieringSession from "@/Types/CashieringSession";
import Button from "primevue/button";
import SessionSummaryModal from "./Partials/SessionSummaryModal.vue";
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
    };
}>();

const page = usePage<PageProps>();
const cashierOptions = computed(() => page.props?.cashiers || []);
const cashierDropdownOptions = computed(() =>
    cashierOptions.value.map((cashier: { id: number; name: string }) => ({
        label: cashier.name,
        value: cashier.id,
    }))
);
const statusOptions = [
    { label: "Closed", value: "closed" },
];

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
const showSessionSummaryModal = ref(false);
const sessionSummary = ref<any>(null);

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

        router.get(route("resto.review-x-transactions"), data, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const selectSession = (session: any) => {
    activeSession.value = session;
};

const handlePrintReport = async () => {
    if (!activeSession.value) return;

    try {
        const response = await axios.get(`/resto/api/session-summary/${activeSession.value.id}`);
        sessionSummary.value = response.data;
        showSessionSummaryModal.value = true;
    } catch (error) {
        console.error('Failed to fetch session summary:', error);
        alert('Failed to load session summary.');
    }
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
        const routeParams = {
            search: newValue,
        };

        router.visit(route("resto.review-x-transactions"), {
            data: routeParams,
            replace: true,
            only: ["sessions"],
            preserveState: true,
            preserveScroll: true,
        });
    }, 250)
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

        router.get(route("resto.review-x-transactions"), data, {
            preserveScroll: true,
            preserveState: true,
        });
    }, 250),
    { deep: true }
);
</script>