<template>
    <Head title="Your Cart" />

    <div class="min-h-screen bg-[linear-gradient(180deg,#fffaf4_0%,#fff 30%,#f6f2ea_100%)]">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <a
                href="#cart-items"
                class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-full focus:bg-orange-600 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white"
            >
                Skip to cart items
            </a>

            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-orange-700">
                        Guest Checkout
                    </p>
                    <h1 class="text-3xl font-black text-stone-900">Your Cart</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-stone-600">
                        Review your items, apply a coupon if you have one, and move to checkout when ready.
                    </p>
                </div>

                <Link
                    href="/"
                    class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-orange-500 hover:text-orange-600"
                >
                    Continue Shopping
                </Link>
            </div>

            <div
                v-if="items.length === 0"
                class="rounded-3xl border border-dashed border-stone-300 bg-white px-8 py-16 text-center"
            >
                <h2 class="text-2xl font-bold text-stone-900">Your cart is empty</h2>
                <p class="mt-3 text-sm text-stone-500">
                    Add products from the public menu to start your order.
                </p>
                <Link
                    href="/"
                    class="mt-6 inline-flex rounded-full bg-orange-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-500"
                >
                    Browse Products
                </Link>
            </div>

            <div v-else class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_24rem]">
                <section
                    id="cart-items"
                    aria-labelledby="cart-items-heading"
                    class="min-w-0 space-y-4"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-3xl bg-white/80 p-4 ring-1 ring-stone-200 backdrop-blur">
                        <div>
                            <h2 id="cart-items-heading" class="text-lg font-bold text-stone-900">
                                Items in cart
                            </h2>
                            <p class="text-sm text-stone-600">
                                {{ totalItems }} item<span v-if="totalItems !== 1">s</span> selected
                            </p>
                        </div>
                        <button
                            type="button"
                            class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-red-300 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-orange-500"
                            @click="clearCart"
                        >
                            Clear cart
                        </button>
                    </div>

                    <div
                        class="overflow-hidden rounded-[2rem] bg-white shadow-sm ring-1 ring-stone-200 xl:max-h-[calc(100vh-15rem)] xl:overflow-y-auto"
                    >
                        <article
                            v-for="(item, index) in items"
                            :key="item.product_id"
                            class="px-5 py-5 sm:px-6"
                            :class="index !== items.length - 1 ? 'border-b border-stone-200' : ''"
                        >
                            <div class="flex flex-col gap-4">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex min-w-0 gap-4">
                                        <img
                                            v-if="item.image_url"
                                            :src="item.image_url"
                                            :alt="item.name"
                                            class="h-20 w-20 flex-none rounded-2xl object-cover"
                                        />
                                        <div
                                            v-else
                                            class="flex h-20 w-20 flex-none items-center justify-center rounded-2xl bg-stone-100 text-stone-400"
                                            aria-hidden="true"
                                        >
                                            No image
                                        </div>

                                        <div class="min-w-0">
                                            <h3 class="text-lg font-bold text-stone-900">
                                                {{ item.name }}
                                            </h3>
                                            <p class="text-sm uppercase tracking-[0.14em] text-stone-500">
                                                {{ item.category || "Menu item" }}
                                            </p>
                                            <p class="mt-2 text-sm font-semibold text-orange-700">
                                                {{ formatCurrency(item.price) }} each
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="self-start rounded-full px-3 py-2 text-sm font-semibold text-stone-500 transition hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                        :aria-label="`Remove ${item.name} from cart`"
                                        @click="cartStore.removeItem(item.product_id)"
                                    >
                                        Remove
                                    </button>
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div
                                        class="inline-flex items-center rounded-full border border-stone-200 bg-stone-50"
                                        role="group"
                                        :aria-label="`Quantity controls for ${item.name}`"
                                    >
                                        <button
                                            type="button"
                                            class="px-4 py-3 text-lg text-stone-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-orange-500"
                                            :aria-label="`Decrease quantity of ${item.name}`"
                                            @click="cartStore.updateQuantity(item.product_id, item.quantity - 1)"
                                        >
                                            -
                                        </button>
                                        <span
                                            class="min-w-12 text-center text-sm font-bold text-stone-900"
                                            aria-live="polite"
                                            aria-atomic="true"
                                        >
                                            {{ item.quantity }}
                                        </span>
                                        <button
                                            type="button"
                                            class="px-4 py-3 text-lg text-stone-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-orange-500"
                                            :aria-label="`Increase quantity of ${item.name}`"
                                            @click="cartStore.updateQuantity(item.product_id, item.quantity + 1)"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <p class="text-base font-bold text-stone-900">
                                        {{ formatCurrency(item.price * item.quantity) }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                <aside class="xl:sticky xl:top-24 xl:self-start">
                    <div
                        class="rounded-3xl bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] ring-1 ring-stone-200"
                        aria-labelledby="order-summary-heading"
                    >
                    <p
                        id="order-summary-heading"
                        class="text-xs font-semibold uppercase tracking-[0.3em] text-orange-700"
                    >
                        Order Summary
                    </p>
                    <p class="mt-2 text-sm leading-6 text-stone-600">
                        A quick summary so you can stay focused without scrolling through the full list again.
                    </p>

                    <form class="mt-5" @submit.prevent="applyCoupon">
                        <label for="coupon-code" class="mb-2 block text-sm font-semibold text-stone-800">
                            Coupon code
                        </label>
                        <div class="flex gap-2">
                            <input
                                id="coupon-code"
                                v-model="couponCode"
                                type="text"
                                autocomplete="off"
                                class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-900 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200"
                                placeholder="Enter coupon"
                                :aria-describedby="couponMessage ? 'coupon-message' : undefined"
                            />
                            <button
                                type="submit"
                                class="rounded-2xl bg-orange-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 disabled:opacity-60"
                                :disabled="couponLoading"
                            >
                                {{ couponLoading ? "Applying" : "Apply" }}
                            </button>
                        </div>
                    </form>

                    <div
                        v-if="couponMessage"
                        id="coupon-message"
                        class="mt-3 rounded-2xl px-4 py-3 text-sm"
                        :class="couponMessageType === 'success' ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200' : 'bg-red-50 text-red-700 ring-1 ring-red-200'"
                        aria-live="polite"
                    >
                        {{ couponMessage }}
                    </div>

                    <div
                        v-if="appliedCoupon"
                        class="mt-3 flex items-start justify-between gap-3 rounded-2xl bg-orange-50 px-4 py-3 ring-1 ring-orange-200"
                    >
                        <div>
                            <p class="text-sm font-semibold text-stone-900">
                                {{ appliedCoupon.code }}
                            </p>
                            <p class="text-sm text-stone-600">
                                {{ appliedCoupon.name }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="text-sm font-semibold text-orange-700 hover:text-orange-800 focus:outline-none focus:ring-2 focus:ring-orange-500"
                            @click="removeCoupon"
                        >
                            Remove
                        </button>
                    </div>

                    <dl class="mt-6 space-y-4">
                        <div class="flex items-center justify-between text-sm text-stone-700">
                            <dt>Items</dt>
                            <dd>{{ totalItems }}</dd>
                        </div>
                        <div class="flex items-center justify-between text-sm text-stone-700">
                            <dt>Subtotal</dt>
                            <dd>{{ formatCurrency(totalAmount) }}</dd>
                        </div>
                        <div
                            v-if="discountAmount > 0"
                            class="flex items-center justify-between text-sm font-semibold text-emerald-700"
                        >
                            <dt>Coupon discount</dt>
                            <dd>-{{ formatCurrency(discountAmount) }}</dd>
                        </div>
                        <div class="border-t border-stone-200 pt-4">
                            <div class="flex items-center justify-between text-lg font-bold text-stone-950">
                                <dt>Total</dt>
                                <dd>{{ formatCurrency(grandTotal) }}</dd>
                            </div>
                        </div>
                    </dl>

                    <Link
                        :href="route('guest.checkout')"
                        class="mt-8 flex w-full items-center justify-center rounded-full bg-orange-600 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500"
                        aria-label="Proceed to checkout"
                    >
                        Proceed to Checkout
                    </Link>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import axios from "axios";
import { Head, Link } from "@inertiajs/vue3";
import { useGuestCartStore } from "@/stores/guestCartStore";

const cartStore = useGuestCartStore();
const items = computed(() => cartStore.items);
const appliedCoupon = computed(() => cartStore.coupon);
const totalItems = computed(() => cartStore.totalItems);
const totalAmount = computed(() => cartStore.totalAmount);
const discountAmount = computed(() => cartStore.discountAmount);
const grandTotal = computed(() => cartStore.grandTotal);
const couponCode = ref(cartStore.coupon?.code ?? "");
const couponLoading = ref(false);
const couponMessage = ref("");
const couponMessageType = ref("success");

watch(
    () => items.value.map((item) => `${item.product_id}:${item.quantity}`).join("|"),
    () => {
        if (cartStore.coupon) {
            couponMessage.value = "Cart changed. Reapply coupon if needed.";
            couponMessageType.value = "success";
        }
    },
);

const clearCart = () => {
    cartStore.clearCart();
    couponCode.value = "";
    couponMessage.value = "";
};

const applyCoupon = async () => {
    couponLoading.value = true;
    couponMessage.value = "";

    try {
        const { data } = await axios.post(route("guest.coupon.validate"), {
            code: couponCode.value,
            items: items.value.map((item) => ({
                product_id: item.product_id,
                quantity: item.quantity,
            })),
            session_id: window.sessionStorage.getItem("guest_coupon_session") || undefined,
        });

        cartStore.setCoupon(data.coupon);
        couponCode.value = data.coupon.code;
        couponMessage.value = "Coupon applied successfully.";
        couponMessageType.value = "success";
    } catch (error) {
        cartStore.clearCoupon();
        couponMessage.value =
            error?.response?.data?.message || "Unable to apply coupon.";
        couponMessageType.value = "error";
    } finally {
        couponLoading.value = false;
    }
};

const removeCoupon = () => {
    cartStore.clearCoupon();
    couponCode.value = "";
    couponMessage.value = "Coupon removed.";
    couponMessageType.value = "success";
};

const formatCurrency = (value) =>
    new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(Number(value || 0));
</script>
