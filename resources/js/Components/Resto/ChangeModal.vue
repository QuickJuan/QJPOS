<template>
    <TransitionRoot :show="show" as="template">
        <Dialog as="div" class="relative z-50" @close="handleClose">
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div
                    class="flex min-h-full items-center justify-center p-4 text-center"
                >
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
                        >
                            <!-- Success Icon -->
                            <div class="flex justify-center mb-4">
                                <div
                                    class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center"
                                >
                                    <CheckCircleIcon
                                        class="w-10 h-10 text-green-600"
                                    />
                                </div>
                            </div>

                            <!-- Title -->
                            <DialogTitle
                                as="h3"
                                class="text-2xl font-bold text-center text-gray-900 mb-2"
                            >
                                {{ hasChange ? changeAmount : "Exact Amount" }}
                            </DialogTitle>

                            <p class="text-center text-gray-600 mb-6">
                                {{
                                    hasChange
                                        ? "Change to return to customer"
                                        : "Payment received exact amount"
                                }}
                            </p>

                            <!-- E-Wallet Option (only show if customer is selected and has change) -->
                            <div
                                v-if="
                                    hasChange &&
                                    canLoadToEWallet &&
                                    showEWalletOption
                                "
                                class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-1">
                                        <WalletIcon
                                            class="w-5 h-5 text-blue-600"
                                        />
                                    </div>
                                    <div class="flex-1">
                                        <h4
                                            class="text-sm font-semibold text-blue-900 mb-1"
                                        >
                                            Load Change to E-Wallet?
                                        </h4>
                                        <p class="text-xs text-blue-700 mb-3">
                                            Customer: {{ customerName }}
                                        </p>
                                        <p class="text-xs text-blue-600 mb-3">
                                            Instead of giving small change, you
                                            can load
                                            <strong>{{ changeAmount }}</strong>
                                            to the customer's e-wallet for
                                            future use.
                                        </p>
                                        <div class="flex gap-2">
                                            <button
                                                @click="handleLoadToEWallet"
                                                :disabled="isLoading"
                                                class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                            >
                                                {{
                                                    isLoading
                                                        ? "Loading..."
                                                        : "Load to E-Wallet"
                                                }}
                                            </button>
                                            <button
                                                @click="
                                                    showEWalletOption = false
                                                "
                                                :disabled="isLoading"
                                                class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 disabled:opacity-50 transition-colors"
                                            >
                                                No, Return Cash
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Success Message (shown after loading to e-wallet) -->
                            <div
                                v-if="eWalletLoadSuccess"
                                class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6"
                            >
                                <div class="flex items-start gap-3">
                                    <CheckCircleIcon
                                        class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"
                                    />
                                    <div class="flex-1">
                                        <h4
                                            class="text-sm font-semibold text-green-900 mb-1"
                                        >
                                            E-Wallet Loaded Successfully!
                                        </h4>
                                        <p class="text-xs text-green-700">
                                            {{ changeAmount }} has been loaded
                                            to {{ customerName }}'s e-wallet.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3">
                                <button
                                    @click="handleClose"
                                    :disabled="isLoading"
                                    class="flex-1 px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                >
                                    {{
                                        eWalletLoadSuccess ? "Done" : "Confirm"
                                    }}
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from "@headlessui/vue";
import { CheckCircleIcon, WalletIcon } from "@heroicons/vue/24/outline";

interface Props {
    show: boolean;
    changeValue: number;
    currencyCode?: string;
    customerId?: number | null;
    customerName?: string;
}

const props = withDefaults(defineProps<Props>(), {
    currencyCode: "PHP",
    customerId: null,
    customerName: "",
});

const emit = defineEmits<{
    (e: "close"): void;
    (e: "loadToEWallet", orderId: number, amount: number): void;
}>();

const showEWalletOption = ref(true);
const eWalletLoadSuccess = ref(false);
const isLoading = ref(false);

const hasChange = computed(() => props.changeValue > 0);

const canLoadToEWallet = computed(() => props.customerId && props.customerName);

const changeAmount = computed(() => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: props.currencyCode,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(props.changeValue);
});

const handleLoadToEWallet = async () => {
    if (!props.customerId || isLoading.value) {
        return;
    }

    isLoading.value = true;

    try {
        // Emit event to parent to handle the actual loading
        emit("loadToEWallet", props.customerId, props.changeValue);

        // Show success message
        eWalletLoadSuccess.value = true;
        showEWalletOption.value = false;
    } catch (error) {
        console.error("Failed to load to e-wallet:", error);
    } finally {
        isLoading.value = false;
    }
};

const handleClose = () => {
    if (isLoading.value) {
        return;
    }

    // Reset state
    showEWalletOption.value = true;
    eWalletLoadSuccess.value = false;

    emit("close");
};
</script>
