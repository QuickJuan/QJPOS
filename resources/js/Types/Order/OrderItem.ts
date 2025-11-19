export default interface OrderItem {
    id: number;
    quantity: number;
    price: number;
    amount: number;
    sub_total?: number;
    order_type?: string | null;
    product?: {
        id: number;
        name: string;
    };
}

