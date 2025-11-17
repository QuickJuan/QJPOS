<template>
    <div class="bg-white p-5 space-y-4">
        <div class="relative max-w-xl mx-auto">
            <span
                class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"
            >
                <MagnifyingGlassIcon class="w-5 h-5" />
            </span>
            <TextField
                v-model="props.search"
                type="text"
                placeholder="Search receipts, users..."
                class="w-full rounded-2xl border border-gray-200 bg-gray-50/70 pl-12 pr-4 py-3 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20"
                @input="() => emit('search', $event)"
            />
        </div>

        <div class="grid grid-cols-1 gap-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="text-xs font-semibold text-gray-500">
                        From Date
                    </label>
                    <Calendar
                        v-model="props.dateFrom"
                        dateFormat="yy-mm-dd"
                        placeholder="Select"
                        class="w-full mt-1 rounded-2xl"
                        @date-select="() => emit('dateFrom', $event)"
                    />
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">
                        To Date
                    </label>
                    <Calendar
                        v-model="props.dateTo"
                        dateFormat="yy-mm-dd"
                        placeholder="Select"
                        class="w-full mt-1 rounded-2xl"
                        @date-select="() => emit('dateTo', $event)"
                    />
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <SelectField
                    v-model="props.status"
                    :options="props.statusOptions"
                    label="Status"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="All Status"
                    showClear
                    class="w-full mt-1 rounded-2xl"
                    @change="() => emit('status', $event)"
                />
                <SelectField
                    label="Cashier"
                    v-model="props.cashier_id"
                    :options="props.cashierDropdownOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="All Cashiers"
                    showClear
                    class="w-full mt-1 rounded-2xl"
                    @change="() => emit('cashier_id', $event)"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import TextField from "@/Components/Form/TextField.vue";
import Calendar from "primevue/calendar";
import SelectField from "@/Components/Form/SelectField.vue";
import MagnifyingGlassIcon from "@/Components/icons/MagnifyingGlassIcon.vue";

const props = defineProps<{
    search: string;
    dateFrom: Date;
    dateTo: Date;
    status: string;
    statusOptions: Array<{ label: string; value: string }>;
    cashierDropdownOptions: Array<{ label: string; value: string }>;
    cashier_id: string;
}>();

const emit = defineEmits<{
    (e: "search", value: string): void;
    (e: "dateFrom", value: Date): void;
    (e: "dateTo", value: Date): void;
    (e: "status", value: string): void;
    (e: "cashier_id", value: string): void;
}>();
</script>
