<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const isActive = (routeName: string) => {
    const currentRoute = route().current();
    return currentRoute === routeName || currentRoute.startsWith(routeName + '.');
};
</script>

<template>
    <nav class="fixed bottom-0 left-0 right-0 z-50 flex justify-around bg-white border-t py-3 shadow md:hidden">
        <Link :href="route('dashboard')" class="flex flex-col items-center text-xl" :class="{ 'text-blue-600': isActive('dashboard') }">🏠</Link>
        <Link :href="route('matches.active')" class="flex flex-col items-center text-xl" :class="{ 'text-blue-600': isActive('matches') }">♟️</Link>
        <Link :href="route('wallet.main')" class="flex flex-col items-center text-xl" :class="{ 'text-blue-600': isActive('wallet') }">💳</Link>
        <Link :href="route('notifications.list')" class="flex flex-col items-center text-xl" :class="{ 'text-blue-600': isActive('notifications') }">🔔</Link>
        <Link :href="route('profile.edit')" class="flex flex-col items-center text-xl" :class="{ 'text-blue-600': isActive('profile') }">⚙️</Link>
    </nav>
</template>
