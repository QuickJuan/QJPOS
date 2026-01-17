<template>
    <header
        class="sticky top-0 z-40 border-b border-white/10 bg-slate-950/95 px-4 backdrop-blur sm:px-6"
    >
        <div class="mx-auto flex max-w-7xl items-center justify-between py-4">
            <!-- Left: Company Name and Brand -->
            <div class="flex items-center gap-3">
                <span class="text-lg font-black tracking-tight text-white">
                    {{ companyName }}
                </span>
                <span class="text-xs uppercase tracking-[0.4em] text-slate-400">
                    Cashier
                </span>
            </div>

            <!-- Center: Slot for custom content -->
            <div class="flex-1 flex justify-center">
                <slot />
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex items-center gap-4">
                <!-- More Options Dropdown -->
                <div
                    v-if="!isOrderTaking"
                    class="relative"
                    ref="moreOptionsRef"
                >
                    <button
                        @click="showMoreOptions = !showMoreOptions"
                        class="flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white/80 transition hover:border-white/40 hover:bg-white/20"
                    >
                        <span class="hidden sm:inline">More Options</span>
                        <ChevronDownIcon class="h-4 w-4" />
                    </button>

                    <!-- Dropdown Menu -->
                    <transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="showMoreOptions"
                            class="absolute right-0 mt-3 w-64 origin-top-right rounded-2xl bg-white/95 text-slate-900 shadow-2xl ring-1 ring-black/5 backdrop-blur"
                        >
                            <div
                                class="space-y-1 py-2 text-sm font-semibold text-slate-700"
                            >
                                <button
                                    @click="handlePendingOrdersClick"
                                    class="flex w-full items-center gap-3 px-4 py-2 transition hover:bg-slate-100"
                                >
                                    <DocumentTextIcon class="h-5 w-5" />
                                    <span>View Pending Orders</span>
                                </button>
                                <button
                                    @click="handleViewTableClick"
                                    class="flex w-full items-center gap-3 px-4 py-2 transition hover:bg-slate-100"
                                >
                                    <DocumentTextIcon class="h-5 w-5" />
                                    <span>View Table</span>
                                </button>
                                <div
                                    class="border-t border-slate-200 my-1"
                                ></div>
                                <button
                                    @click="handleReviewTransactionsClick"
                                    class="flex w-full items-center gap-3 px-4 py-2 transition hover:bg-slate-100"
                                >
                                    <DocumentTextIcon class="h-5 w-5" />
                                    <span>Review Transactions</span>
                                </button>
                                <button
                                    @click="handleReviewXReadingClick"
                                    class="flex w-full items-center gap-3 px-4 py-2 transition hover:bg-slate-100"
                                >
                                    <DocumentChartBarIcon class="h-5 w-5" />
                                    <span>Review X Reading</span>
                                </button>
                                <button
                                    @click="handleCashOutClick"
                                    class="flex w-full items-center gap-3 px-4 py-2 transition hover:bg-slate-100"
                                >
                                    <BanknotesIcon class="h-5 w-5" />
                                    <span>Log Cash Movement</span>
                                </button>
                                <button
                                    @click="handleCashDrawerLogClick"
                                    class="flex w-full items-center gap-3 px-4 py-2 transition hover:bg-slate-100"
                                >
                                    <ClipboardDocumentListIcon
                                        class="h-5 w-5"
                                    />
                                    <span>Cash Drawer Log</span>
                                </button>
                                <button
                                    @click="handlePrinterConfigClick"
                                    class="flex w-full items-center gap-3 px-4 py-2 transition hover:bg-slate-100"
                                >
                                    <PrinterIcon class="h-5 w-5" />
                                    <span>Printer Configuration</span>
                                </button>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- Close Shift Button (Only visible on table view) -->
                <button
                    v-if="isTableView"
                    @click="handleCloseShift"
                    class="flex items-center gap-2 rounded-full bg-warning-500 px-6 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-warning-600"
                >
                    <span class="hidden sm:inline">Close Shift</span>
                    <span class="sm:hidden">Shift</span>
                </button>

                <!-- Cashier Dropdown -->
                <div class="relative" ref="cashierDropdownRef">
                    <button
                        @click="showCashierDropdown = !showCashierDropdown"
                        class="flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-white/80 transition hover:bg-white/15"
                    >
                        <UserIcon class="h-4 w-4" />
                        <span class="hidden sm:inline">{{ cashierName }}</span>
                        <svg
                            class="h-3 w-3 text-white/80"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 9l6 6 6-6"
                            />
                        </svg>
                    </button>

                    <!-- Cashier Dropdown Menu -->
                    <transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="showCashierDropdown"
                            class="absolute right-0 mt-3 w-64 origin-top-right rounded-2xl bg-white/95 text-slate-900 shadow-2xl ring-1 ring-black/5 backdrop-blur"
                        >
                            <div class="border-b border-slate-100 px-4 py-3">
                                <p class="text-sm font-semibold">
                                    {{ cashierName }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    Cashier Account
                                </p>
                            </div>
                            <div
                                class="py-2 text-sm font-semibold text-slate-700"
                            >
                                <button
                                    @click="handleLogout"
                                    class="flex w-full items-center gap-3 px-4 py-2 transition hover:bg-slate-100"
                                >
                                    <ArrowRightOnRectangleIcon
                                        class="h-5 w-5"
                                    />
                                    <span>Logout</span>
                                </button>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useConfirm } from "primevue";
import {
    ChevronDownIcon,
    DocumentTextIcon,
    DocumentChartBarIcon,
    BanknotesIcon,
    ClipboardDocumentListIcon,
    PrinterIcon,
    ArrowRightOnRectangleIcon,
    UserIcon,
} from "@heroicons/vue/24/outline";

const page = usePage();
const confirm = useConfirm();

const props = defineProps<{
    cashierName: string;
    companyName?: string;
    onCloseShift?: () => void;
    onLogout?: () => void;
}>();

const emit = defineEmits<{
    closeShift: [];
    logout: [];
    reviewTransactions: [];
}>();

// Reactive data
const showMoreOptions = ref(false);
const showCashierDropdown = ref(false);
const moreOptionsRef = ref<HTMLDivElement | null>(null);
const cashierDropdownRef = ref<HTMLDivElement | null>(null);

// Computed company name with fallback
const companyName = computed(() => {
    return (
        props.companyName ||
        (page.props as any)?.company_info?.company_name ||
        "Restaurant"
    );
});

// Check if we're on the table view (Preview page)
const isTableView = computed(() => {
    return (
        page.component === "Resto/Preview" || page.component === "Resto/Tables"
    );
});

const isOrderTaking = computed(() => {
    const role = (page.props as any)?.auth?.user?.current_role;
    return (
        String(role || "")
            .toLowerCase()
            .replace(/\s+/g, "_")
            .replace(/-+/g, "_") === "order_taking"
    );
});

// Methods
const handlePendingOrdersClick = () => {
    showMoreOptions.value = false;
    const activeBranch = (page.props as any)?.active_branch;
    router.visit(
        route("resto.pending-orders.index", { branchId: activeBranch?.id }),
    );
};

const handleViewTableClick = () => {
    showMoreOptions.value = false;
    router.visit(route("table-rooms.index"));
};

const handleReviewTransactionsClick = () => {
    showMoreOptions.value = false;
    router.visit(route("transactions.index"));
    emit("reviewTransactions");
};

const handleReviewXReadingClick = () => {
    showMoreOptions.value = false;
    router.visit(route("resto.review-x-readings"));
};

const handleCashOutClick = () => {
    showMoreOptions.value = false;
    router.visit(route("resto.cashier-cashouts.create"));
};

const handleCashDrawerLogClick = () => {
    showMoreOptions.value = false;
    router.visit(route("resto.cashier-cashouts.index"));
};

const handlePrinterConfigClick = () => {
    showMoreOptions.value = false;
    router.visit(route("printer-config.index"));
};

const handleCloseShift = () => {
    showMoreOptions.value = false;
    showCashierDropdown.value = false;
    emit("closeShift");
    if (props.onCloseShift) {
        props.onCloseShift();
    }
};

const handleLogout = () => {
    showCashierDropdown.value = false;
    emit("logout");
    if (props.onLogout) {
        props.onLogout();
    } else {
        router.post(
            route("logout"),
            {},
            {
                preserveState: false,
                preserveScroll: false,
                onSuccess: () => {
                    window.location.href = route("login");
                },
            },
        );
    }
};

// Close dropdowns when clicking outside
const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement;

    if (moreOptionsRef.value && !moreOptionsRef.value.contains(target)) {
        showMoreOptions.value = false;
    }

    if (
        cashierDropdownRef.value &&
        !cashierDropdownRef.value.contains(target)
    ) {
        showCashierDropdown.value = false;
    }
};

// Add and remove click outside listener
onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>
