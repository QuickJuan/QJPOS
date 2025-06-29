<template>
    <div class="min-h-screen flex bg-gray-100">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg flex flex-col justify-between">
            <div>
                <div
                    class="p-6 text-2xl font-bold text-primary tracking-wide border-b border-gray-200"
                >
                    QuickJuan POS
                </div>
                <nav class="mt-6 flex flex-col gap-2 px-4">
                    <a
                        href="#"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 font-medium"
                    >
                        <span>🧾</span> Orders
                    </a>
                    <a
                        href="#"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 font-medium"
                    >
                        <span>🍔</span> Products
                    </a>
                    <a
                        href="#"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 font-medium"
                    >
                        <span>👤</span> Customers
                    </a>
                    <a
                        href="#"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-50 text-gray-700 font-medium"
                    >
                        <span>⚙️</span> Settings
                    </a>
                </nav>
            </div>
            <!-- Customer Info -->
            <div
                class="p-4 border-t border-gray-200 text-sm text-gray-500 flex items-center gap-3"
            >
                <img
                    src="https://randomuser.me/api/portraits/women/44.jpg"
                    class="w-10 h-10 rounded-full object-cover"
                    alt="Customer"
                />
                <div>
                    <div class="font-semibold text-gray-700">Karen Berg</div>
                    <div class="text-xs">
                        Loyalty: <span class="font-medium">55103</span>
                    </div>
                    <div class="text-xs">
                        Balance: <span class="font-medium">$0.00</span>
                    </div>
                    <div class="text-xs">
                        Credit Limit: <span class="font-medium">$1,500.00</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-h-screen">
            <!-- Header -->
            <Header />

            <!-- Tabs -->
            <div class="flex border-b bg-white px-8">
                <button
                    class="py-3 px-6 text-lg font-medium focus:outline-none border-b-2"
                    :class="
                        tab === 'lines'
                            ? 'border-blue-600 text-blue-700'
                            : 'border-transparent text-gray-500'
                    "
                    @click="tab = 'lines'"
                >
                    Lists
                </button>
                <button
                    class="py-3 px-6 text-lg font-medium focus:outline-none border-b-2"
                    :class="
                        tab === 'payments'
                            ? 'border-blue-600 text-blue-700'
                            : 'border-transparent text-gray-500'
                    "
                    @click="tab = 'payments'"
                >
                    Payments
                </button>
            </div>

            <div class="flex flex-1 overflow-hidden">
                <!-- Order List (Lines) -->
                <main class="flex-1 p-6 overflow-y-auto">
                    <div v-if="tab === 'lines'">
                        <OrderItemList
                            :order-items="orderItems"
                            @edit="handleEdit"
                            @delete="handleDelete"
                        />
                    </div>
                    <div
                        v-else
                        class="flex items-center justify-center h-full text-gray-400 text-2xl"
                    >
                        Null
                    </div>
                </main>

                <!-- Actions Panel -->
                <aside
                    class="w-80 bg-white border-l shadow-lg flex flex-col p-4 gap-4"
                >
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <button
                            class="bg-secondary hover:bg-secondary text-white py-3 rounded-lg font-semibold flex flex-col items-center"
                        >
                            <span class="text-xl">#</span>
                            <span class="text-xs">Set quantity</span>
                        </button>
                        <button
                            class="bg-secondary hover:bg-secondary text-white py-3 rounded-lg font-semibold flex flex-col items-center"
                        >
                            <span class="text-xl">💳</span>
                            <span class="text-xs">Scan loyalty</span>
                        </button>
                        <button
                            class="bg-secondary hover:bg-secondary text-white py-3 rounded-lg font-semibold flex flex-col items-center"
                        >
                            <span class="text-xl">🔄</span>
                            <span class="text-xs">Change unit</span>
                        </button>
                        <button
                            class="bg-secondary hover:bg-secondary text-white py-3 rounded-lg font-semibold flex flex-col items-center"
                        >
                            <span class="text-xl">🎫</span>
                            <span class="text-xs">Issue loyalty</span>
                        </button>
                        <button
                            class="bg-secondary hover:bg-secondary text-white py-3 rounded-lg font-semibold flex flex-col items-center"
                        >
                            <span class="text-xl">💬</span>
                            <span class="text-xs">Line comment</span>
                        </button>
                        <button
                            class="bg-secondary hover:bg-secondary text-white py-3 rounded-lg font-semibold flex flex-col items-center"
                        >
                            <span class="text-xl">↩️</span>
                            <span class="text-xs">Return line</span>
                        </button>
                    </div>
                </aside>
            </div>

            <!-- Order Summary & Payment -->
            <Footer
                :order-items="orderItems"
                :subtotal="subtotal"
                :tax="tax"
                :total="total"
            />
        </div>
    </div>

    <!-- Edit Dialog -->
    <Dialog
        v-model:visible="visible"
        modal
        :header="`Edit ${selectedOrderItem?.name || ''}`"
        :style="{ width: '25rem' }"
    >
        <div class="flex flex-col gap-4 mb-4">
            <label for="username" class="font-semibold w-24">Quantity</label>
            <InputNumber
                v-model="selectedOrderItem.quantity"
                showButtons
                buttonLayout="horizontal"
                style="width: 1rem"
                :min="0"
                :max="99"
            >
                <template #incrementbuttonicon>
                    <span class="pi pi-plus" />
                </template>
                <template #decrementbuttonicon>
                    <span class="pi pi-minus" />
                </template>
            </InputNumber>
        </div>
        <div class="flex justify-end gap-2">
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                @click="visible = false"
            />
            <Button type="button" label="Save" @click="visible = false" />
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { Dialog, InputNumber } from "primevue";
import OrderItemList from "@/Components/OrderItemList.vue";
import Header from "./Partials/Header.vue";
import Footer from "./Partials/Footer.vue";

const visible = ref(false);
const selectedOrderItem = ref<any>(null);
const tab = ref<"lines" | "payments">("lines");

// Sample data
const realOrderItems = ref([
    { id: 1, name: "Black Quilted Bag", quantity: 1, price: 200.0 },
    { id: 2, name: "Red Wallet", quantity: 2, price: 50.0 },
    { id: 3, name: "Blue Scarf", quantity: 1, price: 30.0 },
]);

const orderItems = computed(() => realOrderItems.value);

const subtotal = computed(() =>
    orderItems.value.reduce((sum, item) => sum + item.price * item.quantity, 0)
);
const tax = computed(() => subtotal.value * 0.0625); // Example 6.25% tax
const total = computed(() => subtotal.value + tax.value);

const handleEdit = (orderItem: any) => {
    visible.value = true;
    selectedOrderItem.value = orderItem;
};

const handleDelete = (orderItem: any) => {
    // Remove item from realOrderItems
    realOrderItems.value = realOrderItems.value.filter(
        (i: any) => i.id !== orderItem.id
    );
};


</script>
