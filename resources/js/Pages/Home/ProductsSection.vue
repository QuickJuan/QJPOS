<template>
    <section
        id="products"
        class="relative w-full flex flex-col items-center justify-center py-12 px-4 z-10"
    >
        <div
            class="w-full max-w-6xl mx-auto bg-white/30 backdrop-blur rounded-2xl shadow-lg p-8 flex flex-col md:flex-row gap-8 items-center justify-between border border-gray-100"
        >
            <div
                class="flex-1 flex flex-col gap-6 md:gap-8 items-start text-left"
            >
                <div class="flex items-center gap-4">
                    <span class="text-5xl">🖥️</span>
                    <div>
                        <div class="font-bold text-primary text-base">
                            POS Terminal
                        </div>
                        <div class="text-secondary text-sm">
                            Modern, intuitive interface for fast transactions
                            and easy staff training.
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-5xl">📱</span>
                    <div>
                        <div class="font-bold text-primary text-base">
                            Mobile POS
                        </div>
                        <div class="text-secondary text-sm">
                            Sell anywhere with your phone or tablet, perfect for
                            tableside or pop-up sales.
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-5xl">🧾</span>
                    <div>
                        <div class="font-bold text-primary text-base">
                            Digital Receipts
                        </div>
                        <div class="text-secondary text-sm">
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
                    class="w-80 md:w-96 h-auto rounded-xl shadow-md"
                />
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <span class="text-2xl font-bold text-secondary"
                            >+2,000</span
                        >
                        <span class="text-xs text-secondary">Clients</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-2xl font-bold text-secondary"
                            >+5M</span
                        >
                        <span class="text-xs text-secondary">Transactions</span>
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
