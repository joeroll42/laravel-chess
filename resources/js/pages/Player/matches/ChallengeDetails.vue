<script setup lang="ts">
import SidebarNav from '@/components/SidebarNav.vue';
import MobileNav from '@/components/MobileNav.vue';
import { ref, computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps(['challengeDetails']);

const challenge = ref({
    id: props.challengeDetails.id,
    username: props.challengeDetails.user.name,
    opponent: props.challengeDetails.opponent ?? null,
    rank: 1220,
    tokens: props.challengeDetails.tokens,
    stake: props.challengeDetails.stake,
    time_control: props.challengeDetails.time_control,
    online: false,
    notes: 'This is a test challenge',
});

const currentUserId = usePage().props.auth.user.id;

const isOwner = computed(() => currentUserId === props.challengeDetails.user_id);


const hasOpponent = computed(() => !!challenge.value.opponent);

const form = useForm({
    challenge_id: challenge.value.id,
});

const handleContend = () => {
    form.post(route('challenges.contend'), {
        onError: (errors) => {
            const errorMessages = Object.values(errors).join('\n');
            alert(errorMessages);
        },
    });
};

</script>


<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar for desktop -->
        <SidebarNav />

        <!-- Main content -->
        <main class="flex-1 p-6 flexs">
            <div class="w-full max-w-md space-y-4">
                <h1 class="text-xl font-bold">Challenge Details</h1>

                <div class="bg-white rounded-lg shadow p-6 space-y-5">
                    <div class="flex items-center justify-between border-b pb-3">
                        <div>
                            <p class="font-semibold text-gray-800">{{ challenge.username }}</p>
                            <p class="text-sm text-gray-500">Rank: {{ challenge.rank }}</p>
                        </div>

                        <div
                            v-if="challenge.online"
                            class="text-green-600 text-sm font-medium flex items-center gap-1"
                        >
                            <span>Online now</span>
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        </div>

                        <div
                            v-else
                            class="text-gray-400 text-sm font-medium flex items-center gap-1"
                        >
                            <span>Offline</span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                        </div>
                    </div>


                    <div class="space-y-3 text-sm text-gray-700">
                        <div class="flex justify-between items-center">
                            <span>🪙 Tokens Required</span>
                            <span class="font-semibold">{{ challenge.tokens }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>💰 Stake Amount</span>
                            <span class="font-semibold">KES {{ challenge.stake }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>⏱️ Time Control</span>
                            <span class="font-semibold">{{ challenge.time_control }}</span>
                        </div>
                        <div class="flex justify-between items-start">
                            <span>📝 Note</span>
                            <span class="text-right">{{ challenge.notes }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 mt-4">
                        <template v-if="hasOpponent">
                            <div class="bg-gray-100 rounded p-3 text-sm text-gray-700">
                                <p><span class="font-semibold">Opponent:</span> {{ challenge.opponent.name }}</p>
                                <p><span class="font-semibold">Email:</span> {{ challenge.opponent.email }}</p>
                            </div>
                            <Link
                                as="button"
                                :href="route('matches.ready', { id: challenge.id })"
                                class="bg-green-600 hover:bg-green-700 text-white py-2 rounded font-semibold text-sm"
                            >
                                Ready Now
                            </Link>
                        </template>

                        <template v-else-if="!isOwner">
                            <button
                                class="bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold text-sm"
                                @click.prevent="handleContend"
                            >
                                Challenge Now
                            </button>
                        </template>

                        <Link
                            as="button"
                            :href="route('matches.active')"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 rounded font-semibold text-sm"
                        >
                            Back
                        </Link>
                    </div>

                </div>
            </div>
        </main>

        <!-- Bottom nav: mobile only -->
        <MobileNav />
    </div>
</template>
