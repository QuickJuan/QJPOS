import { router } from "@inertiajs/vue3";
import { useToast } from "primevue";
import { route } from "ziggy-js";
import { usePage } from "@inertiajs/vue3";

const page = usePage();

export const useTable = () => {
    const toast = useToast();

    const vacantTable = (tableId: number) => {
        if (!tableId) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Table ID is required",
                life: 3000,
            });
            return;
        }

        router.post(
            route("table-rooms.vacant", tableId),
            {},
            {
                preserveScroll: false,
                preserveState: false,
                onSuccess: (response) => {
                    // Refresh tables to show updated status

                },
                onError: (errors) => {
                    const errorMessage =  page.props.flash?.error

                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: errorMessage,
                        life: 3000,
                    });
                },
            }
        );
    };

    const placeOrder = (tableId: number, cartId: number) => {
        if (!tableId || !cartId) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Table ID and Cart ID are required",
                life: 3000,
            });
            return;
        }

            router.post(
                route("resto.cart.place-order"),
                {
                    'cart_id': cartId,
                    'table_id': tableId
                },
                {
                    preserveScroll: false,
                    preserveState: false,
                    onSuccess: (response) => {


                        // No reload after placing order
                    },
                    onError: (errors) => {
                        toast.add({
                            severity: "error",
                            summary: "Error",
                            detail: errors?.message || "Failed to place order.",
                            life: 3000,
                        });
                    },
                }
            );
    };

    return {
        vacantTable,
        placeOrder,
    };
};
