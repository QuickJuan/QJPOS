<template>
    <TransitionRoot :show="show" as="template">
        <Dialog as="div" class="relative z-50" @close="closeModal">
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all"
                        >
                            <DialogTitle
                                as="h3"
                                class="text-lg font-medium leading-6 text-gray-900 mb-4"
                            >
                                Add New Customer
                            </DialogTitle>

                            <form
                                @submit.prevent="submitForm"
                                class="space-y-4"
                            >
                                <!-- Customer Name -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Customer Name
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.customer_name"
                                        type="text"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                        :class="{
                                            'border-red-500':
                                                errors.customer_name,
                                        }"
                                    />
                                    <p
                                        v-if="errors.customer_name"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.customer_name }}
                                    </p>
                                </div>

                                <!-- Contact Number -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Contact Number
                                    </label>
                                    <input
                                        v-model="form.contact_no"
                                        type="tel"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                        :class="{
                                            'border-red-500': errors.contact_no,
                                        }"
                                    />
                                    <p
                                        v-if="errors.contact_no"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.contact_no }}
                                    </p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Email Address
                                    </label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                        :class="{
                                            'border-red-500': errors.email,
                                        }"
                                    />
                                    <p
                                        v-if="errors.email"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.email }}
                                    </p>
                                </div>

                                <!-- Birth Date -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Birth Date
                                    </label>
                                    <input
                                        v-model="form.birth_date"
                                        type="date"
                                        :max="today"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                    />
                                </div>

                                <!-- Customer Type -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Customer Type
                                    </label>
                                    <select
                                        v-model="form.type"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                    >
                                        <option value="regular">Regular</option>
                                        <option value="vip">VIP</option>
                                    </select>
                                </div>

                                <!-- Preferences -->
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2">
                                        <input
                                            v-model="form.email_subscribe"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-primary focus:ring-primary"
                                        />
                                        <span class="text-sm text-gray-700"
                                            >Subscribe to Email</span
                                        >
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            v-model="form.sms_subscribe"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-primary focus:ring-primary"
                                        />
                                        <span class="text-sm text-gray-700"
                                            >Subscribe to SMS</span
                                        >
                                    </label>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-3 mt-6">
                                    <button
                                        type="submit"
                                        :disabled="processing"
                                        class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 transition-colors disabled:opacity-50"
                                    >
                                        {{
                                            processing
                                                ? "Saving..."
                                                : "Save Customer"
                                        }}
                                    </button>
                                    <button
                                        type="button"
                                        @click="closeModal"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from "@headlessui/vue";

interface Props {
    modelValue: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: "update:modelValue", value: boolean): void;
    (e: "customer-added", customer: any): void;
}>();

const show = computed({
    get: () => props.modelValue,
    set: (value) => emit("update:modelValue", value),
});

const form = ref({
    customer_name: "",
    contact_no: "",
    email: "",
    birth_date: "",
    type: "regular",
    email_subscribe: false,
    sms_subscribe: false,
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const today = computed(() => {
    return new Date().toISOString().split("T")[0];
});

const submitForm = () => {
    processing.value = true;
    errors.value = {};

    router.post(route("customers.store"), form.value, {
        preserveScroll: true,
        onSuccess: (page) => {
            const customer = page.props.customer;
            emit("customer-added", customer);
            closeModal();
            resetForm();
        },
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const closeModal = () => {
    show.value = false;
    resetForm();
};

const resetForm = () => {
    form.value = {
        customer_name: "",
        contact_no: "",
        email: "",
        birth_date: "",
        type: "regular",
        email_subscribe: false,
        sms_subscribe: false,
    };
    errors.value = {};
};
</script>
