<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import {
    BellIcon,
    CheckIcon,
    TrashIcon,
    ArrowTopRightOnSquareIcon,
    InboxArrowDownIcon,
} from "@heroicons/vue/24/outline";
import { CheckCircleIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
    notifications: Object, // paginated
});

const page = usePage();

const markAllRead = () => {
    router.post(
        route("user.inbox.mark-all-read"),
        {},
        { preserveScroll: true },
    );
};

const markRead = (id) => {
    router.post(
        route("user.inbox.mark-read", id),
        {},
        { preserveScroll: true },
    );
};

const remove = (id) => {
    router.delete(route("user.inbox.destroy", id), { preserveScroll: true });
};

const typeIcon = (type) => {
    const icons = {
        leave_request_submitted: "📋",
        leave_request_approved: "✅",
        leave_request_rejected: "❌",
    };
    return icons[type] ?? "🔔";
};

const hasUnread = computed(() =>
    props.notifications?.data?.some((n) => !n.read_at),
);
</script>

<template>
    <AppLayout title="Inbox">
        <template #header>
            <div class="flex items-center gap-3">
                <InboxArrowDownIcon class="h-6 w-6 text-gray-500" />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Inbox
                </h2>
            </div>
        </template>

        <div class="max-w-3xl mx-auto py-10 sm:px-6 lg:px-8">
            <!-- Mark All Read -->
            <div class="flex justify-end mb-4">
                <button
                    v-if="hasUnread"
                    class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-800 font-medium transition"
                    @click="markAllRead"
                >
                    <CheckIcon class="h-4 w-4" />
                    Mark all as read
                </button>
            </div>

            <!-- Notification List -->
            <div
                v-if="notifications.data.length"
                class="divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white shadow-sm"
            >
                <div
                    v-for="notif in notifications.data"
                    :key="notif.id"
                    class="flex items-start gap-4 p-5 transition"
                    :class="!notif.read_at ? 'bg-blue-50/60' : 'bg-white'"
                >
                    <!-- Icon -->
                    <div class="text-2xl flex-shrink-0 mt-0.5">
                        {{ typeIcon(notif.data?.type) }}
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <p
                            class="text-sm text-gray-800"
                            :class="
                                !notif.read_at ? 'font-semibold' : 'font-normal'
                            "
                        >
                            {{ notif.data?.message ?? "New notification" }}
                        </p>
                        <div
                            class="mt-1 flex items-center gap-3 text-xs text-gray-500"
                        >
                            <span>{{
                                new Date(notif.created_at).toLocaleString()
                            }}</span>
                            <a
                                v-if="notif.data?.action_url"
                                :href="notif.data.action_url"
                                class="inline-flex items-center gap-1 text-primary-600 hover:underline"
                                @click="!notif.read_at && markRead(notif.id)"
                            >
                                View
                                <ArrowTopRightOnSquareIcon class="h-3 w-3" />
                            </a>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button
                            v-if="!notif.read_at"
                            title="Mark as read"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-green-600 hover:bg-green-50 transition"
                            @click="markRead(notif.id)"
                        >
                            <CheckCircleIcon class="h-5 w-5" />
                        </button>
                        <button
                            title="Delete"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition"
                            @click="remove(notif.id)"
                        >
                            <TrashIcon class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div
                v-else
                class="text-center py-16 rounded-2xl border border-dashed border-gray-300 bg-white"
            >
                <BellIcon class="mx-auto h-12 w-12 text-gray-300" />
                <p class="mt-4 text-gray-500 text-sm">Your inbox is empty.</p>
            </div>

            <!-- Pagination -->
            <div
                v-if="notifications.last_page > 1"
                class="mt-6 flex justify-center gap-2"
            >
                <Link
                    v-for="link in notifications.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    class="px-3 py-1.5 rounded-lg text-sm border transition"
                    :class="
                        link.active
                            ? 'bg-primary-500 text-white border-primary-500'
                            : link.url
                              ? 'border-gray-300 text-gray-600 hover:border-primary-400 hover:text-primary-600'
                              : 'border-gray-200 text-gray-300 cursor-not-allowed'
                    "
                />
            </div>
        </div>
    </AppLayout>
</template>
