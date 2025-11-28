<template>
    <div class="">
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center gap-3 md:gap-4"
        >
            <TextField
                v-model="searchValue"
                type="text"
                placeholder="Search receipts, users..."
                class="w-full"
                @input="handleSearchInput"
            />

            <div>
                <DatePicker
                    v-model="dateRange"
                    selectionMode="range"
                    :manualInput="false"
                    dateFormat="yy-mm-dd"
                    placeholder="Select Date Range"
                    class="w-full"
                    showIcon
                    iconDisplay="input"
                    @update:model-value="handleRangeUpdate"
                />
            </div>

            <div class="">
                <SelectField
                    v-model="statusValue"
                    :options="props.statusOptions"
                    label="Status"
                    :hideLabel="true"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="All Status"
                    showClear
                    class="w-full rounded-2xl"
                    @change="handleStatusChange"
                />
            </div>

            <div class="">
                <SelectField
                    label="Cashier"
                    :hideLabel="true"
                    v-model="cashierIdValue"
                    :options="props.cashierDropdownOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="All Cashiers"
                    showClear
                    class="w-full rounded-2xl"
                    @change="handleCashierChange"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import TextField from "@/Components/Form/TextField.vue";
import SelectField from "@/Components/Form/SelectField.vue";
import DatePicker from "primevue/datepicker";

const props = defineProps<{
    search: string;
    dateRange: [Date | null, Date | null] | null;
    status: string;
    statusOptions: Array<{ label: string; value: string }>;
    cashierDropdownOptions: Array<{ label: string; value: string }>;
    cashier_id: string;
}>();

const emit = defineEmits<{
    (e: "search", value: string): void;
    (e: "dateRange", value: [string | null, string | null]): void;
    (e: "status", value: string): void;
    (e: "cashier_id", value: string): void;
}>();

// Local reactive values
const searchValue = ref(props.search);
const dateRange = ref<[Date | null, Date | null] | null>(props.dateRange);
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
    () => props.dateRange,
    (newValue) => {
        dateRange.value = newValue;
    },
    { immediate: true }
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

const handleRangeUpdate = () => {
    const [start, end] = (dateRange.value || []) as [Date | null, Date | null];
    emit("dateRange", [
        start ? formatYMD(start) : null,
        end ? formatYMD(end) : null,
    ]);
};

const handleStatusChange = () => {
    emit("status", statusValue.value);
};

const handleCashierChange = () => {
    emit("cashier_id", cashierIdValue.value);
};

function formatYMD(d: Date) {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
}
</script>
