<template>
    <Head :title="`Order ${order.reference_no}`" />

    <div class="min-h-screen bg-stone-100">
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-stone-200">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-orange-600">
                    Order Submitted
                </p>
                <h1 class="mt-3 text-3xl font-black text-stone-900">
                    Your reference number is {{ order.reference_no }}
                </h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">
                    {{ order.confirmation_message }}
                </p>

                <div class="mt-8 grid gap-4 rounded-3xl bg-stone-950 p-6 text-white md:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-orange-300">Customer</p>
                        <p class="mt-2 text-lg font-bold">{{ order.name }}</p>
                        <p class="text-sm text-stone-300">{{ order.phone }}</p>
                        <p class="text-sm text-stone-300">{{ order.address }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-orange-300">Order Details</p>
                        <p class="mt-2 text-sm text-stone-300">
                            Type: {{ order.order_type === "delivery" ? "Delivery" : "Pickup" }}
                        </p>
                        <p class="text-sm text-stone-300">Status: {{ order.status }}</p>
                        <p class="text-sm text-stone-300">
                            Total: {{ formatCurrency(order.total_amount) }}
                        </p>
                    </div>
                </div>

                <div class="mt-8">
                    <h2 class="text-xl font-bold text-stone-900">Items</h2>
                    <div class="mt-4 space-y-3">
                        <div
                            v-for="item in order.items"
                            :key="`${item.product_name}-${item.quantity}`"
                            class="flex items-center justify-between rounded-2xl border border-stone-200 px-4 py-4"
                        >
                            <div>
                                <p class="font-semibold text-stone-900">{{ item.product_name }}</p>
                                <p class="text-sm text-stone-500">
                                    {{ item.quantity }} x {{ formatCurrency(item.price) }}
                                </p>
                            </div>
                            <p class="font-semibold text-stone-900">
                                {{ formatCurrency(item.subtotal) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <Link
                        href="/"
                        class="rounded-full bg-orange-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-500"
                    >
                        Back to Menu
                    </Link>
                    <Link
                        :href="route('guest.cart')"
                        class="rounded-full border border-stone-300 px-6 py-3 text-sm font-semibold text-stone-700 transition hover:border-orange-500 hover:text-orange-600"
                    >
                        Start Another Order
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const formatCurrency = (value) =>
    new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(Number(value || 0));
</script>
