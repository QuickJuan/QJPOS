<template>
    <Dialog
        v-model:visible="props.showSessionSummaryModal"
        modal
        header=""
        :style="{ width: '42rem' }"
        class="bg-white"
        @hide="handleClose"
        @update:visible="handleClose"
    >
        <div
            class="bg-white p-4 rounded border font-mono text-xs text-center"
            ref="printArea"
        >
            <!-- Header -->
            <div class="mb-2 flex flex-col items-center">
                <div v-if="currentBranch?.company_logo">
                    <img
                        :src="currentBranch?.company_logo"
                        :alt="page.props.company_info.company_name"
                        class="w-16 h-auto mx-auto mb-2"
                    />
                </div>
                <p class="font-bold text-base">
                    {{ page.props.company_info.company_name }}
                </p>
                <p class="text-xs">
                    {{ currentBranch?.name }}
                </p>
                <p class="text-xs">
                    {{ currentBranch?.address }}
                </p>
                <p class="text-xs">
                    {{ currentBranch?.phone }}
                </p>
                <p class="text-xs" v-if="currentBranch?.tin">
                    TIN: {{ currentBranch?.tin }}
                </p>
                <p class="text-xs" v-if="currentBranch.registration_number">
                    Registration No.: {{ currentBranch?.registration_number }}
                </p>
            </div>

            <div class="border-t my-4"></div>

            <!-- Body / Content -->
            <div class="text-center font-bold">X Reading Report</div>
            <div>
                <p>Shift No: {{ sessionSummary?.id }}</p>
                <p>Cashier: {{ sessionSummary?.cashier }}</p>
            </div>
            <div class="text-center text-xs mb-2">
                {{ sessionSummary?.shift_start }} -
                {{ sessionSummary?.shift_end }}
            </div>

            <div class="text-left text-xs">
                <div class="flex justify-between">
                    <span class="font-bold">Gross Sales:</span>
                    <span>{{ formatMoney(grossSales) }}</span>
                </div>

                <!-- Discount Breakdown -->
                <div
                    v-if="
                        props.sessionSummary?.meta_data?.discounts &&
                        props.sessionSummary.meta_data.discounts.length > 0
                    "
                    class="mt-2"
                >
                    <div class="font-bold mb-1">DISCOUNT BREAKDOWN</div>
                    <div class="border-t border-b py-1">
                        <div
                            v-for="discount in props.sessionSummary.meta_data
                                .discounts"
                            :key="discount.discount_name"
                            class="flex justify-between ml-2"
                        >
                            <span>{{ discount.discount_name }}:</span>
                            <span>
                                {{ formatMoney(discount.total_discount * -1) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-2">
                    <span>Total Discount:</span>
                    <span>{{
                        formatMoney(
                            (props.sessionSummary?.meta_data?.item_discount ||
                                0) * -1
                        )
                    }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Less Tax:</span>
                    <span>
                        {{
                            formatMoney(
                                (props.sessionSummary?.meta_data?.less_tax ||
                                    0) * -1
                            )
                        }}
                    </span>
                </div>
                <div class="flex justify-between font-bold border-t pt-1 mt-1">
                    <span>Net Sales:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.meta_data?.net_sales
                            )
                        }}
                    </span>
                </div>
                <div class="flex justify-between font-bold border-b pt-1 mt-1">
                    <span>Service Charge:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.meta_data?.service_charge
                            )
                        }}
                    </span>
                </div>
                <!-- total cash net sales + service_charge -->
                <div class="flex justify-between font-bold border-b py-1 mt-1">
                    <span>Total Cash :</span>
                    <span>
                        {{
                            formatMoney(
                                (props.sessionSummary?.meta_data?.net_sales ||
                                    0) +
                                    (props.sessionSummary?.meta_data
                                        ?.service_charge || 0)
                            )
                        }}
                    </span>
                </div>

                <div class="mt-2"></div>

                <div
                    class="flex justify-between"
                    v-if="props.sessionSummary?.meta_data?.vatable_sales > 0"
                >
                    <span>VATable Sales:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.meta_data?.vatable_sales
                            )
                        }}
                    </span>
                </div>
                <div
                    class="flex justify-between"
                    v-if="props.sessionSummary?.meta_data?.vat_amount > 0"
                >
                    <span>VAT Amount:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.meta_data?.vat_amount
                            )
                        }}
                    </span>
                </div>
                <div
                    class="flex justify-between"
                    v-if="props.sessionSummary?.meta_data?.vat_exempt_sales > 0"
                >
                    <span>VAT Exempt Sales:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.meta_data
                                    ?.vat_exempt_sales
                            )
                        }}
                    </span>
                </div>
                <div
                    class="flex justify-between"
                    v-if="props.sessionSummary?.meta_data?.non_vat_sales > 0"
                >
                    <span>Non-VAT Sales:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.meta_data?.non_vat_sales
                            )
                        }}
                    </span>
                </div>
                <div
                    class="flex justify-between"
                    v-if="props.sessionSummary?.meta_data?.zero_rated_sales > 0"
                >
                    <span>Zero-Rated Sales:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.meta_data
                                    ?.zero_rated_sales
                            )
                        }}
                    </span>
                </div>

                <div class="border-t my-2"></div>

                <div class="text-left text-xs">
                    <div class="flex justify-between">
                        <span>Shift No:</span>
                        <span>{{ props.sessionSummary?.id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Invoice Start:</span>
                        <span>{{
                            props.sessionSummary?.meta_data?.min_invoice_no ||
                            "N/A"
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Invoice End:</span>
                        <span>{{
                            props.sessionSummary?.meta_data?.max_invoice_no ||
                            "N/A"
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Bill Start:</span>
                        <span>{{
                            props.sessionSummary?.meta_data?.min_bill_no ||
                            "N/A"
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Bill End:</span>
                        <span>{{
                            props.sessionSummary?.meta_data?.max_bill_no ||
                            "N/A"
                        }}</span>
                    </div>
                </div>

                <div class="border-t my-2"></div>

                <div class="flex justify-between">
                    <span>Refund Count:</span>
                    <span>
                        {{ props.sessionSummary?.meta_data?.refund_count || 0 }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Refund Amount:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.meta_data
                                    ?.refund_amount || 0
                            )
                        }}
                    </span>
                </div>

                <div class="border-t my-2"></div>

                <div class="flex justify-between">
                    <span>Total Orders:</span>
                    <span>
                        {{ props.sessionSummary?.meta_data?.total_orders || 0 }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Total SKU:</span>
                    <span>{{
                        props.sessionSummary?.meta_data?.total_sku || 0
                    }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Total Quantity:</span>
                    <span>{{
                        props.sessionSummary?.meta_data?.total_quantity || 0
                    }}</span>
                </div>

                <div class="border-t my-2"></div>

                <div class="flex justify-between">
                    <span>Beginning Cash:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.beginning_cash || 0
                            )
                        }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span>Cash Denomination:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.cash_denomination_total ||
                                    0
                            )
                        }}
                    </span>
                </div>

                <div class="flex justify-between" v-if="giftCheckTotal > 0">
                    <span>Gift Checks:</span>
                    <span>{{ formatMoney(giftCheckTotal) }}</span>
                </div>

                <div class="flex justify-between">
                    <span>Expected Cash:</span>
                    <span>
                        {{ formatMoney(expectedCash) }}
                    </span>
                </div>
                <div class="border-t my-2"></div>
                <div class="flex justify-between">
                    <span>Variance:</span>
                    <span>
                        {{ variance < 0 ? "-" : "" }}
                        {{ formatMoney(variance) }}
                    </span>
                </div>

                <CashBreakdown
                    v-if="props.sessionSummary?.cash_denomination_details"
                    class="mt-4"
                    :cash-denomination-details="
                        props.sessionSummary.cash_denomination_details
                    "
                />

                <div class="border-t my-2"></div>

                <div class="text-center font-bold">X Reading End</div>
            </div>
        </div>
    </Dialog>

    <ConfirmPopup />
    <Toast />
</template>

<script setup lang="ts">
import CashBreakdown from "@/Components/Resto/CashBreakdown.vue";
import { formatMoney } from "@/Utils/FormatMoney";
import { usePage } from "@inertiajs/vue3";
import { Button, Dialog, ConfirmPopup, Toast, useToast } from "primevue";
import { computed } from "vue";
import { thermalPrinter } from "@/Services/ThermalPrinterService";
import Branch from "@/Types/Branch";

const props = defineProps<{
    showSessionSummaryModal: boolean;
    sessionSummary?: any;
}>();

const emit = defineEmits(["closeModal"]);
const page = usePage();

const handleClose = () => {
    emit("closeModal");
};

const reportDate = computed(() => new Date().toLocaleDateString());

const grossSales = computed(
    () => props.sessionSummary?.meta_data?.gross_sales ?? 0
);

const currentBranch = computed(() => props.sessionSummary.branch as Branch);
const currentCashier = (page.props.auth as any)?.user?.name;

const expectedCash = computed(() => {
    const netSales = props.sessionSummary?.meta_data?.net_sales || 0;
    const serviceCharge = props.sessionSummary?.meta_data?.service_charge || 0;
    return netSales + serviceCharge;
});

const variance = computed(() => {
    const cashDenominationTotal =
        props.sessionSummary?.cash_denomination_total || 0;
    const beginningCash = props.sessionSummary?.beginning_cash || 0;
    const expectedCashValue = expectedCash.value;

    return cashDenominationTotal - expectedCashValue;
});

const giftCheckTotal = computed(() => {
    const details = props.sessionSummary?.cash_denomination_details as any;
    if (!details) return 0;
    return (
        Number(
            details.gift_check_total ?? details.totals?.gift_check_in_base ?? 0
        ) || 0
    );
});
</script>
