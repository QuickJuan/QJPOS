<template>
    <aside
        class="col-span-1 lg:col-span-4 bg-white rounded-xl shadow-lg p-6 flex flex-col lg:sticky lg:top-6 border border-slate-200"
    >
        <div class="flex items-start justify-between mb-6">
            <div>
                <div
                    class="text-xl font-bold text-slate-800 flex items-center gap-3"
                >
                    <div
                        class="w-3 h-3 bg-green-500 rounded-full animate-pulse"
                    />
                    Order Summary
                </div>
                <div class="text-sm text-slate-600 mt-1">
                    Table 1 • 2 guests
                </div>
            </div>
            <div
                class="text-sm text-slate-500 font-medium bg-slate-100 px-3 py-1 rounded-full"
            >
                #Shift:12
            </div>
        </div>

        <div
            class="flex-1 overflow-auto border border-slate-200 rounded-lg p-4 mb-6 max-h-[50vh] bg-slate-50"
        >
            <div v-if="tab === 'lines'">
                <OrderItemList
                    :order-items="props.orderItems"
                    @edit="handleEdit"
                    @delete="handleDelete"
                    @selection-change="handleSelectionChange"
                />
            </div>
            <div
                v-else
                class="flex items-center justify-center h-40 text-slate-400 font-medium"
            >
                No payments view
            </div>
        </div>

        <div class="border-t border-slate-200 pt-6 mt-4 space-y-4">
            <div class="flex justify-between items-center">
                <div class="text-sm text-slate-600 font-medium">Subtotal</div>
                <div class="font-semibold text-slate-800">
                    {{ formatMoney(subtotal.toFixed(2)) }}
                </div>
            </div>
            <div class="flex justify-between items-center">
                <div class="text-sm text-slate-600 font-medium">
                    Tax (6.25%)
                </div>
                <div class="font-semibold text-slate-800">
                    {{ formatMoney(tax.toFixed(2)) }}
                </div>
            </div>
            <div
                class="flex justify-between items-center text-xl font-bold text-slate-900 border-t border-slate-200 pt-4"
            >
                <div>Total</div>
                <div>{{ formatMoney(total.toFixed(2)) }}</div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-6">
                <button
                    class="py-3 px-4 bg-slate-100 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-200 hover:shadow-md transition-all"
                >
                    Place to table
                </button>
                <button
                    class="py-3 px-4 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 hover:shadow-md transition-all"
                >
                    Proceed to checkout
                </button>
            </div>
        </div>
    </aside>

    <!-- Edit Dialog -->
    <Dialog
        v-model:visible="visible"
        modal
        :header="`Edit ${selectedOrderItem?.name || ''}`"
        :style="{ width: '25rem' }"
    >
        <div class="flex flex-col gap-4 mb-4">
            <label for="username" class="font-semibold w-24">Quantity</label>
            <InputNumber
                v-model="selectedOrderItem.quantity"
                showButtons
                buttonLayout="horizontal"
                style="width: 1rem"
                :min="0"
                :max="99"
            >
                <template #incrementicon>
                    <span class="pi pi-plus" />
                </template>
                <template #decrementicon>
                    <span class="pi pi-minus" />
                </template>
            </InputNumber>
        </div>
        <div class="flex justify-end gap-2">
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                @click="visible = false"
            />
            <Button type="button" label="Save" @click="saveEdit" />
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import OrderItemList from "../OrderItemList.vue";
import { Button, Dialog, InputNumber, useConfirm, useToast } from "primevue";
import { formatMoney } from "@/Utils/FormatMoney";
import PageProps from "@/Types/PageProps";

const props = defineProps<{
    orderItems: any[];
    selectedOrderItem: any;
}>();

const toast = useToast();
const page = usePage<PageProps>();
const confirm = useConfirm();
const visible = ref(false);
const tab = ref<"lines" | "payments">("lines");

const subtotal = computed(() =>
    props.orderItems.reduce(
        (sum, item) =>
            sum +
            parseFloat(item.price || item.average_cost || "0") * item.quantity,
        0
    )
);

const tax = computed(() => subtotal.value * 0.0625);
const total = computed(() => subtotal.value + tax.value);
const selectedOrderItem = ref(props.selectedOrderItem);

const handleEdit = (orderItem: any) => {
    visible.value = true;
    selectedOrderItem.value = orderItem;
};

const handleDelete = (orderItem: any) => {
    confirm.require({
        message: "Are you sure you want to remove this?",
        icon: "pi pi-exclamation-triangle",
        rejectProps: {
            label: "Cancel",
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: "Remove",
        },
        accept: () => {
            router.delete(route("retail-cashier.cart.delete", orderItem.id), {
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: page.props.flash.success,
                        life: 3000,
                    });
                },
                onError: (errors) => {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: page.props.flash.error,
                        life: 3000,
                    });
                },
            });
        },
    });
};

const handleSelectionChange = (selectedItems: any[]) => {
    // TODO: Implement selection change handling
    console.log("Selected items:", selectedItems);
};

const saveEdit = () => {
    if (selectedOrderItem.value) {
        console.log(selectedOrderItem.value);

        router.put(
            route("retail-cashier.cart.update", selectedOrderItem.value.id),
            {
                quantity: selectedOrderItem.value.quantity,
                selected_options:
                    selectedOrderItem.value.selected_options || [],
            },
            {
                onSuccess: () => {
                    visible.value = false;

                    toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: page.props.flash.success,
                        life: 3000,
                    });
                },
                onError: (errors) => {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: page.props.flash.error,
                        life: 3000,
                    });
                },
            }
        );
    }
};
</script>
