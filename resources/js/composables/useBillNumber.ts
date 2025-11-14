import { ref } from 'vue';

const BILL_NUMBER_KEY = 'quickjuan_bill_number';

export function useBillNumber() {
    const getNextBillNumber = (): number => {
        const currentNumber = parseInt(localStorage.getItem(BILL_NUMBER_KEY) || '0', 10);
        const nextNumber = currentNumber + 1;
        localStorage.setItem(BILL_NUMBER_KEY, nextNumber.toString());
        return nextNumber;
    };

    const getCurrentBillNumber = (): number => {
        return parseInt(localStorage.getItem(BILL_NUMBER_KEY) || '0', 10);
    };

    const resetBillNumber = (startNumber: number = 1): void => {
        localStorage.setItem(BILL_NUMBER_KEY, startNumber.toString());
    };

    return {
        getNextBillNumber,
        getCurrentBillNumber,
        resetBillNumber,
    };
}
