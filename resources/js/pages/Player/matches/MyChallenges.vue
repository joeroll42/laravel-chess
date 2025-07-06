<script setup lang="ts">
import SidebarNav from '@/components/SidebarNav.vue';
import MobileNav from '@/components/MobileNav.vue';
import { Link } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const props = defineProps(['challenges']);

const showMyChallanges = ref(false);

const myChallenges = reactive([...props.challenges]);
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar for desktop -->
        <SidebarNav />

        <!-- Main content -->
        <main class="flex-1 space-y-6 p-6">
            <h1 class="text-2xl font-bold">My Challenges</h1>

            <!-- Tabs -->
            <div class="mb-4 flex flex-col gap-2">
                <Link
                    as="button"
                    :href="route('matches.create-challenge')"
                    @click="showMyChallanges = false"
                    class="rounded bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700"
                    :class="{ 'opacity-50': showMyChallanges }"
                >
                    + Create Challenge
                </Link>
                <Link
                    as="button"
                    :href="route('matches.active')"
                    @click="showMyChallanges = false"
                    class="rounded bg-gray-200 px-4 py-2 font-medium text-gray-700 hover:bg-gray-300"
                    :class="{ 'bg-blue-100 text-blue-700': showMyChallanges }"
                >
                    All Matches
                </Link>
            </div>

            <div class="grid gap-3">
                <Link as="div" v-for="challenge in myChallenges" :key="challenge.id" :href="route('matches.challenge-details',[challenge.id])" class="flex items-center justify-between rounded-lg bg-white p-3 shadow">
                    <div>
                        <p class="font-semibold text-gray-800">
                            <template v-if="challenge.opponent_id === $page.props.auth.user.id">
                                @{{ challenge.user?.name }}
                            </template>
                            <template v-else-if="challenge.opponent">
                                @{{ challenge.opponent?.name }}
                            </template>
                            <template v-else>
                                Waiting for opponent...
                            </template>
                        </p>
                        <p class="text-sm text-gray-600">Tokens 🎟️· {{ challenge.tokens }}  </p>
                        <p class="text-sm text-gray-600">Stake: KES {{ challenge.stake }}</p>
                        <p class="text-sm text-gray-500">Time Control: {{ challenge.time_control }}</p>
                    </div>

                    <div class="flex gap-3 self-start">
                        <span
                            class="rounded-full px-2 py-1 text-xs font-medium"
                            :class="{
                                'bg-yellow-100 text-yellow-800': challenge.request_state === 'pending',
                                'bg-green-100 text-green-700': challenge.request_state === 'accepted',
                            }"
                        >
                            {{ challenge.request_state }}
                        </span>
                    </div>
                </Link>
            </div>
        </main>

        <!-- Bottom nav: mobile only -->
        <MobileNav />
    </div>
</template>
