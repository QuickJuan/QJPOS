<template>
    <div>
        <!-- Customer Selection Button -->
        <button
            @click="showModal = true"
            class="w-full flex items-center justify-between px-4 py-3 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
            <div class="flex items-center gap-3">
                <UserIcon class="w-5 h-5 text-gray-400" />
                <div class="text-left">
                    <p class="text-xs text-gray-500">Customer</p>
                    <p class="text-sm font-medium text-gray-900">
                        {{
                            selectedCustomer?.customer_name ||
                            "Walk-in Customer"
                        }}
                    </p>
                </div>
            </div>
            <ChevronRightIcon class="w-5 h-5 text-gray-400" />
        </button>

        <!-- Customer Selection Modal -->
        <TransitionRoot :show="showModal" as="template">
            <Dialog as="div" class="relative z-50" @close="showModal = false">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black bg-opacity-25" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div
                        class="flex min-h-full items-center justify-center p-4"
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
                                class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all"
                            >
                                <DialogTitle
                                    as="h3"
                                    class="text-lg font-medium leading-6 text-gray-900 mb-4"
                                >
                                    Select Customer
                                </DialogTitle>

                                <!-- Search Input -->
                                <div class="mb-4">
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search customers..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                    />
                                </div>

                                <!-- Walk-in Option -->
                                <div
                                    @click="selectCustomer(null)"
                                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 cursor-pointer mb-2"
                                >
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center"
                                    >
                                        <UserIcon
                                            class="w-6 h-6 text-gray-500"
                                        />
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            Walk-in Customer
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            No customer record
                                        </p>
                                    </div>
                                </div>

                                <!-- Customer List -->
                                <div class="max-h-60 overflow-y-auto space-y-2">
                                    <div
                                        v-for="customer in filteredCustomers"
                                        :key="customer.id"
                                        @click="selectCustomer(customer)"
                                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 cursor-pointer"
                                    >
                                        <div
                                            class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center"
                                        >
                                            <span
                                                class="text-primary-600 font-semibold"
                                            >
                                                {{
                                                    getInitials(
                                                        customer.customer_name
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <p
                                                class="font-medium text-gray-900"
                                            >
                                                {{ customer.customer_name }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{
                                                    customer.contact_no ||
                                                    customer.email ||
                                                    "No contact info"
                                                }}
                                            </p>
                                        </div>
                                        <span
                                            v-if="customer.type === 'vip'"
                                            class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full"
                                        >
                                            VIP
                                        </span>
                                    </div>
                                </div>

                                <!-- Add New Customer Button -->
                                <button
                                    @click="showAddCustomerForm = true"
                                    class="w-full mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 transition-colors"
                                >
                                    + Add New Customer
                                </button>

                                <!-- Close Button -->
                                <button
                                    @click="showModal = false"
                                    class="w-full mt-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                                >
                                    Cancel
                                </button>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Add Customer Modal -->
        <AddCustomerModal
            v-model="showAddCustomerForm"
            @customer-added="handleCustomerAdded"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { UserIcon, ChevronRightIcon } from "@heroicons/vue/24/outline";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from "@headlessui/vue";
import AddCustomerModal from "./AddCustomerModal.vue";

interface Customer {
    id: number;
    customer_name: string;
    contact_no?: string;
    email?: string;
    type: "regular" | "vip";
}

const props = defineProps<{
    customers: Customer[];
    modelValue?: Customer | null;
}>();

const emit = defineEmits<{
    (e: "update:modelValue", value: Customer | null): void;
}>();

const showModal = ref(false);
const showAddCustomerForm = ref(false);
const searchQuery = ref("");
const selectedCustomer = ref<Customer | null>(props.modelValue || null);

const filteredCustomers = computed(() => {
    if (!searchQuery.value) {
        return props.customers;
    }
    const query = searchQuery.value.toLowerCase();
    return props.customers.filter(
        (customer) =>
            customer.customer_name.toLowerCase().includes(query) ||
            customer.contact_no?.toLowerCase().includes(query) ||
            customer.email?.toLowerCase().includes(query)
    );
});

const selectCustomer = (customer: Customer | null) => {
    selectedCustomer.value = customer;
    emit("update:modelValue", customer);
    showModal.value = false;
    searchQuery.value = "";
};

const getInitials = (name: string) => {
    return name
        .split(" ")
        .map((n) => n[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
};

const handleCustomerAdded = (customer: Customer) => {
    selectCustomer(customer);
    showAddCustomerForm.value = false;
};
</script>
