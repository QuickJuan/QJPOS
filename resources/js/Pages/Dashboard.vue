<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, onMounted } from "vue";
import {
    BellIcon,
    CogIcon,
    ChevronDownIcon,
    ChevronRightIcon,
    MagnifyingGlassIcon as SearchIcon,
} from "@heroicons/vue/24/outline";
import Chart from "chart.js/auto";

const enabled = ref(false);

const devices = ref([
    { name: "Refridgerator", on: true, color: "bg-purple-500" },
    { name: "Router", on: true, color: "bg-yellow-500" },
    { name: "Music System", on: true, color: "bg-orange-500" },
    { name: "Lamps", on: true, color: "bg-cyan-500" },
]);

const homeControls = ref([
    { name: "Refridgerator", on: true, icon: "⚡️" },
    { name: "Temperature", on: true, icon: "🌡️" },
    { name: "Air Conditioner", on: false, icon: "💨" },
    { name: "Lights", on: false, icon: "💡" },
]);

const members = [
    { name: "Scarlett", role: "Admin", avatar: "/images/auth-image.jpg" },
    { name: "Nariya", role: "Full Access", avatar: "/images/auth-image.jpg" },
    { name: "Riya", role: "Full Access", avatar: "/images/auth-image.jpg" },
    { name: "Dad", role: "Full Access", avatar: "/images/auth-image.jpg" },
    { name: "Mom", role: "Full Access", avatar: "/images/auth-image.jpg" },
];

onMounted(() => {
    const ctx = document.getElementById("powerChart");
    if (ctx) {
        new Chart(ctx, {
            type: "line",
            data: {
                labels: [
                    "Jan",
                    "Feb",
                    "Mar",
                    "Apr",
                    "May",
                    "June",
                    "July",
                    "Aug",
                ],
                datasets: [
                    {
                        label: "Electricity Consumed",
                        data: [20, 25, 40, 30, 50, 45, 75, 73],
                        borderColor: "rgba(249, 115, 22, 0.8)",
                        backgroundColor: "rgba(251, 146, 60, 0.2)",
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: (context) => {
                            const index = context.dataIndex;
                            return index === 6
                                ? "rgba(249, 115, 22, 1)"
                                : "rgba(255, 255, 255, 0)";
                        },
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return value + "%";
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                },
            },
        });
    }
});
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="p-8 bg-gray-50 min-h-screen">
            <div class="flex justify-between items-center mb-8">
                <div class="relative">
                    <SearchIcon
                        class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                    />
                    <input
                        type="text"
                        placeholder="Search"
                        class="pl-10 pr-4 py-2 rounded-lg bg-white shadow-sm w-80"
                    />
                </div>
                <div class="flex items-center space-x-4">
                    <CogIcon class="h-6 w-6 text-gray-500" />
                    <BellIcon class="h-6 w-6 text-gray-500" />
                    <div class="flex items-center space-x-2">
                        <img
                            src="/images/auth-image.jpg"
                            alt="Scarlett"
                            class="h-8 w-8 rounded-full"
                        />
                        <span class="font-semibold">Scarlett</span>
                        <ChevronDownIcon class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-8">
                <div class="col-span-2">
                    <div
                        class="bg-yellow-100 p-6 rounded-2xl flex items-center justify-between mb-8"
                    >
                        <div>
                            <h1 class="text-2xl font-bold">Hello, Scarlett!</h1>
                            <p class="text-gray-600">
                                Welcome Home! The air quality is good & fresh
                                you can go out today.
                            </p>
                            <div class="mt-4 flex items-center space-x-8">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-2">🌡️</span>
                                    <div>
                                        <p class="font-bold">+25°C</p>
                                        <p class="text-sm text-gray-600">
                                            Outdoor temperature
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-2xl mr-2">☁️</span>
                                    <div>
                                        <p class="font-bold">Fuzzy cloudy</p>
                                        <p class="text-sm text-gray-600">
                                            Weather
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <img
                            src="/images/auth-image.jpg"
                            alt="Illustration"
                            class="h-40"
                        />
                    </div>

                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold">Scarlett's Home</h2>
                            <div
                                class="flex items-center space-x-2 text-sm text-gray-500"
                            >
                                <span>35% 💧</span>
                                <span>15℃ 🌡️</span>
                                <div
                                    class="flex items-center bg-white p-1 rounded-lg"
                                >
                                    <span>Living Room</span>
                                    <ChevronDownIcon class="h-4 w-4" />
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-4 gap-4">
                            <div
                                v-for="(control, index) in homeControls"
                                :key="control.name"
                                :class="[
                                    control.on
                                        ? 'bg-purple-600 text-white'
                                        : 'bg-white',
                                    'p-4 rounded-2xl shadow-sm cursor-pointer',
                                ]"
                                @click="
                                    homeControls[index].on =
                                        !homeControls[index].on
                                "
                            >
                                <div class="flex justify-between items-start">
                                    <span class="text-2xl">{{
                                        control.icon
                                    }}</span>
                                    <div
                                        class="w-11 h-6 rounded-full relative"
                                        :class="[
                                            control.on
                                                ? 'bg-white'
                                                : 'bg-gray-200',
                                        ]"
                                    >
                                        <div
                                            class="w-4 h-4 rounded-full absolute top-1 transition-transform"
                                            :class="[
                                                control.on
                                                    ? 'translate-x-6 bg-purple-600'
                                                    : 'translate-x-1 bg-white',
                                            ]"
                                        ></div>
                                    </div>
                                </div>
                                <p class="mt-4 font-semibold">
                                    {{ control.name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold">
                                Living Room Temperature
                            </h2>
                            <div
                                class="w-11 h-6 rounded-full relative cursor-pointer"
                                :class="[
                                    enabled ? 'bg-purple-600' : 'bg-gray-200',
                                ]"
                                @click="enabled = !enabled"
                            >
                                <div
                                    class="w-4 h-4 rounded-full absolute top-1 bg-white transition-transform"
                                    :class="[
                                        enabled
                                            ? 'translate-x-6'
                                            : 'translate-x-1',
                                    ]"
                                ></div>
                            </div>
                        </div>
                        <div
                            class="bg-white p-6 rounded-2xl text-center relative"
                        >
                            <div class="inline-block relative">
                                <div
                                    class="w-48 h-48 rounded-full border-[16px] border-gray-200 flex items-center justify-center"
                                >
                                    <div class="text-4xl font-bold">
                                        25°<span class="text-lg">C</span>
                                    </div>
                                </div>
                                <div
                                    class="absolute top-0 left-0 w-48 h-48"
                                    style="transform: rotate(-90deg)"
                                >
                                    <svg
                                        class="w-full h-full"
                                        viewBox="0 0 100 100"
                                    >
                                        <circle
                                            cx="50"
                                            cy="50"
                                            r="42"
                                            stroke="url(#temp-gradient)"
                                            stroke-width="8"
                                            fill="none"
                                            stroke-linecap="round"
                                            stroke-dasharray="180"
                                            stroke-dashoffset="0"
                                        ></circle>
                                        <defs>
                                            <linearGradient
                                                id="temp-gradient"
                                                x1="0%"
                                                y1="0%"
                                                x2="100%"
                                                y2="0%"
                                            >
                                                <stop
                                                    offset="0%"
                                                    stop-color="#8B5CF6"
                                                />
                                                <stop
                                                    offset="100%"
                                                    stop-color="#F87171"
                                                />
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                </div>
                            </div>
                            <div
                                class="flex justify-between items-center mt-4 w-56 mx-auto"
                            >
                                <button class="bg-gray-200 p-2 rounded-lg">
                                    -
                                </button>
                                <div class="text-sm">
                                    <span class="text-red-500">25°C</span>
                                    <span class="text-gray-400 mx-2">|</span>
                                    <span class="text-purple-500">05°C</span>
                                </div>
                                <button
                                    class="bg-purple-600 text-white p-2 rounded-lg"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-1 space-y-8">
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold">My Devices</h2>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm">ON</span>
                                <ChevronDownIcon class="h-4 w-4" />
                                <button class="bg-white p-1 rounded-lg">
                                    <ChevronRightIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div
                                v-for="(device, index) in devices"
                                :key="device.name"
                                :class="[
                                    device.color,
                                    'p-4 rounded-2xl text-white cursor-pointer',
                                ]"
                                @click="devices[index].on = !devices[index].on"
                            >
                                <div class="flex justify-between items-start">
                                    <p class="font-semibold">
                                        {{ device.name }}
                                    </p>
                                    <div
                                        class="w-11 h-6 rounded-full relative"
                                        :class="[
                                            device.on
                                                ? 'bg-white/50'
                                                : 'bg-black/20',
                                        ]"
                                    >
                                        <div
                                            class="w-4 h-4 rounded-full absolute top-1 bg-white transition-transform"
                                            :class="[
                                                device.on
                                                    ? 'translate-x-6'
                                                    : 'translate-x-1',
                                            ]"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold">Members</h2>
                            <button class="bg-white p-1 rounded-lg">
                                <ChevronRightIcon class="h-5 w-5" />
                            </button>
                        </div>
                        <div class="bg-white p-4 rounded-2xl">
                            <div
                                v-for="member in members"
                                :key="member.name"
                                class="flex items-center justify-between py-2"
                            >
                                <div class="flex items-center">
                                    <img
                                        :src="member.avatar"
                                        :alt="member.name"
                                        class="h-10 w-10 rounded-full mr-3"
                                    />
                                    <div>
                                        <p class="font-semibold">
                                            {{ member.name }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ member.role }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    class="w-2 h-2 rounded-full"
                                    :class="{
                                        'bg-green-400': member.role !== 'Admin',
                                        'bg-purple-500':
                                            member.role === 'Admin',
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold">Power Consumed</h2>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm">Month</span>
                                <ChevronDownIcon class="h-4 w-4" />
                                <button class="bg-white p-1 rounded-lg">
                                    <ChevronRightIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded-2xl">
                            <div
                                class="flex justify-between items-center text-sm mb-2"
                            >
                                <p>Electricity Consumed</p>
                                <p class="font-semibold">73% Spending</p>
                            </div>
                            <div class="h-32">
                                <canvas id="powerChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
