<template>
    <div class="h-screen bg-gray-50 flex flex-col">
        <!-- Toggle Button for Mobile/Tablet Sidebar -->
        <button
            @click="toggleSidebar"
            class="lg:hidden fixed bottom-4 left-4 z-50 bg-primary text-white rounded-full p-4 shadow-lg hover:bg-primary-600 transition-all"
        >
            <Bars3Icon v-if="!showSidebar" class="w-6 h-6" />
            <XMarkIcon v-else class="w-6 h-6" />
        </button>

        <!-- Sidebar Overlay (Mobile/Tablet) -->
        <div
            v-if="showSidebar"
            @click="toggleSidebar"
            class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40 transition-opacity"
        ></div>

        <!-- Sidebar (Mobile/Tablet) -->
        <aside
            :class="[
                'lg:hidden fixed left-0 top-0 bottom-0 w-80 bg-white shadow-2xl z-40 transform transition-transform duration-300 ease-in-out overflow-y-auto',
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
                        @click="handleTablesClick"
                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <TableCellsIcon class="w-5 h-5" />
                        <span class="font-medium">Tables</span>
                    </button>

                    <button
                        v-if="checkCurrentRoute('retail-cashier.tables')"
                        @click="handleCashieringClick"
                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <TableCellsIcon class="w-5 h-5" />
                        <span class="font-medium">Cashiering</span>
                    </button>

                    <button
                        @click="handleReviewTransactionsClick"
                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <DocumentTextIcon class="w-5 h-5" />
                        <span class="font-medium">Review Transactions</span>
                    </button>

                    <button
                        @click="handleReports"
                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <ChartBarIcon class="w-5 h-5" />
                        <span class="font-medium">Reports</span>
                    </button>

                    <button
                        @click="handleSettings"
                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <CogIcon class="w-5 h-5" />
                        <span class="font-medium">Settings</span>
                    </button>

                    <button
                        @click="handleHelp"
                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <QuestionMarkCircleIcon class="w-5 h-5" />
                        <span class="font-medium">Help</span>
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

        <!-- Footer (Desktop Only) -->
        <footer
            class="hidden lg:block bg-white border-t border-gray-200 shadow-lg"
        >
            <div class="px-6 py-4">
                <div class="grid grid-cols-12 gap-4 items-center">
                    <!-- Left Column: Cashier Info -->
                    <div class="col-span-2">
                        <div class="text-sm">
                            <p class="text-gray-500">Cashier</p>
                            <p class="font-semibold text-gray-900">
                                {{ cashierName }}
                            </p>
                        </div>
                    </div>

                    <!-- Center Column: Barcode Scanner & Options -->
                    <div class="col-span-8">
                        <div class="flex items-center gap-4 justify-start">
                            <!-- Barcode Scanner -->
                            <div class="flex items-center gap-2">
                                <QrCodeIcon class="w-5 h-5 text-gray-600" />
                                <input
                                    v-model="barcodeInput"
                                    type="text"
                                    placeholder="Scan barcode or enter product code..."
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64"
                                    @keyup.enter="handleBarcodeSearch"
                                />
                                <button
                                    @click="handleBarcodeSearch"
                                    class="px-4 py-2 bg-primary text-white rounded-lg transition-colors"
                                >
                                    Search
                                </button>
                            </div>

                            <!-- Hold Button -->
                            <button
                                @click="handleTablesClick"
                                class="flex items-center gap-2 px-4 py-2 text-white rounded-lg bg-primary transition-colors"
                            >
                                <TableCellsIcon class="w-5 h-5" />
                                Tables
                            </button>

                            <button
                                v-if="
                                    checkCurrentRoute('retail-cashier.tables')
                                "
                                @click="handleCashieringClick"
                                class="flex items-center gap-2 px-4 py-2 text-white rounded-lg bg-primary transition-colors"
                            >
                                <TableCellsIcon class="w-5 h-5" />
                                Cashiering
                            </button>

                            <!-- Tables Button -->
                            <button
                                @click="handleReviewTransactionsClick"
                                class="flex items-center gap-2 px-4 py-2 text-white rounded-lg bg-primary transition-colors"
                            >
                                <TableCellsIcon class="w-5 h-5" />
                                Review Transactions
                            </button>

                            <!-- More Options -->
                            <div class="relative">
                                <button
                                    @click="toggleMoreOptions"
                                    class="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                                >
                                    <EllipsisHorizontalIcon class="w-5 h-5" />
                                    More
                                </button>

                                <!-- More Options Dropdown -->
                                <div
                                    v-if="showMoreOptions"
                                    class="absolute bottom-full mb-2 right-0 bg-white border border-gray-200 rounded-lg shadow-lg py-2 min-w-48 z-50"
                                >
                                    <button
                                        @click="handleReports"
                                        class="w-full px-4 py-2 text-left hover:bg-gray-100 flex items-center gap-2"
                                    >
                                        <ChartBarIcon class="w-4 h-4" />
                                        Reports
                                    </button>
                                    <button
                                        @click="handleSettings"
                                        class="w-full px-4 py-2 text-left hover:bg-gray-100 flex items-center gap-2"
                                    >
                                        <CogIcon class="w-4 h-4" />
                                        Settings
                                    </button>
                                    <button
                                        @click="handleHelp"
                                        class="w-full px-4 py-2 text-left hover:bg-gray-100 flex items-center gap-2"
                                    >
                                        <QuestionMarkCircleIcon
                                            class="w-4 h-4"
                                        />
                                        Help
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Close Shift & Logout -->
                    <div class="col-span-2">
                        <div class="flex items-center gap-2 justify-end">
                            <button
                                @click="handleCloseShift"
                                class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors text-sm"
                            >
                                Close Shift
                            </button>
                            <button
                                @click="handleLogout"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm"
                            >
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Toast Notifications -->
        <Toast />
        <ConfirmPopup />
    </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useConfirm } from "primevue";
import { useToast } from "primevue";
import Header from "@/Pages/RetailCashier/Partials/Header.vue";
import { ConfirmPopup, Toast } from "primevue";
import {
    QrCodeIcon,
    TableCellsIcon,
    EllipsisHorizontalIcon,
    ChartBarIcon,
    CogIcon,
    QuestionMarkCircleIcon,
    Bars3Icon,
    XMarkIcon,
    DocumentTextIcon,
} from "@heroicons/vue/24/outline";

const page = usePage();
const confirm = useConfirm();
const toast = useToast();

// Props for cashier info (can be passed from parent components)
const props = defineProps<{
    currentUser?: any;
}>();

// Reactive data
const barcodeInput = ref("");
const showMoreOptions = ref(false);
const showSidebar = ref(false);

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
    router.visit(route("retail-cashier.tables"));
};

const handleReviewTransactionsClick = () => {
    showSidebar.value = false;
    router.visit(route("transactions.index"));
};

const handleCashieringClick = () => {
    showSidebar.value = false;
    router.visit(route("retail-cashier.index"));
};

const toggleMoreOptions = () => {
    showMoreOptions.value = !showMoreOptions.value;
};

const handleReports = () => {
    showMoreOptions.value = false;
    showSidebar.value = false;
    // Navigate to reports page
    router.visit(route("dashboard"));
};

const handleSettings = () => {
    showMoreOptions.value = false;
    showSidebar.value = false;
    // Navigate to settings page
    toast.add({
        severity: "info",
        summary: "Settings",
        detail: "Settings page coming soon",
        life: 3000,
    });
};

const handleHelp = () => {
    showMoreOptions.value = false;
    showSidebar.value = false;
    // Show help dialog or navigate to help page
    toast.add({
        severity: "info",
        summary: "Help",
        detail: "Help documentation coming soon",
        life: 3000,
    });
};

const handleCloseShift = () => {
    showSidebar.value = false;
    confirm.require({
        message:
            "Are you sure you want to close your shift? This will end your current cashier session.",
        header: "Close Shift Confirmation",
        icon: "pi pi-exclamation-triangle",
        rejectClass: "p-button-secondary p-button-outlined",
        rejectLabel: "Cancel",
        acceptLabel: "Close Shift",
        accept: () => {
            // Handle close shift logic
            router.post(
                route("retail-cashier.session.close"),
                {},
                {
                    onSuccess: () => {
                        toast.add({
                            severity: "success",
                            summary: "Shift Closed",
                            detail: "Your shift has been closed successfully",
                            life: 3000,
                        });
                        // Redirect to dashboard or login
                        router.visit(route("dashboard"));
                    },
                    onError: () => {
                        toast.add({
                            severity: "error",
                            summary: "Error",
                            detail: "Failed to close shift",
                            life: 3000,
                        });
                    },
                }
            );
        },
    });
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

// Close dropdown when clicking outside
const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement;
    if (!target.closest(".relative")) {
        showMoreOptions.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>
