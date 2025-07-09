<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';
import { ref } from 'vue';

const prop = defineProps(['heading','notify'])
const unread = ref(prop.notify ?? false);
</script>
<template>
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-2xl font-bold">{{heading}}</h1>
        <div class="flex items-center space-x-4">
            <Link
                :href="route('notifications.list')"
                class="relative flex flex-col items-center text-gray-600 hover:text-blue-600"
            >
                <Bell v-if="unread" class="h-6 w-6 text-red-400" fill="var(--color-red-400)" />
                <Bell v-else class="h-6 w-6 text-gray-600" />
            </Link>
        </div>
    </div>
</template>
