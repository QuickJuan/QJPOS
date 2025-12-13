<template>
    <div class="flex-grow h-full">
        <!-- Pagination at Top -->
        <div
            v-if="paginatedSessions?.links"
            class="flex justify-center mb-4 px-4 sm:px-0"
        >
            <div class="flex flex-wrap gap-1 justify-center">
                <button
                    v-for="link in paginatedSessions.links"
                    :key="link.label"
                    :class="[
                        'px-3 py-2 text-sm border rounded',
                        link.active
                            ? 'bg-blue-500 text-white border-blue-500'
                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                        !link.url ? 'opacity-50 cursor-not-allowed' : '',
                    ]"
                    :disabled="!link.url"
                    v-html="link.label"
                    @click="handlePageClick(link.url)"
                />
            </div>
        </div>

        <!-- Sessions List -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <!-- List Header (Hidden on mobile) -->
            <div
                class="hidden sm:grid sm:grid-cols-12 gap-4 px-4 py-3 bg-gray-50 border-b border-gray-200 text-sm font-medium text-gray-700"
            >
                <div class="col-span-2">Session #</div>
                <div class="col-span-3">Cashier</div>
                <div class="col-span-3">Closed At</div>
                <div class="col-span-2 text-right">Total Sales</div>
                <div class="col-span-2 text-right">Status</div>
            </div>

            <!-- List Items -->
            <div
                v-for="(session, index) in sessions"
                :key="session.id"
                :class="[
                    'cursor-pointer transition-colors',
                    activeSession?.id === session.id
                        ? 'bg-blue-50'
                        : 'hover:bg-gray-50',
                    index !== sessions.length - 1
                        ? 'border-b border-gray-200'
                        : '',
                ]"
                @click="$emit('selectSession', session)"
            >
                <!-- Mobile Layout -->
                <div class="sm:hidden px-4 py-3">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="font-medium text-gray-900">
                                Session #{{ session.id }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ session.cashier?.name || "Unknown Cashier" }}
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                        >
                            Closed
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <p class="text-gray-500">
                            {{ formatDate(session.closing_time) }}
                        </p>
                        <p class="font-medium text-gray-900">
                            ₱{{ formatMoney(session.total_sales) }}
                        </p>
                    </div>
                </div>

                <!-- Desktop Layout -->
                <div
                    class="hidden sm:grid sm:grid-cols-12 gap-4 px-4 py-3 text-sm"
                >
                    <div class="col-span-2 font-medium text-gray-900">
                        #{{ session.id }}
                    </div>
                    <div class="col-span-3 text-gray-900">
                        {{ session.cashier?.name || "Unknown Cashier" }}
                    </div>
                    <div class="col-span-3 text-gray-600">
                        {{ formatDate(session.closing_time) }}
                    </div>
                    <div
                        class="col-span-2 text-right font-medium text-gray-900"
                    >
                        ₱{{ formatMoney(session.total_sales) }}
                    </div>
                    <div class="col-span-2 text-right">
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                        >
                            Closed
                        </span>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="sessions.length === 0"
                class="px-4 py-8 text-center text-gray-500"
            >
                No sessions found
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import CashieringSession from "@/Types/CashieringSession";

interface PaginatedSessions {
    data: CashieringSession[];
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

const props = defineProps<{
    sessions: CashieringSession[];
    activeSession: CashieringSession | null;
    paginatedSessions?: PaginatedSessions;
}>();

const emit = defineEmits<{
    selectSession: [session: CashieringSession];
    goToPage: [url: string | null];
}>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString(undefined, {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "numeric",
        minute: "2-digit",
    });
};

const formatMoney = (amount: number | string) => {
    const num = typeof amount === "string" ? parseFloat(amount) : amount;
    return num.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const handlePageClick = (url: string | null) => {
    emit("goToPage", url);
};
</script>
