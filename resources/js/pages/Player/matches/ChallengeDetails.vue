<script setup lang="ts">
import SidebarNav from '@/components/SidebarNav.vue';
import MobileNav from '@/components/MobileNav.vue';
import { ref, computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps(['challengeDetails']);
const page = usePage();
const currentUserId = page.props.auth.user.id;

const challenge = ref({
    ...props.challengeDetails,
    rank: 1220,
    online: false,
    notes: 'This is a test challenge',
});

const isOwner = computed(() => currentUserId === challenge.value.user_id);
const hasOpponent = computed(() => !!challenge.value.opponent);

const myInfo = computed(() => isOwner.value ? challenge.value.user : challenge.value.opponent);
const otherInfo = computed(() => isOwner.value ? challenge.value.opponent : challenge.value.user);

const isMatchComplete = computed(() =>
    ['won', 'loss', 'draw'].includes(challenge.value.challenge_status)
);

const form = useForm({
    challenge_id: challenge.value.id,
});

const handleContend = () => {
    form.post(route('challenges.contend'), {
        onError: (errors) => {
            alert(Object.values(errors).join('\n'));
        },
    });
};
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <SidebarNav />

        <!-- Main content -->
        <main class="flex-1 p-6">
            <div class="w-full max-w-md space-y-4">
                <h1 class="text-xl font-bold">Challenge Details</h1>

                <div class="bg-white rounded-lg shadow p-6 space-y-5">
                    <!-- My Info Section -->
                    <div v-if="myInfo" class="flex items-center justify-between border-b pb-3">
                        <div>
                            <p class="font-semibold text-gray-800">{{ myInfo.name }}</p>
                            <p class="text-sm text-gray-500">Rank: {{ challenge.rank }}</p>
                        </div>
                        <div
                            :class="challenge.online ? 'text-green-600' : 'text-gray-400'"
                            class="text-sm font-medium flex items-center gap-1"
                        >
                            <span>{{ challenge.online ? 'Online now' : 'Offline' }}</span>
                            <span :class="challenge.online ? 'bg-green-500' : 'bg-gray-400'" class="w-2 h-2 rounded-full"></span>
                        </div>
                    </div>
                    <div v-else class="flex items-center justify-between border-b pb-3">
                        <div>
                            <p class="font-semibold text-gray-800">{{ challenge.user.name }}</p>
                            <p class="text-sm text-gray-500">Rank: {{ challenge.rank }}</p>
                        </div>
                        <div
                            :class="challenge.online ? 'text-green-600' : 'text-gray-400'"
                            class="text-sm font-medium flex items-center gap-1"
                        >
                            <span>{{ challenge.online ? 'Online now' : 'Offline' }}</span>
                            <span :class="challenge.online ? 'bg-green-500' : 'bg-gray-400'" class="w-2 h-2 rounded-full"></span>
                        </div>
                    </div>

                    <!-- Match Details -->
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

                    <!-- Other User Info -->
                    <div v-if="hasOpponent" class="bg-gray-100 rounded p-3 text-sm text-gray-700">
                        <p><span class="font-semibold">Opponent:</span> {{ otherInfo.name }}</p>
                        <p><span class="font-semibold">Email:</span> {{ otherInfo.email }}</p>
                    </div>

                    <!-- Action Section -->
                    <div class="flex flex-col gap-3 mt-4">
                        <!-- Conditional Buttons -->
                        <template v-if="hasOpponent">
                            <Link
                                v-if="isMatchComplete"
                                as="button"
                                :href="route('matches.results', { id: challenge.id })"
                                class="bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold text-sm"
                            >
                                View Results
                            </Link>
                            <Link
                                v-else
                                as="button"
                                :href="route('matches.ready', { id: challenge.id })"
                                class="bg-green-600 hover:bg-green-700 text-white py-2 rounded font-semibold text-sm"
                            >
                                Ready Now
                            </Link>
                        </template>

                        <!-- Contend Button -->
                        <button
                            v-else-if="!isOwner"
                            @click.prevent="handleContend"
                            class="bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold text-sm"
                        >
                            Challenge Now
                        </button>

                        <!-- Back Button -->
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

        <!-- Mobile nav -->
        <MobileNav />
    </div>
</template>
