<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SettingsLayout from '@/layouts/settings/Layout.vue';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    chess_com_link:user.chess_com_link,
    lichess_link:user.lichess_link
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true
    });
};

const logout = () => {
    form.post(route('logout'), {
        preserveScroll: true
    });
};
</script>

<template>
    <SettingsLayout>
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Profile information" description="Update your name and email address" />

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name"
                           placeholder="Full name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="Email address"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="name">Chess.com Username</Label>
                    <Input id="chess_com_link" class="mt-1 block w-full" v-model="form.chess_com_link" autocomplete="chess_com_link"
                           placeholder="Chess.com Username" />
                    <InputError class="mt-2" :message="form.errors.chess_com_link" />
                </div>

                <div class="grid gap-2">
                    <Label for="name">Lichess Username</Label>
                    <Input id="lichess_link" class="mt-1 block w-full" v-model="form.lichess_link" autocomplete="lichess_link"
                           placeholder="Lichess Username" />
                    <InputError class="mt-2" :message="form.errors.lichess_link" />
                </div>

                <div v-if="mustVerifyEmail && !user.email_verified_at">
                    <p class="-mt-4 text-sm text-muted-foreground">
                        Your email address is unverified.
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        >
                            Click here to resend the verification email.
                        </Link>
                    </p>

                    <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                        A new verification link has been sent to your email address.
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing">Save</Button>

                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                    </Transition>
                </div>
                <form @submit.prevent="logout" class="flex items-center gap-4">
                    <Button type="submit" class="btn">Log out</Button>
                </form>
            </form>
        </div>
    </SettingsLayout>
</template>
