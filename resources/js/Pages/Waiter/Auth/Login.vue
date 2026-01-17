<template>
    <div
        class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-primary-50 flex items-center justify-center p-4"
    >
        <div class="w-full max-w-5xl">
            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
                <!-- Header -->
                <div class="text-center space-y-3 mb-8">
                    <div class="flex justify-center mb-2">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl shadow-lg flex items-center justify-center"
                        >
                            <svg
                                class="w-9 h-9 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                />
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-3xl font-bold text-neutral-900">
                        Waiter Login
                    </h2>
                    <p class="text-neutral-600">Sign in to take orders</p>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="handleSubmit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                        <!-- Left Column: Branch & Email -->
                        <div class="space-y-5 md:order-1 order-1">
                            <!-- Branch Selection -->
                            <div class="space-y-2">
                                <SelectField
                                    id="branch"
                                    v-model="form.branch"
                                    label="Select Branch"
                                    :options="branches"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Choose your branch"
                                    required
                                    searchable
                                    :error="form.errors.branch"
                                />
                            </div>

                            <!-- Email Selection -->
                            <div class="space-y-2">
                                <SelectField
                                    id="email"
                                    v-model="form.email"
                                    label="Select Your Email"
                                    :options="branchUsers"
                                    optionLabel="email"
                                    optionValue="email"
                                    placeholder="Choose your email"
                                    required
                                    :error="form.errors.email"
                                    :disabled="!form.branch || loadingUsers"
                                />
                            </div>
                        </div>

                        <!-- Right Column: OTP & Keypad -->
                        <div class="space-y-5 md:order-2 order-2">
                            <!-- OTP Input -->
                            <div class="space-y-3">
                                <label
                                    class="block text-sm font-medium text-neutral-700 text-center"
                                >
                                    Enter 6-Digit OTP
                                </label>
                                <div class="flex justify-center">
                                    <InputOtp
                                        v-model="otpValue"
                                        :length="6"
                                        integerOnly
                                        :disabled="!form.email"
                                        @complete="handleOtpComplete"
                                    >
                                        <template
                                            #default="{ attrs, events, index }"
                                        >
                                            <input
                                                type="text"
                                                v-bind="attrs"
                                                v-on="events"
                                                class="w-10 h-12 md:w-12 md:h-14 text-center text-xl md:text-2xl font-bold border-2 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all disabled:bg-neutral-100 disabled:cursor-not-allowed"
                                                :class="{
                                                    'border-red-500':
                                                        form.errors.otp,
                                                    'border-neutral-300':
                                                        !form.errors.otp,
                                                }"
                                            />
                                        </template>
                                    </InputOtp>
                                </div>
                                <p
                                    v-if="form.errors.otp"
                                    class="text-sm text-red-600 mt-1 text-center"
                                >
                                    {{ form.errors.otp }}
                                </p>
                            </div>

                            <!-- Numeric Keypad (Hidden on Mobile) -->
                            <div class="hidden md:grid grid-cols-3 gap-2">
                                <button
                                    v-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]"
                                    :key="num"
                                    type="button"
                                    @click="addDigit(num.toString())"
                                    class="h-14 text-xl font-semibold rounded-lg bg-neutral-100 hover:bg-neutral-200 active:bg-neutral-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="form.processing || !form.email"
                                >
                                    {{ num }}
                                </button>
                                <button
                                    type="button"
                                    @click="clearOtp"
                                    class="h-14 text-lg font-semibold rounded-lg bg-red-100 hover:bg-red-200 active:bg-red-300 text-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="form.processing || !form.email"
                                >
                                    Clear
                                </button>
                                <button
                                    type="button"
                                    @click="addDigit('0')"
                                    class="h-14 text-xl font-semibold rounded-lg bg-neutral-100 hover:bg-neutral-200 active:bg-neutral-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="form.processing || !form.email"
                                >
                                    0
                                </button>
                                <button
                                    type="button"
                                    @click="backspace"
                                    class="h-14 text-lg font-semibold rounded-lg bg-neutral-100 hover:bg-neutral-200 active:bg-neutral-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="form.processing || !form.email"
                                >
                                    ⌫
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <PrimaryButton
                            type="submit"
                            :disabled="
                                form.processing ||
                                !form.branch ||
                                !form.email ||
                                otpValue.length !== 6
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
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import SelectField from "@/Components/Form/SelectField.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputOtp from "primevue/inputotp";
import { useToast } from "primevue";

const toast = useToast();
const page = usePage();

const branches = ref([]);
const branchUsers = ref([]);
const loadingUsers = ref(false);
const otpValue = ref("");

const form = useForm({
    email: "",
    branch: "",
    otp: "",
});

// Watch otpValue and sync with form.otp
watch(otpValue, (newVal) => {
    form.otp = newVal;
});

onMounted(async () => {
    // Fetch branches
    try {
        const response = await axios.get("/api/branches");
        branches.value = response.data.data || [];
    } catch (error) {
        console.error("Failed to fetch branches:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to load branches",
            life: 3000,
        });
    }
});

// Watch for branch selection to load users automatically
watch(
    () => form.branch,
    async (branchId) => {
        if (branchId) {
            await loadBranchUsers(branchId);
        } else {
            branchUsers.value = [];
            form.email = "";
        }
    }
);

const loadBranchUsers = async (branchId) => {
    try {
        loadingUsers.value = true;
        const response = await axios.get(`/api/branches/${branchId}/users`);
        branchUsers.value = response.data.data || [];
    } catch (error) {
        console.error("Failed to fetch branch users:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to load users for this branch",
            life: 3000,
        });
    } finally {
        loadingUsers.value = false;
    }
};

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
            console.error("OTP verify error:", errors);
            if (errors.otp) {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errors.otp,
                    life: 3000,
                });
            }
            if (errors.email) {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errors.email,
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
            form.otp = "";
        },
    });
};

const handleOtpComplete = () => {
    // Auto-submit when OTP is complete
    if (otpValue.value.length === 6 && form.branch && form.email) {
        verifyOtp();
    }
};

const addDigit = (digit) => {
    if (otpValue.value.length < 6) {
        otpValue.value += digit;
    }
};

const backspace = () => {
    otpValue.value = otpValue.value.slice(0, -1);
};

const clearOtp = () => {
    otpValue.value = "";
    form.otp = "";
    form.clearErrors("otp");
};
</script>
