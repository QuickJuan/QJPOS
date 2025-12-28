<template>
    <TableShapeBase
        :chairs="2"
        :status="status"
        :width="width"
        :height="height"
    >
        <slot />
    </TableShapeBase>
</template>
<script setup lang="ts">
import TableShapeBase from "./TableShapeBase.vue";
defineProps<{ status?: string; width: number; height: number }>();
</script>
