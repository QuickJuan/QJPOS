<template>
    <Dialog
        :visible="visible"
        modal
        :header="`Reserve Table: ${props.table?.name}`"
        :style="{ width: '600px' }"
        :closable="true"
        @hide="closeModal"
        @update:visible="$emit('update:visible', $event)"
    >
        <form @submit.prevent="confirmReservation">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label
                        for="datepicker-from"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Reservation Start
                    </label>
                    <DatePicker
                        id="datepicker-from"
                        v-model="datetime12hFrom"
                        showTime
                        showIcon
                        hourFormat="12"
                        fluid
                        :min-date="minDate"
                        placeholder="Select start date and time"
                    />
                </div>
                <div>
                    <label
                        for="datepicker-to"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Reservation End
                    </label>
                    <DatePicker
                        id="datepicker-to"
                        v-model="datetime12hTo"
                        showTime
                        showIcon
                        hourFormat="12"
                        fluid
                        :min-date="minDate"
                        placeholder="Select end date and time"
                    />
                </div>
            </div>
            <div class="mb-4">
                <TextField
                    label="Customer Name"
                    id="customer_name"
                    v-model="form.name"
                    type="text"
                    class="w-full"
                    required
                    placeholder="Enter customer name"
                    :error="form.errors.name"
                />
            </div>
            <div class="mb-4">
                <TextField
                    label="Number of Pax"
                    id="pax"
                    v-model="form.pax"
                    type="number"
                    class="w-full"
                    required
                    placeholder="Enter number of people"
                    :error="form.errors.pax"
                />
            </div>
            <div class="mb-4">
                <TextField
                    label="Contact Number"
                    id="contact_number"
                    v-model="form.contact_number"
                    type="text"
                    class="w-full"
                    placeholder="Enter contact number"
                    :error="form.errors.contact_number"
                />
            </div>
            <div class="mb-4">
                <TextField
                    label="Contact Email"
                    id="contact_email"
                    v-model="form.contact_email"
                    type="email"
                    class="w-full"
                    placeholder="Enter contact email"
                    :error="form.errors.contact_email"
                />
            </div>
            <div class="mb-4">
                <Textarea
                    v-model="form.notes"
                    label="Notes"
                    id="notes"
                    class="w-full"
                    placeholder="Enter contact email"
                    :error="form.errors.notes"
                />
            </div>
        </form>
        <template #footer>
            <Button label="Cancel" severity="secondary" @click="closeModal" />
            <Button
                label="Reserve Table"
                severity="success"
                :loading="form.processing"
                :disabled="!isValidReservation"
                @click="confirmReservation"
            />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import DatePicker from "primevue/datepicker";
import TextField from "@/Components/Form/TextField.vue";
import Textarea from "@/Components/Form/Textarea.vue";
import PageProps from "@/Types/PageProps";

interface Props {
    visible: boolean;
    table: any;
}

const page = usePage<PageProps>();
const props = defineProps<Props>();

const emit = defineEmits<{
    "update:visible": [value: boolean];
}>();

const form = useForm({
    table_room_id: "",
    reservation_from: "",
    reservation_to: "",
    name: "",
    pax: null as number | null,
    contact_number: "",
    contact_email: "",
    notes: "",
});

const datetime12hFrom = ref<Date | null>(null);
const datetime12hTo = ref<Date | null>(null);
const toast = useToast();

const minDate = computed(() => new Date());

const isValidReservation = computed(() => {
    if (!datetime12hFrom.value || !form.name || !form.pax) return false;

    const reservationDateTimeFrom = new Date(datetime12hFrom.value);
    const now = new Date();
    const isPast = reservationDateTimeFrom.getTime() < now.getTime() - 60000; // Allow up to 1 minute ago

    if (isPast) return false;

    // For occupied tables, it must be future (at least 30 min ahead)
    if (props.table?.status === "occupied") {
        const minTime = new Date(now.getTime() + 30 * 60 * 1000); // 30 minutes from now
        if (reservationDateTimeFrom < minTime) return false;
    }

    // If end time is set, we need to make sure it's after start time
    if (datetime12hTo.value) {
        const reservationDateTimeTo = new Date(datetime12hTo.value);
        if (reservationDateTimeTo <= reservationDateTimeFrom) return false;
    }

    return true;
});

watch(
    () => props.visible,
    (visible) => {
        if (visible) {
            datetime12hFrom.value = null;
            datetime12hTo.value = null;
            form.reset();
        }
    }
);

watch(datetime12hFrom, (newVal) => {
    if (newVal && newVal.getTime() < new Date().getTime() - 60000) {
        datetime12hFrom.value = null;
        toast.add({
            severity: "warn",
            summary: "Invalid Time",
            detail: "Cannot select past times",
            life: 3000,
        });
    }
});

watch(datetime12hTo, (newVal) => {
    if (newVal && datetime12hFrom.value && newVal <= datetime12hFrom.value) {
        datetime12hTo.value = null;
        toast.add({
            severity: "warn",
            summary: "Invalid End Time",
            detail: "End time must be after start time",
            life: 3000,
        });
    }
});

const closeModal = () => {
    emit("update:visible", false);
    form.reset();
    datetime12hFrom.value = null;
    datetime12hTo.value = null;
};

const confirmReservation = () => {
    if (!props.table || !isValidReservation.value) return;

    const reservationDateTimeFrom = new Date(datetime12hFrom.value!);

    form.table_room_id = props.table.id;
    form.reservation_from = reservationDateTimeFrom;
    form.reservation_to = datetime12hTo.value
        ? new Date(datetime12hTo.value)
        : "";

    form.post(route("table-rooms.reserve"), {
        onSuccess: () => {
            toast.add({
                severity: "success",
                summary: "Table Reserved",
                detail: page.props.flash.success,
                life: 3000,
            });
            closeModal();
        },
        onError: () => {
            toast.add({
                severity: "error",
                summary: "Reservation Failed",
                detail:
                    page.props.flash.error ?? "Failed to create reservation",
                life: 3000,
            });
        },
    });
};
</script>
