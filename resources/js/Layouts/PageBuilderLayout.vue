<template>
    <MainLayout>
        <template #default>
            <slot />
        </template>
    </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from "./AppLayout.vue";
</script>
