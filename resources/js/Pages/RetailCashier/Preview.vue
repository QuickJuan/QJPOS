<template>
    <HomeLayout>
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
                <p class="text-sm font-medium text-gray-600">Current Branch</p>
                <p class="text-lg font-semibold text-primary">
                    {{ props.activeBranch?.name || "No Branch Selected" }}
                </p>
                <p class="text-xs text-gray-500">
                    Code: {{ props.activeBranch?.branch_code || "N/A" }}
                </p>
            </div>
        </div>

        <!-- Session Status -->
        <div
            class="mb-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6"
        >
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                Session Status
            </h3>
            <div v-if="props.openSession" class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-3 h-3 bg-green-500 rounded-full animate-pulse"
                    ></div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">
                            You have a pending cashiering session. Would you
                            like to continue it, or start a new session and
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
                        Continue Session
                    </button>
                    <button
                        @click="confirmCloseSessionModal"
                        class="px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-colors"
                    >
                        Close Session
                    </button>
                </div>
            </div>
            <div v-else class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">
                            No Active Session
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
                        Start New Session
                    </button>
                </div>
            </div>
        </div>

        <!-- Close Session Dialog -->
        <CloseSessionModal
            :show-close-dialog="showCloseDialog"
            :session-summary="props.sessionSummary"
            :open-session="props.openSession"
            @confirm-close-session="confirmCloseSession"
            @close-modal="showCloseDialog = false"
        />
    </HomeLayout>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { route } from "ziggy-js";
import { router } from "@inertiajs/vue3";
import CashieringSession from "@/Types/CashieringSession";
import Branch from "@/Types/Branch";
import { usePage } from "@inertiajs/vue3";
import TextField from "@/Components/Form/TextField.vue";
import { useConfirm, useToast } from "primevue";
import PageProps from "@/Types/PageProps";
import HomeLayout from "@/Layouts/HomeLayout.vue";
import CloseSessionModal from "./Partials/CloseSessionModal.vue";

const props = defineProps<{
    activeBranch: Branch;
    openSession: CashieringSession | null;
    sessionSummary: any;
}>();

const page = usePage<PageProps>();
const toast = useToast();

const beginningCash = ref("");
const showCloseDialog = ref(false);

const continueSession = () => {
    router.visit(route("retail-cashier.index"));
};

const confirmCloseSessionModal = (event: any) => {
    showCloseDialog.value = true;
};

const confirmCloseSession = (data: any) => {
    console.log(data)
    router.post(
        route("retail-cashier.session.close"),
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

const startSession = () => {
    if (!beginningCash.value || parseFloat(beginningCash.value) < 0) {
        alert("Please enter a valid beginning cash amount");
        return;
    }

    router.post(
        route("retail-cashier.session.start"),
        {
            beginning_cash: parseFloat(beginningCash.value),
            branch_id: page.props?.active_branch?.id || null,
        },
        {
            onSuccess: () => {
                // Refresh the page to show the new session
                window.location.reload();
            },
            onError: (errors) => {
                alert(
                    "Failed to start session: " +
                        (errors.beginning_cash || "Unknown error")
                );
            },
        }
    );
};
</script>
