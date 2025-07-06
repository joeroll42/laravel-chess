<script setup lang="ts">
import SidebarNav from '@/components/SidebarNav.vue';
import MobileNav from '@/components/MobileNav.vue';
import { ref } from 'vue';

defineProps(['user']);

const onlineUsers = ref<any[]>([]);

window.Echo.join('online-users')
    .here((users: any[]) => {
        console.log('Current online users:', users);
        onlineUsers.value = users;
    })
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar for desktop -->
        <SidebarNav />

        <!-- Main content -->
        <main class="flex-1 space-y-6 p-2">
            <h1 class="text-2xl font-bold">Active Users</h1>
            <p>Welcome, {{ user.name }}</p>
            <div>
                <ul>
                    <li class="mb-1 w-[300px] p-4 shadow" v-for="item in onlineUsers">
                        <div class="flex">
                            <h4 class="w-[150px]">Name:</h4>
                            <p>{{ item.name }}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </main>

        <!-- Bottom nav: mobile only -->
        <MobileNav />
    </div>
</template>
