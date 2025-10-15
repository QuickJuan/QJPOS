<template>
    <Dialog
        v-model:visible="props.showProductOptionsDialog"
        modal
        :header="`Customize ${props.selectedProduct?.name || ''}`"
        :style="{ width: '80rem' }"
        class="bg-white"
    >
        <div class="bg-white">
            <TabView v-if="props.selectedProduct?.options" class="p-2">
                <TabPanel
                    v-for="option in props.selectedProduct.options"
                    :key="option.id"
                    :header="option.name"
                    :value="option.id"
                >
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                        <div
                            v-for="item in option.option_items"
                            :key="item.id"
                            :class="[
                                'relative border-2 rounded-xl p-4 cursor-pointer transition-all duration-200 hover:shadow-lg',
                                selectedOptions[option.id]?.id === item.id
                                    ? 'border-indigo-500 bg-indigo-50 shadow-md'
                                    : 'border-gray-200 hover:border-indigo-300'
                            ]"
                            @click="selectedOptions[option.id] = item"
                        >
                            <!-- Selection Indicator -->
                            <div
                                v-if="selectedOptions[option.id]?.id === item.id"
                                class="absolute top-2 right-2 w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center"
                            >
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>

                            <!-- Option Image -->
                            <div class="h-24 flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg mb-3">
                                <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Option Details -->
                            <div class="text-center">
                                <h5 class="font-semibold text-gray-900 mb-1">{{ item.name }}</h5>
                                <div
                                    v-if="item.price && parseFloat(item.price) > 0"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
                                >
                                    +₱{{ parseFloat(item.price).toFixed(2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </TabPanel>
            </TabView>

            <div
                class="mt-6 bg-slate-50 rounded-lg p-4 border border-slate-200"
            >
                <div class="flex justify-between items-center">
                    <span class="text-slate-600 font-medium">Base Price:</span>
                    <span class="text-slate-800">
                        ${{
                            parseFloat(
                                props.selectedProduct?.average_cost || "0"
                            ).toFixed(2)
                        }}
                    </span>
                </div>
                <div
                    v-if="Object.keys(selectedOptions).length > 0"
                    class="mt-2 space-y-1"
                >
                    <div
                        v-for="(optionItem, optionId) in selectedOptions"
                        :key="optionId"
                        class="flex justify-between items-center text-sm"
                    >
                        <span class="text-slate-600">
                            {{ optionItem?.name }}
                        </span>
                        <span class="text-green-600">
                            +${{
                                parseFloat(optionItem?.price || "0").toFixed(2)
                            }}
                        </span>
                    </div>
                </div>
                <div
                    class="flex justify-between items-center pt-3 mt-3 border-t border-slate-300"
                >
                    <span class="text-lg font-bold text-slate-800">Total:</span>
                    <span class="text-xl font-bold text-indigo-600">
                        ${{ calculateOptionTotal().toFixed(2) }}
                    </span>
                </div>
            </div>
        </div>

        <template #footer>
            <div
                class="flex justify-end gap-3 bg-slate-50 px-6 py-4 border-t border-slate-200"
            >
                <Button
                    type="button"
                    label="Cancel"
                    severity="secondary"
                    @click="emit('closeModal')"
                    class="px-6 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:border-slate-400 rounded-lg font-medium"
                />
                <Button
                    type="button"
                    label="Add to Order"
                    @click="handleConfirmAddToCart"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium shadow-sm"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { Button, Dialog, TabView, TabPanel } from "primevue";

const props = defineProps<{
    showProductOptionsDialog: boolean;
    selectedProduct: any;
    selectedOptions: Record<string, any>;
}>();

const emit = defineEmits<{
    confirmAddToCart: [];
    closeModal: [];
}>();

const calculateOptionTotal = () => {
    if (!props.selectedProduct) return 0;

    let total = parseFloat(props.selectedProduct.average_cost || "0");

    Object.values(props.selectedOptions).forEach((optionItem: any) => {
        if (optionItem && optionItem.price) {
            total += parseFloat(optionItem.price);
        }
    });

    return total;
};

const handleConfirmAddToCart = () => {
    emit("confirmAddToCart");
};
</script>
