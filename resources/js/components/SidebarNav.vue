<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const isActive = (routeName: string) => {
    const currentRoute = route().current();
    return currentRoute === routeName || currentRoute.startsWith(routeName + '.');
};
</script>

<template>
    <aside class="hidden w-64 flex-col space-y-6 bg-slate-900 px-4 py-6 text-white md:flex">
        <nav class="space-y-3">
            <h2 class="text-lg font-semibold">Dashboard</h2>
            <ul class="space-y-2">
                <li class="px-1 py-2">
                    <Link :href="route('dashboard')" :class="{ 'text-blue-400': isActive('dashboard') }">🏠 Dashboard </Link>
                </li>
                <li class="px-1 py-2">
                    <Link :href="route('matches.active')" :class="{ 'text-blue-400': isActive('matches') }">♟️ Matches </Link>
                </li>
                <li class="px-1 py-2">
                    <Link :href="route('wallet.main')" :class="{ 'text-blue-400': isActive('wallet') }">💳 Wallet</Link>
                </li>
                <li class="px-1 py-2">
                    <Link :href="route('notifications.list')" :class="{ 'text-blue-400': isActive('notifications') }">🔔 Notifications </Link>
                </li>
                <li class="px-1 py-2">
                    <Link :href="route('profile.edit')" :class="{ 'text-blue-400': isActive('profile') }">⚙️ Settings </Link>
                </li>
            </ul>
        </nav>
    </aside>
</template>
