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
    <div class="relative inline-flex items-center">
        <!--
            A native <select> (not a custom-built dropdown) so the control is
            keyboard- and screen-reader-accessible for free. The native arrow
            is hidden (appearance-none) and replaced with our own chevron +
            flag icon (flag of the currently active language). These use plain PHYSICAL left-*/right-* (not logical
            ps-/pe-) deliberately: this component always sits inside a header
            row that's hardcoded to dir="rtl" regardless of locale (see
            AuthSplitLayout.vue), so logical properties here would resolve
            backwards (start = right) — physical properties are what actually
            keeps the chevron fixed on the left and the icon fixed on the
            right in both languages, matching the reference.
        -->
        <select
            :value="locale"
            aria-label="Language"
            class="appearance-none rounded-full border border-gray-200 bg-white/80 py-1.5 pl-7 pr-8 text-center text-sm font-medium text-gray-700 shadow-sm backdrop-blur transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="switchTo($event.target.value)"
        >
            <option value="en">English</option>
            <option value="he">עברית</option>
        </select>

        <svg class="pointer-events-none absolute left-2.5 h-3 w-3 text-gray-400" viewBox="0 0 12 12" fill="none" aria-hidden="true">
            <path d="M2.5 4.5L6 8l3.5-3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>

        <!-- Flag of the currently active language, not the target to switch to. -->
        <svg v-if="locale === 'he'" class="pointer-events-none absolute right-2.5 h-3 w-4 rounded-[1px]" viewBox="0 0 20 14" aria-hidden="true">
            <rect width="20" height="14" fill="#ffffff" />
            <rect y="1.5" width="20" height="2.2" fill="#0038b8" />
            <rect y="10.3" width="20" height="2.2" fill="#0038b8" />
            <path d="M10 5.1 L11.6 7.85 L8.4 7.85 Z" fill="none" stroke="#0038b8" stroke-width="0.6" />
            <path d="M10 10.1 L11.6 7.35 L8.4 7.35 Z" fill="none" stroke="#0038b8" stroke-width="0.6" />
        </svg>
        <svg v-else class="pointer-events-none absolute right-2.5 h-3 w-4 rounded-[1px]" viewBox="0 0 20 14" aria-hidden="true">
            <rect width="20" height="14" fill="#b22234" />
            <rect y="2" width="20" height="2" fill="#ffffff" />
            <rect y="6" width="20" height="2" fill="#ffffff" />
            <rect y="10" width="20" height="2" fill="#ffffff" />
            <rect width="8" height="8" fill="#3c3b6e" />
        </svg>
    </div>
</template>
