<script setup lang="ts">
import SidebarNav from '@/components/SidebarNav.vue';
import MobileNav from '@/components/MobileNav.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const currentTab = ref('deposit');

const wallet = {
    balance: 5000,
};

const depositForm = ref({
    method: 'TinyPesa (M-Pesa STK Push)',
    amount: 0,
    phone: '',
});

const handleDeposit = () => {
    router.visit(route('wallet.deposit-details', [1]))
}
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar for desktop -->
        <SidebarNav />

        <!-- Main content -->
        <main class="flex-1 p-6 space-y-6">
            <h1 class="text-2xl font-bold">Deposit Details</h1>
            <p class="text-sm text-gray-600">Review and confirm your deposit</p>

            <!-- Dark Banner -->
            <div class="bg-gray-900 rounded-xl text-white p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Current Balance</p>
                    <p class="text-2xl font-bold">KES {{ wallet.balance.toLocaleString() }}</p>
                </div>
            </div>

            <!-- Deposit Form -->
            <div v-if="currentTab === 'deposit'" class="max-w-xl border  rounded-lg p-6 bg-white space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <input
                        type="text"
                        v-model="depositForm.method"
                        readonly
                        class="w-full rounded border px-3 py-2 bg-gray-100 text-sm"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount to Deposit</label>
                    <input
                        type="text"
                        v-model="depositForm.amount"
                        class="w-full rounded border px-3 py-2 text-sm focus:ring focus:border-blue-500"
                        placeholder="KES 1000"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input
                        type="tel"
                        v-model="depositForm.phone"
                        class="w-full rounded border px-3 py-2 text-sm focus:ring focus:border-blue-500"
                        placeholder="2547XX XXX XXX"
                    />
                </div>

                <button @click.prevent="handleDeposit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded text-sm font-semibold">
                    Deposit Now
                </button>
            </div>
        </main>

        <!-- Bottom nav: mobile only -->
        <MobileNav />
    </div>
</template>
