<template>
    <Dialog
        :visible="show"
        modal
        :closable="!isLoading"
        :dismissableMask="!isLoading"
        @update:visible="handleClose"
        class="w-full max-w-md"
    >
        <template #container="{ closeCallback }">
            <div
                class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl"
            >
                <!-- Success Icon -->
                <div class="flex justify-center mb-4">
                    <div
                        class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center"
                    >
                        <CheckCircleIcon class="w-10 h-10 text-green-600" />
                    </div>
                </div>

                <!-- Title -->
                <h3 class="text-2xl font-bold text-center text-gray-900 mb-2">
                    {{
                        hasChange
                            ? canLoadToEWallet && eWalletAmount > 0
                                ? formatCurrency(remainingChange)
                                : changeAmount
                            : "Exact Amount"
                    }}
                </h3>

                <p class="text-center text-gray-600 mb-6">
                    {{
                        hasChange
                            ? canLoadToEWallet && eWalletAmount > 0
                                ? "Cash to return to customer"
                                : "Change to return to customer"
                            : "Payment received exact amount"
                    }}
                </p>

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
                                {{ formatCurrency(eWalletAmount) }} has been
                                loaded to {{ effectiveCustomerName }}'s
                                e-wallet.
                                <span v-if="remainingChange > 0">
                                    Return {{ formattedRemainingChange }} as
                                    cash.
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- E-Wallet Amount Input (show if customer is available and has change) -->
                <div
                    v-if="hasChange && canLoadToEWallet && !eWalletLoadSuccess"
                    class="mb-6"
                >
                    <!-- Selected Customer Display -->
                    <div
                        class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0"
                                >
                                    <span class="text-sm font-bold text-white">
                                        {{ getInitials(effectiveCustomerName) }}
                                    </span>
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-blue-600 font-medium"
                                    >
                                        Loading to E-Wallet
                                    </p>
                                    <p class="text-sm font-bold text-blue-900">
                                        {{ effectiveCustomerName }}
                                    </p>
                                </div>
                            </div>
                            <button
                                v-if="selectedCustomer"
                                @click="
                                    selectedCustomer = null;
                                    eWalletAmount = 0;
                                    searchQuery = '';
                                "
                                class="text-xs text-blue-600 hover:text-blue-700 font-medium underline"
                            >
                                Change Customer
                            </button>
                        </div>
                    </div>

                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Load Amount to E-Wallet
                    </label>
                    <div class="relative">
                        <input
                            v-model.number="eWalletAmount"
                            @input="handleAmountInput"
                            type="number"
                            step="0.01"
                            min="0"
                            :max="props.changeValue"
                            placeholder="Enter amount"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                    <div
                        class="flex justify-between mt-2 text-xs text-gray-600"
                    >
                        <span>Total change: {{ changeAmount }}</span>
                        <span v-if="eWalletAmount > 0 && remainingChange > 0"
                            >Cash to return:
                            {{ formattedRemainingChange }}</span
                        >
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button
                            @click="eWalletAmount = props.changeValue"
                            class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors"
                        >
                            Load All
                        </button>
                        <button
                            v-if="hasDecimalCents"
                            @click="eWalletAmount = decimalAmount"
                            class="px-3 py-1 text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 rounded transition-colors"
                        >
                            Decimals Only ({{ formatCurrency(decimalAmount) }})
                        </button>
                        <button
                            @click="
                                eWalletAmount = Math.floor(props.changeValue)
                            "
                            class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors"
                        >
                            Round Down
                        </button>
                    </div>
                </div>

                <!-- Quick Action: Load Decimals (show when no customer but has decimal change) -->
                <div
                    v-if="
                        hasChange &&
                        !canLoadToEWallet &&
                        hasDecimalCents &&
                        !eWalletLoadSuccess
                    "
                    class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6"
                >
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-1">
                            <WalletIcon class="w-5 h-5 text-blue-600" />
                        </div>
                        <div class="flex-1">
                            <h4
                                class="text-sm font-semibold text-blue-900 mb-1"
                            >
                                Load Decimal Change?
                            </h4>
                            <p class="text-xs text-blue-600 mb-3">
                                Load {{ formatCurrency(decimalAmount) }} to
                                customer's e-wallet to avoid small change?
                            </p>

                            <!-- Customer Selection -->
                            <div class="mt-3" v-if="!selectedCustomer">
                                <label
                                    class="block text-xs font-medium text-gray-700 mb-2"
                                >
                                    Select Customer
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="searchQuery"
                                        @focus="showCustomerList = true"
                                        @blur="
                                            setTimeout(
                                                () =>
                                                    (showCustomerList = false),
                                                200
                                            )
                                        "
                                        type="text"
                                        placeholder="Search by name, contact, or email..."
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <!-- Customer Dropdown -->
                                    <div
                                        v-if="
                                            showCustomerList &&
                                            filteredCustomers.length > 0
                                        "
                                        class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                                    >
                                        <button
                                            v-for="customer in filteredCustomers.slice(
                                                0,
                                                10
                                            )"
                                            :key="customer.id"
                                            @click="selectCustomer(customer)"
                                            class="w-full px-3 py-2 text-left hover:bg-gray-50 flex items-center gap-2 border-b border-gray-100 last:border-0"
                                        >
                                            <div
                                                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                                            >
                                                <span
                                                    class="text-xs font-semibold text-blue-600"
                                                >
                                                    {{
                                                        getInitials(
                                                            customer.customer_name
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div
                                                    class="text-sm font-medium text-gray-900 truncate"
                                                >
                                                    {{ customer.customer_name }}
                                                </div>
                                                <div
                                                    class="text-xs text-gray-500 truncate"
                                                >
                                                    {{
                                                        customer.contact_no ||
                                                        customer.email
                                                    }}
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Selected Customer Display -->
                            <div
                                v-else
                                class="mt-3 bg-white border border-gray-200 rounded-lg p-3"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                                        >
                                            <span
                                                class="text-xs font-semibold text-blue-600"
                                            >
                                                {{
                                                    getInitials(
                                                        selectedCustomer.customer_name
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div>
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{
                                                    selectedCustomer.customer_name
                                                }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{
                                                    selectedCustomer.contact_no ||
                                                    selectedCustomer.email
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        @click="
                                            selectedCustomer = null;
                                            searchQuery = '';
                                        "
                                        class="text-xs text-blue-600 hover:text-blue-700 font-medium"
                                    >
                                        Change
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div v-if="!eWalletLoadSuccess" class="space-y-3">
                    <!-- E-Wallet Button (show if customer is available and has change) -->
                    <button
                        v-if="hasChange && canLoadToEWallet"
                        @click="handleLoadToEWallet"
                        :disabled="isLoading || !isValidAmount"
                        class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center justify-center gap-2"
                    >
                        <WalletIcon class="w-5 h-5" />
                        {{
                            isLoading
                                ? "Loading to E-Wallet..."
                                : "Load " +
                                  formatCurrency(eWalletAmount) +
                                  " to E-Wallet"
                        }}
                    </button>

                    <!-- Confirm Button -->
                    <button
                        @click="handleClose"
                        :disabled="isLoading"
                        class="w-full px-6 py-3 font-semibold rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                        :class="
                            hasChange && canLoadToEWallet
                                ? 'bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-500'
                                : 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500'
                        "
                    >
                        {{
                            hasChange && canLoadToEWallet
                                ? remainingChange > 0
                                    ? `Return ${formattedRemainingChange} as Cash`
                                    : "Skip E-Wallet"
                                : "Confirm"
                        }}
                    </button>
                </div>

                <!-- Done Button (after success) -->
                <div v-else class="flex gap-3">
                    <button
                        @click="handleClose"
                        class="flex-1 px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                        Done
                    </button>
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import Dialog from "primevue/dialog";
import { CheckCircleIcon, WalletIcon } from "@heroicons/vue/24/outline";

interface Props {
    show: boolean;
    changeValue: number;
    currencyCode?: string;
    customerId?: number | null;
    customerName?: string;
    orderId?: number | null;
    customers?: any[];
}

const props = withDefaults(defineProps<Props>(), {
    currencyCode: "PHP",
    customerId: null,
    customerName: "",
    orderId: null,
    customers: () => [],
});

const emit = defineEmits<{
    (e: "close"): void;
    (e: "loadToEWallet", customerId: number, amount: number): void;
}>();

const eWalletLoadSuccess = ref(false);
const isLoading = ref(false);
const eWalletAmount = ref(0);
const selectedCustomer = ref<any>(null);
const searchQuery = ref("");
const showCustomerList = ref(false);

const hasChange = computed(() => props.changeValue > 0);

const filteredCustomers = computed(() => {
    console.log("ChangeModal - Total customers:", props.customers?.length || 0);
    console.log("ChangeModal - Search query:", searchQuery.value);

    if (!searchQuery.value) {
        return props.customers;
    }
    const query = searchQuery.value.toLowerCase();
    const filtered = props.customers.filter(
        (customer: any) =>
            customer.customer_name?.toLowerCase().includes(query) ||
            customer.contact_no?.toLowerCase().includes(query) ||
            customer.email?.toLowerCase().includes(query)
    );

    console.log("ChangeModal - Filtered customers:", filtered.length);
    return filtered;
});

const effectiveCustomerId = computed(
    () => selectedCustomer.value?.id || props.customerId
);
const effectiveCustomerName = computed(
    () => selectedCustomer.value?.customer_name || props.customerName
);

const hasDecimalCents = computed(() => {
    const cents = Math.round((props.changeValue % 1) * 100);
    return cents > 0;
});

const decimalAmount = computed(() => {
    const decimal = props.changeValue % 1;
    return Math.round(decimal * 100) / 100;
});

const isValidAmount = computed(() => {
    return eWalletAmount.value > 0 && eWalletAmount.value <= props.changeValue;
});

const remainingChange = computed(() => {
    const remaining = props.changeValue - eWalletAmount.value;
    return Math.round(remaining * 100) / 100;
});

const formattedRemainingChange = computed(() => {
    return formatCurrency(remainingChange.value);
});

const canLoadToEWallet = computed(
    () => effectiveCustomerId.value && effectiveCustomerName.value
);

const changeAmount = computed(() => {
    return formatCurrency(props.changeValue);
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: props.currencyCode,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
};

const handleAmountInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const value = parseFloat(target.value);
    if (!isNaN(value)) {
        eWalletAmount.value = Math.round(value * 100) / 100;
    }
};

const handleLoadToEWallet = async () => {
    if (!effectiveCustomerId.value || isLoading.value || !isValidAmount.value) {
        return;
    }

    isLoading.value = true;

    try {
        // Emit event to parent to handle the actual loading with the specified amount
        emit("loadToEWallet", effectiveCustomerId.value, eWalletAmount.value);

        // Show success message
        eWalletLoadSuccess.value = true;
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
    eWalletLoadSuccess.value = false;
    eWalletAmount.value = 0;
    selectedCustomer.value = null;
    searchQuery.value = "";
    showCustomerList.value = false;

    emit("close");
};

const selectCustomer = (customer: any) => {
    selectedCustomer.value = customer;
    showCustomerList.value = false;
    searchQuery.value = "";
    // Auto-fill decimal amount if available
    if (hasDecimalCents.value && !eWalletAmount.value) {
        eWalletAmount.value = Math.round(decimalAmount.value * 100) / 100;
    }
};

const getInitials = (name: string) => {
    return (
        name
            ?.split(" ")
            .map((n: string) => n[0])
            .join("")
            .toUpperCase()
            .slice(0, 2) || "?"
    );
};

// Watch for when modal opens to debug
watch(
    () => props.show,
    (newValue) => {
        if (newValue) {
            console.log("===== CHANGE MODAL OPENED =====");
            console.log("Props customers:", props.customers);
            console.log("Total customers:", props.customers?.length || 0);
            console.log("Customer ID:", props.customerId);
            console.log("Customer Name:", props.customerName);
            console.log("Change Value:", props.changeValue);
            console.log("Can load to e-wallet:", canLoadToEWallet.value);
        }
    }
);
</script>
