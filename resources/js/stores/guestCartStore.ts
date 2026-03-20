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

const STORAGE_KEY = "guest_cart";

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

export const useGuestCartStore = defineStore("guestCart", () => {
    const items = ref<GuestCartItem[]>(loadFromStorage());

    const totalItems = computed(() =>
        items.value.reduce((sum, i) => sum + i.quantity, 0),
    );

    const totalAmount = computed(() =>
        items.value.reduce((sum, i) => sum + i.price * i.quantity, 0),
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

    function clearCart() {
        items.value = [];
        localStorage.removeItem(STORAGE_KEY);
    }

    return {
        items,
        totalItems,
        totalAmount,
        addItem,
        removeItem,
        updateQuantity,
        clearCart,
    };
});
