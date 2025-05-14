<template>
    <section
        id="products"
        class="relative w-full flex flex-col items-center justify-center py-12 px-4 z-10"
    >
        <div
            class="w-full max-w-6xl mx-auto bg-white/80 backdrop-blur rounded-2xl shadow-lg p-8 flex flex-col md:flex-row gap-8 items-center justify-between border border-gray-100"
        >
            <div class="flex-1 flex flex-col gap-6 md:gap-8">
                <div class="flex items-center gap-4">
                    <span class="text-5xl">🖥️</span>
                    <div>
                        <div class="text-xl font-bold text-gray-900">
                            POS Terminal
                        </div>
                        <div class="text-gray-600">
                            Modern, intuitive interface for fast transactions
                            and easy staff training.
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-5xl">📱</span>
                    <div>
                        <div class="text-xl font-bold text-gray-900">
                            Mobile POS
                        </div>
                        <div class="text-gray-600">
                            Sell anywhere with your phone or tablet, perfect for
                            tableside or pop-up sales.
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-5xl">🧾</span>
                    <div>
                        <div class="text-xl font-bold text-gray-900">
                            Digital Receipts
                        </div>
                        <div class="text-gray-600">
                            Send receipts via SMS or email, reduce paper waste
                            and delight customers.
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex-1 flex flex-col items-center justify-center gap-6">
                <img
                    src="/images/resto.png"
                    alt="POS Machine"
                    class="w-64 h-auto rounded-xl shadow-md"
                />
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <span class="text-2xl font-bold text-amber-500"
                            >+2,000</span
                        >
                        <span class="text-xs text-gray-500">Clients</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-2xl font-bold text-amber-500"
                            >+5M</span
                        >
                        <span class="text-xs text-gray-500">Transactions</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
// No script logic needed for this static section
</script>

<style scoped></style>
