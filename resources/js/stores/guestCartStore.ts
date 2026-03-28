import { defineStore } from "pinia";
import { ref, computed } from "vue";

export interface GuestCartItem {
    product_id: number;
    name: string;
    price: number;
    image_url: string | null;
    category: string | null;
    quantity: number;
}

export interface GuestCoupon {
    id: number;
    code: string;
    name: string;
    description?: string | null;
    type: string;
    value: number;
    discount_amount: number;
    minimum_amount?: number | null;
}

const STORAGE_KEY = "guest_cart";
const COUPON_STORAGE_KEY = "guest_cart_coupon";

function loadFromStorage(): GuestCartItem[] {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}

function saveToStorage(items: GuestCartItem[]) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
}

function loadCouponFromStorage(): GuestCoupon | null {
    try {
        const raw = localStorage.getItem(COUPON_STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

function saveCouponToStorage(coupon: GuestCoupon | null) {
    if (!coupon) {
        localStorage.removeItem(COUPON_STORAGE_KEY);
        return;
    }

    localStorage.setItem(COUPON_STORAGE_KEY, JSON.stringify(coupon));
}

export const useGuestCartStore = defineStore("guestCart", () => {
    const items = ref<GuestCartItem[]>(loadFromStorage());
    const coupon = ref<GuestCoupon | null>(loadCouponFromStorage());

    const totalItems = computed(() =>
        items.value.reduce((sum, i) => sum + i.quantity, 0),
    );

    const totalAmount = computed(() =>
        items.value.reduce((sum, i) => sum + i.price * i.quantity, 0),
    );

    const discountAmount = computed(() =>
        Math.min(coupon.value?.discount_amount ?? 0, totalAmount.value),
    );

    const grandTotal = computed(() =>
        Math.max(0, totalAmount.value - discountAmount.value),
    );

    function addItem(product: Omit<GuestCartItem, "quantity">) {
        const existing = items.value.find(
            (i) => i.product_id === product.product_id,
        );
        if (existing) {
            existing.quantity++;
        } else {
            items.value.push({ ...product, quantity: 1 });
        }
        saveToStorage(items.value);
    }

    function removeItem(productId: number) {
        items.value = items.value.filter((i) => i.product_id !== productId);
        saveToStorage(items.value);
    }

    function updateQuantity(productId: number, quantity: number) {
        if (quantity < 1) {
            removeItem(productId);
            return;
        }
        const item = items.value.find((i) => i.product_id === productId);
        if (item) {
            item.quantity = quantity;
            saveToStorage(items.value);
        }
    }

    function setCoupon(nextCoupon: GuestCoupon | null) {
        coupon.value = nextCoupon;
        saveCouponToStorage(nextCoupon);
    }

    function clearCoupon() {
        setCoupon(null);
    }

    function clearCart() {
        items.value = [];
        localStorage.removeItem(STORAGE_KEY);
        clearCoupon();
    }

    return {
        items,
        coupon,
        totalItems,
        totalAmount,
        discountAmount,
        grandTotal,
        addItem,
        removeItem,
        updateQuantity,
        setCoupon,
        clearCoupon,
        clearCart,
    };
});
