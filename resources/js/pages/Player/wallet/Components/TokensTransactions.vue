<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';

const props = defineProps(['transactions', 'tokens']);

const showTokenModal = ref(false);
const tokenForm = ref({
    amount: 10,
});

const buying = ref(false);

const buyTokens = async () => {
    buying.value = true;

    try {
        await axios.post(route('wallet.buy-tokens'), {
            amount: tokenForm.value.amount,
        });

        alert('Tokens purchased successfully.');
        window.location.reload();
    } catch (error: any) {
        console.error(error);
        alert(error.response?.data?.message || 'Token purchase failed.');
    } finally {
        buying.value = false;
        showTokenModal.value = false;
    }
};

</script>

<template>
    <div class="space-y-2 rounded-lg bg-white p-4 shadow">
        <div class="flex items-center justify-between border-b pb-2">
            <div class="text-sm text-gray-600">🎟️ Your Token Balance</div>
            <div class="text-lg font-semibold text-red-600">{{ tokens }}</div>
            <button @click="showTokenModal = true" class="ml-auto rounded bg-blue-600 px-3 py-1 text-sm font-medium text-white">Buy Tokens</button>
        </div>

        <div
            v-for="(tx, index) in transactions"
            :key="index"
            class="flex justify-between rounded border px-3 py-2 text-sm text-gray-700"
        >
            <div>
                {{ tx.tokens }} Tokens
                <span v-if="tx.note">({{ tx.note }})</span>
            </div>
            <div>{{ tx.date }}</div>
        </div>

        <!-- Buy Tokens Modal -->
        <div v-if="showTokenModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="w-full max-w-[320px] space-y-4 rounded-lg bg-white p-6 shadow">
                <h2 class="text-lg font-semibold">Buy Tokens</h2>

                <div>
                    <label class="text-sm font-medium text-gray-700">Number of Tokens</label>
                    <input v-model="tokenForm.amount" type="number" min="1" class="mt-1 w-full rounded border px-3 py-2 text-sm" />
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button class="rounded px-4 py-2 text-sm text-gray-600 hover:text-gray-800" @click="showTokenModal = false">Cancel</button>
                    <button
                        :disabled="buying"
                        class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        @click="buyTokens"
                    >
                        {{ buying ? 'Processing...' : 'Buy Tokens' }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>
