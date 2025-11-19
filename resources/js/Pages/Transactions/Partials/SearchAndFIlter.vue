<template>
    <div class="bg-white p-5 space-y-4">
        <div class="relative max-w-xl mx-auto">
            <span
                class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"
            >
                <MagnifyingGlassIcon class="w-5 h-5" />
            </span>
            <TextField
                v-model="searchValue"
                type="text"
                placeholder="Search receipts, users..."
                class="w-full rounded-2xl border border-gray-200 bg-gray-50/70 pl-12 pr-4 py-3 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20"
                @input="handleSearchInput"
            />
        </div>

        <div class="grid grid-cols-1 gap-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="text-xs font-semibold text-gray-500">
                        From Date
                    </label>
                    <Calendar
                        v-model="dateFromValue"
                        dateFormat="yy-mm-dd"
                        placeholder="Select"
                        class="w-full mt-1 rounded-2xl"
                        @date-select="handleDateFromSelect"
                    />
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">
                        To Date
                    </label>
                    <Calendar
                        v-model="dateToValue"
                        dateFormat="yy-mm-dd"
                        placeholder="Select"
                        class="w-full mt-1 rounded-2xl"
                        @date-select="handleDateToSelect"
                    />
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <SelectField
                    v-model="statusValue"
                    :options="props.statusOptions"
                    label="Status"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="All Status"
                    showClear
                    class="w-full mt-1 rounded-2xl"
                    @change="handleStatusChange"
                />
                <SelectField
                    label="Cashier"
                    v-model="cashierIdValue"
                    :options="props.cashierDropdownOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="All Cashiers"
                    showClear
                    class="w-full mt-1 rounded-2xl"
                    @change="handleCashierChange"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import TextField from "@/Components/Form/TextField.vue";
import Calendar from "primevue/calendar";
import SelectField from "@/Components/Form/SelectField.vue";
import MagnifyingGlassIcon from "@/Components/icons/MagnifyingGlassIcon.vue";

const props = defineProps<{
    search: string;
    dateFrom: string | null;
    dateTo: string | null;
    status: string;
    statusOptions: Array<{ label: string; value: string }>;
    cashierDropdownOptions: Array<{ label: string; value: string }>;
    cashier_id: string;
}>();

const emit = defineEmits<{
    (e: "search", value: string): void;
    (e: "dateFrom", value: string | null): void;
    (e: "dateTo", value: string | null): void;
    (e: "status", value: string): void;
    (e: "cashier_id", value: string): void;
}>();

// Local reactive values
const searchValue = ref(props.search);
const dateFromValue = ref(props.dateFrom);
const dateToValue = ref(props.dateTo);
const statusValue = ref(props.status);
const cashierIdValue = ref(props.cashier_id);

// Watch props and update local values
watch(
    () => props.search,
    (newValue) => {
        searchValue.value = newValue;
    }
);

watch(
    () => props.dateFrom,
    (newValue) => {
        dateFromValue.value = newValue;
    }
);

watch(
    () => props.dateTo,
    (newValue) => {
        dateToValue.value = newValue;
    }
);

watch(
    () => props.status,
    (newValue) => {
        statusValue.value = newValue;
    }
);

watch(
    () => props.cashier_id,
    (newValue) => {
        cashierIdValue.value = newValue;
    }
);

// Event handlers
const handleSearchInput = () => {
    emit("search", searchValue.value);
};

const handleDateFromSelect = () => {
    emit("dateFrom", dateFromValue.value);
};

const handleDateToSelect = () => {
    emit("dateTo", dateToValue.value);
};

const handleStatusChange = () => {
    emit("status", statusValue.value);
};

const handleCashierChange = () => {
    emit("cashier_id", cashierIdValue.value);
};
</script>
