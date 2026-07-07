<script setup>
import { onMounted, ref } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    type: { type: String, default: 'text' },
});

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="relative">
        <span
            v-if="type === 'email' || type === 'password'"
            class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400"
        >
            <svg
                v-if="type === 'email'"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                class="h-4 w-4"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M2.25 6.75c0-.621.504-1.125 1.125-1.125h17.25c.621 0 1.125.504 1.125 1.125v10.5c0 .621-.504 1.125-1.125 1.125H3.375A1.125 1.125 0 012.25 17.25V6.75z"
                />
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9 6 9-6" />
            </svg>
            <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                class="h-4 w-4"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M16.5 10.5V7.5a4.5 4.5 0 10-9 0v3m-.75 0h10.5A1.5 1.5 0 0118.75 12v6a1.5 1.5 0 01-1.5 1.5H6.75a1.5 1.5 0 01-1.5-1.5v-6a1.5 1.5 0 011.5-1.5z"
                />
            </svg>
        </span>

        <input
            v-bind="$attrs"
            :type="type"
            v-model="model"
            ref="input"
            class="block w-full rounded-lg border-0 bg-white py-2.5 text-start text-sm text-slate-800 shadow-inner ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="type === 'email' || type === 'password' ? 'ps-10 pe-4' : 'px-4'"
        />
    </div>
</template>
