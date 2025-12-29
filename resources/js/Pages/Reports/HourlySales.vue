<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">
                                Hourly Sales Report - {{ monthLabel }}
                            </h2>
                            <div class="flex gap-4 items-center">
                                <label
                                    class="text-sm font-medium text-gray-700"
                                >
                                    Select Month:
                                </label>
                                <input
                                    type="month"
                                    v-model="selectedMonth"
                                    @change="filterByMonth"
                                    class="border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>

                        <!-- Sales Table -->
                        <div class="overflow-x-auto">
                            <table
                                class="min-w-full divide-y divide-gray-200 border"
                            >
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border"
                                        >
                                            Time
                                        </th>
                                        <th
                                            v-for="day in daysOfWeek"
                                            :key="day"
                                            class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border"
                                        >
                                            {{ day }}
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border bg-gray-100"
                                        >
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="(row, index) in reportData"
                                        :key="index"
                                    >
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border font-medium"
                                        >
                                            {{ row.time }}
                                        </td>
                                        <td
                                            v-for="day in daysOfWeek"
                                            :key="day"
                                            class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-900 border"
                                        >
                                            {{ formatNumber(row[day]) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-center font-semibold text-gray-900 border bg-gray-50"
                                        >
                                            {{ formatNumber(row.Total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { ref } from "vue";

const props = defineProps<{
    reportData: any[];
    selectedMonth: string;
    monthLabel: string;
}>();

const selectedMonth = ref(props.selectedMonth);

const daysOfWeek = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Sunday",
];

const filterByMonth = () => {
    router.get(
        route("reports.hourly-sales"),
        { month: selectedMonth.value },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const formatNumber = (value: number) => {
    return value.toLocaleString();
};
</script>
