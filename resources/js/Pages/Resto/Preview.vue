<template>
    <CashieringLayout>
        <div class="w-full max-w-7xl mx-auto py-10">
            <!-- Welcome Section -->
            <div
                class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center"
            >
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">
                        Welcome back, {{ page.props.auth.user.name }}!
                    </h2>
                </div>
                <div class="mt-4 sm:mt-0 text-right">
                    <p class="text-sm font-medium text-gray-600">
                        Current Branch
                    </p>
                    <p class="text-lg font-semibold text-primary">
                        {{
                            page.props.active_branch?.name ||
                            "No Branch Selected"
                        }}
                    </p>
                    <p class="text-xs text-gray-500">
                        Code:
                        {{ page.props.active_branch?.branch_code || "N/A" }}
                    </p>
                </div>
            </div>

            <!-- Session Status -->
            <div
                class="mb-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Shift Status
                </h3>
                <div v-if="props.openSession" class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-3 h-3 bg-green-500 rounded-full animate-pulse"
                        ></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                You have a pending cashiering shift. Would you
                                like to continue it, or start a new shift and
                                close the pending one?
                            </p>
                            <p class="text-xs text-gray-500">
                                Started:
                                {{
                                    new Date(
                                        props.openSession.started_time
                                    ).toLocaleString()
                                }}
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <button
                            @click="continueSession"
                            class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-500 transition-colors"
                        >
                            Continue Shift
                        </button>
                        <button
                            @click="confirmCloseSessionModal"
                            class="px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-colors"
                        >
                            Close Shift
                        </button>
                    </div>
                </div>
                <div v-else class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                No Active Shift
                            </p>
                            <p class="text-xs text-gray-500">
                                You need to start a cashier session to begin.
                            </p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <TextField
                                label="Beginning Cash Amount"
                                v-model="beginningCash"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="Enter beginning cash amount"
                            />
                        </div>
                        <button
                            @click="startSession"
                            :disabled="
                                !beginningCash || parseFloat(beginningCash) < 0
                            "
                            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                        >
                            Start New Shift
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Close Session Dialog -->
        <CloseSessionModal
            :show-close-dialog="showCloseDialog"
            :open-session="props.openSession"
            @close-modal="showCloseDialog = false"
            @confirm-close-session="confirmCloseSession"
        />

        <!-- Session Summary Modal -->
        <SessionSummaryModal
            :show-session-summary-modal="showSessionSummaryModal"
            :open-session="props.openSession"
            :session-summary="sessionSummaryData"
            :current-user="page.props.auth.user"
            @close-modal="showSessionSummaryModal = false"
            @confirm-close="confirmCloseSessionSummaryModal"
        />
    </CashieringLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { route } from "ziggy-js";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import CashieringSession from "@/Types/CashieringSession";
import Branch from "@/Types/Branch";
import { usePage } from "@inertiajs/vue3";
import TextField from "@/Components/Form/TextField.vue";
import { useConfirm, useToast } from "primevue";
import PageProps from "@/Types/PageProps";
import HomeLayout from "@/Layouts/HomeLayout.vue";
import CloseSessionModal from "./Partials/CloseSessionModal.vue";
import SessionSummaryModal from "./Partials/SessionSummaryModal.vue";
import { useCashier } from "@/composables/useCashier";
import CashieringLayout from "@/Layouts/CashieringLayout.vue";

const props = defineProps<{
    openSession: CashieringSession | null;
}>();

const page = usePage<PageProps>();
const toast = useToast();
const { closeShift } = useCashier();

const beginningCash = ref("");
const showCloseDialog = ref(false);
const showSessionSummaryModal = ref(false);
const sessionSummaryData = ref(null);

// Check for auto_close query parameter and open modal automatically
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("auto_close") === "true" && props.openSession) {
        showCloseDialog.value = true;
    }
});

const continueSession = () => {
    router.visit(route("resto.index"));
};

const confirmCloseSessionModal = (event: any) => {
    showCloseDialog.value = true;
};

const confirmCloseSession = async (data: any) => {
    const payload = {
        cash_denomination_details: data.currencyBreakdown,
        cash_denomination: data.totalCashCounted,
        shift_no: page.props.current_cashier_session?.id,
        cashier_id: page.props.current_cashier_session?.cashier_id,
    };

    const result = await closeShift(payload);

    console.log("close shift result :", result);

    showCloseDialog.value = false;

    if (result.success) {
        sessionSummaryData.value = result.session;
        showSessionSummaryModal.value = true;

        toast.add({
            severity: "success",
            summary: "Success",
            detail: result.data.message || "Session closed successfully",
            life: 3000,
        });

        // Logout after showing the summary modal
        setTimeout(() => {
            router.post(route("logout"));
        }, 2000);
    } else {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: result.error,
            life: 3000,
        });
    }
};

const startSession = () => {
    if (!beginningCash.value || parseFloat(beginningCash.value) < 0) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Please enter a valid beginning cash amount",
            life: 3000,
        });
        return;
    }

    router.post(
        route("resto.session.start"),
        {
            beginning_cash: parseFloat(beginningCash.value),
            branch_id: page.props?.active_branch?.id || null,
        },
        {
            onSuccess: () => {
                //
            },
            onError: (errors) => {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        "Failed to start session: " +
                        (errors.beginning_cash ||
                            errors.message ||
                            "Unknown error"),
                    life: 3000,
                });
            },
        }
    );
};

const confirmCloseSessionSummaryModal = () => {
    showSessionSummaryModal.value = false;

    // Create a form and submit it to logout endpoint for full page reload
    const form = document.createElement("form");
    form.method = "POST";
    form.action = route("logout");

    // Add CSRF token
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
    if (csrfToken) {
        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "_token";
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }

    document.body.appendChild(form);
    form.submit();
};
</script>
