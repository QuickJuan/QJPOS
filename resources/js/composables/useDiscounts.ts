import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

interface Discount {
    id: number
    discount_name: string
    description?: string
    amount: number
    type: 'percentage' | 'fixed' | 'amount'
    discount_type?: string
    remove_tax: boolean
    requires_customer_info: boolean
    required_pax: boolean
    created_at: string
    updated_at: string
}

interface DiscountsByType {
    percentage: Discount[]
    fixed: Discount[]
    other: Discount[]
}

export function useDiscounts() {
    const page = usePage()

    // Get all available discounts from shared Inertia props
    const availableDiscounts = computed(() => {
        const discountData = page.props.available_discounts as any

        console.log('=== DISCOUNT DEBUG ===')
        console.log('Raw discount data:', discountData)
        console.log('Discount data type:', typeof discountData)
        console.log('Is array:', Array.isArray(discountData))
        console.log('Has data property:', discountData && 'data' in discountData)

        // Handle Laravel ResourceCollection format (wrapped in .data)
        if (discountData && Array.isArray(discountData.data)) {
            console.log('Using ResourceCollection format (.data):', discountData.data.length)
            return discountData.data as Discount[]
        }

        // Handle direct array format
        if (Array.isArray(discountData)) {
            console.log('Using direct array format:', discountData.length)
            return discountData as Discount[]
        }

        console.log('No valid discount data found')
        return []
    })

    // Get discounts grouped by type
    const discountsByType = computed((): DiscountsByType => {
        const discounts = availableDiscounts.value

        return {
            percentage: discounts.filter(d => d.type === 'percentage'),
            fixed: discounts.filter(d => ['fixed', 'amount'].includes(d.type)),
            other: discounts.filter(d => !['percentage', 'fixed', 'amount'].includes(d.type))
        }
    })

    // Get discount by ID
    const getDiscountById = (discountId: string | number) => {
        return availableDiscounts.value.find(d => d.id == discountId)
    }

    // Calculate discount amount for given items
    const calculateDiscountAmount = (discountId: string | number, items: any[]): number => {
        const discount = getDiscountById(discountId)
        if (!discount) return 0

        // Calculate subtotal of selected items
        const subtotal = items.reduce((sum, item) => {
            const price = parseFloat(item.price || item.average_cost || '0')
            const quantity = item.quantity || 1
            return sum + (price * quantity)
        }, 0)

        // Calculate base amount (vatable if remove_tax is true)
        const baseAmount = discount.remove_tax ? subtotal / 1.12 : subtotal

        // Calculate discount based on type
        switch (discount.type) {
            case 'percentage':
                return baseAmount * (discount.amount / 100)
            case 'fixed':
            case 'amount':
                return Math.min(discount.amount, baseAmount)
            default:
                return 0
        }
    }

    // Check if discount requires customer info
    const requiresCustomerInfo = (discountId: string | number): boolean => {
        const discount = getDiscountById(discountId)
        return discount?.requires_customer_info || false
    }

    // Get discount statistics
    const discountStats = computed(() => {
        const discounts = availableDiscounts.value

        return {
            total: discounts.length,
            percentage: discountsByType.value.percentage.length,
            fixed: discountsByType.value.fixed.length,
            customerInfoRequired: discounts.filter(d => d.requires_customer_info).length
        }
    })

    // Check if any discounts are available
    const hasDiscounts = computed(() => availableDiscounts.value.length > 0)

    // Get formatted discount display text
    const getDiscountDisplayText = (discount: Discount): string => {
        if (discount.type === 'percentage') {
            return `${discount.amount}%`
        }
        return `₱${discount.amount}`
    }

    return {
        // Data
        availableDiscounts,
        discountsByType,
        discountStats,
        hasDiscounts,

        // Methods
        getDiscountById,
        calculateDiscountAmount,
        requiresCustomerInfo,
        getDiscountDisplayText,
    }
}

// Export types for use in components
export type { Discount, DiscountsByType }
