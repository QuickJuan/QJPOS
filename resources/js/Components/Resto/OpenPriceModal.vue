<template>
    <Dialog
        :visible="modelValue"
        modal
        header="Set Price"
        class="w-full max-w-lg"
        @update:visible="(value: boolean) => emit('update:modelValue', value)"
    >
        <div class="space-y-4">
            <div>
                <p class="text-sm text-slate-600">
                    {{ product?.name || "Open Price Item" }}
                </p>
                <p class="text-xs text-slate-500">
                    Enter the selling price and request a manager OTP approval.
                </p>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-semibold text-slate-700"
                    >Price</label
                >
                <InputNumber
                    v-model="form.price"
                    mode="currency"
                    currency="PHP"
                    locale="en-PH"
                    :min="0"
                    class="w-full"
                    input-class="w-full"
                    :step="1"
                />
                <p v-if="form.price <= 0" class="text-xs text-rose-600">
                    Price must be greater than zero.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-700"
                        >Approver Email</label
                    >
                    <InputText
                        v-model="form.approverEmail"
                        placeholder="manager@branch.com"
                        class="w-full"
                    />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-700"
                        >Approver OTP</label
                    >
                    <InputText
                        v-model="form.otpCode"
                        placeholder="6-digit code"
                        maxlength="6"
                        class="w-full"
                    />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50"
                    @click="emit('update:modelValue', false)"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg bg-primary text-white font-semibold hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="!isValid"
                    @click="handleConfirm"
                >
                    Confirm Price
                </button>
            </div>
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { computed, reactive, watch } from "vue";
import Dialog from "primevue/dialog";
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";

const props = defineProps<{
    modelValue: boolean;
    product?: any;
    defaultPrice?: number;
}>();

const emit = defineEmits<{
    "update:modelValue": [value: boolean];
    confirm: [
        payload: { price: number; approverEmail: string; otpCode: string },
    ];
}>();

const form = reactive({
    price: props.defaultPrice ?? 0,
    approverEmail: "",
    otpCode: "",
});

watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            form.price =
                props.defaultPrice ?? Number(props.product?.price ?? 0) ?? 0;
            form.approverEmail = "";
            form.otpCode = "";
        }
    },
);

const isValid = computed(
    () => form.price > 0 && !!form.approverEmail && form.otpCode.length >= 6,
);

const handleConfirm = () => {
    if (!isValid.value) return;

    emit("confirm", {
        price: Number(form.price),
        approverEmail: form.approverEmail,
        otpCode: form.otpCode,
    });
};
</script>
