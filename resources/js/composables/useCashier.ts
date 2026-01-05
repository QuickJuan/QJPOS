import { ref, computed, watch } from "vue";
import type { Ref } from "vue";
import { httpPost } from "@/Utils/axiosHelper";
import { route } from "ziggy-js";

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

export interface SettlePaymentPayload {
    cart_id: number;
    payment_method_id?: number;
    currency_id?: number | null;
    amount_in_payment_currency?: number;
    total_amount: number;
    amount_paid?: number;
    reference_number?: string;
    notes?: string;
    payment_details?: Record<string, any>;
    customer_id?: number;
    is_mixed_payment?: boolean;
    payments?: Array<any>;
}

export interface ClosingCashBreakdownPayload {
    base_currency_id: number | string | null;
    base_currency_code: string | null;
    base_currency_symbol?: string | null;
    gift_check_total?: number;
    totals?: {
        cash_in_base: number;
        gift_check_in_base: number;
        combined_in_base: number;
        variance_in_base?: number;
        expected_cash_in_base?: number;
        change_paid_in_base?: number;
    };
    currencies: Array<{
        currency_id: number | string;
        currency_code: string;
        currency_name: string;
        symbol?: string;
        exchange_rate: number;
        amount_in_currency: number;
        amount_in_base: number;
        total_amount?: number; // legacy support
        total_in_base?: number; // legacy support
        expected_in_currency?: number;
        expected_in_base?: number;
        variance_in_currency?: number;
        variance_in_base?: number;
        denominations?: Array<{
            id?: number | string;
            label?: string;
            value: number;
            count: number;
            total: number;
        }>;
    }>;
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

        // Also update the alternative localStorage key used by other components
        try {
            const cashierStateKey = "quickjuan_cashier_state";
            const existingState = localStorage.getItem(cashierStateKey);
            let state = existingState ? JSON.parse(existingState) : {};

            if (cartId) {
                state.cartId = cartId;
            } else {
                delete state.cartId;
            }
            state.lastUpdated = new Date().toISOString();

            localStorage.setItem(cashierStateKey, JSON.stringify(state));
        } catch (error) {
            console.error("Failed to update quickjuan_cashier_state:", error);
        }
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

    // Settle payment for a cart
    const settlePayment = async (paymentData: SettlePaymentPayload) => {
        // Build payload based on payment type
        let payload: any;

        if (paymentData.is_mixed_payment) {
            // Mixed payment mode - send payments array
            payload = {
                cart_id: paymentData.cart_id,
                is_mixed_payment: true,
                payments: paymentData.payments,
                total_amount: paymentData.total_amount,
            };
        } else {
            // Single payment mode - send individual fields
            payload = {
                cart_id: paymentData.cart_id,
                payment_method_id: paymentData.payment_method_id,
                currency_id: paymentData.currency_id,
                amount_in_payment_currency: paymentData.amount_in_payment_currency,
                amount_paid: paymentData.amount_paid,
                total_amount: paymentData.total_amount,
                reference_number: paymentData.reference_number,
                notes: paymentData.notes,
                payment_details: paymentData.payment_details,
                customer_id: paymentData.customer_id,
            };
        }

        const response = await httpPost(
            route("resto.cart.settle-bill"),
            payload
        );

        // Remove the settled cart from open carts if successful
        if (response.success) {
            removeCart(paymentData.cart_id);
        }

        return response;
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

    // Close cashier shift
    const closeShift = async (payload: {
        cash_denomination_details: ClosingCashBreakdownPayload;
        cash_denomination: number;
        shift_no: number;
        cashier_id: number;
    }) => {
        return await httpPost(route("resto.session.close"), payload);
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
        settlePayment,
        initializeFromServerData,
        closeShift,

        // Raw state access
        openCarts: computed(() => cashierState.value.openCarts),
        selectedCartId: computed(() => cashierState.value.selectedCartId),
        cashierSessionId: computed(() => cashierState.value.cashierSessionId),
        tableId: computed(() => cashierState.value.tableId),
        locationType: computed(() => cashierState.value.locationType),
    };
};
