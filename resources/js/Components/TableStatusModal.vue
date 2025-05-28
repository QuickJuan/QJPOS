<template>
    <transition name="modal">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
        >
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-xs">
                <h2 class="text-lg font-bold mb-4">
                    Table Status: {{ tableData.name }}
                </h2>
                <form @submit.prevent="onSave">
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1"
                            >Customer Name</label
                        >
                        <input
                            v-model="localData.customer"
                            type="text"
                            class="w-full border rounded px-2 py-1"
                            placeholder="Enter customer name"
                        />
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1"
                            >Status</label
                        >
                        <span
                            class="inline-block px-2 py-1 rounded text-xs font-semibold"
                            :class="{
                                'bg-green-200 text-green-800':
                                    localData.status === 'vacant',
                                'bg-yellow-200 text-yellow-800':
                                    localData.status === 'reserved',
                                'bg-red-200 text-red-800':
                                    localData.status === 'occupied',
                            }"
                        >
                            {{
                                localData.status.charAt(0).toUpperCase() +
                                localData.status.slice(1)
                            }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-4">
                        <button
                            v-if="localData.status === 'vacant'"
                            type="button"
                            @click="emitAction('occupy')"
                            class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600"
                        >
                            Occupy
                        </button>
                        <button
                            v-if="localData.status === 'vacant'"
                            type="button"
                            @click="emitAction('reserve')"
                            class="px-3 py-1 rounded bg-yellow-500 text-white hover:bg-yellow-600"
                        >
                            Reserve
                        </button>
                        <button
                            v-if="localData.status === 'reserved'"
                            type="button"
                            @click="emitAction('occupy')"
                            class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600"
                        >
                            Occupy
                        </button>
                        <button
                            v-if="localData.status === 'reserved'"
                            type="button"
                            @click="emitAction('vacant')"
                            class="px-3 py-1 rounded bg-green-500 text-white hover:bg-green-600"
                        >
                            Vacant
                        </button>
                        <button
                            v-if="localData.status === 'occupied'"
                            type="button"
                            @click="emitAction('vacant')"
                            class="px-3 py-1 rounded bg-green-500 text-white hover:bg-green-600"
                        >
                            Vacant
                        </button>
                        <button
                            v-if="localData.status === 'occupied'"
                            type="button"
                            @click="emitAction('checkout')"
                            class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700"
                        >
                            Checkout
                        </button>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button
                            type="button"
                            @click="$emit('close')"
                            class="px-3 py-1 rounded bg-gray-300 hover:bg-gray-400"
                        >
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { reactive, watch, toRefs } from "vue";
const props = defineProps({
    show: Boolean,
    tableData: {
        type: Object,
        required: true,
    },
});
const emit = defineEmits(["close", "save", "action"]);

const localData = reactive({
    status: props.tableData.status || "vacant",
    customer: props.tableData.customer || "",
    name: props.tableData.name || "",
});

watch(
    () => props.tableData,
    (val) => {
        localData.status = val.status || "vacant";
        localData.customer = val.customer || "";
        localData.name = val.name || "";
    }
);

function onSave() {
    emit("save", { ...localData });
}
function emitAction(action) {
    emit("action", action);
}
</script>
