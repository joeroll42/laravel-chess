<script setup lang="ts">
import SidebarNav from '@/components/SidebarNav.vue';
import MobileNav from '@/components/MobileNav.vue';
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar for desktop -->
        <SidebarNav />

        <!-- Main content -->
        <main class="flex-1 p-6 space-y-6">
            <h1 class="text-2xl font-bold">Help and Support</h1>

            <!-- Your content here -->

        </main>

        <!-- Bottom nav: mobile only -->
        <MobileNav />
    </div>
</template>

<style scoped>

</style>
