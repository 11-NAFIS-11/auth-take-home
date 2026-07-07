<script setup>
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';
import { useTrans } from '@/composables/useTrans';
import backgroundImage from '../../images/auth-background.png';

defineProps({
    title: { type: String, required: true },
});

const { isRtl } = useTrans();
</script>

<template>
    <!--
        This outer split is forced to a fixed left-to-right order (image panel
        always on the left, form panel always on the right) regardless of
        locale — the reference design keeps this macro layout fixed and only
        mirrors the text/content inside the form panel. Without this explicit
        dir="ltr", the ambient RTL direction from <html dir="rtl"> would flip
        the visual order of these two flex children, which is not what the
        reference shows.
    -->
    <div class="relative flex min-h-screen flex-col bg-white lg:flex-row" dir="ltr">
        <div
            class="relative hidden overflow-hidden bg-slate-950 bg-cover bg-center lg:block lg:w-3/5"
            :style="{ backgroundImage: `url(${backgroundImage})` }"
        />

        <div class="relative flex flex-1 flex-col px-6 py-8 sm:px-10" :dir="isRtl ? 'rtl' : 'ltr'">
            <!--
                Pinned to dir="rtl" unconditionally (not tied to the current
                locale) so the switcher always renders on the left and the
                brand name always on the right, in both languages — matching
                every reference screenshot provided. Without this override,
                this row would inherit the parent's locale-driven dir and
                mirror sides when switching to English, which is the same
                class of bug as the outer split-panel fix above.
            -->
            <div class="flex items-center justify-between" dir="rtl">
                <p class="text-lg font-semibold tracking-wide text-slate-900">QuantiTop</p>
                <LocaleSwitcher />
            </div>

            <div class="flex flex-1 flex-col items-center justify-center py-8">
                <div class="w-full max-w-sm">
                    <div class="rounded-2xl border-t-4 border-blue-600 bg-white p-8 shadow-lg shadow-slate-200/50 ring-1 ring-slate-100">
                        <h1 class="mb-6 text-center text-lg font-bold text-blue-700">
                            {{ title }}
                        </h1>

                        <slot />
                    </div>
                </div>
            </div>
        </div>

        <div class="fixed inset-x-0 bottom-0 h-1 bg-gradient-to-r from-blue-700 via-blue-400 to-blue-700" aria-hidden="true" />
    </div>
</template>
