<template>
    <!-- Import the same POS interface as cashier, but with waiter mode -->
    <component
        :is="PosInterface"
        v-bind="$props"
        :is-waiter-mode="true"
    />
</template>

<script setup>
import { defineAsyncComponent } from 'vue';

// Lazy load the POS interface from Resto
const PosInterface = defineAsyncComponent(() =>
    import('@/Pages/Resto/Index.vue')
);

// Accept all the same props as Resto/Index
const props = defineProps({
    categories: Array,
    currentTable: Object,
    selectedCategorySlug: String,
    products: Array,
    categoryName: String,
    tableId: [String, Number],
    orderType: String,
    isWaiterMode: {
        type: Boolean,
        default: true
    }
});
</script>

const tableNumber = ref("");
const loading = ref(true);

onMounted(async () => {
    await fetchTableInfo();
});

const fetchTableInfo = async () => {
    try {
        loading.value = true;
        const response = await axios.get(
            route("api.tables.table-with-cart", props.tableId)
        );
        const table = response.data.data;
        tableNumber.value = table.table_number;
    } catch (error) {
        console.error("Failed to fetch table info:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to load table information",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};
</script>
