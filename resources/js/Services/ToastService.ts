import { useToast as usePrimeToast } from "primevue/usetoast";

export function useToast() {
    const toast = usePrimeToast();

    const showToast = (
        message: string,
        severity: "success" | "info" | "warn" | "error" = "info",
        life: number = 3000
    ) => {
        toast.add({
            severity,
            summary: severity.charAt(0).toUpperCase() + severity.slice(1),
            detail: message,
            life,
        });
    };

    return {
        showToast,
        toast,
    };
}
