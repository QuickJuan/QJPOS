<template>
    <Dialog
        :visible="props.visible"
        modal
        header="Select Order Type"
        :style="{ width: '20rem' }"
        @update:visible="handleClose"
    >
        <div class="space-y-3">
            <button
                v-for="type in orderTypes"
                :key="type.value"
                @click="selectType(type.value)"
                :class="[
                    'w-full py-3 px-4 rounded-lg font-medium transition-colors flex items-center gap-3',
                    selectedOrderType === type.value
                        ? 'bg-primary text-white'
                        : 'bg-gray-100 text-secondary-700 hover:bg-gray-200',
                ]"
            >
                <component :is="type.icon" class="w-5 h-5" />
                <div class="text-left">
                    <div class="font-semibold">{{ type.label }}</div>
                    <div class="text-xs opacity-75">
                        {{ type.description }}
                    </div>
                </div>
                <CheckIcon
                    v-if="selectedOrderType === type.value"
                    class="w-5 h-5 ml-auto"
                />
            </button>
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { Dialog } from "primevue";
import {
    HomeIcon,
    ShoppingBagIcon,
    TruckIcon,
    CheckIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps<{
    visible: boolean;
    selectedOrderType: string;
    orderTypes: any[];
}>();

const emit = defineEmits<{
    updateOrderType: [type: string];
    "update:visible": [value: boolean];
}>();

const selectType = (type: string) => {
    alert("selecting type: " + type);
    emit("updateOrderType", type);
    emit("update:visible", false);
};

const handleClose = () => {
    emit("update:visible", false);
};
</script>
