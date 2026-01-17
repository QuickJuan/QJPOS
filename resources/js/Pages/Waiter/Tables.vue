<template>
    <AppLayout title="Tables">
        <!-- Copy the entire table management interface from Resto/Tables.vue -->
        <div
            class="flex flex-col h-screen bg-neutral-100 overflow-y-hidden"
        >
            <!-- Toolbar -->
            <div class="p-3 bg-white shadow sticky top-0 z-10">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-xl font-bold text-neutral-900">Tables - Order Taking</h1>
                    <Link
                        :href="route('waiter.home')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-neutral-300 rounded-lg text-neutral-700 hover:bg-neutral-50 transition-colors"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        Back to Home
                    </Link>
                </div>

                <!-- Location Tabs -->
                <div class="space-y-4">
                    <div
                        v-for="group in groupedLocations"
                        :key="group.type"
                        class="space-y-2"
                    >
                        <div class="text-xs font-semibold uppercase text-neutral-500">
                            {{ group.label }}
                        </div>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                            <button
                                v-for="loc in group.locations"
                                :key="loc.id"
                                @click="selectedLocationId = loc.id"
                                :class="[
                                    'px-4 py-2 rounded text-sm font-semibold border transition-colors',
                                    selectedLocationId === loc.id
                                        ? 'bg-primary text-white border-primary'
                                        : 'bg-white text-neutral-700 hover:bg-neutral-50 border-neutral-300',
                                ]"
                            >
                                {{ loc.name }} ({{ loc?.tableRoomCount || 0 }})
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Grid -->
            <div class="flex-1 overflow-y-auto p-4">
                <div v-if="loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary-500 border-t-transparent"></div>
                    <p class="text-neutral-600 mt-4">Loading tables...</p>
                </div>

                <div v-else-if="filteredTables.length === 0" class="text-center py-12">
                    <p class="text-neutral-500">No tables found in this location</p>
                </div>

                <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                    <Link
                        v-for="table in filteredTables"
                        :key="table.id"
                        :href="route('waiter.order', table.id)"
                        :class="[
                            'relative p-6 rounded-lg border-2 transition-all cursor-pointer hover:shadow-lg',
                            getTableStatusClass(table)
                        ]"
                    >
                        <div class="text-center">
                            <div class="text-2xl font-bold mb-2">
                                {{ table.name }}
                            </div>
                            <div class="text-sm opacity-75">
                                {{ getTableStatusText(table) }}
                            </div>
                            <div v-if="table.current_order" class="text-xs mt-2 opacity-60">
                                {{ table.current_order.items_count || 0 }} items
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";

const loading = ref(true);
const locations = ref([]);
const tables = ref([]);
const selectedLocationId = ref(null);

const groupedLocations = computed(() => {
    const groups = {
        dine_in: { type: 'dine_in', label: 'Dine In', locations: [] },
        takeout: { type: 'takeout', label: 'Take Out', locations: [] },
        delivery: { type: 'delivery', label: 'Delivery', locations: [] },
    };

    locations.value.forEach(loc => {
        if (groups[loc.type]) {
            groups[loc.type].locations.push(loc);
        }
    });

    return Object.values(groups).filter(g => g.locations.length > 0);
});

const filteredTables = computed(() => {
    if (!selectedLocationId.value) return tables.value;
    return tables.value.filter(t => t.location_id === selectedLocationId.value);
});

const loadData = async () => {
    try {
        loading.value = true;
        const [locationsRes, tablesRes] = await Promise.all([
            axios.get('/api/locations'),
            axios.get('/api/table-rooms')
        ]);

        locations.value = locationsRes.data.data || [];
        tables.value = tablesRes.data.data || [];

        if (locations.value.length > 0 && !selectedLocationId.value) {
            selectedLocationId.value = locations.value[0].id;
        }
    } catch (error) {
        console.error('Error loading data:', error);
    } finally {
        loading.value = false;
    }
};

const getTableStatusClass = (table) => {
    if (!table.current_order) {
        return 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100';
    }
    return 'bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-100';
};

const getTableStatusText = (table) => {
    if (!table.current_order) return 'Available';
    return 'Occupied';
};

onMounted(() => {
    loadData();
});
</script>
                        'group relative bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 p-6 border-2',
                        getTableStatusClass(table),
                    ]"
                >
                    <!-- Status Badge -->
                    <div class="absolute top-4 right-4">
                        <span
                            :class="[
                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                getStatusBadgeClass(table),
                            ]"
                        >
                            {{ getStatusLabel(table) }}
                        </span>
                    </div>

                    <!-- Table Info -->
                    <div class="mt-2">
                        <h3 class="text-2xl font-bold text-neutral-900 mb-2">
                            {{ table.table_number }}
                        </h3>
                        <p class="text-sm text-neutral-600 mb-4">
                            {{ table.room?.name || "No Room" }}
                        </p>

                        <!-- Capacity -->
                        <div
                            class="flex items-center gap-2 text-neutral-600 mb-2"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                            <span class="text-sm"
                                >Seats: {{ table.capacity }}</span
                            >
                        </div>

                        <!-- Order Info if occupied -->
                        <div
                            v-if="table.cart"
                            class="mt-4 pt-4 border-t border-neutral-200"
                        >
                            <p class="text-sm text-neutral-600">
                                Bill:
                                <span class="font-semibold">{{
                                    table.cart.bill_number
                                }}</span>
                            </p>
                            <p class="text-sm text-neutral-600">
                                Items:
                                <span class="font-semibold">{{
                                    table.cart.items_count || 0
                                }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Action Indicator -->
                    <div class="absolute bottom-4 right-4">
                        <svg
                            class="w-6 h-6 text-neutral-400 group-hover:text-primary-600 transition-colors"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div
                v-if="!loading && tables.length === 0"
                class="text-center py-12"
            >
                <svg
                    class="mx-auto h-12 w-12 text-neutral-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                    />
                </svg>
                <p class="text-neutral-600 mt-4">No tables available</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useToast } from "primevue";

const toast = useToast();
const page = usePage();

const loading = ref(true);
const tables = ref([]);

const activeBranch = computed(() => page.props.active_branch);

onMounted(async () => {
    await fetchTables();
});

const fetchTables = async () => {
    try {
        loading.value = true;
        const branchId = activeBranch.value?.id;

        if (!branchId) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "No active branch selected",
                life: 3000,
            });
            return;
        }

        const response = await axios.get(
            route("api.tables.branch-tables", branchId)
        );
        tables.value = response.data.data || [];
    } catch (error) {
        console.error("Failed to fetch tables:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to load tables",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const getTableStatusClass = (table) => {
    if (table.cart) {
        return "border-yellow-300 bg-yellow-50";
    }
    return "border-green-300 bg-green-50 hover:border-green-400";
};

const getStatusBadgeClass = (table) => {
    if (table.cart) {
        return "bg-yellow-100 text-yellow-800";
    }
    return "bg-green-100 text-green-800";
};

const getStatusLabel = (table) => {
    if (table.cart) {
        return "Occupied";
    }
    return "Available";
};
</script>
