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

        <!-- Toast Notifications -->
        <Toast />
        <ConfirmPopup />

        <!-- Close Session Modal -->
        <CloseSessionModal
            :session-summary="props.sessionSummary"
            :show-close-dialog="showCloseDialog"
            :open-session="openSession"
            :current-user="props.currentUser"
            @confirm-close-session="handleConfirmCloseSession"
            @close-modal="showCloseDialog = false"
        />
    </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useConfirm } from "primevue";
import { useToast } from "primevue";
import Header from "@/Pages/Resto/Partials/Header.vue";
import { ConfirmPopup, Toast } from "primevue";
import CloseSessionModal from "@/Pages/Resto/Partials/CloseSessionModal.vue";
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
    openSession?: any;
    sessionSummary?: any;
}>();

// Reactive data
const barcodeInput = ref("");
const showMoreOptions = ref(false);
const showSidebar = ref(false);
const showCloseDialog = ref(false);

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
    router.visit(route("transactions.index"));
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

const handleConfirmCloseSession = (data: any) => {
    console.log(data);
    router.post(
        route("resto.session.close"),
        {
            cash_denomination: data.denominationData,
            closing_cash: data.totalCashCounted,
        },
        {
            onSuccess: () => {
                showCloseDialog.value = false;
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Session closed successfully",
                    life: 3000,
                });
            },
            onError: (errors) => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errors.error || "Failed to close session",
                    life: 3000,
                });
            },
        }
    );
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
