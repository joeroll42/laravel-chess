<script setup lang="ts">
import SidebarNav from '@/components/SidebarNav.vue';
import MobileNav from '@/components/MobileNav.vue';
import PageHeading from '@/components/PageHeading.vue';
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar for desktop -->
        <SidebarNav />

        <!-- Main content -->
        <main class="flex-1 p-6 space-y-6">
            <PageHeading :heading="'Edit Profile'"/>

        </main>

        <!-- Bottom nav: mobile only -->
        <MobileNav />
    </div>
</template>

<style scoped>

</style>
