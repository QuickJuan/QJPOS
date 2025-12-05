import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useOrderStore = defineStore('order', () => {
    // State
    const selectedOrderType = ref<string | null>(null);
    const tableId = ref<number | null>(null);
    const locationType = ref<string | null>(null);
    const selectedOrderItem = ref<any>(null);

    // Order type options
    const orderTypes = [
        {
            value: 'dine-in',
            label: 'Dine-in',
            description: 'Customer will eat at the restaurant',
        },
        {
            value: 'takeout',
            label: 'Takeout',
            description: 'Customer will take food to go',
        },
        {
            value: 'delivery',
            label: 'Delivery',
            description: 'Food will be delivered to customer',
        },
    ];

    // Getters
    const getCurrentOrderType = computed(() => selectedOrderType.value);
    const getTableId = computed(() => tableId.value);
    const getLocationType = computed(() => locationType.value);
    const getSelectedOrderItem = computed(() => selectedOrderItem.value);

    const getOrderTypeLabel = computed(() => {
        const orderType = orderTypes.find(
            (type) => type.value === selectedOrderType.value
        );
        return orderType?.label || 'Not Selected';
    });

    // Actions
    function setOrderType(type: string) {
        selectedOrderType.value = type;
    }

    function setTableId(id: number | null) {
        tableId.value = id;
    }

    function setLocationType(type: string | null) {
        locationType.value = type;
    }

    function setSelectedOrderItem(item: any) {
        selectedOrderItem.value = item;
    }

    function initializeFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const urlTableId = params.get('tableId');
        const urlLocationType = params.get('locationType');

        if (urlTableId) {
            tableId.value = parseInt(urlTableId);
        }
        if (urlLocationType) {
            locationType.value = urlLocationType;
        }
    }

    function reset() {
        selectedOrderType.value = null;
        tableId.value = null;
        locationType.value = null;
        selectedOrderItem.value = null;
    }

    return {
        // State
        selectedOrderType,
        tableId,
        locationType,
        selectedOrderItem,
        orderTypes,

        // Getters
        getCurrentOrderType,
        getTableId,
        getLocationType,
        getSelectedOrderItem,
        getOrderTypeLabel,

        // Actions
        setOrderType,
        setTableId,
        setLocationType,
        setSelectedOrderItem,
        initializeFromUrl,
        reset,
    };
});
