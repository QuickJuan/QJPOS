<template>
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto">
            <!-- Top: Header (keeps existing Header component) -->
            <Header />

            <main>
                <slot />
            </main>
        </div>

        <!-- Toast Notifications -->
        <Toast />
        <ConfirmPopup />
    </div>
</template>
<script setup lang="ts">
import Header from "@/Pages/RetailCashier/Partials/Header.vue";
import { ConfirmPopup, Toast } from "primevue";
</script>
