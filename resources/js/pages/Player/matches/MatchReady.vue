<script setup lang="ts">
import SidebarNav from '@/components/SidebarNav.vue';
import MobileNav from '@/components/MobileNav.vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps(['challenge']);

const match = {
    id: props.challenge.id,
    challenger: props.challenge.user.name,
    opponent: props.challenge.opponent.name,
    tokens: props.challenge.tokens,
    stake: props.challenge.stake,
    timeControl: props.challenge.time_control,
    status: 'Funds Secured in Escrow',
    platform: props.challenge.platform.link,
};

const handleGetMatchResults = async () => {
    try {
        const resp = await axios.get(route('matches.get-results', [match.id]));

        if (resp.data.status === 'success') {
            const resultStatus = resp.data.resultStatus;

            if (resultStatus === 'pending') {
                alert("Match results are still being processed. Please try again in a few minutes.");
            } else if (resultStatus === 'success') {
                router.visit(route('matches.results', [match.id]));
            }
        } else {
            console.warn('Unexpected status:', resp.data.status);
        }

    } catch (error) {
        console.error('Error fetching match results:', error);
    }
};


</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar for desktop -->
        <SidebarNav />

        <!-- Main content -->
        <main class="flex-1 p-6">
            <h1 class="text-2xl font-bold mb-6">Match Ready</h1>

            <div class="max-w-xl bg-white shadow border border-gray-200 rounded-lg p-6 space-y-4">
                <div class="text-sm text-gray-700">
                    <p class="mb-2"><strong>Challenger:</strong> <span class="text-black">{{ match.challenger }}</span></p>
                    <p class="mb-2"><strong>Opponent:</strong> <span class="text-black">{{ match.opponent }}</span></p>
                    <hr class="mb-2">
                    <p class="mb-2"><strong>Stake:</strong> KES {{ match.stake }}</p>
                    <p class="mb-2"><strong>Tokens:</strong> {{ match.tokens }}</p>
                    <p class="mb-2"><strong>Time Control:</strong> {{ match.timeControl }}</p>
                    <hr class="mb-2">
                    <p class="text-green-600 font-medium mt-2">✔ {{ match.status }}</p>
                </div>
                <p class="text-sm text-orange-600 mt-2">⏱️ You have 2 minutes to start</p>


                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-4">
                    <button
                        @click.prevent="handleGetMatchResults"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded text-sm font-semibold"
                    >
                        Fetch Results
                    </button>
                </div>

            </div>
        </main>


        <!-- Bottom nav: mobile only -->
        <MobileNav />
    </div>
</template>

