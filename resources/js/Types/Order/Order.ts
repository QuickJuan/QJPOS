import OrderItem from "./OrderItem";

export default interface Order {
    id: number;
    cashier?: { name: string };
    table_room?: { customer_name: string; name?: string };
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
