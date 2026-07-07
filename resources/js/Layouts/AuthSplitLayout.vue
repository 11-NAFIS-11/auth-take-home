<script setup>
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';

defineProps({
    title: { type: String, required: true },
});
</script>

<template>
    <div class="relative flex min-h-screen flex-col bg-white lg:flex-row">
        <div class="relative hidden overflow-hidden bg-slate-950 lg:block lg:w-3/5">
            <div class="cityscape absolute inset-0" aria-hidden="true" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent" aria-hidden="true" />
            <div class="glow absolute bottom-24 start-16 h-24 w-24 rounded-full" aria-hidden="true" />
        </div>

        <div class="relative flex flex-1 flex-col px-6 py-8 sm:px-10">
            <div class="flex items-center justify-between">
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

<style scoped>
.cityscape {
    background-image:
        repeating-linear-gradient(
            90deg,
            rgba(148, 163, 184, 0.15) 0px,
            rgba(148, 163, 184, 0.15) 1px,
            transparent 1px,
            transparent 34px
        ),
        repeating-linear-gradient(
            0deg,
            rgba(148, 163, 184, 0.12) 0px,
            rgba(148, 163, 184, 0.12) 1px,
            transparent 1px,
            transparent 28px
        ),
        linear-gradient(160deg, #1e293b 0%, #0f172a 55%, #020617 100%);
}

.cityscape::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(90deg, transparent 0 8%, #0b1220 8% 22%, transparent 22% 30%, #0b1220 30% 46%, transparent 46% 58%, #0b1220 58% 74%, transparent 74% 84%, #0b1220 84% 100%);
    clip-path: polygon(0 100%, 5% 40%, 12% 55%, 20% 20%, 28% 48%, 36% 30%, 45% 60%, 54% 15%, 63% 45%, 72% 25%, 81% 52%, 90% 10%, 100% 38%, 100% 100%);
    opacity: 0.9;
}

.glow {
    background: radial-gradient(circle, rgba(191, 219, 254, 0.9) 0%, rgba(191, 219, 254, 0.15) 60%, transparent 100%);
    filter: blur(2px);
}
</style>
