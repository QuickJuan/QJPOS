import OrderItem from "./OrderItem";

export default interface Order {
    id: number;
    invoice_no?: string | number;
    bill_number?: string | number;
    order_date?: string;
    status?: string;
    table_number?: string | null;

    // Customer Information
    customer?: {
        id: number | null;
        name: string | null;
        phone: string | null;
        email: string | null;
        address: string | null;
    };

    // Cashier Information
    cashier?: {
        id: number;
        name: string;
    };

    // Branch Information
    branch?: {
        id: number;
        name: string;
        address: string;
        phone: string;
        tin: string | null;
        registration_number: string | null;
        receipt_headers: string[];
        receipt_footer: string[];
    };

    // Totals
    totals?: {
        total_amount: string | number;
        tax_amount: string | number;
        discount_amount: string | number;
        total_due: string | number;
        less_tax: number;
        less_discount: number;
        vatable_sales: string | number;
        vat_amount: string | number;
        vat_exempt_sales: string | number;
        zero_rated_sales: string | number;
        non_vat_sales: number;
    };

    // Payment Information
    payment?: {
        method: string | null;
        amount_paid: number;
        change: number;
        status: string | null;
    };

    // Order Items grouped by order type
    order_items?: Array<{
        orderId: number;
        orderType: string;
        orderItems: Array<{
            id: number;
            orderType: string;
            order_id: number;
            description: string;
            quantity: string | number;
            price: string | number;
            amount: string | number;
            lessTax: string | number;
            discount: string;
            discount_amount: number;
            sub_total: string | number;
            vatable_sales: string | number;
            vat_amount: string | number;
            modifiers: string[];
            sub_items: any[];
        }>;
    }>;

    // Metadata
    meta?: {
        printed_at: string;
        receipt_type: string;
        refund?: {
            requested_by: string;
            supervisor: string;
            notes: string;
            refunded_at: string;
        };
    };

    // Legacy compatibility
    created_at?: string;
    payment_method?: string;
    total_amount?: number;
    order_items_legacy?: OrderItem[];
    meta_data?: {
        refund?: {
            requested_by: string;
            supervisor: string;
            notes: string;
            refunded_at: string;
        };
        [key: string]: any;
    };
}
