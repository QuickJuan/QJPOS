import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "primevue";
import PageProps from "@/Types/PageProps";
import { useOrderStore } from "@/stores/orderStore";

export const useProduct = () => {
    const page = usePage<PageProps>();
    const toast = useToast();
    const orderStore = useOrderStore();

    const showPackagingModal = ref(false);
    const selectedProductForPackaging = ref<any>(null);

    const buildOptionItemsPayload = (option: any) => {
        return (
            option.optionItems
                ?.filter((item: any) => (item.quantity ?? 0) > 0)
                .map((item: any) => ({
                    id: item.id,
                    product_id: item.product_id,
                    product_packaging_id: item.product_packaging_id,
                    price: item.price,
                    quantity: item.quantity ?? 0,
                })) || []
        );
    };

    const addToCart = (
        product: any,
        packaging?: any,
        options: {
            tableId?: string | null;
            selectedOrderType?: string;
        } = {}
    ) => {
        // Use order type from store, fallback to options parameter, then default to dine-in
        const selectedOrderType = orderStore.selectedOrderType || options.selectedOrderType || "dine-in";
        const { tableId = null } = options;

        // Check if product has options that require user selection (is_default = false)
        const hasNonDefaultOptions = product.options?.some((option: any) => !option.is_default);

        // Prepare default options to auto-add
        const defaultOptions =
            product.options
                ?.filter((option: any) => option.is_default)
                .map((option: any) => ({
                    option_id: option.id,
                    option_name: option.option_name,
                    max_quantity: option.max_quantity,
                    is_default: true,
                    items: buildOptionItemsPayload(option),
                })) || [];

        const hasDefaultChildItems = defaultOptions.some(
            (option: any) => option.items?.length
        );

        if (packaging) {
            // Packaging already selected, proceed
            if (hasNonDefaultOptions) {
                // Has options that need user selection, redirect to options page with packaging
                router.visit(
                    route("resto.product.options", {
                        product: product.id,
                        orderType: selectedOrderType,
                        tableId: tableId,
                        packagingId: packaging.id,
                    })
                );
            } else {
                // No user-selectable options or only default options, add to cart with packaging
                router.post(
                    route("resto.cart.add"),
                    {
                        quantity: 1,
                        product_id: product.id,
                        product_packaging_id: packaging.id,
                        table_id: tableId,
                        total_price: parseFloat(packaging.price || 0),
                        order_type: selectedOrderType,
                        selected_options: defaultOptions,
                        withParent: hasDefaultChildItems,
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
                    if (hasNonDefaultOptions) {
                        // Has options that need user selection, redirect to options page with packaging
                        router.visit(
                            route("resto.product.options", {
                                product: product.id,
                                orderType: selectedOrderType,
                                tableId: tableId,
                                packagingId: packaging.id,
                            })
                        );
                    } else {
                        // No user-selectable options or only default options, add to cart with packaging
                        router.post(
                            route("resto.cart.add"),
                            {
                                quantity: 1,
                                product_id: product.id,
                                product_packaging_id: packaging.id,
                                table_id: tableId,
                                total_price: parseFloat(packaging.price || 0),
                                order_type: selectedOrderType,
                                selected_options: defaultOptions,
                                withParent: hasDefaultChildItems,
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
                if (hasNonDefaultOptions) {
                    // Redirect to options page
                    router.visit(
                        route("resto.product.options", {
                            product: product.id,
                            orderType: selectedOrderType,
                            tableId: tableId,
                        })
                    );
                } else {
                    // No user-selectable options or only default options, add to cart without packaging
                    router.post(
                        route("resto.cart.add"),
                        {
                            quantity: 1,
                            product_id: product.id,
                            product_packaging_id: null,
                            table_id: tableId,
                            total_price: parseFloat(product.average_cost || "0"),
                            order_type: selectedOrderType,
                            selected_options: defaultOptions,
                            withParent: hasDefaultChildItems,
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
