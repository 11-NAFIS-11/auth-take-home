<script setup>
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';
import { useTrans } from '@/composables/useTrans';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const { t } = useTrans();
</script>

<template>
    <Head :title="t('Hello World')" />

    <div class="flex min-h-screen flex-col bg-slate-50">
        <header class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4">
            <p class="text-lg font-semibold tracking-wide text-slate-900">QuantiTop</p>
            <LocaleSwitcher />
        </header>

        <main class="flex flex-1 flex-col items-center justify-center px-6 text-center">
            <h1 class="text-4xl font-bold text-slate-900">Hello World</h1>
            <p class="mt-3 text-slate-500">
                {{ t("You're signed in as") }} <span class="font-medium text-slate-700">{{ user.email }}</span>
            </p>

            <Link
                as="button"
                method="post"
                :href="route('logout')"
                class="mt-8 inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
            >
                {{ t('Log out') }}
            </Link>
        </main>
    </div>
</template>
