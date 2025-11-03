<template>
    <div class="min-h-screen bg-gray-100 py-8">
        <div class="max-w-4xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Receipt Preview</h1>
                    <button
                        @click="printReceipt"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
                    >
                        Print Receipt
                    </button>
                </div>

                <!-- Receipt Preview -->
                <div class="flex justify-center mb-6">
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <ReceiptLayout
                            :business-name="businessInfo.name"
                            :business-address="businessInfo.address"
                            :business-phone="businessInfo.phone"
                            :receipt-number="receiptData.receiptNumber"
                            :receipt-date="receiptData.date"
                            :table-number="receiptData.tableNumber"
                            :cashier-name="receiptData.cashierName"
                            :order-type="receiptData.orderType"
                            :order-items="receiptData.orderItems"
                            :subtotal="receiptData.subtotal"
                            :tax-amount="receiptData.taxAmount"
                            :discount-amount="receiptData.discountAmount"
                            :total-amount="receiptData.totalAmount"
                            :payment-info="receiptData.paymentInfo"
                            :footer-message="businessInfo.footerMessage"
                        />
                    </div>
                </div>

                <!-- Sample Data Controls -->
                <div class="border-t pt-6">
                    <h2 class="text-lg font-semibold mb-4">Sample Data</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Business Name
                            </label>
                            <input
                                v-model="businessInfo.name"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Receipt Number
                            </label>
                            <input
                                v-model="receiptData.receiptNumber"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Table Number
                            </label>
                            <input
                                v-model="receiptData.tableNumber"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Cashier Name
                            </label>
                            <input
                                v-model="receiptData.cashierName"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Order Type
                            </label>
                            <select
                                v-model="receiptData.orderType"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="dine-in">Dine-in</option>
                                <option value="takeout">Takeout</option>
                                <option value="delivery">Delivery</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button
                            @click="loadSampleData"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors"
                        >
                            Load Sample Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import ReceiptLayout from '@/Components/ReceiptLayout.vue';

// Business information
const businessInfo = reactive({
    name: 'Quick Juan Restaurant',
    address: '123 Main Street, Makati City, Philippines',
    phone: '(02) 123-4567',
    footerMessage: 'Generated by Quick Juan POS System'
});

// Receipt data
const receiptData = reactive({
    receiptNumber: '001234',
    date: new Date().toISOString(),
    tableNumber: 'Table 5',
    cashierName: 'John Doe',
    orderType: 'dine-in',
    orderItems: [] as any[],
    subtotal: 0,
    taxAmount: 0,
    discountAmount: 0,
    totalAmount: 0,
    paymentInfo: null as any
});

// Sample data
const loadSampleData = () => {
    receiptData.orderItems = [
        {
            id: 1,
            product_name: 'Chicken Joy',
            quantity: 2,
            unit_price: 85.00,
            price: 170.00,
            selected_options: [
                { id: 1, name: 'Extra Rice', price: 15.00 },
                { id: 2, name: 'Regular Drink', price: 20.00 }
            ],
            discount: 0
        },
        {
            id: 2,
            product_name: 'Tuna Pie',
            quantity: 1,
            unit_price: 45.00,
            price: 45.00,
            selected_options: [],
            discount: 5.00
        },
        {
            id: 3,
            product_name: 'Jolly Hotdog',
            quantity: 3,
            unit_price: 25.00,
            price: 75.00,
            selected_options: [
                { id: 3, name: 'Cheese', price: 5.00 }
            ],
            discount: 0
        }
    ];

    // Calculate totals
    const subtotal = receiptData.orderItems.reduce((sum, item) => sum + item.price, 0);
    const discountAmount = receiptData.orderItems.reduce((sum, item) => sum + (item.discount || 0), 0);
    const discountedSubtotal = subtotal - discountAmount;
    const taxAmount = discountedSubtotal * 0.12; // 12% VAT
    const totalAmount = discountedSubtotal + taxAmount;

    receiptData.subtotal = subtotal;
    receiptData.discountAmount = discountAmount;
    receiptData.taxAmount = taxAmount;
    receiptData.totalAmount = totalAmount;

    receiptData.paymentInfo = {
        amount_paid: 350.00,
        change: 350.00 - totalAmount,
        method: 'Cash'
    };
};

// Print function
const printReceipt = () => {
    window.print();
};

// Load sample data on mount
loadSampleData();
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .receipt-container, .receipt-container * {
        visibility: visible;
    }
    .receipt-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        max-width: none;
    }
}
</style>
