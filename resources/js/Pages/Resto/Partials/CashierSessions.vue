<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Cashier Sessions</h3>
            <div class="text-sm text-gray-500">
                {{ sessions.length }} session{{ sessions.length !== 1 ? 's' : '' }}
            </div>
        </div>

        <div class="space-y-2 max-h-96 overflow-y-auto">
            <div
                v-for="session in sessions"
                :key="session.id"
                :class="[
                    'p-4 border rounded-lg cursor-pointer transition-colors',
                    activeSession?.id === session.id
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                ]"
                @click="$emit('selectSession', session)"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">
                            Session #{{ session.id }}
                        </p>
                        <p class="text-sm text-gray-600">
                            {{ session.cashier?.name || 'Unknown Cashier' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Closed: {{ formatDate(session.closing_time) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">
                            ₱{{ session.total_sales }}
                        </p>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Closed
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="paginatedSessions?.links" class="flex justify-center mt-4">
            <div class="flex space-x-1">
                <button
                    v-for="link in paginatedSessions.links"
                    :key="link.label"
                    :class="[
                        'px-3 py-2 text-sm border rounded',
                        link.active
                            ? 'bg-blue-500 text-white border-blue-500'
                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                    ]"
                    :disabled="!link.url"
                    v-html="link.label"
                    @click="handlePageClick(link.url)"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import CashieringSession from '@/Types/CashieringSession';

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
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

const handlePageClick = (url: string | null) => {
    emit('goToPage', url);
};
</script>