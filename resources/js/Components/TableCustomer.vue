<template>
    <div>
        <div class="flex items-center justify-between mb-3">
            <div>
                <div class="text-sm font-medium text-gray-700">Customer</div>
                <p
                    class="text-base font-semibold text-gray-900"
                    v-if="localTable.customer"
                >
                    {{ localTable.customer }}
                </p>
                <p class="text-xs text-gray-500" v-else>No customer</p>
            </div>
            <div class="text-right">
                <div class="text-sm font-medium text-gray-700">Pax</div>
                <p class="text-base font-semibold text-gray-900">
                    {{ localTable.pax || 1 }}
                </p>
            </div>
            <button
                type="button"
                class="ml-2 px-2 py-1 rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200 text-xs"
                @click="showModal = true"
                aria-label="Edit customer info"
            >
                Edit
            </button>
        </div>
        <transition name="modal">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
            >
                <div
                    class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative"
                >
                    <h2 class="text-lg font-bold mb-4">Edit Customer Info</h2>
                    <form
                        @submit.prevent="onSave"
                        class="flex flex-col gap-2"
                        aria-labelledby="sidebar-title"
                    >
                        <div class="mb-1">
                            <label class="block text-sm font-medium mb-1"
                                >Status</label
                            >
                            <span
                                class="inline-block px-2 py-1 rounded text-xs font-semibold"
                                :class="{
                                    'bg-green-200 text-green-800':
                                        localTable.status === 'vacant',
                                    'bg-yellow-200 text-yellow-800':
                                        localTable.status === 'reserved',
                                    'bg-red-200 text-red-800':
                                        localTable.status === 'occupied',
                                }"
                                :aria-label="
                                    'Table status: ' + localTable.status
                                "
                            >
                                {{
                                    localTable.status.charAt(0).toUpperCase() +
                                    localTable.status.slice(1)
                                }}
                            </span>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium mb-1"
                                for="customer-name-input"
                            >
                                Customer Name
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="customer-name-input"
                                v-model="localTable.customer"
                                type="text"
                                class="w-full border rounded px-2 py-1 focus:ring-2 focus:ring-indigo-500"
                                placeholder="Enter customer name"
                                aria-label="Customer Name"
                                :class="{ 'border-red-500': customerNameError }"
                                @blur="validateCustomerName"
                            />
                            <div
                                v-if="customerNameError"
                                class="text-xs text-red-500 mt-1"
                            >
                                Customer name is required.
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <label
                                class="block text-sm font-medium mb-1"
                                for="pax-input"
                                >Pax</label
                            >
                            <button
                                type="button"
                                class="px-2 py-1 bg-gray-200 rounded"
                                @click="decrementPax"
                                tabindex="-1"
                            >
                                -
                            </button>
                            <input
                                id="pax-input"
                                v-model.number="localTable.pax"
                                type="number"
                                min="1"
                                class="w-16 border rounded px-2 py-1 focus:ring-2 focus:ring-indigo-500 text-center"
                                @focus="showPaxButtons = true"
                                @blur="hidePaxButtons"
                            />
                            <button
                                type="button"
                                class="px-2 py-1 bg-gray-200 rounded"
                                @click="incrementPax"
                                tabindex="-1"
                            >
                                +
                            </button>
                        </div>
                        <div
                            v-if="showPaxButtons"
                            class="grid grid-cols-3 gap-1 mt-2 h-10"
                        >
                            <button
                                type="button"
                                class="px-2 py-1 bg-indigo-100 rounded hover:bg-indigo-200"
                                @mousedown.prevent="setPax(2)"
                            >
                                2
                            </button>
                            <button
                                type="button"
                                class="px-2 py-1 bg-indigo-100 rounded hover:bg-indigo-200"
                                @mousedown.prevent="setPax(4)"
                            >
                                4
                            </button>
                            <button
                                type="button"
                                class="px-2 py-1 bg-indigo-100 rounded hover:bg-indigo-200"
                                @mousedown.prevent="setPax(6)"
                            >
                                6
                            </button>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button
                                type="submit"
                                class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700 focus:ring-2 focus:ring-indigo-500 text-xs"
                                :disabled="
                                    customerNameError ||
                                    !localTable.customer.trim()
                                "
                            >
                                Save
                            </button>
                            <button
                                type="button"
                                class="px-3 py-1 rounded bg-gray-300 hover:bg-gray-400 text-xs"
                                @click="cancelEdit"
                            >
                                Cancel
                            </button>
                        </div>
                        <button
                            type="button"
                            class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 bg-gray-100 rounded-full p-1 focus:outline-none"
                            @click="cancelEdit"
                            aria-label="Close modal"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, defineProps, defineEmits } from "vue";
const props = defineProps({
    value: Object,
});
const emit = defineEmits(["save"]);
const localTable = ref({ ...props.value });
const showPaxButtons = ref(false);
const customerNameError = ref(false);
const showModal = ref(false);

watch(
    () => props.value,
    (val) => {
        localTable.value = { ...val };
    }
);

function validateCustomerName() {
    customerNameError.value = !localTable.value.customer?.trim();
}
function incrementPax() {
    localTable.value.pax = (localTable.value.pax || 1) + 1;
}
function decrementPax() {
    if ((localTable.value.pax || 1) > 1) localTable.value.pax--;
}
function setPax(val: number) {
    localTable.value.pax = val;
    showPaxButtons.value = false;
}
function hidePaxButtons() {
    setTimeout(() => (showPaxButtons.value = false), 100);
}
function onSave() {
    validateCustomerName();
    if (!customerNameError.value && localTable.value.customer?.trim()) {
        emit("save", { ...localTable.value });
        showModal.value = false;
    }
}
function cancelEdit() {
    localTable.value = { ...props.value };
    showModal.value = false;
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
