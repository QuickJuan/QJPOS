import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import PageProps from "@/Types/PageProps";

export const useProduct = () => {
    const page = usePage<PageProps>();
    const toast = useToast();

    const showPackagingModal = ref(false);
    const selectedProductForPackaging = ref<any>(null);

    const addToCart = (
        product: any,
        packaging?: any,
        options: {
            tableId?: string | null;
            selectedOrderType?: string;
        } = {}
    ) => {
        const { tableId = null, selectedOrderType = "dine-in" } = options;

        if (packaging) {
            // Packaging already selected, proceed
            if (product.options && product.options.length > 0) {
                // Has options, redirect to options page with packaging
                router.visit(
                    route("retail-cashier.product.options", {
                        product: product.id,
                        orderType: selectedOrderType,
                        tableId: tableId,
                        packagingId: packaging.id,
                    })
                );
            } else {
                // No options, add to cart with packaging
                router.post(
                    route("retail-cashier.cart.add"),
                    {
                        quantity: 1,
                        product_id: product.id,
                        product_packaging_id: packaging.id,
                        table_id: tableId,
                        total_price: parseFloat(packaging.price || 0),
                        order_type: selectedOrderType,
                        selected_options: [],
                    },
                    {
                        preserveScroll: false,
                        onSuccess: () => {
                            toast.add({
                                severity: "success",
                                summary: "Success",
                                detail: page.props.flash.success,
                                life: 3000,
                            });
                        },
                        onError: (errors) => {
                            console.error("Failed to add item to cart:", errors);
                        },
                    }
                );
            }
        } else {
            // No packaging provided, check if product has packagings
            if (
                product.product_packagings &&
                product.multiple_packaging && // Check if the multiple packaging is true
                product.product_packagings.length > 0
            ) {
                if (product.product_packagings.length === 1) {
                    // Auto-select the single packaging
                    const packaging = product.product_packagings[0];
                    if (product.options && product.options.length > 0) {
                        // Has options, redirect to options page with packaging
                        router.visit(
                            route("retail-cashier.product.options", {
                                product: product.id,
                                orderType: selectedOrderType,
                                tableId: tableId,
                                packagingId: packaging.id,
                            })
                        );
                    } else {
                        // No options, add to cart with packaging
                        router.post(
                            route("retail-cashier.cart.add"),
                            {
                                quantity: 1,
                                product_id: product.id,
                                product_packaging_id: packaging.id,
                                table_id: tableId,
                                total_price: parseFloat(packaging.price || 0),
                                order_type: selectedOrderType,
                                selected_options: [],
                            },
                            {
                                preserveScroll: false,
                                onSuccess: () => {
                                    toast.add({
                                        severity: "success",
                                        summary: "Success",
                                        detail: page.props.flash.success,
                                        life: 3000,
                                    });
                                },
                                onError: (errors) => {
                                    console.error(
                                        "Failed to add item to cart:",
                                        errors
                                    );
                                },
                            }
                        );
                    }
                } else {
                    // Multiple packagings, show modal
                    selectedProductForPackaging.value = product;
                    showPackagingModal.value = true;
                }
            } else {
                // No packaging, check options
                if (product.options && product.options.length > 0) {
                    // Redirect to options page
                    router.visit(
                        route("retail-cashier.product.options", {
                            product: product.id,
                            orderType: selectedOrderType,
                            tableId: tableId,
                        })
                    );
                } else {
                    // No options, add to cart without packaging
                    router.post(
                        route("retail-cashier.cart.add"),
                        {
                            quantity: 1,
                            product_id: product.id,
                            product_packaging_id: null,
                            table_id: tableId,
                            total_price: parseFloat(product.average_cost || "0"),
                            order_type: selectedOrderType,
                            selected_options: [],
                        },
                        {
                            preserveScroll: false,
                            onSuccess: () => {
                                toast.add({
                                    severity: "success",
                                    summary: "Success",
                                    detail: page.props.flash.success,
                                    life: 3000,
                                });
                            },
                            onError: (errors) => {
                                console.error(
                                    "Failed to add item to cart:",
                                    errors
                                );
                            },
                        }
                    );
                }
            }
        }
    };

    const handlePackagingConfirm = (packaging: any, options?: { tableId?: string | null; selectedOrderType?: string }) => {
        const product = selectedProductForPackaging.value;
        if (product) {
            addToCart(product, packaging, options);
        }
        showPackagingModal.value = false;
        selectedProductForPackaging.value = null;
    };

    const handlePackagingCancel = () => {
        showPackagingModal.value = false;
        selectedProductForPackaging.value = null;
    };

    return {
        addToCart,
        showPackagingModal,
        selectedProductForPackaging,
        handlePackagingConfirm,
        handlePackagingCancel,
    };
};
