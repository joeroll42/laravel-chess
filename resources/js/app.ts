import '../css/app.css';

import { createInertiaApp, usePage } from '@inertiajs/vue3';
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

// Laravel Echo + Pusher config (but don’t subscribe yet)
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
    withCredentials: true,
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
    },
});

createInertiaApp({
    title: title => (title ? `${title} - ${import.meta.env.VITE_APP_NAME}` : import.meta.env.VITE_APP_NAME),
    resolve: name => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);

        // === REAL-TIME PRESENCE: only if authenticated ===
        const page = usePage<{ auth: { user: { id: number } | null } }>();
        if (page.props.auth.user) {
            const onlineUsers = ref<any[]>([]);
            const offlineTimeouts = new Map<number, ReturnType<typeof setTimeout>>();
            let currentUserId: number | null = null;

            // Fetch current user ID
            axios.get(route('active-user'))
                .then(res => { currentUserId = res.data.id; })
                .catch(() => { /* ignore */ });

            // Subscribe to join & leave only after we have user ID
            // (you could also guard on page.props.auth.user.id)
            window.Echo.join('online-users')
                .here((users: any[]) => {
                    onlineUsers.value = users;
                })
                .joining((user: any) => {
                    if (!onlineUsers.value.some(u => u.id === user.id)) {
                        onlineUsers.value.push(user);
                    }
                    if (offlineTimeouts.has(user.id)) {
                        clearTimeout(offlineTimeouts.get(user.id)!);
                        offlineTimeouts.delete(user.id);
                    }
                    if (user.id === currentUserId) {
                        axios.post('/auth/users/online').catch(console.error);
                    }
                })
                .leaving((user: any) => {
                    const timeout = setTimeout(() => {
                        onlineUsers.value = onlineUsers.value.filter(u => u.id !== user.id);
                        if (user.id === currentUserId) {
                            axios.post('/auth/users/offline').catch(console.error);
                        }
                        offlineTimeouts.delete(user.id);
                    }, 2000);
                    offlineTimeouts.set(user.id, timeout);
                });
        }

        // Initialize theme after everything
        initializeTheme();
    },
    progress: {
        color: '#4B5563',
    },
});
