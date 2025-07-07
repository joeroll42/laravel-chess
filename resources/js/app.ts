import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { DefineComponent, ref } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios';

// Set Pusher globally
window.Pusher = Pusher;

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

// Laravel Echo + Pusher config
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: true,
    encrypted: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    withCredentials:true,
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
    },
});

const onlineUsers = ref<any[]>([]);
const offlineTimeouts = new Map(); // key = user.id

let currentUserId = null;

try {
    const response = await axios.get(route('active-user'));
    currentUserId = response.data.id;
} catch (error) {
    // Silent fail: log or ignore
}


window.Echo.join('online-users')
    .here((users: any[]) => {
        onlineUsers.value = users;
    })
    .joining((user: any) => {
        console.log(`${user.name} joining`);

        // Add to online list
        if (!onlineUsers.value.some(u => u.id === user.id)) {
            onlineUsers.value.push(user);
        }

        // Cancel any offline timeout (if any)
        if (offlineTimeouts.has(user.id)) {
            clearTimeout(offlineTimeouts.get(user.id));
            offlineTimeouts.delete(user.id);
        }

        // ✅ Only notify server if it's *this* user's session
        if (user.id === currentUserId) {
            axios.post('/users/online').catch(console.error);
        }
    })
    .leaving((user: any) => {
        console.log(`${user.name} leaving`);

        // Delay leaving logic to account for unexpected disconnects
        const timeout = setTimeout(() => {
            onlineUsers.value = onlineUsers.value.filter(u => u.id !== user.id);

            if (user.id === currentUserId) {
                axios.post('/users/offline').catch(console.error);
            }

            offlineTimeouts.delete(user.id);
        }, 2000);

        offlineTimeouts.set(user.id, timeout);
    });


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();



