<script setup lang="ts">
import MobileNav from '@/components/MobileNav.vue';
import SidebarNav from '@/components/SidebarNav.vue';
import { ref } from 'vue';

interface NotificationItem {
    id: number;
    title: string;
    message: string;
    type: string;
    timestamp: string;
    details?: string;
}

// type: 'match',
// type: 'deposit',
// type: 'withdrawal',

const notifications = ref<NotificationItem[]>([
    {
        id: 1,
        title: '🎉 Match Won',
        message: "You've won a match against @ChessMaster22!",
        type: 'match',
        timestamp: '2m ago',
        details: `Congratulations! You've won your match against @opponent123.\nMatch Type: Blitz (5+0)\nTokens Used: 3\nWinnings: KES 600\nDate: June 30, 2025`,
    },
    {
        id: 2,
        title: '📥 Deposit Received',
        message: 'Your TinyPesa deposit of KES 1,000 was successful.',
        type: 'deposit',
        timestamp: '5h ago',
    },
    {
        id: 3,
        title: '📤 P2P Withdrawal Request',
        message: 'Brian O. has sent KES 500. Confirm once received.',
        type: 'withdrawal',
        timestamp: '10h ago',
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
            <h1 class="mb-4 text-2xl font-bold">Notifications</h1>
            <p class="mb-4 text-sm text-gray-600">Messages and updates about your activity</p>

            <div class="space-y-3">
                <div
                    v-for="note in notifications"
                    :key="note.id"
                    @click="openNotification(note)"
                    class="flex cursor-pointer items-center justify-between rounded-lg border bg-white px-4 py-3 hover:bg-gray-100"
                >
                    <div class="text-sm text-gray-700">
                        <p class="font-medium">{{ note.title }}</p>
                        <p class="text-xs text-gray-500">{{ note.message }}</p>
                    </div>
                    <span class="text-xs text-gray-400">{{ note.timestamp }}</span>
                </div>
            </div>

            <!-- Notification Detail Modal -->
            <div v-if="selectedNotification" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="closeNotification">
                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-lg font-semibold">{{ selectedNotification.title }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ selectedNotification.timestamp }}</p>
                        </div>
                        <button @click="closeNotification" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>

                    <p class="mt-4 text-sm whitespace-pre-line text-gray-700">
                        {{ selectedNotification.details || selectedNotification.message }}
                    </p>

                    <div class="mt-6 text-right">
                        <button class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="closeNotification">
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
