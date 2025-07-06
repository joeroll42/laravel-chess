<script setup lang="ts">
import { ref } from 'vue';
import SidebarNav from '@/components/SidebarNav.vue';
import MobileNav from '@/components/MobileNav.vue';

interface NotificationItem {
    id: number;
    title: string;
    message: string;
    type: string;
    timestamp: string;
    details?: string;
}

const notifications = ref<NotificationItem[]>([
    {
        id: 1,
        title: '🎉 Match Won',
        message: "You've won a match against @ChessMaster22!",
        type: 'match',
        timestamp: '2m ago',
        details: `Congratulations! You've won your match against @opponent123.\nMatch Type: Blitz (5+0)\nTokens Used: 3\nWinnings: KES 600\nDate: June 30, 2025`
    },
    {
        id: 2,
        title: '📥 Deposit Received',
        message: 'Your TinyPesa deposit of KES 1,000 was successful.',
        type: 'deposit',
        timestamp: '5h ago'
    },
    {
        id: 3,
        title: '📤 P2P Withdrawal Request',
        message: 'Brian O. has sent KES 500. Confirm once received.',
        type: 'withdrawal',
        timestamp: '10h ago'
    },
]);

const selectedNotification = ref<NotificationItem | null>(null);

function openNotification(note: NotificationItem) {
    selectedNotification.value = note;
}

function closeNotification() {
    selectedNotification.value = null;
}
</script>


<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar for desktop -->
        <SidebarNav />

        <!-- Main content -->
        <div class="min-h-screen bg-gray-50 p-6">
            <h1 class="text-2xl font-bold mb-4">Notifications</h1>
            <p class="text-sm text-gray-600 mb-4">Messages and updates about your activity</p>

            <div class="space-y-3">
                <div
                    v-for="note in notifications"
                    :key="note.id"
                    @click="openNotification(note)"
                    class="bg-white hover:bg-gray-100 cursor-pointer border rounded-lg px-4 py-3 flex justify-between items-center"
                >
                    <div class="text-sm text-gray-700">
                        <p class="font-medium">{{ note.title }}</p>
                        <p class="text-gray-500 text-xs">{{ note.message }}</p>
                    </div>
                    <span class="text-xs text-gray-400">{{ note.timestamp }}</span>
                </div>
            </div>

            <!-- Notification Detail Modal -->
            <div
                v-if="selectedNotification"
                class="fixed inset-0 z-50 bg-black/30 flex items-center justify-center"
                @click.self="closeNotification"
            >
                <div class="bg-white max-w-md w-full rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-lg font-semibold">{{ selectedNotification.title }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ selectedNotification.timestamp }}</p>
                        </div>
                        <button @click="closeNotification" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>

                    <p class="text-sm text-gray-700 mt-4 whitespace-pre-line">{{ selectedNotification.details || selectedNotification.message }}</p>

                    <div class="mt-6 text-right">
                        <button
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium"
                            @click="closeNotification"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom nav: mobile only -->
        <MobileNav />
    </div>
</template>


