<template>
    <Head title="Checkout" />

    <div class="min-h-screen bg-stone-100">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-orange-600">
                        Guest Checkout
                    </p>
                    <h1 class="text-3xl font-black text-stone-900">Checkout</h1>
                </div>

                <Link
                    :href="route('guest.cart')"
                    class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-orange-500 hover:text-orange-600"
                >
                    Back to Cart
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
                    <h2 class="text-xl font-bold text-stone-900">Customer Information</h2>

                    <form class="mt-6 space-y-5" @submit.prevent="submit">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-stone-700">
                                Full name
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-900 focus:border-orange-500 focus:outline-none"
                            />
                            <p v-if="errors.name" class="mt-2 text-sm text-red-500">{{ errors.name }}</p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-stone-700">
                                    Phone number
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-900 focus:border-orange-500 focus:outline-none"
                                />
                                <p v-if="errors.phone" class="mt-2 text-sm text-red-500">{{ errors.phone }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-stone-700">
                                    Email
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-900 focus:border-orange-500 focus:outline-none"
                                />
                                <p v-if="errors.email" class="mt-2 text-sm text-red-500">{{ errors.email }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-stone-700">
                                Address
                            </label>
                            <textarea
                                v-model="form.address"
                                rows="4"
                                class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-900 focus:border-orange-500 focus:outline-none"
                            ></textarea>
                            <p v-if="errors.address" class="mt-2 text-sm text-red-500">{{ errors.address }}</p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-stone-700">
                                    Order type
                                </label>
                                <select
                                    v-model="form.order_type"
                                    class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-900 focus:border-orange-500 focus:outline-none"
                                >
                                    <option value="pickup">Pickup</option>
                                    <option value="delivery">Delivery</option>
                                </select>
                                <p v-if="errors.order_type" class="mt-2 text-sm text-red-500">{{ errors.order_type }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-stone-700">
                                    Notes
                                </label>
                                <textarea
                                    v-model="form.notes"
                                    rows="4"
                                    class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-900 focus:border-orange-500 focus:outline-none"
                                    placeholder="Optional instructions"
                                ></textarea>
                                <p v-if="errors.notes" class="mt-2 text-sm text-red-500">{{ errors.notes }}</p>
                            </div>
                        </div>

                        <p class="rounded-2xl bg-orange-50 px-4 py-3 text-sm leading-6 text-stone-700 ring-1 ring-orange-200">
                            We will inform you once the order is received, confirmed, and ready.
                        </p>

                        <button
                            type="submit"
                            class="inline-flex rounded-full bg-orange-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-500 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="processing"
                        >
                            {{ processing ? "Submitting..." : "Submit Order" }}
                        </button>
                    </form>
                </section>

                <aside class="rounded-3xl bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] ring-1 ring-stone-200">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-orange-700">
                        Order Summary
                    </p>

                    <div class="mt-6 space-y-4">
                        <div
                            v-for="item in items"
                            :key="item.product_id"
                            class="flex items-start justify-between gap-4 border-b border-stone-200 pb-4"
                        >
                            <div>
                                <p class="font-semibold text-stone-900">{{ item.name }}</p>
                                <p class="text-sm text-stone-500">
                                    {{ item.quantity }} x {{ formatCurrency(item.price) }}
                                </p>
                            </div>
                            <p class="font-semibold text-stone-900">
                                {{ formatCurrency(item.price * item.quantity) }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="coupon"
                        class="mt-6 rounded-2xl bg-orange-50 px-4 py-3 ring-1 ring-orange-200"
                    >
                        <p class="text-sm font-semibold text-stone-900">
                            Coupon applied: {{ coupon.code }}
                        </p>
                        <p class="mt-1 text-sm text-emerald-700">
                            Discount: -{{ formatCurrency(discountAmount) }}
                        </p>
                    </div>

                    <div class="mt-6 border-t border-stone-200 pt-4">
                        <div class="flex items-center justify-between text-sm text-stone-700">
                            <span>Subtotal</span>
                            <span>{{ formatCurrency(totalAmount) }}</span>
                        </div>
                        <div
                            v-if="discountAmount > 0"
                            class="mt-3 flex items-center justify-between text-sm font-semibold text-emerald-700"
                        >
                            <span>Coupon discount</span>
                            <span>-{{ formatCurrency(discountAmount) }}</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-lg font-bold text-stone-950">
                            <span>Total</span>
                            <span>{{ formatCurrency(grandTotal) }}</span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { useGuestCartStore } from "@/stores/guestCartStore";

const cartStore = useGuestCartStore();
const items = computed(() => cartStore.items);
const totalAmount = computed(() => cartStore.totalAmount);
const coupon = computed(() => cartStore.coupon);
const discountAmount = computed(() => cartStore.discountAmount);
const grandTotal = computed(() => cartStore.grandTotal);
const processing = ref(false);
const errors = reactive({});

const form = reactive({
    name: "",
    phone: "",
    email: "",
    address: "",
    order_type: "pickup",
    notes: "",
});

onMounted(() => {
    if (cartStore.items.length === 0) {
        router.visit(route("guest.cart"));
    }
});

const submit = () => {
    processing.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);

    router.post(
        route("guest.checkout.store"),
        {
            ...form,
            coupon_code: coupon.value?.code ?? null,
            items: items.value.map((item) => ({
                product_id: item.product_id,
                quantity: item.quantity,
            })),
        },
        {
            onSuccess: () => cartStore.clearCart(),
            onError: (formErrors) => Object.assign(errors, formErrors),
            onFinish: () => {
                processing.value = false;
            },
        },
    );
};

const formatCurrency = (value) =>
    new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(Number(value || 0));
</script>
