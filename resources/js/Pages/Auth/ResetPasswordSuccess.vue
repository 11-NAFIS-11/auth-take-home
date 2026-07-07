<script setup>
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useTrans } from '@/composables/useTrans';
import { Head, Link } from '@inertiajs/vue3';

const { t } = useTrans();
</script>

<template>
    <AuthSplitLayout :title="t('Password changed successfully!')">
        <Head :title="t('Password changed successfully!')" />

        <div class="flex flex-col items-center text-center">
            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="h-7 w-7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>

            <p class="mb-6 text-sm text-slate-500">
                {{ t('You can now log in using your new password.') }}
            </p>

            <Link :href="route('login')" class="w-full">
                <PrimaryButton>{{ t('Go to login') }}</PrimaryButton>
            </Link>
        </div>
    </AuthSplitLayout>
</template>
