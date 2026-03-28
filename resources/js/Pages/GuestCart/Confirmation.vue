<template>
    <Head :title="`Order ${order.reference_no}`" />

    <div class="min-h-screen bg-stone-100">
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
            <div
                class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-stone-200 space-y-8"
            >
                <!-- Header -->
                <div>
                    <p
                        class="text-sm font-semibold uppercase tracking-[0.3em] text-orange-600"
                    >
                        Order Submitted
                    </p>
                    <h1 class="mt-3 text-3xl font-black text-stone-900">
                        Your reference number is {{ order.reference_no }}
                    </h1>
                    <p class="mt-2 text-sm leading-7 text-stone-500">
                        {{ order.confirmation_message }}
                    </p>
                </div>

                <!-- Status Tracker -->
                <div class="rounded-2xl bg-stone-50 ring-1 ring-stone-200 p-6">
                    <p
                        class="text-xs font-semibold uppercase tracking-widest text-stone-500 mb-5"
                    >
                        Order Status
                    </p>
                    <div class="flex items-start gap-0">
                        <template
                            v-for="(step, idx) in statusSteps"
                            :key="step.key"
                        >
                            <div class="flex flex-col items-center flex-1">
                                <!-- Circle -->
                                <div
                                    :class="[
                                        'flex h-9 w-9 items-center justify-center rounded-full border-2 transition-all',
                                        stepState(step.key) === 'done'
                                            ? 'border-orange-500 bg-orange-500 text-white'
                                            : stepState(step.key) === 'active'
                                              ? 'border-orange-500 bg-white text-orange-600'
                                              : 'border-stone-300 bg-white text-stone-400',
                                    ]"
                                >
                                    <!-- checkmark when done -->
                                    <svg
                                        v-if="stepState(step.key) === 'done'"
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                    <!-- pulse dot when active -->
                                    <span
                                        v-else-if="
                                            stepState(step.key) === 'active'
                                        "
                                        class="h-2.5 w-2.5 rounded-full bg-orange-500 animate-pulse"
                                    />
                                    <!-- empty dot -->
                                    <span
                                        v-else
                                        class="h-2 w-2 rounded-full bg-stone-300"
                                    />
                                </div>
                                <!-- Label -->
                                <p
                                    :class="[
                                        'mt-2 text-center text-xs font-semibold leading-tight',
                                        stepState(step.key) !== 'upcoming'
                                            ? 'text-stone-800'
                                            : 'text-stone-400',
                                    ]"
                                >
                                    {{ step.label }}
                                </p>
                            </div>

                            <!-- Connector line (not after last) -->
                            <div
                                v-if="idx < statusSteps.length - 1"
                                class="mt-4 h-0.5 flex-1 transition-colors"
                                :class="
                                    stepState(step.key) === 'done'
                                        ? 'bg-orange-400'
                                        : 'bg-stone-200'
                                "
                            />
                        </template>
                    </div>
                </div>

                <!-- Customer + Order Details -->
                <div
                    class="grid gap-4 rounded-3xl bg-stone-950 p-6 text-white md:grid-cols-2"
                >
                    <div>
                        <p
                            class="text-xs uppercase tracking-[0.3em] text-orange-300"
                        >
                            Customer
                        </p>
                        <p class="mt-2 text-lg font-bold">{{ order.name }}</p>
                        <p class="text-sm text-stone-300">{{ order.phone }}</p>
                        <p class="text-sm text-stone-300">
                            {{ order.address }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="text-xs uppercase tracking-[0.3em] text-orange-300"
                        >
                            Order Details
                        </p>
                        <p class="mt-2 text-sm text-stone-300">
                            Type:
                            {{
                                order.order_type === "delivery"
                                    ? "Delivery"
                                    : "Pickup"
                            }}
                        </p>
                        <p class="text-sm text-stone-300">
                            Total: {{ formatCurrency(order.total_amount) }}
                        </p>
                    </div>
                </div>

                <!-- QR Code -->
                <div
                    class="flex flex-col items-center gap-4 rounded-2xl border border-stone-200 bg-stone-50 p-6"
                >
                    <p class="text-sm font-semibold text-stone-700">
                        Scan to track your order
                    </p>
                    <canvas ref="qrCanvas" class="rounded-xl" />
                    <button
                        @click="downloadQr"
                        class="inline-flex items-center gap-2 rounded-full border border-stone-300 px-5 py-2.5 text-sm font-semibold text-stone-700 transition hover:border-orange-500 hover:text-orange-600"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 4v11"
                            />
                        </svg>
                        Download QR Code
                    </button>
                    <p class="text-xs text-stone-400 text-center">
                        Save or screenshot this QR code to return to this page
                        anytime.
                    </p>
                </div>

                <!-- Items -->
                <div>
                    <h2 class="text-xl font-bold text-stone-900">Items</h2>
                    <div class="mt-4 space-y-3">
                        <div
                            v-for="item in order.items"
                            :key="`${item.product_name}-${item.quantity}`"
                            class="flex items-center justify-between rounded-2xl border border-stone-200 px-4 py-4"
                        >
                            <div>
                                <p class="font-semibold text-stone-900">
                                    {{ item.product_name }}
                                </p>
                                <p class="text-sm text-stone-500">
                                    {{ item.quantity }} x
                                    {{ formatCurrency(item.price) }}
                                </p>
                            </div>
                            <p class="font-semibold text-stone-900">
                                {{ formatCurrency(item.subtotal) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-3">
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
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import QRCode from "qrcode";

const props = defineProps({
    order: { type: Object, required: true },
    pageUrl: { type: String, required: true },
    appName: { type: String, default: "" },
});

// ── Status Tracker ────────────────────────────────────────────────────────────

const currentStatus = ref(props.order.status);

const statusSteps = computed(() => {
    const isDelivery = props.order.order_type === "delivery";
    return [
        { key: "pending", label: "Order Received" },
        { key: "confirmed", label: "Confirmed" },
        { key: "preparing", label: "Preparing" },
        {
            key: "ready",
            label: isDelivery ? "Out for Delivery" : "Ready for Pickup",
        },
    ];
});

const statusOrder = ["pending", "confirmed", "preparing", "ready"];

function stepState(key) {
    const current = statusOrder.indexOf(currentStatus.value);
    const idx = statusOrder.indexOf(key);
    if (idx < current) return "done";
    if (idx === current) return "active";
    return "upcoming";
}

// Poll for status updates every 15 seconds
let pollTimer = null;

async function pollStatus() {
    try {
        const res = await fetch(
            route("guest.order.status", props.order.reference_no),
            { headers: { Accept: "application/json" } },
        );
        if (res.ok) {
            const data = await res.json();
            currentStatus.value = data.status;
        }
    } catch {
        // network error — ignore, will retry
    }
}

// ── QR Code ───────────────────────────────────────────────────────────────────

const qrCanvas = ref(null);

async function renderQr() {
    if (!qrCanvas.value) return;
    await QRCode.toCanvas(qrCanvas.value, props.pageUrl, {
        width: 200,
        margin: 2,
        color: { dark: "#1c1917", light: "#fafaf9" },
    });
}

function downloadQr() {
    const canvas = qrCanvas.value;
    if (!canvas) return;

    // Draw branded version with label onto a temp canvas
    const padding = 20;
    const labelH = 36;
    const tmp = document.createElement("canvas");
    tmp.width = canvas.width + padding * 2;
    tmp.height = canvas.height + padding * 2 + labelH;
    const ctx = tmp.getContext("2d");

    // Background
    ctx.fillStyle = "#fafaf9";
    ctx.fillRect(0, 0, tmp.width, tmp.height);

    // QR
    ctx.drawImage(canvas, padding, padding);

    // Label
    ctx.fillStyle = "#ea580c";
    ctx.font = "bold 13px sans-serif";
    ctx.textAlign = "center";
    ctx.fillText(
        `Order: ${props.order.reference_no}`,
        tmp.width / 2,
        canvas.height + padding + labelH / 2 + 5,
    );

    const link = document.createElement("a");
    link.download = `order-${props.order.reference_no}.png`;
    link.href = tmp.toDataURL("image/png");
    link.click();
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────

onMounted(async () => {
    await renderQr();

    // Only poll if order is not yet in final state
    if (!["ready"].includes(currentStatus.value)) {
        pollTimer = setInterval(pollStatus, 15_000);
    }
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
});

// ── Helpers ───────────────────────────────────────────────────────────────────

const formatCurrency = (value) =>
    new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(Number(value || 0));
</script>
