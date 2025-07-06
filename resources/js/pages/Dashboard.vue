<script setup lang="ts">
import SidebarNav from '@/components/SidebarNav.vue';
import MobileNav from '@/components/MobileNav.vue';

const props = defineProps(['user'])

const balance = props.user.balance;
const tokens = props.user.token_balance;
const recentMatches = [
    { opponent: '@opponent123', result: 'Loss', platform: 'Lichess', tokens: 2, stake: 0, date: 'Jun 29' },
    { opponent: '@kenmaster', result: 'Win', platform: 'Chess.com', tokens: 2, stake: 70, date: 'Jun 28' },
];


</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar for desktop -->
        <SidebarNav />

        <!-- Main content -->
        <main class="flex-1 p-2 space-y-6">
            <h1 class="text-2xl font-bold">Your Dashboard</h1>
            <p>Welcome, {{user.name}}</p>

            <!-- Wallet + Tokens -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-lg bg-white shadow p-4">
                    <p class="text-sm text-gray-500">Wallet Balance</p>
                    <p class="text-xl font-semibold">KES {{ balance.toLocaleString() }}</p>
                </div>
                <div class="rounded-lg bg-white shadow p-4">
                    <p class="text-sm text-gray-500">Game Tokens</p>
                    <p class="text-xl font-semibold text-pink-600">🎟️ {{ tokens }}</p>
                </div>
            </div>

            <!-- Recent Matches -->
            <div>
                <h2 class="text-lg font-medium mb-3">Recent Matches</h2>
                <div class="space-y-2">
                    <div
                        v-for="(match, i) in recentMatches"
                        :key="i"
                        class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm"
                    >
                        <div>
                            <p class="font-semibold">vs. {{ match.opponent }}</p>
                            <p class="text-sm text-gray-500">
                                {{ match.platform }} · 🎟️ {{ match.tokens }} · KES {{ match.stake }} · {{ match.date }}
                            </p>
                        </div>
                        <span
                            class="px-2 py-1 text-sm rounded-full font-medium"
                            :class="{
                'bg-green-100 text-green-600': match.result === 'Win',
                'bg-red-100 text-red-600': match.result === 'Loss',
              }"
                        >
              {{ match.result }}
            </span>
                    </div>
                </div>
            </div>
        </main>

        <!-- Bottom nav: mobile only -->
        <MobileNav />
    </div>
</template>
