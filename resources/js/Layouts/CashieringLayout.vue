<template>
    <div class="h-screen bg-neutral-50 flex flex-col">
        <!-- Header Component -->
        <CashierHeader
            :cashier-name="cashierName"
            :company-name="companyName"
            @close-shift="handleCloseShift"
            @logout="handleLogout"
            @review-transactions="handleReviewTransactionsClick"
        />

        <!-- Sidebar Overlay (Mobile/Tablet) -->
        <div
            v-if="showSidebar"
            @click="toggleSidebar"
            class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-[65] transition-opacity"
        ></div>

        <!-- Sidebar (Mobile/Tablet) -->
        <aside
            :class="[
                'lg:hidden fixed left-0 bottom-0 w-80 bg-white shadow-2xl z-[70] transform transition-transform duration-300 ease-in-out overflow-y-auto',
                'top-[56px]',
                showSidebar ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="p-6">
                <!-- Cashier Info -->
                <div class="mb-6 pb-6 border-b border-neutral-200">
                    <p class="text-sm text-neutral-500 mb-1">Cashier</p>
                    <p class="text-lg font-bold text-neutral-900">
                        {{ cashierName }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button
                        v-if="!checkCurrentRoute('resto.tables')"
                        @click="handleTablesClick"
                        class="w-full flex items-center gap-3 px-4 py-3 text-neutral-700 hover:bg-neutral-100 rounded-lg transition-colors"
                    >
                        <TableCellsIcon class="w-5 h-5" />
                        <span class="font-medium">Tables</span>
                    </button>

                    <button
                        @click="handleCashOutClick"
                        class="w-full flex items-center gap-3 px-4 py-3 text-neutral-700 hover:bg-neutral-100 rounded-lg transition-colors"
                    >
                        <BanknotesIcon class="w-5 h-5" />
                        <span class="font-medium">Log Cash Movement</span>
                    </button>

                    <button
                        @click="handleCashDrawerLogClick"
                        class="w-full flex items-center gap-3 px-4 py-3 text-neutral-700 hover:bg-neutral-100 rounded-lg transition-colors"
                    >
                        <ClipboardDocumentListIcon class="w-5 h-5" />
                        <span class="font-medium">Cash Drawer Log</span>
                    </button>

                    <button
                        @click="handleReviewTransactionsClick"
                        class="w-full flex items-center gap-3 px-4 py-3 text-neutral-700 hover:bg-neutral-100 rounded-lg transition-colors"
                    >
                        <DocumentTextIcon class="w-5 h-5" />
                        <span class="font-medium">Review Transactions</span>
                    </button>
                </div>

                <!-- Bottom Actions -->
                <div class="mt-6 pt-6 border-t border-neutral-200 space-y-3">
                    <button
                        @click="handleCloseShift"
                        class="w-full px-4 py-3 bg-warning text-white rounded-lg hover:bg-warning-600 transition-colors font-medium"
                    >
                        Close Shift
                    </button>
                    <button
                        @click="handleLogout"
                        class="w-full px-4 py-3 bg-error text-white rounded-lg hover:bg-error-700 transition-colors font-medium"
                    >
                        Logout
                    </button>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col bg-neutral-50 overflow-y-auto">
            <slot />
        </div>

        <!-- PWA Install Banner -->
        <PWAInstallBanner />

        <!-- Toast Notifications -->
        <Toast />
        <ConfirmPopup />

        <!-- Session Summary Modal -->
        <SessionSummaryModal
            :show-session-summary-modal="showSessionSummaryModal"
            :session-summary="sessionSummaryData"
            @close-modal="handleCloseSummaryModal"
        />
    </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import Toast from "primevue/toast";
import ConfirmPopup from "primevue/confirmpopup";
import SessionSummaryModal from "@/Pages/Resto/Partials/SessionSummaryModal.vue";
import PWAInstallBanner from "@/Components/PWAInstallBanner.vue";
import CashierHeader from "@/Components/CashierHeader.vue";
import { useCashier } from "@/composables/useCashier";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import {
    TableCellsIcon,
    DocumentTextIcon,
    ClipboardDocumentListIcon,
    BanknotesIcon,
} from "@heroicons/vue/24/outline";

const page = usePage();
const confirm = useConfirm();
const toast = useToast();
const { closeShift } = useCashier();

// Props for cashier info (can be passed from parent components)
const props = defineProps<{
    currentUser?: any;
    openSession?: any;
    sessionSummary?: any;
}>();

// Reactive data
const showSidebar = ref(false);

const showSessionSummaryModal = ref(false);
const sessionSummaryData = ref(null);
const shouldLogoutAfterSummary = ref(false);

// Computed properties
const cashierName = computed(() => {
    return (
        props.currentUser?.name ||
        (page.props as any).auth?.user?.name ||
        "Unknown Cashier"
    );
});

const companyName = computed(() => {
    return (page.props as any)?.company_info?.company_name || "Restaurant";
});

const checkCurrentRoute = (currentRoute: any) => {
    return route().current() == currentRoute;
};

// Methods
const toggleSidebar = () => {
    showSidebar.value = !showSidebar.value;
};

const handleTablesClick = () => {
    showSidebar.value = false;
    router.visit(route("resto.tables"));
};

const handleReviewTransactionsClick = () => {
    showSidebar.value = false;
    router.visit(route("transactions.index"));
};

const handleCashOutClick = () => {
    showSidebar.value = false;
    router.visit(route("resto.cashier-cashouts.create"));
};

const handleCashDrawerLogClick = () => {
    showSidebar.value = false;
    router.visit(route("resto.cashier-cashouts.index"));
};

const handleCloseShift = () => {
    showSidebar.value = false;
    router.visit(route("resto.close-shift"));
};

const handleLogout = () => {
    showSidebar.value = false;
    router.post(route("logout"));
};

const handleCloseSummaryModal = () => {
    showSessionSummaryModal.value = false;
    // Logout after closing modal
    router.post(route("logout"));
};

// Close sidebar when clicking outside
const handleClickOutsideSidebar = (event: Event) => {
    const target = event.target as HTMLElement;
    if (!target.closest("aside") && !target.closest("[data-sidebar-toggle]")) {
        showSidebar.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutsideSidebar);
    window.addEventListener("toggle-sidebar", toggleSidebar);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutsideSidebar);
    window.removeEventListener("toggle-sidebar", toggleSidebar);
});
</script>
