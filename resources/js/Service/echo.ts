import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { onlineUsers, type OnlineUser } from '@/stores/presence.ts'

// Set Pusher globally
window.Pusher = Pusher;

// Laravel Echo + Pusher config (but don’t subscribe yet)
export const echo = new Echo({
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

export function initPresence() {
    echo.join('presence-online')
        .here((users: OnlineUser[]) => {
            onlineUsers.value = users
        })
        .joining((user: OnlineUser) => {
            onlineUsers.value.push(user)
        })
        .leaving((user: OnlineUser) => {
            onlineUsers.value = onlineUsers.value.filter(u => u.id !== user.id)
        })

    // leave only on real unload
    window.addEventListener('beforeunload', () => {
        echo.leave('presence-online')
    })
}
