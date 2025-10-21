<template>
    <div
        :class="wrapperClasses"
        :style="{
            width: width + 'px',
            height: height + 'px',
            borderColor: borderColor,
        }"
    >
        <slot />
        <!-- Seats -->
        <template v-for="seat in seatLayout" :key="seat.key">
            <div
                :style="{
                    top: seat.top + 'px',
                    left: seat.left + 'px',
                }"
                class="absolute w-4 h-2 rounded bg-gray-300"
            />
        </template>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
    chairs: number;
    status?: string;
    width: number;
    height: number;
}>();

const statusColors: Record<string, string> = {
    occupied: "#F59E0B", // amber
    reserved: "#D97706", // darker amber
    vacant: "#10B981", // green
};

const borderColor = computed(
    () => statusColors[props.status || "vacant"] || "#9CA3AF"
);

// Simple seat layout generator around perimeter
const seatLayout = computed(() => {
    const seats: { key: string; top: number; left: number }[] = [];
    const pad = 6;
    const horizontalCount = Math.min(props.chairs, Math.ceil(props.chairs / 2));
    const verticalCount = props.chairs - horizontalCount;
    const w = props.width;
    const h = props.height;
    // Top row
    for (let i = 0; i < Math.min(horizontalCount, 4); i++) {
        seats.push({
            key: "top-" + i,
            top: pad,
            left: (w / (Math.min(horizontalCount, 4) + 1)) * (i + 1) - 10,
        });
    }
    // Bottom row
    for (let i = 0; i < Math.min(horizontalCount, 4); i++) {
        seats.push({
            key: "bottom-" + i,
            top: h - pad - 8,
            left: (w / (Math.min(horizontalCount, 4) + 1)) * (i + 1) - 10,
        });
    }
    // Left side
    for (let i = 0; i < Math.min(verticalCount, 4); i++) {
        seats.push({
            key: "left-" + i,
            top: (h / (Math.min(verticalCount, 4) + 1)) * (i + 1) - 6,
            left: pad,
        });
    }
    // Right side
    for (let i = 0; i < Math.min(verticalCount, 4); i++) {
        seats.push({
            key: "right-" + i,
            top: (h / (Math.min(verticalCount, 4) + 1)) * (i + 1) - 6,
            left: w - pad - 16,
        });
    }
    return seats;
});

const wrapperClasses = computed(
    () =>
        "relative rounded-xl border-4 bg-white flex items-center justify-center"
);
</script>

<style scoped></style>
