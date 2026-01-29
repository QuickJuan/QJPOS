<template>
    <div class="min-h-screen bg-neutral-50">
        <div
            class="mx-auto max-w-6xl min-h-screen flex flex-col md:flex-row overflow-hidden rounded-3xl shadow-2xl bg-white"
        >
            <!-- Banner -->
            <div
                class="relative hidden md:flex md:w-1/2 bg-gradient-to-br from-primary-600 to-primary-800"
            >
                <img
                    src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&q=80"
                    alt="Waiter serving food"
                    class="w-full h-full object-cover opacity-80"
                    loading="lazy"
                />
                <div class="absolute inset-0 bg-primary-900/60" />
                <div
                    class="absolute inset-0 flex items-center justify-center p-10 text-white"
                >
                    <div class="space-y-5 text-center">
                        <div
                            class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 border border-white/20"
                        >
                            <UsersGroupIcon class="w-8 h-8" />
                        </div>
                        <div class="space-y-2">
                            <h2 class="text-3xl font-semibold">Waiter Login</h2>
                            <p class="text-sm text-white/80">
                                Sign in to take orders
                            </p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-2xl font-semibold">
                                Serve faster. Smile more.
                            </h3>
                            <p class="text-sm text-white/80">
                                Quick, secure access for your waitstaff to start
                                taking orders without friction.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Column -->
            <div
                class="flex-1 flex items-center justify-center p-6 md:p-10 overflow-y-auto"
            >
                <div class="w-full max-w-xl">
                    <!-- Mobile Header -->
                    <div class="md:hidden space-y-3 mb-6 text-center">
                        <div class="flex justify-center">
                            <div
                                class="w-12 h-12 rounded-2xl bg-primary-100 text-primary-700 flex items-center justify-center"
                            >
                                <UsersGroupIcon class="w-7 h-7" />
                            </div>
                        </div>
                        <div class="flex flex-col items-center space-y-2">
                            <div
                                v-if="companyLogo"
                                class="h-10 flex items-center"
                            >
                                <img
                                    :src="companyLogo"
                                    alt="Company logo"
                                    class="max-h-10 object-contain"
                                    loading="lazy"
                                />
                            </div>
                            <h2 class="text-2xl font-bold text-neutral-900">
                                Waiter Login
                            </h2>
                            <p class="text-neutral-600">
                                Sign in to take orders
                            </p>
                        </div>
                    </div>

                    <!-- Desktop Logo -->
                    <div
                        v-if="companyLogo"
                        class="hidden md:flex justify-center"
                    >
                        <img
                            :src="companyLogo"
                            alt="Company logo"
                            class="h-10 object-contain"
                            loading="lazy"
                        />
                    </div>

                    <div class="p-0">
                        <form @submit.prevent="handleSubmit">
                            <div class="grid grid-cols-1 gap-6 md:gap-6">
                                <div class="space-y-5 md:order-1 order-1">
                                    <div class="space-y-2">
                                        <SelectField
                                            id="branch"
                                            v-model="form.branch"
                                            label="Select Branch"
                                            :options="props.branches"
                                            optionLabel="name"
                                            optionValue="id"
                                            placeholder="Choose your branch"
                                            required
                                            searchable
                                            :error="form.errors.branch"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <TextField
                                            id="identifier"
                                            v-model="form.identifier"
                                            label="Username or Email"
                                            type="text"
                                            placeholder="Enter your username or email"
                                            :error="form.errors.identifier"
                                            :disabled="!form.branch"
                                            required
                                        />
                                    </div>
                                </div>

                                <div class="space-y-5 md:order-2 order-2">
                                    <div class="space-y-3">
                                        <label
                                            class="block text-sm font-medium text-neutral-700 text-center"
                                        >
                                            {{ codeLabel }}
                                        </label>
                                        <div class="flex justify-center">
                                            <InputOtp
                                                v-model="otpValue"
                                                :length="resolvedCodeLength"
                                                integerOnly
                                                mask
                                                :disabled="codeEntryDisabled"
                                                @complete="handleCodeComplete"
                                            >
                                                <template
                                                    #default="{ attrs, events }"
                                                >
                                                    <input
                                                        type="password"
                                                        inputmode="numeric"
                                                        v-bind="attrs"
                                                        v-on="events"
                                                        class="w-10 h-12 md:w-12 md:h-14 text-center text-xl md:text-2xl font-bold border-2 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all disabled:bg-neutral-100 disabled:cursor-not-allowed"
                                                        :class="{
                                                            'border-red-500':
                                                                codeError,
                                                            'border-neutral-300':
                                                                !codeError,
                                                        }"
                                                    />
                                                </template>
                                            </InputOtp>
                                        </div>
                                        <p
                                            v-if="codeError"
                                            class="text-sm text-red-600 mt-1 text-center"
                                        >
                                            {{ codeError }}
                                        </p>
                                    </div>

                                    <div
                                        class="grid grid-cols-3 gap-2 justify-center max-w-xs mx-auto md:max-w-full"
                                    >
                                        <button
                                            v-for="num in [
                                                1, 2, 3, 4, 5, 6, 7, 8, 9,
                                            ]"
                                            :key="num"
                                            type="button"
                                            @click="addDigit(num.toString())"
                                            class="h-10 md:h-12 text-lg md:text-xl font-semibold rounded-lg bg-neutral-100 hover:bg-neutral-200 active:bg-neutral-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            :disabled="codeEntryDisabled"
                                        >
                                            {{ num }}
                                        </button>
                                        <button
                                            type="button"
                                            @click="clearOtp"
                                            class="h-10 md:h-12 text-base md:text-lg font-semibold rounded-lg bg-red-100 hover:bg-red-200 active:bg-red-300 text-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            :disabled="codeEntryDisabled"
                                        >
                                            Clear
                                        </button>
                                        <button
                                            type="button"
                                            @click="addDigit('0')"
                                            class="h-10 md:h-12 text-lg md:text-xl font-semibold rounded-lg bg-neutral-100 hover:bg-neutral-200 active:bg-neutral-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            :disabled="codeEntryDisabled"
                                        >
                                            0
                                        </button>
                                        <button
                                            type="button"
                                            @click="backspace"
                                            class="h-10 md:h-12 text-base md:text-lg font-semibold rounded-lg bg-neutral-100 hover:bg-neutral-200 active:bg-neutral-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            :disabled="codeEntryDisabled"
                                        >
                                            ⌫
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <PrimaryButton
                                    type="submit"
                                    :disabled="
                                        form.processing ||
                                        !form.branch ||
                                        !form.identifier ||
                                        otpValue.length !== resolvedCodeLength
                                    "
                                    :loading="form.processing"
                                    class="w-full py-3 text-base font-semibold"
                                >
                                    Login
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import SelectField from "@/Components/Form/SelectField.vue";
import TextField from "@/Components/Form/TextField.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputOtp from "primevue/inputotp";
import { useToast } from "primevue";
import UsersGroupIcon from "@/Components/Icons/UsersGroupIcon.vue";

const toast = useToast();
const page = usePage();

const props = defineProps({
    branches: {
        type: Array,
        default: () => [],
    },
    loginMethod: {
        type: String,
        default: "otp",
    },
    codeLength: {
        type: Number,
        default: 6,
    },
});

const resolvedLoginMethod = computed(() =>
    ["otp", "pincode"].includes(props.loginMethod) ? props.loginMethod : "otp",
);

const resolvedCodeLength = computed(
    () => props.codeLength || (resolvedLoginMethod.value === "pincode" ? 4 : 6),
);

const codeField = computed(() =>
    resolvedLoginMethod.value === "pincode" ? "pincode" : "otp",
);

const codeLabel = computed(() =>
    resolvedLoginMethod.value === "pincode"
        ? `Enter ${resolvedCodeLength.value}-Digit Pincode`
        : `Enter ${resolvedCodeLength.value}-Digit OTP`,
);

const otpValue = ref("");

const form = useForm({
    identifier: "",
    branch: props.branches.length === 1 ? props.branches[0].id : "",
    otp: "",
    pincode: "",
});

const companyName = computed(() => {
    return page.props?.company_info?.company_name || "QuickJuan";
});

const companyLogo = computed(() => {
    return (
        page.props?.company_info?.company_logo ||
        page.props?.company_info?.logo ||
        page.props?.company_info?.logo_url ||
        ""
    );
});

watch(otpValue, (newVal) => {
    form[codeField.value] = newVal;
    const opposite = codeField.value === "otp" ? "pincode" : "otp";
    form[opposite] = "";
});

const codeError = computed(() => form.errors[codeField.value]);
const codeEntryDisabled = computed(
    () => !form.identifier || !form.branch || form.processing,
);

const handleSubmit = () => {
    verifyOtp();
};

const verifyOtp = () => {
    form.post(route("waiter.verify-otp"), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            toast.add({
                severity: "success",
                summary: "Success",
                detail: "Login successful!",
                life: 3000,
            });
        },
        onError: (errors) => {
            console.error("Waiter login error:", errors);
            if (errors[codeField.value]) {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errors[codeField.value],
                    life: 3000,
                });
            }
            if (errors.identifier) {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errors.identifier,
                    life: 3000,
                });
            }
            if (errors.branch) {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errors.branch,
                    life: 3000,
                });
            }
            otpValue.value = "";
            form[codeField.value] = "";
        },
    });
};

const handleCodeComplete = () => {
    if (
        otpValue.value.length === resolvedCodeLength.value &&
        form.branch &&
        form.identifier
    ) {
        verifyOtp();
    }
};

const addDigit = (digit) => {
    if (otpValue.value.length < resolvedCodeLength.value) {
        otpValue.value += digit;
    }
};

const backspace = () => {
    otpValue.value = otpValue.value.slice(0, -1);
};

const clearOtp = () => {
    otpValue.value = "";
    form[codeField.value] = "";
    form.clearErrors(codeField.value);
};
</script>
