<script setup>
import { router } from '@inertiajs/vue3';
import { useTrans } from '@/composables/useTrans';

const { locale } = useTrans();

function switchTo(target) {
    if (target === locale.value) {
        return;
    }

    // A full reload (rather than an Inertia SPA transition) guarantees the
    // <html dir/lang> attributes, which are only set server-side in
    // app.blade.php, are always correct for the new locale.
    router.post(
        `/locale/${target}`,
        {},
        { onSuccess: () => window.location.reload() },
    );
}
</script>

<template>
    <div
        class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white/80 p-1 text-sm font-medium shadow-sm backdrop-blur"
        role="group"
        aria-label="Language"
    >
        <button
            type="button"
            class="rounded-full px-3 py-1 transition"
            :class="locale === 'en' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-800'"
            :aria-pressed="locale === 'en'"
            @click="switchTo('en')"
        >
            English
        </button>
        <button
            type="button"
            class="rounded-full px-3 py-1 transition"
            :class="locale === 'he' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-800'"
            :aria-pressed="locale === 'he'"
            @click="switchTo('he')"
        >
            עברית
        </button>
    </div>
</template>
