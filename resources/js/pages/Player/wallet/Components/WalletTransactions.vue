<script setup lang="ts">
const props = defineProps(['depositTransactions'])

</script>

<template>
    <!-- Wallet Transactions Tab -->
    <div class="space-y-4 rounded-lg bg-white p-4 shadow">
        <div class="border-b pb-2 text-sm font-medium text-gray-600">Recent Transactions</div>

        <div
            v-for="(tx, i) in depositTransactions"
            :key="i"
            class="flex items-center justify-between rounded border px-3 py-2 text-sm"
        >
            <div>
                <div class="font-medium">
                    {{ tx.type === 'deposit' ? 'Deposit via TinyPesa' : 'P2P Withdrawal' }}
                </div>
                <div class="text-xs text-gray-500">{{ tx.date }}</div>
            </div>
            <div :class="['font-semibold', tx.type === 'deposit' ? 'text-green-600' : 'text-red-500']">
                {{ tx.type === 'deposit' ? '+' : '-' }} KES {{ tx.amount.toLocaleString() }}
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
