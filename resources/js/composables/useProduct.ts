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
    const showOpenPriceModal = ref(false);
    const pendingOpenPrice = ref<{
        payload: Record<string, any>;
        product: any;
        defaultPrice: number;
        options?: {
            onSuccess?: () => void;
            onError?: (errors: any) => void;
        };
    } | null>(null);

    const postAddToCart = (
        payload: Record<string, any>,
        product: any,
        basePrice: number,
        options: {
            force?: boolean;
            onSuccess?: () => void;
            onError?: (errors: any) => void;
        } = {},
    ) => {
        const force = options.force ?? false;

        if (!force && product?.open_price) {
            pendingOpenPrice.value = {
                payload,
                product,
                defaultPrice: basePrice,
                options,
            };
            showOpenPriceModal.value = true;
            return;
        }

        router.post(
            route("resto.cart.add"),
            payload,
            {
                preserveScroll: false,
                onSuccess: () => {
                    if (options.onSuccess) {
                        options.onSuccess();
                    } else {
                        toast.add({
                            severity: "success",
                            summary: "Success",
                            detail: page.props.flash.success,
                            life: 3000,
                        });
                    }
                },
                onError: (errors) => {
                    console.error("Failed to add item to cart:", errors);
                    options.onError?.(errors);
                },
            }
        );
    };

    const submitCartPayload = (
        payload: Record<string, any>,
        product: any,
        basePrice: number,
        options?: {
            force?: boolean;
            onSuccess?: () => void;
            onError?: (errors: any) => void;
        },
    ) => {
        postAddToCart(payload, product, basePrice, options);
    };

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
                    receipt_name:
                        item.product?.receipt_alias ||
                        item.product?.name ||
                        item.product_packaging?.unit_measure ||
                        item.product_packaging?.name ||
                        item.name ||
                        null,
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
                postAddToCart(
                    {
                        quantity: 1,
                        product_id: product.id,
                        product_packaging_id: packaging.id,
                        table_id: tableId,
                        total_price: parseFloat(packaging.price || 0),
                        order_type: selectedOrderType,
                        product_type: product.product_type,
                        selected_options: defaultOptions,
                        withParent: hasDefaultChildItems,
                    },
                    product,
                    parseFloat(packaging.price || 0),
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
                        postAddToCart(
                            {
                                quantity: 1,
                                product_id: product.id,
                                product_packaging_id: packaging.id,
                                table_id: tableId,
                                total_price: parseFloat(packaging.price || 0),
                                order_type: selectedOrderType,
                                product_type: product.product_type,
                                selected_options: defaultOptions,
                                withParent: hasDefaultChildItems,
                            },
                            product,
                            parseFloat(packaging.price || 0),
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
                    const basePrice = Number(product.price ?? product.average_cost ?? 0);

                    postAddToCart(
                        {
                            quantity: 1,
                            product_id: product.id,
                            product_packaging_id: null,
                            table_id: tableId,
                            total_price: basePrice,
                            order_type: selectedOrderType,
                            product_type: product.product_type,
                            selected_options: defaultOptions,
                            withParent: hasDefaultChildItems,
                        },
                        product,
                        basePrice,
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

    const handleOpenPriceConfirm = (payload: { price: number; approverEmail: string; otpCode: string }) => {
        if (!pendingOpenPrice.value) return;

        const { payload: basePayload, product, options } =
            pendingOpenPrice.value;

        postAddToCart(
            {
                ...basePayload,
                override_price: payload.price,
                approver_email: payload.approverEmail,
                otp_code: payload.otpCode,
            },
            product,
            payload.price,
            {
                ...(options || {}),
                force: true,
                onSuccess: () => {
                    showOpenPriceModal.value = false;
                    pendingOpenPrice.value = null;

                    if (options?.onSuccess) {
                        options.onSuccess();
                    } else {
                        toast.add({
                            severity: "success",
                            summary: "Success",
                            detail: page.props.flash.success,
                            life: 3000,
                        });
                    }
                },
                onError: (errors: any) => {
                    const firstError = (val: any) => {
                        if (Array.isArray(val)) return val[0];
                        if (typeof val === "string") return val;
                        if (val && typeof val.message === "string") return val.message;
                        return null;
                    };

                    const message =
                        firstError(errors?.approver_email) ||
                        firstError(errors?.otp_code) ||
                        firstError(errors?.override_price) ||
                        "Failed to add item to cart.";

                    toast.add({
                        severity: "error",
                        summary: "Approval Failed",
                        detail: message,
                        life: 3500,
                    });

                    // Keep modal open and payload intact for retry
                    showOpenPriceModal.value = true;
                    pendingOpenPrice.value = {
                        payload: basePayload,
                        product,
                        defaultPrice: payload.price,
                        options,
                    };

                    options?.onError?.(errors);
                },
            },
        );
    };

    const handleOpenPriceCancel = () => {
        showOpenPriceModal.value = false;
        pendingOpenPrice.value = null;
    };

    return {
        addToCart,
        showPackagingModal,
        selectedProductForPackaging,
        handlePackagingConfirm,
        handlePackagingCancel,
        showOpenPriceModal,
        pendingOpenPrice,
        handleOpenPriceConfirm,
        handleOpenPriceCancel,
        submitCartPayload,
    };
};
