<template>
    <div class="h-screen bg-gray-50 flex flex-col">
        <!-- Header (Desktop Only) -->
        <header
            class="hidden lg:block bg-white border-b border-gray-200 relative z-50"
        >
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <!-- Left: Cashier Info -->
                    <div class="flex-shrink-0">
                        <div class="text-sm">
                            <p class="text-gray-500">Cashier</p>
                            <p class="font-semibold text-gray-900">
                                {{ cashierName }}
                            </p>
                        </div>
                    </div>

                    <!-- Right: Action Buttons -->
                    <div class="flex items-center gap-3">
                        <!-- More Options Dropdown -->
                        <div class="relative">
                            <button
                                @click="showMoreOptions = !showMoreOptions"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium text-sm flex items-center gap-2"
                            >
                                More Options
                                <ChevronDownIcon class="w-4 h-4" />
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                v-if="showMoreOptions"
                                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                            >
                                <button
                                    @click="handleReviewTransactionsClick"
                                    class="w-full px-4 py-2 text-left text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-3"
                                >
                                    <DocumentTextIcon class="w-5 h-5" />
                                    <span>Review Transactions</span>
                                </button>
                                <button
                                    @click="handleReviewXReadingClick"
                                    class="w-full px-4 py-2 text-left text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-3"
                                >
                                    <DocumentChartBarIcon class="w-5 h-5" />
                                    <span>Review X Reading</span>
                                </button>
                                <button
                                    @click="handleCashOutClick"
                                    class="w-full px-4 py-2 text-left text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-3"
                                >
                                    <BanknotesIcon class="w-5 h-5" />
                                    <span>Log Cash Movement</span>
                                </button>
                                <button
                                    @click="handleCashDrawerLogClick"
                                    class="w-full px-4 py-2 text-left text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-3"
                                >
                                    <ClipboardDocumentListIcon
                                        class="w-5 h-5"
                                    />
                                    <span>Cash Drawer Log</span>
                                </button>
                                <button
                                    @click="handlePrinterConfigClick"
                                    class="w-full px-4 py-2 text-left text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-3"
                                >
                                    <PrinterIcon class="w-5 h-5" />
                                    <span>Printer Configuration</span>
                                </button>
                            </div>
                        </div>

                        <button
                            @click="handleCloseShift"
                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors font-medium text-sm"
                        >
                            Close Shift
                        </button>
                        <button
                            @click="handleLogout"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>

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
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <p class="text-sm text-gray-500 mb-1">Cashier</p>
                    <p class="text-lg font-bold text-gray-900">
                        {{ cashierName }}
                    </p>
                </div>

                <!-- Barcode Scanner -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Scan Product
                    </label>
                    <div class="flex gap-2">
                        <input
                            v-model="barcodeInput"
                            type="text"
                            placeholder="Scan barcode..."
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                            @keyup.enter="handleBarcodeSearch"
                        />
                        <button
                            @click="handleBarcodeSearch"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 transition-colors"
                        >
                            <QrCodeIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button
                        v-if="!checkCurrentRoute('resto.tables')"
                        @click="handleTablesClick"
                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <TableCellsIcon class="w-5 h-5" />
                        <span class="font-medium">Tables</span>
                    </button>

                    <button
                        @click="handleCashOutClick"
                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <BanknotesIcon class="w-5 h-5" />
                        <span class="font-medium">Log Cash Movement</span>
                    </button>

                    <button
                        @click="handleCashDrawerLogClick"
                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <ClipboardDocumentListIcon class="w-5 h-5" />
                        <span class="font-medium">Cash Drawer Log</span>
                    </button>

                    <button
                        @click="handleReviewTransactionsClick"
                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <DocumentTextIcon class="w-5 h-5" />
                        <span class="font-medium">Review Transactions</span>
                    </button>
                </div>

                <!-- Bottom Actions -->
                <div class="mt-6 pt-6 border-t border-gray-200 space-y-3">
                    <button
                        @click="handleCloseShift"
                        class="w-full px-4 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors font-medium"
                    >
                        Close Shift
                    </button>
                    <button
                        @click="handleLogout"
                        class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium"
                    >
                        Logout
                    </button>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col bg-gray-50 overflow-y-auto">
            <slot />
        </div>

        <!-- PWA Install Banner -->
        <PWAInstallBanner />

        <!-- Toast Notifications -->
        <Toast />
        <ConfirmPopup />

        <!-- Close Session Modal -->
        <CloseSessionModal
            :show-close-dialog="showCloseDialog"
            :open-session="page.props.current_cashier_session"
            :current-user="props.currentUser"
            @confirm-close-session="handleConfirmCloseSession"
            @close-modal="showCloseDialog = false"
        />

        <!-- Session Summary Modal -->
        <SessionSummaryModal
            :show-session-summary-modal="showSessionSummaryModal"
            :open-session="page.props.current_cashier_session"
            :session-summary="sessionSummaryData"
            :current-user="page.props.auth.user"
            @close-modal="handleCloseSummaryModal"
            @confirm-close="handleCloseSummaryModal"
        />
    </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useConfirm } from "primevue";
import { useToast } from "primevue";
import { ConfirmPopup, Toast } from "primevue";
import CloseSessionModal from "@/Pages/Resto/Partials/CloseSessionModal.vue";
import SessionSummaryModal from "@/Pages/Resto/Partials/SessionSummaryModal.vue";
import PWAInstallBanner from "@/Components/PWAInstallBanner.vue";
import { useCashier } from "@/composables/useCashier";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import {
    QrCodeIcon,
    TableCellsIcon,
    DocumentTextIcon,
    ChevronDownIcon,
    PrinterIcon,
    ClockIcon,
    DocumentChartBarIcon,
    BanknotesIcon,
    ClipboardDocumentListIcon,
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
const barcodeInput = ref("");
const showMoreOptions = ref(false);
const showSidebar = ref(false);
const showCloseDialog = ref(false);

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

const checkCurrentRoute = (currentRoute: any) => {
    return route().current() == currentRoute;
};

// Methods
const toggleSidebar = () => {
    showSidebar.value = !showSidebar.value;
};

const handleBarcodeSearch = () => {
    if (barcodeInput.value.trim()) {
        // Emit event to parent or handle barcode search
        // For now, let's just log it
        console.log("Barcode search:", barcodeInput.value);

        // You can emit this to parent component if needed
        // emit('barcodeScanned', barcodeInput.value);

        // Clear input after search
        barcodeInput.value = "";

        toast.add({
            severity: "info",
            summary: "Barcode Search",
            detail: `Searching for: ${barcodeInput.value}`,
            life: 3000,
        });

        showSidebar.value = false;
    }
};

const handleTablesClick = () => {
    showSidebar.value = false;
    router.visit(route("resto.tables"));
};

const handleReviewTransactionsClick = () => {
    showSidebar.value = false;
    showMoreOptions.value = false;
    router.visit(route("transactions.index"));
};

const handleReviewXReadingClick = () => {
    showMoreOptions.value = false;
    router.visit(route("resto.review-x-readings"));
};

const handleCashOutClick = () => {
    showSidebar.value = false;
    showMoreOptions.value = false;
    router.visit(route("resto.cashier-cashouts.create"));
};

const handleCashDrawerLogClick = () => {
    showSidebar.value = false;
    showMoreOptions.value = false;
    router.visit(route("resto.cashier-cashouts.index"));
};

const handlePrinterConfigClick = () => {
    showMoreOptions.value = false;
    router.visit(route("printer-config.index"));
};

const handleEndShiftClick = () => {
    showMoreOptions.value = false;
    handleCloseShift();
};

const handleCloseShift = () => {
    showSidebar.value = false;
    showCloseDialog.value = true;
};

const handleLogout = () => {
    showSidebar.value = false;
    confirm.require({
        message: "Are you sure you want to logout?",
        header: "Logout Confirmation",
        icon: "pi pi-exclamation-triangle",
        rejectClass: "p-button-secondary p-button-outlined",
        rejectLabel: "Cancel",
        acceptLabel: "Logout",
        accept: () => {
            router.post(route("logout"));
        },
    });
};

const handleConfirmCloseSession = async (data: any) => {
    let payload = {
        cash_denomination_details: data.denominationData,
        cash_denomination: data.totalCashCounted,
        shift_no: page.props.current_cashier_session?.id,
        cashier_id: page.props.current_cashier_session?.cashier_id,
    };

    const result = await closeShift(payload);

    console.log("close shift result :", result);

    showCloseDialog.value = false;

    if (result.success) {
        sessionSummaryData.value = result.session;

        // Try to print using thermal printer first
        try {
            // Load receipt printer config
            await thermalPrinter.loadPrinterConfig("receipt");

            // Connect to printer if not already connected
            if (!thermalPrinter.isConnected()) {
                const connected = await thermalPrinter.connectToPrinterType(
                    "receipt"
                );
                if (!connected) {
                    // Printer not available, show modal
                    showSessionSummaryModal.value = true;
                    toast.add({
                        severity: "warn",
                        summary: "Printer Unavailable",
                        detail: "Session closed successfully. Printer not available - please print manually.",
                        life: 5000,
                    });
                    return;
                }
            }

            // Print session summary
            await thermalPrinter.printSessionSummary(result.session);

            // Show success message and logout
            toast.add({
                severity: "success",
                summary: "Success",
                detail: "Session closed and printed successfully",
                life: 3000,
            });

            // Logout after successful print
            setTimeout(() => {
                router.post(route("logout"));
            }, 1500);
        } catch (error) {
            console.error("Failed to print session summary:", error);
            // Show modal as fallback
            showSessionSummaryModal.value = true;
            toast.add({
                severity: "warn",
                summary: "Print Failed",
                detail: "Session closed successfully. Failed to print - please print manually.",
                life: 5000,
            });
        }
    } else {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: result.error,
            life: 3000,
        });
    }
};

const handleCloseSummaryModal = () => {
    showSessionSummaryModal.value = false;
    // Logout after closing modal
    router.post(route("logout"));
};

// Close dropdown when clicking outside
const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement;
    if (!target.closest(".relative")) {
        showMoreOptions.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
    window.addEventListener("toggle-sidebar", toggleSidebar);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
    window.removeEventListener("toggle-sidebar", toggleSidebar);
});
</script>
