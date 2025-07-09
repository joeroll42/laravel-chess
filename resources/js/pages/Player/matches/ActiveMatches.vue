<script setup lang="ts">
import { reactive, ref } from 'vue';
import SidebarNav from '@/components/SidebarNav.vue';
import MobileNav from '@/components/MobileNav.vue';
import { Link } from '@inertiajs/vue3';
import PageHeading from '@/components/PageHeading.vue';

const props = defineProps(['challenges'])
const showMyChallanges = ref(false);

const challenges = reactive([...props.challenges])
const availableMatches = challenges.map((challenge) => {
    return {
        id:challenge.id,
        username: challenge.user.name,
        rank: 1220,
        tokens: challenge.tokens,
        stake: challenge.stake,
        timeControl: challenge.time_control,
    }
})

</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar for desktop -->
        <SidebarNav />

        <!-- Main content -->
        <main class="flex-1 p-6 space-y-6">
            <PageHeading :heading="'Active Challenges'"/>


            <!-- Tabs -->
            <div class="flex flex-col gap-2 mb-4">
                <Link as="button"
                      :href="route('matches.create-challenge')"
                    class="px-4 py-2 rounded bg-blue-600 text-white font-medium hover:bg-blue-700"
                    :class="{ 'opacity-50': showMyChallanges }"
                >
                    + Create Challenge
                </Link>
                <Link as="button" :href="route('matches.my-challenges')"
                    class="px-4 py-2 rounded bg-gray-200 text-gray-700 font-medium hover:bg-gray-300"
                    :class="{ 'bg-blue-100 text-blue-700': showMyChallanges }"
                >
                    My Challenges
                </Link>
            </div>

            <!-- Active Challenges -->
            <div v-if="showMyChallanges" class="space-y-4">
                <div
                    v-for="(challenge, i) in availableMatches"
                    :key="i"
                    class="flex justify-between items-center rounded-lg bg-white shadow p-4"
                >
                    <div>
                        <p class="font-semibold text-gray-800">{{ challenge.username }}</p>
                        <p class="text-sm text-gray-500">
                            Rank: {{ challenge.rank }} <br />
                            🎟️ {{ challenge.tokens }} Tokens · KES {{ challenge.stake }} <br />
                            Time Control: {{ challenge.timeControl }}
                        </p>
                    </div>
                    <div class="flex flex-col items-end space-y-1">
                        <span class="text-green-500 text-sm font-medium">● Online</span>
                        <button @click.prevent="console.log('here')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded text-sm">
                            Challenge
                        </button>
                    </div>
                </div>
            </div>

            <!-- Match List -->
            <div v-else class="space-y-3">
                <div
                    v-for="(match, i) in availableMatches"
                    :key="i"
                    class="flex justify-between items-center bg-white shadow-sm rounded-lg p-4"
                >
                    <div>
                        <p class="font-semibold text-gray-800">User: {{ match.username }}</p>
                        <p class="text-sm text-gray-500">
                            Rank: {{ match.rank }} · Tokens: 🎟️ {{ match.tokens }} · Stake: KES {{ match.stake }}
                        </p>
                    </div>
                    <Link as="button" :href="route('matches.challenge-details',[match.id])" class="bg-green-500 hover:bg-green-600 text-white px-4 py-1 rounded text-sm">
                        Challenge
                    </Link>
                </div>
            </div>
        </main>

        <!-- Bottom nav: mobile only -->
        <MobileNav />
    </div>
</template>
