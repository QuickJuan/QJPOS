<template>
    <aside
        class="flex w-64 flex-col bg-slate-950 text-white shadow-xl ring-1 ring-white/5"
    >
        <div class="border-b border-white/5 px-6 py-5">
            <p class="text-xs uppercase tracking-[0.35em] text-slate-500">
                Active Branch
            </p>
            <p class="mt-2 text-xl font-semibold leading-tight">
                {{ branchName }}
            </p>
        </div>

        <nav class="flex-1 space-y-1 px-3 py-4">
            <Link
                v-for="item in navigation"
                :key="item.name"
                :href="item.href"
                class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold transition"
                :class="[
                    item.active
                        ? 'bg-primary-500/10 text-white shadow-inner ring-1 ring-primary-400/40'
                        : 'text-slate-300 hover:bg-white/5 hover:text-white',
                ]"
                :aria-current="item.active ? 'page' : undefined"
            >
                <span
                    class="h-10 w-1 rounded-full"
                    :class="
                        item.active
                            ? 'bg-primary-400 shadow-[0_0_12px_rgba(56,189,248,0.65)]'
                            : 'bg-transparent'
                    "
                />
                <component
                    :is="item.icon"
                    class="h-5 w-5 flex-shrink-0"
                    :class="
                        item.active
                            ? 'text-primary-200'
                            : 'text-slate-500 group-hover:text-primary-200'
                    "
                />
                <span
                    class="truncate"
                    :class="
                        item.active
                            ? 'tracking-[0.25em] text-[11px] uppercase text-primary-100'
                            : ''
                    "
                >
                    {{ item.name }}
                </span>
                <!-- Unread badge -->
                <span
                    v-if="item.badge && item.badge.value > 0"
                    class="ml-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                >
                    {{ item.badge.value > 99 ? "99+" : item.badge.value }}
                </span>
                <span
                    v-else-if="item.active"
                    class="ml-auto h-2.5 w-2.5 rounded-full bg-primary-300/90 shadow-[0_0_8px_rgba(56,189,248,0.6)]"
                />
            </Link>
        </nav>

        <div class="border-t border-white/5 px-3 py-4">
            <button
                type="button"
                class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold text-slate-100 transition hover:bg-red-500/10 hover:text-red-200"
                @click="handleLogout"
            >
                <LogoutIcon class="h-5 w-5" />
                Logout
            </button>
        </div>
    </aside>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import {
    UserCircleIcon,
    EnvelopeIcon,
    ClockIcon,
    BanknotesIcon,
    BuildingLibraryIcon,
    ShoppingCartIcon,
    CalendarDaysIcon,
    ArrowRightOnRectangleIcon as LogoutIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    logout: Function,
});

const page = usePage();

const branchName = computed(
    () => page?.props?.active_branch?.name || "QuickJuan Branch",
);

const currentUrl = computed(() => page?.url || window.location.pathname);

const unreadCount = computed(
    () => page?.props?.unread_notifications_count ?? 0,
);

const rawNavigation = [
    {
        name: "Profile",
        href: "/user/profile",
        icon: UserCircleIcon,
    },
    {
        name: "Inbox",
        href: "/user/inbox",
        icon: EnvelopeIcon,
        badge: unreadCount,
    },
    {
        name: "Attendance",
        href: "/user/attendance",
        icon: ClockIcon,
    },
    {
        name: "Payroll",
        href: "/user/payroll",
        icon: BanknotesIcon,
    },
    {
        name: "Leaves",
        href: "/user/leaves",
        icon: CalendarDaysIcon,
    },
    {
        name: "Loans",
        href: "/user/loans",
        icon: BuildingLibraryIcon,
    },
    {
        name: "Back to Cashiering",
        href: "/resto/home",
        icon: ShoppingCartIcon,
    },
];

const navigation = computed(() =>
    rawNavigation.map((item) => ({
        ...item,
        active: currentUrl.value.startsWith(item.href),
    })),
);

const handleLogout = () => {
    if (props.logout) {
        props.logout();
    }
};
</script>
