<template>
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">
            {{ content.title }}
        </h2>

        <!-- Success state -->
        <div v-if="submitted" class="flex flex-col items-start gap-3 py-8">
            <div class="flex items-center gap-3">
                <svg
                    class="w-8 h-8 text-green-500 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <p class="text-lg font-semibold text-green-700">
                    {{
                        content.success_message ||
                        "Thank you! We will get back to you soon."
                    }}
                </p>
            </div>
            <button
                @click="resetForm"
                class="text-sm text-gray-500 underline hover:text-gray-700"
            >
                Send another message
            </button>
        </div>

        <form v-else @submit.prevent="submitForm" class="max-w-2xl space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <template v-for="field in fields" :key="field.key">
                    <div
                        :class="
                            field.type === 'textarea' ? 'md:col-span-2' : ''
                        "
                    >
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            {{ field.label }}
                            <span v-if="field.required" class="text-red-500"
                                >*</span
                            >
                        </label>

                        <input
                            v-if="
                                field.type === 'text' ||
                                field.type === 'email' ||
                                field.type === 'phone'
                            "
                            v-model="form[field.key]"
                            :type="inputType(field.type)"
                            :required="field.required"
                            :placeholder="field.placeholder || ''"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-600"
                        />

                        <textarea
                            v-else-if="field.type === 'textarea'"
                            v-model="form[field.key]"
                            :rows="field.rows || 4"
                            :required="field.required"
                            :placeholder="field.placeholder || ''"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-600"
                        ></textarea>

                        <select
                            v-else-if="field.type === 'select'"
                            v-model="form[field.key]"
                            :required="field.required"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-600"
                        >
                            <option value="" disabled>Select an option</option>
                            <option
                                v-for="option in field.options || []"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </select>

                        <input
                            v-else
                            v-model="form[field.key]"
                            type="text"
                            :required="field.required"
                            :placeholder="field.placeholder || ''"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-600"
                        />
                    </div>
                </template>
            </div>

            <p v-if="errorMessage" class="text-sm text-red-600">
                {{ errorMessage }}
            </p>

            <button
                type="submit"
                :disabled="submitting"
                class="flex items-center gap-2 bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-orange-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
            >
                <svg
                    v-if="submitting"
                    class="w-4 h-4 animate-spin"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    />
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8v8z"
                    />
                </svg>
                {{
                    submitting
                        ? "Sending…"
                        : content.button_text || "Send Message"
                }}
            </button>

            <p v-if="recaptchaEnabled" class="text-xs text-gray-400">
                Protected by reCAPTCHA.
                <a
                    href="https://policies.google.com/privacy"
                    target="_blank"
                    class="underline"
                    >Privacy</a
                >
                &amp;
                <a
                    href="https://policies.google.com/terms"
                    target="_blank"
                    class="underline"
                    >Terms</a
                >
                apply.
            </p>
        </form>
    </div>
</template>

<script setup>
import {
    ref,
    reactive,
    computed,
    watch,
    onMounted,
    onBeforeUnmount,
} from "vue";
import axios from "axios";

const props = defineProps({
    content: Object,
    settings: Object,
});

// ─── reCAPTCHA v3 ─────────────────────────────────────────────────────────
const RECAPTCHA_SITE_KEY = import.meta.env.VITE_RECAPTCHA_SITE_KEY || "";
const recaptchaEnabled = computed(() => !!RECAPTCHA_SITE_KEY);

let recaptchaScriptEl = null;

const loadRecaptchaScript = () => {
    if (!recaptchaEnabled.value || document.getElementById("recaptcha-script"))
        return;
    recaptchaScriptEl = document.createElement("script");
    recaptchaScriptEl.id = "recaptcha-script";
    recaptchaScriptEl.src = `https://www.google.com/recaptcha/api.js?render=${RECAPTCHA_SITE_KEY}`;
    recaptchaScriptEl.async = true;
    document.head.appendChild(recaptchaScriptEl);
};

const getRecaptchaToken = () => {
    return new Promise((resolve) => {
        if (!recaptchaEnabled.value || !window.grecaptcha) {
            resolve(null);
            return;
        }
        window.grecaptcha.ready(() => {
            window.grecaptcha
                .execute(RECAPTCHA_SITE_KEY, { action: "contact_form" })
                .then(resolve)
                .catch(() => resolve(null));
        });
    });
};

onMounted(loadRecaptchaScript);

// ─── Form fields ───────────────────────────────────────────────────────────
const slugify = (value) =>
    value
        .toString()
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, "_")
        .replace(/^_+|_+$/g, "");

const fields = computed(() => {
    const provided = props.content?.fields;
    if (Array.isArray(provided) && provided.length) {
        return provided.map((field, index) => ({
            label: field.label || "Field",
            type: field.type || "text",
            required: !!field.required,
            placeholder: field.placeholder || "",
            options: field.options || [],
            rows: field.rows,
            key: field.key || slugify(field.label || `field_${index}`),
        }));
    }
    return [
        { key: "name", label: "Name", type: "text", required: true },
        { key: "email", label: "Email", type: "email", required: true },
        { key: "phone", label: "Phone", type: "phone", required: false },
        { key: "message", label: "Message", type: "textarea", required: true },
    ];
});

const form = reactive({});

const initializeForm = () => {
    fields.value.forEach((field) => {
        if (!(field.key in form)) form[field.key] = "";
    });
};

initializeForm();
watch(
    () => fields.value,
    () => initializeForm(),
    { deep: true },
);

const inputType = (type) => {
    if (type === "email") return "email";
    if (type === "phone") return "tel";
    return "text";
};

// ─── Submission ────────────────────────────────────────────────────────────
const submitted = ref(false);
const submitting = ref(false);
const errorMessage = ref("");

const resetForm = () => {
    submitted.value = false;
    Object.keys(form).forEach((k) => (form[k] = ""));
};

const submitForm = async () => {
    submitting.value = true;
    errorMessage.value = "";

    try {
        const recaptchaToken = await getRecaptchaToken();

        const byType = (type) => fields.value.find((f) => f.type === type)?.key;
        const nameKey =
            fields.value.find((f) => f.key === "name")?.key ??
            fields.value.find((f) => f.type === "text")?.key;
        const emailKey = byType("email");
        const phoneKey = byType("phone");
        const msgKey = byType("textarea");

        const payload = {
            name: (nameKey ? form[nameKey] : null) ?? "",
            email: (emailKey ? form[emailKey] : null) ?? "",
            phone: (phoneKey ? form[phoneKey] : null) ?? "",
            message: (msgKey ? form[msgKey] : null) ?? "",
            fields: { ...form },
            ...(recaptchaToken ? { recaptcha_token: recaptchaToken } : {}),
        };

        await axios.post("/contact-form", payload);
        submitted.value = true;
    } catch (error) {
        const serverMsg = error?.response?.data?.message;
        const validationErrors = error?.response?.data?.errors;
        if (validationErrors) {
            errorMessage.value = Object.values(validationErrors)
                .flat()
                .join(" ");
        } else {
            errorMessage.value =
                serverMsg || "Something went wrong. Please try again.";
        }
    } finally {
        submitting.value = false;
    }
};
</script>
