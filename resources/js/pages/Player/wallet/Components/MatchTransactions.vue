<script setup lang="ts">
const props = defineProps(['matchTransactions'])
</script>

<template>
    <!-- Match Transactions Tab -->
    <div class="space-y-4 rounded-lg bg-white p-4 shadow">
        <div class="border-b pb-2 text-sm font-medium text-gray-600">Recent Match Transactions</div>

        <div v-for="(match, i) in matchTransactions" :key="i"
             class="flex items-center justify-between rounded border px-3 py-2 text-sm">
            <div>
                <div class="font-semibold text-gray-800">{{ match.tag }}</div>
                <div class="text-xs text-gray-500">
                    vs. {{ match.opponent }} • {{ match.result }} • {{ match.tokens }} Tokens • {{ match.date }}
                </div>
            </div>
            <div
                :class="[
                    'font-semibold',

                    match.type === 'credit' ? 'text-green-600' : 'text-red-600'
                ]"
            >
                {{ match.type === 'credit' ? '+' : '-' }} KES {{ match.amount }}
            </div>
        </div>
    </div>
</template>
