<template>
    <Dialog
        v-model:visible="props.showSessionSummaryModal"
        modal
        header=""
        :style="{ width: '22rem' }"
        class="bg-white"
        @hide="handleClose"
        @update:visible="handleClose"
    >
        <div
            class="bg-white p-4 rounded border font-mono text-xs text-center"
            ref="printArea"
        >
            <div class="mb-2">
                <p class="font-bold text-sm">
                    {{ props.merchantName || "QUICKJUAN POS" }}
                </p>
                <p class="text-xs">
                    {{ currentBranch?.address || "" }}
                </p>
                <p class="text-xs">
                    Operated By:
                    {{ currentCashier || "" }}
                </p>
                <p class="text-xs" v-if="currentBranch?.tin">
                    TIN: {{ currentBranch?.tin }}
                </p>
                <p class="text-xs" v-if="currentBranch.registration_number">
                    Registration No.: {{ currentBranch?.registration_number }}
                </p>
            </div>

            <div class="border-t my-2"></div>

            <div class="text-center font-bold">X Reading Report</div>
            <div class="text-center text-xs mb-2">{{ reportDate }}</div>

            <div class="text-left text-xs">
                <div class="flex justify-between">
                    <span>Gross Sales:</span>
                    <span>{{ formatMoney(grossSales) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Regular Discount:</span>
                    <span>{{ formatMoney(regularDiscount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Senior Discount:</span>
                    <span>{{ formatMoney(seniorDiscount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>PWD Discount:</span>
                    <span>{{ formatMoney(pwdDiscount) }}</span>
                </div>
                <div class="flex justify-between font-bold border-t pt-1 mt-1">
                    <span>Net Sales:</span>
                    <span>{{ formatMoney(netSales) }}</span>
                </div>

                <div class="mt-2"></div>
                <div class="flex justify-between">
                    <span>Non-VAT Sales:</span>
                    <span>{{ formatMoney(nonVatSales) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>VAT Sales:</span>
                    <span>{{ formatMoney(vatSales) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>VAT:</span>
                    <span>{{ formatMoney(vatAmount) }}</span>
                </div>

                <div class="border-t my-2"></div>

                <div class="flex justify-between">
                    <span>Counter ID Start:</span>
                    <span>{{
                        props.openSession?.counter_id_start || "-"
                    }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Counter ID End:</span>
                    <span>{{ props.openSession?.counter_id_end || "-" }}</span>
                </div>

                <div class="border-t my-2"></div>

                <div class="flex justify-between">
                    <span>Cancelled Tx:</span>
                    <span>{{
                        props.sessionSummary?.cancelled_count || 0
                    }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Cancelled Amount:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.cancelled_amount || 0
                            )
                        }}
                    </span>
                </div>

                <div class="border-t my-2"></div>

                <div class="flex justify-between">
                    <span>No of Transactions:</span>
                    <span>
                        {{ props.sessionSummary?.transactions_count || 0 }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Number of SKU:</span>
                    <span>{{ props.sessionSummary?.sku_count || 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Total Quantity:</span>
                    <span>{{ props.sessionSummary?.total_quantity || 0 }}</span>
                </div>

                <div class="border-t my-2"></div>

                <div class="flex justify-between">
                    <span>Previous Reading:</span>
                    <span>
                        {{
                            formatMoney(
                                props.sessionSummary?.previous_reading || 0
                            )
                        }}
                    </span>
                </div>
                <div class="flex justify-between font-bold">
                    <span>Net Sales:</span>
                    <span>{{ formatMoney(netSales) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Running Total:</span>
                    <span>{{ formatMoney(runningTotal) }}</span>
                </div>

                <div class="border-t my-2"></div>

                <div class="text-center font-bold">X Reading End</div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button
                    type="button"
                    label="Close"
                    severity="secondary"
                    @click="emit('closeModal')"
                />
                <Button
                    type="button"
                    label="Print"
                    @click="printReport"
                    class="p-button-primary"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { formatMoney } from "@/Utils/FormatMoney";
import { usePage } from "@inertiajs/vue3";
import { Button, Dialog } from "primevue";
import { computed, ref } from "vue";

const props = defineProps<{
    showSessionSummaryModal: boolean;
    openSession: any;
    sessionSummary?: any;
    currentUser?: any;
    totalCashCounted?: number;
    merchantName?: string;
    merchantAddress?: string;
    operatorName?: string;
    merchantTin?: string;
}>();

const emit = defineEmits(["closeModal", "confirmClose"]);
const page = usePage();

const handleClose = () => {
    emit("closeModal");
};

const printArea = ref<HTMLElement | null>(null);

const printReport = () => {
    const contentEl = printArea.value;
    if (!contentEl) {
        window.print();
        return;
    }

    const w = (window as any).open("", "_blank", "width=600,height=800");
    if (!w) {
        window.print();
        return;
    }

    const styles = Array.from(
        document.querySelectorAll('link[rel="stylesheet"], style')
    )
        .map((n) => n.outerHTML)
        .join("\n");

    w.document.write(
        `<!doctype html><html><head><meta charset="utf-8"><title>Session Summary</title>${styles}</head><body>`
    );
    w.document.write(contentEl.innerHTML);
    w.document.write("</body></html>");
    w.document.close();
    w.focus();
    setTimeout(() => {
        w.print();
        // keep window open so user can choose to close; optionally close automatically
        // w.close();
    }, 500);
};

const reportDate = computed(() => new Date().toLocaleDateString());

const grossSales = computed(
    () =>
        props.sessionSummary?.gross_sales ??
        props.sessionSummary?.total_sales ??
        0
);
const regularDiscount = computed(
    () =>
        props.sessionSummary?.regular_discount ??
        props.sessionSummary?.regular_discount_amount ??
        0
);
const seniorDiscount = computed(
    () => props.sessionSummary?.senior_discount ?? 0
);
const pwdDiscount = computed(() => props.sessionSummary?.pwd_discount ?? 0);
const netSales = computed(
    () =>
        props.sessionSummary?.net_sales ??
        props.sessionSummary?.total_sales ??
        0
);
const nonVatSales = computed(() => props.sessionSummary?.non_vat_sales ?? 0);
const vatSales = computed(
    () => props.sessionSummary?.vat_sales ?? netSales.value
);
const vatAmount = computed(
    () =>
        props.sessionSummary?.vat_amount ??
        props.sessionSummary?.vat ??
        vatSales.value * 0.12
);
const runningTotal = computed(() => props.sessionSummary?.running_total ?? 0);

const currentBranch = page.props.active_branch;
const currentCashier = page.props.auth.user?.name;
</script>
