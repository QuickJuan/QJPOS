import OrderItem from "./OrderItem";

export default interface Order {
    id: number;
    cashier?: { name: string };
    table_room?: { customer_name: string; name?: string };
    customer?: { customer_name: string };
    or_number?: string;
    bill_number?: string;
    payment_method: string;
    total_amount: number;
    status: string;
    created_at: string;
    meta_data?: {
        refund?: {
            requested_by: string;
            supervisor: string;
            notes: string;
            refunded_at: string;
        };
        [key: string]: any;
    };
    order_items?: OrderItem[];
}
