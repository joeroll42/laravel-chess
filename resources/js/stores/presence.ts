// resources/js/stores/presence.ts
import { ref } from 'vue'

/**
 * A reactive array containing the currently online users.
 * Components can import and watch this.
 */
export interface OnlineUser {
    id: number
    name: string
}

export const onlineUsers = ref<OnlineUser[]>([])
