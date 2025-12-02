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

    const unmergeFromTable = (tableId: number) => {
        if (!tableId) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Table ID is required",
                life: 3000,
            });
            return;
        }

        router.put(
            route("table-rooms.unmerge", tableId),
            {},
            {
                preserveScroll: false,
                preserveState: false,
                onSuccess: (response) => {
                    // toast.add({
                    //     severity: "success",
                    //     summary: "Success",
                    //     detail: "Table unmerged successfully",
                    //     life: 3000,
                    // });
                    // router.reload({ only: ["tables"] });
                },
                onError: (errors) => {
                    const errorMessage = page.props.flash?.error || "Failed to unmerge table";
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

    const unmergeTables = (tableId: number) => {
        if (!tableId) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Table ID is required",
                life: 3000,
            });
            return;
        }

        router.put(
            route("table-rooms.unmerge-all", tableId),
            {},
            {
                preserveScroll: false,
                preserveState: false,
                onSuccess: (response) => {
                    toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: "All merged tables unmerged successfully",
                        life: 3000,
                    });
                    router.reload({ only: ["tables"] });
                },
                onError: (errors) => {
                    const errorMessage = page.props.flash?.error || "Failed to unmerge tables";
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

    const viewOrder = (table: any) => {
        if (!table?.id) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Table ID is required",
                life: 3000,
            });
            return;
        }

        // If table is merged to another, use the parent table id
        const tableId = table.merge_to ? table.merge_to : table.id;

        router.visit(
            route("resto.index", {
                tableId: tableId,
            })
        );
    };

    return {
        vacantTable,
        placeOrder,
        unmergeFromTable,
        unmergeTables,
        viewOrder,
    };
};
