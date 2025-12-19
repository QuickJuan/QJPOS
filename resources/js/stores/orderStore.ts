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
        const urlOrderType = params.get('orderType');

        if (urlTableId) {
            tableId.value = parseInt(urlTableId);
        }
        if (urlLocationType) {
            locationType.value = urlLocationType;
        }
        if (urlOrderType) {
            selectedOrderType.value = urlOrderType;
        } else {
            // Default to dine-in if not specified
            selectedOrderType.value = 'dine-in';
        }
    }

    function updateUrlParams() {
        const params = new URLSearchParams(window.location.search);

        if (selectedOrderType.value) {
            params.set('orderType', selectedOrderType.value);
        }
        if (tableId.value) {
            params.set('tableId', tableId.value.toString());
        }

        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.replaceState({}, '', newUrl);
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
        updateUrlParams,
        reset,
    };
});
