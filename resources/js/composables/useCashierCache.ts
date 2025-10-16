import { ref, Ref } from 'vue';

interface CacheItem<T> {
    data: T;
    timestamp: number;
    expiresAt: number;
}

const CACHE_DURATION = 60 * 60 * 1000; // 1 hour in milliseconds

class LocalStorageCache {
    private getKey(key: string): string {
        // Include tenant info or user info to avoid cross-tenant data leakage
        const tenantId = (window as any).tenant?.id || 'default';
        return `quickjuan_cache_${tenantId}_${key}`;
    }

    set<T>(key: string, data: T, customDuration?: number): void {
        const duration = customDuration || CACHE_DURATION;
        const cacheItem: CacheItem<T> = {
            data,
            timestamp: Date.now(),
            expiresAt: Date.now() + duration,
        };

        try {
            localStorage.setItem(this.getKey(key), JSON.stringify(cacheItem));
        } catch (error) {
            console.warn('Failed to set cache item:', error);
        }
    }

    get<T>(key: string): T | null {
        try {
            const cached = localStorage.getItem(this.getKey(key));
            if (!cached) return null;

            const cacheItem: CacheItem<T> = JSON.parse(cached);

            // Check if cache has expired
            if (Date.now() > cacheItem.expiresAt) {
                this.remove(key);
                return null;
            }

            return cacheItem.data;
        } catch (error) {
            console.warn('Failed to get cache item:', error);
            return null;
        }
    }

    remove(key: string): void {
        try {
            localStorage.removeItem(this.getKey(key));
        } catch (error) {
            console.warn('Failed to remove cache item:', error);
        }
    }

    clear(): void {
        try {
            const tenantId = (window as any).tenant?.id || 'default';
            const prefix = `quickjuan_cache_${tenantId}_`;

            // Remove only keys that match our tenant prefix
            Object.keys(localStorage)
                .filter(key => key.startsWith(prefix))
                .forEach(key => localStorage.removeItem(key));
        } catch (error) {
            console.warn('Failed to clear cache:', error);
        }
    }

    invalidatePattern(pattern: string): void {
        try {
            const tenantId = (window as any).tenant?.id || 'default';
            const prefix = `quickjuan_cache_${tenantId}_`;

            Object.keys(localStorage)
                .filter(key => key.startsWith(prefix) && key.includes(pattern))
                .forEach(key => localStorage.removeItem(key));
        } catch (error) {
            console.warn('Failed to invalidate cache pattern:', error);
        }
    }
}

const cache = new LocalStorageCache();

export const useCashierCache = () => {
    const categories: Ref<any[]> = ref([]);
    const discounts: Ref<any[]> = ref([]);
    const isLoading = ref(false);

    const CATEGORIES_KEY = 'categories_with_products';
    const DISCOUNTS_KEY = 'available_discounts';

    const loadCategories = (serverData?: any[]) => {
        if (serverData && Array.isArray(serverData) && serverData.length > 0) {
            // Validate and filter server data
            const validCategories = serverData.filter(cat => cat && cat.id && cat.name);
            if (validCategories.length > 0) {
                categories.value = validCategories;
                cache.set(CATEGORIES_KEY, validCategories);
                console.log('Categories saved to localStorage and loaded:', validCategories.length, 'items');
                return;
            }
        }

        // If no valid server data provided, try to load from localStorage
        const cached = cache.get<any[]>(CATEGORIES_KEY);
        if (cached && Array.isArray(cached) && cached.length > 0) {
            // Validate cached data
            const validCategories = cached.filter(cat => cat && cat.id && cat.name);
            if (validCategories.length > 0) {
                categories.value = validCategories;
                console.log('Categories loaded from localStorage cache:', validCategories.length, 'items');
                return;
            }
        }

        console.log('No valid categories found in server data or cache');
    };

    const loadDiscounts = (serverData?: any[]) => {
        if (serverData && Array.isArray(serverData)) {
            // Validate and filter server data
            const validDiscounts = serverData.filter(discount => discount && discount.id);
            discounts.value = validDiscounts;
            cache.set(DISCOUNTS_KEY, validDiscounts);
            console.log('Discounts cached:', validDiscounts.length, 'items');
        } else {
            // Try to load from cache
            const cached = cache.get<any[]>(DISCOUNTS_KEY);
            if (cached && Array.isArray(cached)) {
                // Validate cached data
                const validDiscounts = cached.filter(discount => discount && discount.id);
                discounts.value = validDiscounts;
                console.log('Discounts loaded from cache:', validDiscounts.length, 'items');
            }
        }
    };

    const invalidateCategories = () => {
        cache.remove(CATEGORIES_KEY);
        categories.value = [];
        console.log('Categories cache invalidated');
    };

    const invalidateDiscounts = () => {
        cache.remove(DISCOUNTS_KEY);
        discounts.value = [];
        console.log('Discounts cache invalidated');
    };

    const invalidateAll = () => {
        cache.clear();
        categories.value = [];
        discounts.value = [];
        console.log('All cache invalidated');
    };

    const refreshData = async () => {
        isLoading.value = true;
        try {
            // Force refresh by visiting the current route without cache
            const currentUrl = window.location.pathname + window.location.search;
            window.location.href = currentUrl + (currentUrl.includes('?') ? '&' : '?') + 'refresh=1';
        } catch (error) {
            console.error('Failed to refresh data:', error);
        } finally {
            isLoading.value = false;
        }
    };

    return {
        categories,
        discounts,
        isLoading,
        loadCategories,
        loadDiscounts,
        invalidateCategories,
        invalidateDiscounts,
        invalidateAll,
        refreshData,
    };
};

export default cache;
