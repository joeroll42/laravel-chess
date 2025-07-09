<script setup lang="ts">
import { ref } from 'vue';
import PageHeading from '@/components/PageHeading.vue';

const isEditing = ref(false); // toggle to true for edit mode

const form = ref({
    username: '@opponentKenya',
    stake: 500,
    platform: 'Lichess',
    tokens: 3,
    timeControl: '5+0 Blitz',
    allowRematch: false,
});
</script>

<template>
    <main class="flex-1 p-6">

        <PageHeading :heading="'Edit Challenge'"/>

        <div class="max-w-xl bg-white shadow rounded-lg p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Opponent Username</label>
                <input
                    v-model="form.username"
                    type="text"
                    class="w-full border rounded px-3 py-2 text-sm focus:ring focus:border-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stake Amount (KES)</label>
                <input
                    v-model.number="form.stake"
                    type="number"
                    class="w-full border rounded px-3 py-2 text-sm focus:ring focus:border-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Platform</label>
                <select
                    v-model="form.platform"
                    class="w-full border rounded px-3 py-2 text-sm focus:ring focus:border-blue-500"
                >
                    <option>Lichess</option>
                    <option>Chess.com</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Time Control</label>
                <select
                    v-model="form.timeControl"
                    class="w-full border rounded px-3 py-2 text-sm focus:ring focus:border-blue-500"
                >
                    <option>5+0 Blitz</option>
                    <option>3+2 Blitz</option>
                    <option>10+5 Rapid</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tokens to Stake</label>
                <input
                    v-model.number="form.tokens"
                    type="number"
                    class="w-full border rounded px-3 py-2 text-sm focus:ring focus:border-blue-500"
                />
            </div>

            <div class="flex items-center space-x-2">
                <input
                    v-model="form.allowRematch"
                    type="checkbox"
                    id="rematch"
                    class="text-blue-600 focus:ring-blue-500"
                />
                <label for="rematch" class="text-sm text-gray-700">Allow rematch if opponent agrees</label>
            </div>

            <div class="flex justify-between mt-4">
                <button
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-semibold"
                >
                    {{ isEditing ? 'Update Challenge' : 'Create Challenge' }}
                </button>

                <button
                    v-if="isEditing"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-semibold"
                >
                    Cancel Challenge
                </button>
            </div>
        </div>
    </main>
</template>
