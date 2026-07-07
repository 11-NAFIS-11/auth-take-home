<script setup>
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useTrans } from '@/composables/useTrans';
import { Head, useForm } from '@inertiajs/vue3';
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';

const CODE_LENGTH = 4;

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    resendAvailableInSeconds: {
        type: Number,
        default: 0,
    },
    status: {
        type: String,
    },
});

const { t } = useTrans();

const form = useForm({
    code: '',
});

const resendForm = useForm({});

const digits = ref(Array(CODE_LENGTH).fill(''));
const boxes = ref([]);

const secondsLeft = ref(props.resendAvailableInSeconds);
let timer = null;

onMounted(() => {
    timer = setInterval(() => {
        if (secondsLeft.value > 0) {
            secondsLeft.value -= 1;
        }
    }, 1000);

    nextTick(() => boxes.value[0]?.focus());
});

onBeforeUnmount(() => clearInterval(timer));

function formattedCountdown() {
    const minutes = Math.floor(secondsLeft.value / 60).toString().padStart(2, '0');
    const seconds = (secondsLeft.value % 60).toString().padStart(2, '0');

    return `${minutes}:${seconds}`;
}

function onDigitInput(index, event) {
    const value = event.target.value.replace(/[^0-9]/g, '').slice(-1);
    digits.value[index] = value;

    if (value && index < CODE_LENGTH - 1) {
        boxes.value[index + 1]?.focus();
    }

    if (digits.value.every((d) => d !== '')) {
        submit();
    }
}

function onKeydown(index, event) {
    if (event.key === 'Backspace' && !digits.value[index] && index > 0) {
        boxes.value[index - 1]?.focus();
    }
}

function onPaste(event) {
    const pasted = (event.clipboardData?.getData('text') || '').replace(/[^0-9]/g, '').slice(0, CODE_LENGTH);

    if (!pasted) {
        return;
    }

    event.preventDefault();
    digits.value = pasted.padEnd(CODE_LENGTH, ' ').split('').map((c) => c.trim());
    nextTick(() => boxes.value[Math.min(pasted.length, CODE_LENGTH - 1)]?.focus());

    if (pasted.length === CODE_LENGTH) {
        submit();
    }
}

const submit = () => {
    form.code = digits.value.join('');
    form.post(route('two-factor.verify'), {
        onError: () => {
            digits.value = Array(CODE_LENGTH).fill('');
            nextTick(() => boxes.value[0]?.focus());
        },
    });
};

const resend = () => {
    resendForm.post(route('two-factor.resend'), {
        preserveScroll: true,
        onSuccess: () => {
            secondsLeft.value = 60;
            digits.value = Array(CODE_LENGTH).fill('');
            nextTick(() => boxes.value[0]?.focus());
        },
    });
};
</script>

<template>
    <AuthSplitLayout :title="t('Two-factor verification')">
        <Head :title="t('Two-factor verification')" />

        <p class="mb-6 text-center text-sm text-slate-500">
            {{ t('We sent a 4-digit code to') }} <span class="font-medium text-slate-700">{{ email }}</span>
        </p>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" novalidate>
            <div class="flex justify-center gap-3" dir="ltr">
                <input
                    v-for="(digit, index) in digits"
                    :key="index"
                    :ref="(el) => (boxes[index] = el)"
                    :value="digit"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="1"
                    :aria-label="`Digit ${index + 1}`"
                    class="h-14 w-14 rounded-lg border-0 bg-white text-center text-2xl font-semibold text-slate-800 shadow-inner ring-1 ring-inset ring-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @input="onDigitInput(index, $event)"
                    @keydown="onKeydown(index, $event)"
                    @paste="onPaste"
                />
            </div>

            <InputError class="text-center" :message="form.errors.code" />

            <PrimaryButton class="mt-6" :disabled="form.processing" :processing="form.processing">
                {{ form.processing ? t('Verifying…') : t('Verify') }}
            </PrimaryButton>
        </form>

        <div class="mt-6 flex items-center justify-center gap-2 text-center text-sm text-slate-500">
            <span v-if="secondsLeft > 0" class="font-medium text-slate-400" dir="ltr">{{ formattedCountdown() }}</span>
            <button
                type="button"
                class="font-medium text-blue-600 hover:text-blue-800 disabled:cursor-not-allowed disabled:text-slate-400"
                :disabled="secondsLeft > 0 || resendForm.processing"
                @click="resend"
            >
                {{ t('Resend code') }}
            </button>
        </div>

        <InputError class="text-center" :message="resendForm.errors.code" />
    </AuthSplitLayout>
</template>
