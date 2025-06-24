<template>
  <aside class="w-64 bg-purple-700 text-white flex flex-col">
    <div class="p-4 text-2xl font-bold">QJPOS</div>
    <nav class="flex-1 px-2 space-y-2">
      <a
        v-for="item in navigation"
        :key="item.name"
        :href="item.href"
        :class="[
          item.current ? 'bg-purple-800' : 'hover:bg-purple-600',
          'group flex items-center px-2 py-2 text-sm font-medium rounded-md'
        ]"
      >
        <component :is="item.icon" class="h-6 w-6 mr-3" />
        {{ item.name }}
      </a>
    </nav>
    <div class="p-4">
      <a href="#" @click.prevent="logout" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-purple-600">
        <LogoutIcon class="h-6 w-6 mr-3" />
        Logout
      </a>
    </div>
  </aside>
</template>

<script setup>
import {
  HomeIcon,
  RectangleGroupIcon,
  RectangleStackIcon,
  TagIcon,
  ShieldCheckIcon,
  MapPinIcon,
  UsersIcon,
  ChartBarIcon,
  ArrowRightOnRectangleIcon as LogoutIcon,
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';

defineProps({
    logout: Function,
});

const navigation = ref([
  { name: 'Home', href: '#', icon: HomeIcon, current: true },
  { name: 'Products', href: '#', icon: RectangleGroupIcon, current: false },
  { name: 'Categories', href: '#', icon: RectangleStackIcon, current: false },
  { name: 'Brands', href: '#', icon: TagIcon, current: false },
  { name: 'Security', href: '#', icon: ShieldCheckIcon, current: false },
  { name: 'Branches', href: '#', icon: MapPinIcon, current: false },
  { name: 'Users', href: '#', icon: UsersIcon, current: false },
  { name: 'Reports', href: '#', icon: ChartBarIcon, current: false },
]);
</script>
