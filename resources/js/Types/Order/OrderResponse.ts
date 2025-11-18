import Order from "./Order";

export default interface OrderResponse {
    data: Order[];
    current_page: number;
    from: number;
    to: number;
    total: number;
    links: Array<{ url: string; label: string; active: boolean }>;
}
