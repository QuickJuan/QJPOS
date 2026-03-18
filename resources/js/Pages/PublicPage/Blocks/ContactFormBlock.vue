<template>
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">
            {{ content.title }}
        </h2>
        <form @submit.prevent="submitForm" class="max-w-2xl space-y-6">
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
                            rows="field.rows || 4"
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
            <button
                type="submit"
                class="bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-orange-700 transition-colors"
            >
                {{ content.button_text || "Send Message" }}
            </button>
            <p v-if="message" class="text-sm" :class="messageClass">
                {{ message }}
            </p>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import axios from "axios";

const props = defineProps({
    content: Object,
    settings: Object,
});

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
        return provided.map((field, index) => {
            const key = field.key || slugify(field.label || `field_${index}`);
            return {
                label: field.label || "Field",
                type: field.type || "text",
                required: !!field.required,
                placeholder: field.placeholder || "",
                options: field.options || [],
                rows: field.rows,
                key,
            };
        });
    }

    // Fallback defaults
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
        if (!(field.key in form)) {
            form[field.key] = "";
        }
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

const message = ref("");
const messageClass = ref("");

const submitting = ref(false);

const submitForm = async () => {
    submitting.value = true;
    message.value = "";
    try {
        const payload = {
            name: form.name ?? "",
            email: form.email ?? "",
            phone: form.phone ?? "",
            message: form.message ?? "",
            fields: { ...form },
        };

        await axios.post("/contact-form", payload);
        message.value = "Thank you! We will get back to you soon.";
        messageClass.value = "text-green-600";
        Object.keys(form).forEach((key) => (form[key] = ""));
    } catch (error) {
        console.error(error);
        message.value = "Something went wrong. Please try again.";
        messageClass.value = "text-red-600";
    } finally {
        submitting.value = false;
    }
};
</script>
