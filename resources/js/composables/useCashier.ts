import { ref, computed, watch } from "vue";
import type { Ref } from "vue";

export interface CartItem {
    id: number;
    product_id: number;
    product_name: string;
    quantity: number;
    price: number;
    amount: number;
    order_type: string;
    table_id?: number | null;
    product_packaging_id?: number | null;
    selected_options?: any[];
    discount?: number;
    sub_total: number;
    is_served?: boolean;
    is_void?: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface Cart {
    id: number;
    table_id?: number | null;
    customer_id?: number | null;
    order_type: string;
    status: string;
    items: CartItem[];
    sub_total: number;
    total_amount: number;
    created_at?: string;
    updated_at?: string;
}

export interface CashierState {
    selectedCartId: number | null;
    openCarts: Cart[];
    cashierSessionId: number | null;
    tableId: string | null;
    locationType: string | null;
}

const CASHIER_STORAGE_KEY = "quickjuan_cashier_state";

// Initialize reactive state
const cashierState: Ref<CashierState> = ref({
    selectedCartId: null,
    openCarts: [],
    cashierSessionId: null,
    tableId: null,
    locationType: null,
});

// Load initial state from localStorage
const loadCashierState = (): CashierState => {
    try {
        const stored = localStorage.getItem(CASHIER_STORAGE_KEY);
        if (stored) {
            const parsed = JSON.parse(stored);
            return {
                selectedCartId: parsed.selectedCartId || null,
                openCarts: Array.isArray(parsed.openCarts) ? parsed.openCarts : [],
                cashierSessionId: parsed.cashierSessionId || null,
                tableId: parsed.tableId || null,
                locationType: parsed.locationType || null,
            };
        }
    } catch (error) {
        console.error("Failed to load cashier state from localStorage:", error);
    }
    return {
        selectedCartId: null,
        openCarts: [],
        cashierSessionId: null,
        tableId: null,
        locationType: null,
    };
};

// Save state to localStorage
const saveCashierState = (state: CashierState) => {
    try {
        localStorage.setItem(CASHIER_STORAGE_KEY, JSON.stringify(state));
    } catch (error) {
        console.error("Failed to save cashier state to localStorage:", error);
    }
};

// Initialize state
cashierState.value = loadCashierState();

// Watch for changes and save to localStorage
watch(
    cashierState,
    (newState) => {
        saveCashierState(newState);
    },
    { deep: true }
);

export const useCashier = () => {
    // Computed properties
    const selectedCart = computed(() => {
        if (!cashierState.value.selectedCartId) return null;
        return cashierState.value.openCarts.find(
            cart => cart.id === cashierState.value.selectedCartId
        ) || null;
    });

    const hasOpenCarts = computed(() => {
        return cashierState.value.openCarts.length > 0;
    });

    const cartCount = computed(() => {
        return cashierState.value.openCarts.length;
    });

    // Actions
    const setSelectedCart = (cartId: number | null) => {
        cashierState.value.selectedCartId = cartId;
    };

    const addCart = (cart: Cart) => {
        const existingIndex = cashierState.value.openCarts.findIndex(
            c => c.id === cart.id
        );

        if (existingIndex >= 0) {
            // Update existing cart
            cashierState.value.openCarts[existingIndex] = cart;
        } else {
            // Add new cart
            cashierState.value.openCarts.push(cart);
        }

        // Auto-select if no cart is selected
        if (!cashierState.value.selectedCartId) {
            cashierState.value.selectedCartId = cart.id;
        }
    };

    const updateCart = (cartId: number, updates: Partial<Cart>) => {
        const cartIndex = cashierState.value.openCarts.findIndex(
            c => c.id === cartId
        );

        if (cartIndex >= 0) {
            cashierState.value.openCarts[cartIndex] = {
                ...cashierState.value.openCarts[cartIndex],
                ...updates
            };
        }
    };

    const removeCart = (cartId: number) => {
        cashierState.value.openCarts = cashierState.value.openCarts.filter(
            c => c.id !== cartId
        );

        // If removed cart was selected, select another or clear selection
        if (cashierState.value.selectedCartId === cartId) {
            cashierState.value.selectedCartId =
                cashierState.value.openCarts.length > 0
                    ? cashierState.value.openCarts[0].id
                    : null;
        }
    };

    const clearAllCarts = () => {
        cashierState.value.openCarts = [];
        cashierState.value.selectedCartId = null;
    };

    const setCashierSession = (sessionId: number | null) => {
        cashierState.value.cashierSessionId = sessionId;
    };

    const setTableInfo = (tableId: string | null, locationType: string | null) => {
        cashierState.value.tableId = tableId;
        cashierState.value.locationType = locationType;
    };

    const switchToCart = (cartId: number) => {
        const cart = cashierState.value.openCarts.find(c => c.id === cartId);
        if (cart) {
            setSelectedCart(cartId);
            if (cart.table_id) {
                setTableInfo(cart.table_id.toString(), cart.order_type);
            }
        }
    };

    // Utility functions
    const getCartByTableId = (tableId: number) => {
        return cashierState.value.openCarts.find(
            cart => cart.table_id === tableId
        );
    };

    const getCartsByOrderType = (orderType: string) => {
        return cashierState.value.openCarts.filter(
            cart => cart.order_type === orderType
        );
    };

    // Initialize from server data if provided
    const initializeFromServerData = (serverCarts: Cart[], currentCart?: Cart) => {
        if (serverCarts && Array.isArray(serverCarts)) {
            cashierState.value.openCarts = serverCarts;

            if (currentCart && currentCart.id) {
                setSelectedCart(currentCart.id);
            } else if (serverCarts.length > 0) {
                setSelectedCart(serverCarts[0].id);
            }
        }
    };

    return {
        // State
        cashierState: cashierState.value,

        // Computed
        selectedCart,
        hasOpenCarts,
        cartCount,

        // Actions
        setSelectedCart,
        addCart,
        updateCart,
        removeCart,
        clearAllCarts,
        setCashierSession,
        setTableInfo,
        switchToCart,

        // Utilities
        getCartByTableId,
        getCartsByOrderType,
        initializeFromServerData,

        // Raw state access
        openCarts: computed(() => cashierState.value.openCarts),
        selectedCartId: computed(() => cashierState.value.selectedCartId),
        cashierSessionId: computed(() => cashierState.value.cashierSessionId),
        tableId: computed(() => cashierState.value.tableId),
        locationType: computed(() => cashierState.value.locationType),
    };
};
