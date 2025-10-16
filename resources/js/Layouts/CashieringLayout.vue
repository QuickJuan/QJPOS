<template>
    <div class="h-screen bg-gray-50 flex flex-col">
        <div class="flex-1 flex flex-col bg-gray-50 overflow-hidden">
            <slot />
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 shadow-lg">
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
                                Customers
                            </button>

                            <!-- Tables Button -->
                            <button
                                @click="handleTablesClick"
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

// Computed properties
const cashierName = computed(() => {
    return (
        props.currentUser?.name ||
        (page.props as any).auth?.user?.name ||
        "Unknown Cashier"
    );
});

// Methods
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
    }
};

const handleTablesClick = () => {
    // Navigate to tables page or show table selection
    router.visit(route("table-ordering.index"));
};

const toggleMoreOptions = () => {
    showMoreOptions.value = !showMoreOptions.value;
};

const handleReports = () => {
    showMoreOptions.value = false;
    // Navigate to reports page
    router.visit(route("dashboard"));
};

const handleSettings = () => {
    showMoreOptions.value = false;
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
    // Show help dialog or navigate to help page
    toast.add({
        severity: "info",
        summary: "Help",
        detail: "Help documentation coming soon",
        life: 3000,
    });
};

const handleCloseShift = () => {
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
