export default interface CashieringSession {
    id: number;
    business_date: string;
    cashier_id: number;
    started_time: string;
    closing_time: string;
    beginning_cash: number;
    closing_cash: number;
    total_sales: number;
    check_by: string;
    cash_denomination: Object;
}
