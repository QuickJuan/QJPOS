import { router } from "@inertiajs/vue3";
import { useToast } from "primevue";
import { route } from "ziggy-js";
import { usePage } from "@inertiajs/vue3";
import { httpPost, showErrorToast, showSuccessToast } from "@/Utils/axiosHelper";

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

    const placeOrder = async (tableId: number, cartId: number) => {
        if (!tableId || !cartId) {
            showErrorToast("Error", "Table ID and Cart ID are required");
            return;
        }

        const response = await httpPost(
            route("resto.cart.place-order"),
            {
                cart_id: cartId,
                table_id: tableId,
            },
            {
                successCallback: (data: any) => {
                    // Get locationId from response data
                    const locationId = data?.tableRoom?.table_room_location_id;

                    console.log("response data:", data);
                    showSuccessToast("Success", data.message || "Order placed successfully");

                    // // Redirect to table rooms with locationId
                    // setTimeout(() => {
                    //     router.visit(
                    //         route("table-rooms.index", { locationId: locationId })
                    //     );
                    // }, 500);
                },
            }
        );

        if (!response.success) {
            showErrorToast("Error", response.error || "Failed to place order");
        }
    };

    const takeOrder = (tableId: number, data: { pax: number; guest_name: string }) => {
        if (!tableId ) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Please select table",
                life: 3000,
            });
            return;
        }

        router.post(
            route("resto.cart.create-order"),
            {
                table_id: tableId,
                pax: data.pax,
                guest_name: data.guest_name || 'Guest',
            },
            {
                preserveScroll: false,
                preserveState: false,
                onError: (errors) => {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: errors?.message || "Failed to take order",
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

        // Get locationId from URL if available
        const urlParams = new URLSearchParams(window.location.search);
        const locationId = urlParams.get('locationId');
        let requestUrl = route("table-rooms.unmerge", tableId);
        if (locationId) {
            requestUrl += `?locationId=${locationId}`;
        }

        router.put(
            requestUrl,
            {},
            {
                preserveScroll: false,
                preserveState: false,
                onSuccess: (response) => {
                    // Success handled by controller redirect
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

        // Get locationId from URL if available
        const urlParams = new URLSearchParams(window.location.search);
        const locationId = urlParams.get('locationId');
        let requestUrl = route("table-rooms.unmerge-all", tableId);
        if (locationId) {
            requestUrl += `?locationId=${locationId}`;
        }

        router.put(
            requestUrl,
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
        const tableId = table.mergedTo ? table.mergedTo : table.id;

        router.visit(
            route("resto.index", {
                tableId: tableId,
            })
        );
    };

    const mergeTable = (table: any) => {
        // This will trigger the merge modal in the parent component
        // The parent component listens for this and shows the merge modal
        if (!table?.id) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Table ID is required",
                life: 3000,
            });
            return;
        }
        // Emit event through window for parent to listen
        window.dispatchEvent(new CustomEvent('table-merge-requested', { detail: { table } }));
    };

    const claimOrder = (tableId: number) => {
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
            route("resto.cart.claim-order", {
                tableId: tableId,
            }),
            {},
            {
                onSuccess: () => {
                    toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Order claimed successfully",
                        life: 3000,
                    });
                    router.reload({ only: ["tables"] });
                },
                onError: (errors) => {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: errors?.message || "Failed to claim order",
                        life: 3000,
                    });
                },
            }
        );
    };

    const transferNumber = (table: any) => {
        if (!table?.id) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Table ID is required",
                life: 3000,
            });
            return;
        }
        // Emit event through window for parent to listen
        window.dispatchEvent(new CustomEvent('table-transfer-requested', { detail: { table } }));
    };

    const reserveTable = (table: any) => {
        if (!table?.id) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Table ID is required",
                life: 3000,
            });
            return;
        }
        // Emit event through window for parent to listen
        window.dispatchEvent(new CustomEvent('table-reserve-requested', { detail: { table } }));
    };

    return {
        vacantTable,
        placeOrder,
        takeOrder,
        unmergeFromTable,
        unmergeTables,
        viewOrder,
        mergeTable,
        claimOrder,
        transferNumber,
        reserveTable,
    };
};
