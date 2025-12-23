import { usePage } from "@inertiajs/vue3";
import { httpGet } from "@/Utils/axiosHelper";
import { thermalPrinter } from "@/Services/ThermalPrinterService";

export const usePrintBill = () => {
    const page = usePage();

    const fetchBillData = async (cartId: number) => {
        return httpGet(`/api/carts/${cartId}/print-bill`);
    };

    const buildThermalBillPayload = (billData: any) => {
        return {
            storeName: page.props.company_info?.company_name || "QuickJuan",
            branch: billData.branch,
            billNumber: billData.bill_number,
            tableName: billData.table_number,
            cashier: billData.cashier,
            items: billData.cart_items || [],
            totals: billData.totals || {},
        };
    };

    const sendBillToPrinter = async (billData: any) => {
        const payload = buildThermalBillPayload(billData);
        await thermalPrinter.printBill(payload);
    };

    const printBill = async (cartId: number) => {
        const response = await fetchBillData(cartId);

        if (!response.success || !response.data) {
            throw new Error(response.error || "Failed to fetch bill data");
        }

        await sendBillToPrinter(response.data);

        return response.data;
    };

    return {
        fetchBillData,
        sendBillToPrinter,
        buildThermalBillPayload,
        printBill,
    };
};
