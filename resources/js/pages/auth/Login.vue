<template>
    <div class="min-h-screen flex items-center justify-center bg-[#f8fbff] px-4">
        <div class="w-full max-w-sm space-y-6">
            <h2 class="text-center text-2xl font-bold text-gray-900">Welcome back</h2>

            <div v-if="status" class="text-center text-green-600 text-sm">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <input
                        type="email"
                        v-model="form.email"
                        placeholder="Email"
                        autocomplete="email"
                        class="w-full rounded-lg bg-gray-100 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border border-red-500': form.errors.email }"
                    />
                    <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div>
                    <input
                        type="password"
                        v-model="form.password"
                        placeholder="Password"
                        autocomplete="current-password"
                        class="w-full rounded-lg bg-gray-100 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border border-red-500': form.errors.password }"
                    />
                    <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div class="flex justify-between text-sm text-blue-600">
                    <a v-if="canResetPassword" :href="route('password.request')" class="hover:underline">
                        Forgot password?
                    </a>
                </div>

                <button
                    type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Logging in...</span>
                    <span v-else>Log in</span>
                </button>

                <p class="text-center text-sm text-gray-600">
                    Don’t have an account?
                    <a :href="route('register')" class="text-blue-600 hover:underline">Register</a>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>
