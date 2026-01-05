<template>
    <div class="p-4 border-b border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-bold text-secondary-800">Order Summary</h2>
            <div
                class="text-sm text-secondary-600 font-medium bg-gray-200 px-3 py-1 rounded-full"
            >
                #Shift: {{ currentShift }}
            </div>
        </div>

        <!-- Customer Info -->
        <div class="space-y-2">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <UserIcon class="w-4 h-4 text-secondary-500" />
                    <span class="text-sm text-secondary-600">Customer:</span>
                    <span class="text-sm font-medium text-secondary-800">
                        {{ displayCustomerName }}
                    </span>
                </div>
                <button
                    type="button"
                    @click="showCustomerModal = true"
                    class="px-2 py-1 text-xs font-medium text-primary-600 hover:text-primary-700 hover:bg-primary-50 rounded-md transition-colors"
                >
                    {{ selectedCustomer ? "Change" : "Select" }}
                </button>
            </div>

            <!-- Selected Table Label -->
            <div class="flex items-center gap-2">
                <span class="text-sm text-secondary-600">Table:</span>
                <span class="text-sm font-medium text-secondary-800">
                    {{ tableInfo?.name || "No Table Selected" }}
                </span>
            </div>
        </div>
    </div>

    <!-- Customer Selection Modal -->
    <Dialog
        v-model:visible="showCustomerModal"
        modal
        header="Select Customer"
        :style="{ width: '500px', maxWidth: '95vw' }"
        :closable="true"
    >
        <div class="space-y-4">
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700">
                    Search Customer
                </label>
                <div class="relative">
                    <input
                        v-model="customerSearchQuery"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                        placeholder="Search by name, phone, or email"
                        autocomplete="off"
                    />
                    <span
                        v-if="isCustomerSearchLoading"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-400"
                    >
                        <i class="pi pi-spin pi-spinner text-sm"></i>
                    </span>
                </div>
                <p class="text-xs text-gray-500">
                    Type at least 2 characters to search
                </p>
                <p v-if="customerSearchError" class="text-xs text-red-500">
                    {{ customerSearchError }}
                </p>
            </div>

            <!-- Customer Results -->
            <div
                v-if="customerResults.length > 0"
                class="max-h-60 overflow-y-auto rounded-lg border border-gray-200 divide-y"
            >
                <button
                    v-for="customer in customerResults"
                    :key="customer.id"
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-3 text-left transition hover:bg-gray-50"
                    :class="{
                        'bg-primary-50': selectedCustomer?.id === customer.id,
                    }"
                    @click="selectCustomer(customer)"
                >
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ customer.customer_name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{
                                customer.contact_no ||
                                customer.email ||
                                "No contact info"
                            }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold text-primary-600">
                            {{ customer.e_wallet?.points_balance || 0 }} pts
                        </p>
                    </div>
                </button>
            </div>

            <div
                v-else-if="
                    customerSearchQuery.length >= 2 && !isCustomerSearchLoading
                "
                class="text-center py-8 text-gray-500 text-sm"
            >
                No customers found
            </div>
        </div>

        <template #footer>
            <div class="flex gap-2">
                <Button
                    label="Walk-in (No Customer)"
                    severity="secondary"
                    outlined
                    class="flex-1"
                    @click="clearCustomer"
                />
                <Button
                    label="Confirm"
                    severity="success"
                    class="flex-1"
                    :disabled="!selectedCustomer"
                    @click="confirmCustomer"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { UserIcon } from "@heroicons/vue/24/outline";
import { ref, computed, watch, onBeforeUnmount } from "vue";
import { usePage } from "@inertiajs/vue3";
import PageProps from "@/Types/PageProps";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import axios from "axios";
import { route } from "ziggy-js";

const props = defineProps<{
    cart: any;
    tableInfo: any;
}>();

const emit = defineEmits<{
    selectTable: [];
    tableChanged: [data: { table: any | null; cart: any | null }];
    customerSelected: [customer: any | null];
}>();

const page = usePage<PageProps>();
const currentShift = ref(props.cart?.id);

// Customer selection state
const showCustomerModal = ref(false);
const selectedCustomer = ref<any | null>(props.cart?.customer || null);
const customerSearchQuery = ref("");
const customerResults = ref<any[]>([]);
const customerSearchError = ref<string | null>(null);
const isCustomerSearchLoading = ref(false);
let customerSearchTimeout: ReturnType<typeof setTimeout> | null = null;
const MIN_CUSTOMER_SEARCH_LENGTH = 2;

// Watch for cart changes to update selected customer
watch(
    () => props.cart?.customer,
    (newCustomer) => {
        if (newCustomer) {
            selectedCustomer.value = newCustomer;
        }
    },
    { immediate: true }
);

const displayCustomerName = computed(() => {
    if (selectedCustomer.value) {
        return selectedCustomer.value.customer_name;
    }
    if (props.cart?.customer) {
        return props.cart.customer.customer_name;
    }
    return props.tableInfo?.customer_name || "Walk-in Customer";
});

// Get branch ID from page props
const branchId = computed(() => page.props?.active_branch?.id);
const currentTableId = computed(() => props.tableInfo?.id || null);

const fetchCustomers = async (term: string) => {
    try {
        isCustomerSearchLoading.value = true;
        customerSearchError.value = null;
        const { data } = await axios.get(route("customers.search"), {
            params: { query: term },
        });

        if (Array.isArray(data)) {
            customerResults.value = data;
        } else if (Array.isArray(data?.data)) {
            customerResults.value = data.data;
        } else {
            customerResults.value = [];
        }
    } catch (error) {
        console.error("Customer search failed", error);
        customerResults.value = [];
        customerSearchError.value =
            "Unable to load customers right now. Please try again.";
    } finally {
        isCustomerSearchLoading.value = false;
    }
};

const handleCustomerSearch = (nextQuery: string) => {
    const trimmed = nextQuery.trim();

    if (customerSearchTimeout) {
        clearTimeout(customerSearchTimeout);
        customerSearchTimeout = null;
    }

    if (!trimmed || trimmed.length < MIN_CUSTOMER_SEARCH_LENGTH) {
        customerResults.value = [];
        customerSearchError.value = null;
        return;
    }

    customerSearchTimeout = setTimeout(() => {
        fetchCustomers(trimmed);
    }, 300);
};

watch(customerSearchQuery, handleCustomerSearch);

const selectCustomer = (customer: any) => {
    selectedCustomer.value = customer;
};

const clearCustomer = () => {
    selectedCustomer.value = null;
    emit("customerSelected", null);
    showCustomerModal.value = false;
    customerSearchQuery.value = "";
    customerResults.value = [];
};

const confirmCustomer = () => {
    emit("customerSelected", selectedCustomer.value);
    showCustomerModal.value = false;
    customerSearchQuery.value = "";
    customerResults.value = [];
};

watch(
    () => showCustomerModal.value,
    (isOpen) => {
        if (!isOpen) {
            customerSearchQuery.value = "";
            customerResults.value = [];
            customerSearchError.value = null;
        }
    }
);

onBeforeUnmount(() => {
    if (customerSearchTimeout) {
        clearTimeout(customerSearchTimeout);
    }
});

const handleTableChanged = (tableData: {
    table: any | null;
    cart: any | null;
}) => {
    emit("tableChanged", tableData);
};
</script>
